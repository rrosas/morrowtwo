<?php

/*////////////////////////////////////////////////////////////////////////////////
    MorrowTwo - a PHP-Framework for efficient Web-Development
    Copyright (C) 2009  Christoph Erdmann, R.David Cummins

    This file is part of MorrowTwo <http://code.google.com/p/morrowtwo/>

    MorrowTwo is free software:  you can redistribute it and/or modify
    it under the terms of the GNU Lesser General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Lesser General Public License for more details.

    You should have received a copy of the GNU Lesser General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
////////////////////////////////////////////////////////////////////////////////*/


namespace Morrow\Core;

use Morrow\Factory;
use Morrow\Factoryproxy;

/**
 * The main class which defines the cycle of a request.
 */
class Frontcontroller {
	/**
	 * Will be set by the run() method as default error handler.
	 * It throws an exception to normalize the handling of errors and exceptions.
	 *
	 * @param	Parameters are as defined by set_error_handler() in PHP.
	 * @return	null
	 */
	public function errorHandler($errno, $errstr, $errfile, $errline) {
		// get actual error_reporting
		$error_reporting = error_reporting();

		// request for @ error-control operator
		if ($error_reporting == 0) return;

		// return if error should not get processed
		if (($errno & $error_reporting) === 0) return;

		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	/**
	 * Will be set by the run() method as global exception handler.
	 * @param	object	$exception	The thrown exception.
	 * @return null
	 */
	public function exceptionHandler($exception) {
		try {
			// load errorhandler
			$debug = Factory::load('Debug');
			$debug->errorhandler($exception);
		} catch (\Exception $e) {
			echo "<pre>$exception</pre>\n\n";

			// useful if the \Exception handler itself contains errors
			echo "<pre>The Debug class threw an exception:\n$e</pre>";
		}
	}
	
	/**
	 * A PSR-0 compatible autoloader which tries to loads project specific models.
	 * @param	string	$namespace	A fully defined class name incl. namespace path
	 * @return	null
	 */
	/*
	protected function _autoload($namespace) {
		if (!defined('PROJECT_PATH')) return;

		// explode namespace to single nodes
		$namespace_nodes = explode('\\', $namespace);
		if (!isset($namespace_nodes[1]) || $namespace_nodes[1] != 'Models') return;
		
		// Each _ character in the CLASS NAME is converted to a DIRECTORY_SEPARATOR. The _ character has no special meaning in the namespace.
		$classname = DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, array_pop($namespace_nodes)) . '.php';

		include (PROJECT_PATH . '_models' . $classname);
	}
	*/

	/**
	 * Loads config files in an array.
	 * First it searches for a file _default.php then it tries to load the config for the current HOST and then for the Server IP address.
	 * @param	string	$directory	The directory path where the config files are.
	 * @return	array	An array with the config.
	 */
	protected function _loadConfigVars($directory) {
		// load main config
		$config = include ($directory.'_default.php');

		// overwrite with server specific config
		$file1 = $directory.$_SERVER['HTTP_HOST'].'.php';
		$file2 = $directory.(isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR']).'.php'; // On Windows IIS 7 you must use $_SERVER['LOCAL_ADDR'] rather than $_SERVER['SERVER_ADDR'] to get the server's IP address.
		if (is_file($file1)) $config = array_merge($config, include($file1));
		elseif (is_file($file2)) $config = array_merge($config, include($file2));

		return $config;
	}

	/**
	 * This function contains the main application flow
	 */
	public function __construct() {
		/* global settings
		********************************************************************************************/
		// compress the output
		if (!ob_start("ob_gzhandler")) ob_start();

		// include E_STRICT in error_reporting
		error_reporting(E_ALL | E_STRICT);

		// register autoloader for project specific models
		//spl_autoload_register(array($this, '_autoload'));

		/* declare errorhandler (needs config class)
		********************************************************************************************/
		set_error_handler(array($this, 'errorHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));

		/* register main config in the config class
		********************************************************************************************/
		$this->config = Factory::load('Config'); // config class for config vars

		// load vars
		$config = $this->_loadConfigVars(APP_PATH . 'configs/');

		// register config in class
		foreach ($config as $key => $array) {
			$this->config->set($key, $array);
		}
			
		$config = $this->config->get();

		/* set timezone 
		********************************************************************************************/
		if (!date_default_timezone_set($config['locale']['timezone'])) {
			throw new \Exception(__METHOD__.'<br>date_default_timezone_set() failed.');
		}

		/* load input class
		********************************************************************************************/
		$this->input	= Factory::load('Input', $_GET ,$_POST, $_FILES); // input class for all user input

		/* load page class and set nodes
		********************************************************************************************/
		if (!isset($_SERVER['PATH_INFO'])) $_SERVER['PATH_INFO'] = '';
		$url		= trim($_SERVER['PATH_INFO'], '/');
		$nodes		= explode('/', $url);
		$this->page	= Factory::load('Page'); // config class for page vars
		$this->page->set('nodes', $nodes);

		/* load languageClass and define alias
		********************************************************************************************/
		$lang['possible']		= $config['languages'];
		$lang['language_path']	= APP_PATH .'languages/';
		$lang['search_paths']	= array(
			VENDOR_PATH			.'Morrow/*.php',
			VENDOR_USER_PATH	.'*.php',
			APP_PATH			.'templates/*',
			APP_PATH			.'*.php'
		);
		$this->language = Factory::load('Language', $lang);

		// language via path
		if (isset($nodes[0]) && $this->language->isValid($nodes[0])) {
			$input_lang_nodes = array_shift($nodes);
			$this->page->set('nodes', $nodes);
		}
		
		// language via input
		$lang['actual'] = $this->input->get('language');

		if ($lang['actual'] === null && isset($input_lang_nodes)) {
			$lang['actual'] = $input_lang_nodes;
		}

		if ($lang['actual'] !== null) $this->language->set($lang['actual']);

		/* url routing
		********************************************************************************************/
		$routes	= $config['routing'];
		$url	= implode('/', $this->page->get('nodes'));

		// iterate all rules
		foreach ($routes as $rule => $new_url) {
			$rule		= trim($rule, '/');
			$new_url	= trim($new_url, '/');

			// rebuild route to a preg pattern
			$preg_route	= preg_replace('=\\:[a-z0-9_]+=i', '([^/]+)', $rule); // match parameters
			$preg_route	= preg_replace('=/\\*[a-z0-9_]+=i', '(.*)', $preg_route); // match asterisk

			$pattern	= '=^'.$preg_route.'$=i';

			// does this rule match?
			preg_match($pattern, $url, $hits);

			// no hits? then goto next rule
			if (count($hits) == 0) continue;
						
			// skip first entry because it contains the complete string and not only the search result
			array_shift($hits);
			
			// if there is an asterisk the last entry is for the params
			if (strpos($rule, '*') !== false) {
				$blind_parameters = explode('/', trim($hits[0], '/'));
				
				// get asterisk param names
				preg_match_all('=\*([a-z0-9_-]+)=i', $rule, $param_asterisk_keys);
				$blind_key = $param_asterisk_keys[1][0];
				
				$this->input->set($blind_key, $blind_parameters);
			}
			
			// get param names
			if (strpos($rule, ':') !== false) {
				preg_match_all('=:([a-z0-9_-]+)=i', $rule, $param_keys);
				$params = array_combine($param_keys[1], $hits);
				
				// replace all known params in new_url
				foreach ($params as $key => $param) {
					$new_url = str_replace(":$key", $params[$key], $new_url);
				}

				// register new params in the input class
				foreach ($params as $key => $param) {
					$this->input->set($key, $param);
				}
			}

			$url = $new_url;
		}

		// set nodes in page class
		$nodes = explode('/', $url);
		$nodes = array_map('strtolower', $nodes);
		$this->page->set('nodes', $nodes);

		/* prepare some internal variables
		********************************************************************************************/
		$alias					= implode('_', $nodes);
		$global_controller_file	= APP_PATH .'_default.php';
		$page_controller_file	= APP_PATH . $alias .'.php';
		$path					= implode('/', $nodes).'/';
		$query					= $this->input->getGet();
		$fullpath				= $path . (count($query) > 0 ? '?' . http_build_query($query, '', '&') : '');
		
		/* load classes we need anyway
		********************************************************************************************/
		$this->view	= Factory::load('View');
		$this->url	= Factory::load('Url', $this->language->get(), $lang['possible'], $fullpath);
		
		/* prepare classes so the user has less to pass
		********************************************************************************************/
		Factory::prepare('Cache', APP_PATH .'temp/codecache/');
		Factory::prepare('Debug', $config['debug']);
		Factory::prepare('Image', 'temp/thumbs/');
		Factory::prepare('Log', $config['log']);
		Factory::prepare('Navigation', Factory::load('Language')->getTree(), $alias);
		Factory::prepare('Pagesession', 'page.' . $alias);
		Factory::prepare('Session', $config['session']);
		Factory::prepare('Security', new Factoryproxy('Session'), $this->view, $this->input, $this->url);

		/* define page params
		********************************************************************************************/
		// We have to strip x nodes from the end of the base href
		// Depends on the htaccess entry point
		$basehref_depth = (int)$this->input->get('morrow_basehref_depth');
		$base_href = preg_replace('|([^/]+/){'. $basehref_depth .'}$|', '', $this->url->getBaseHref());

		$this->page->set('base_href', $base_href);
		$this->page->set('alias', $alias);
		$this->page->set('controller', $page_controller_file);
		$this->page->set('path', $path);
		$this->page->set('fullpath', $fullpath);

		/* load controller and render page
		********************************************************************************************/
		// include global controller class
		include($global_controller_file);

		// include page controller class
		if (is_file($page_controller_file)) {
			include($page_controller_file);
			$controller = new \App\PageController();
			if (method_exists($controller, 'setup')) $controller->setup();
			$controller->run();
			if (method_exists($controller, 'teardown')) $controller->teardown();
		} else {
			$controller = new \App\DefaultController();
			if (method_exists($controller, 'setup')) $controller->setup();
			if (method_exists($controller, 'teardown')) $controller->teardown();
		}

		// assign the content to the view
		$this->view->setContent('page', $this->page->get());

		$view = $this->view->get();
		$headers = $view['headers'];
		$handle = $view['content'];
		$meta_data = stream_get_meta_data($handle);
		
		// output headers
		foreach ($headers as $h) header($h);
		
		rewind($handle);
		fpassthru($handle);
		fclose($handle);

		ob_end_flush();
		
		// if we have zipped data bigger than 1 MB
		if ($meta_data['wrapper_type'] == 'plainfile') unlink($meta_data['uri']);
	}
}
