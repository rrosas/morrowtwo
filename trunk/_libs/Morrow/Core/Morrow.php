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

/**
 * The main class which defines the cycle of a request.
 */
class Morrow {
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
			// useful if the \Exception handler itself contains errors
			echo "<pre>$e</pre>";
		}
	}
	
	/**
	 * A PSR-0 compatible autoloader which maps a namespace to a file structure.
	 * @param	string	$namespace	A fully defined class name incl. namespace path
	 * @return	null
	 */
	protected function _autoload($namespace) {
		// explode namespace to single nodes
		$namespace_nodes = explode('\\', $namespace);
		
		// Each _ character in the CLASS NAME is converted to a DIRECTORY_SEPARATOR. The _ character has no special meaning in the namespace.
		$classname = DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, array_pop($namespace_nodes)) . '.php';

		// generate relative class path
		$classpath = '_libs/' . implode(DIRECTORY_SEPARATOR, $namespace_nodes) . $classname;
		
		// First we try the external libs. That way the user can replace all core libs if he wants to
		if (defined('PROJECT_PATH')) {
			$try[] = PROJECT_PATH . $classpath;
			
			if (isset($namespace_nodes[1]) && $namespace_nodes[1] == 'Models') {
				$try[] = PROJECT_PATH . '_models' . $classname;
			}
		}
		
		// first try to find the user replaced or added class
		$try[] = FW_PATH . $classpath;
		
		$found = false;
		foreach ($try as $path) {
			if (is_file($path)) {
				$found = true;
				include ($path);
				break;
			}
		}	
		
		if (!$found) {
			throw new \Exception("Could not autoload $namespace trying the following paths:<br /><br />".implode('<br />', $try));
		}
	}
	
	/**
	 * Loads config files in an array.
	 * First it searches for a file _default.php then it tries to load the config for the current HOST and then for the Server IP address.
	 * @param	string	$directory	The directory path where the config files are.
	 * @return	array	An array with the config.
	 */
	protected function _loadConfigVars($directory) {
		// load main config
		$config = include ($directory.'_configs/_default.php');

		// overwrite with server specific config
		$file1 = $directory.'_configs/'.$_SERVER['HTTP_HOST'].'.php';
		$file2 = $directory.'_configs/'.$_SERVER['SERVER_ADDR'].'.php';
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

		// define the global FW_PATH constant
		define('FW_PATH', realpath(__DIR__ .'/../../..') . '/');

		// register autoloader
		spl_autoload_register(array($this, '_autoload'));

		/* register main config in the config class
		********************************************************************************************/
		$this->config = Factory::load('Config'); // config class for config vars

		// load vars
		$config = $this->_loadConfigVars(FW_PATH);

		// register config in class
		foreach ($config as $key => $array) {
			$this->config->set($key, $array);
		}
			
		/* declare errorhandler (needs config class)
		********************************************************************************************/
		set_error_handler(array($this, 'errorHandler'));
		set_exception_handler(array($this, 'exceptionHandler'));

		/* set timezone 
		********************************************************************************************/
		if (!date_default_timezone_set($this->config->get('locale.timezone'))) {
			throw new \Exception(__METHOD__.'<br>date_default_timezone_set() failed.');
		}

		/* prepare some constructor variables
		********************************************************************************************/
		Factory::prepare('Log', FW_PATH.'_logs/log_'.date("y-m-d").'.txt');
		Factory::prepare('Debug', $this->config->get('debug'), FW_PATH.'_logs/'.date("y-m-d").'.txt');

		/* load classes
		********************************************************************************************/
		$this->page		= Factory::load('Page'); // config class for page vars
		$this->input	= Factory::load('Input'); // input class for all user input

		/* define project
		********************************************************************************************/
		$url			= trim($this->input->get('morrow_content'), '/');
		$url_nodes		= explode('/', $url);
		$this->page->set('nodes', $url_nodes);

		$inpath			= $this->config->get('projects');
		$project_folder	= array_shift($inpath);
		$this->config->set('default_project', $project_folder);

		$possible_project_path = $url_nodes[0];

		// set standard
		$project_relpath = $project_folder;

		// search for projects in splitted request
		foreach ($inpath as $project_url) {
			if ($project_url == $possible_project_path) {
				$project_relpath = $project_url;
				// reset splitted nodes in config
				array_shift($url_nodes);
				$this->page->set('nodes', $url_nodes);
			}
		}

		// define project constants
		define('PROJECT_PATH', FW_PATH . $project_relpath . '/');
		define('PROJECT_RELPATH', $project_relpath . '/');

		/* register project config in the config class
		********************************************************************************************/
		// load vars
		$config = $this->_loadConfigVars(PROJECT_PATH);
		
		// register project config in config class
		foreach ($config as $key => $array) {
			$this->config->set($key, $array);
		}

		/* load session
		********************************************************************************************/
		$sessionHandler = $this->config->get('session.handler');
		if (empty($sessionHandler)) $sessionHandler = 'Session';
		$this->session = Factory::load($sessionHandler.':session', $this->config->get('session'), $this->input->get());
		
		/* load languageClass and define alias
		********************************************************************************************/
		$lang['possible'] = $this->config->get('languages');
		$lang['language_path'] = PROJECT_PATH . '_i18n/';
		$lang['i18n_paths'] = array(
			FW_PATH			. '_libs/*.php',
			PROJECT_PATH	. '_libs/*.php',
			PROJECT_PATH	. '_templates/*',
			PROJECT_PATH	. '*.php'
		);
		$this->language = Factory::load('Language', $lang);

		// language via path
		$nodes = $this->page->get('nodes');
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
		$routes	= $this->config->get('routing');
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
		$this->page->set('nodes', explode('/', $url));

		// nodes are only allowed to have a-z, 0-9, - and .
		$nodes = $this->page->get('nodes');
		foreach ($nodes as $node) {
			if (preg_match('|[^0-9a-z.-]|i', $node)) throw new \Exception('URL node names are only allowed to consist of a-z, 0-9, "." and "-".');
		}

		/* prepare some internal variables
		********************************************************************************************/
		$alias = $url;
		$controller_path		= PROJECT_PATH.'_controllers/';
		$global_controller_file	= $controller_path.'_default.php';
		$page_controller_file	= $controller_path.$alias.'.php';
		$path = implode('/', $nodes).'/';
		$query = $this->input->getGet();
		unset($query['morrow_content']);
		$fullpath = $path . (count($query) > 0 ? '?' . http_build_query($query, '', '&') : '');
		$this->view = Factory::load('View');

		/* prepare some constructor variables
		********************************************************************************************/
		Factory::prepare('Cache', PROJECT_PATH.'temp/_codecache/');
		Factory::prepare('Image', PROJECT_PATH . 'temp/thumbs/');
		Factory::prepare('Navigation', Factory::load('Language')->getTree(), $alias);
		Factory::prepare('Pagesession', 'page.' . $alias);
		Factory::prepare('Url', $this->config->get('projects'), $this->language->get(), $lang['possible'], $fullpath);
		Factory::prepare('Security', $this->session, $this->view, $this->input, Factory::load('Url'));

		/* define project paths
		********************************************************************************************/
		$domain = Factory::load('Url')->getBaseHref();

		$this->page->set('base_href', $domain);
		$this->page->set('alias', $alias);
		$this->page->set('controller', $page_controller_file);
		$this->page->set('path', $path);
		$this->page->set('project_relpath', PROJECT_RELPATH);
		$this->page->set('project_path', $domain . $project_relpath . '/');
		$this->page->set('fullpath', $fullpath);


		/* load controller and render page
		********************************************************************************************/
		// make sure to get language content for page alias (??????????)
		//$this->language->getContent($this->page->get('alias'));

		// include global controller class
		include($global_controller_file);

		// include page controller class
		if (is_file($page_controller_file)) {
			include($page_controller_file);
			$controller = new \Morrow\PageController();
			if (method_exists($controller, 'setup')) $controller->setup();
			$controller->run();
			if (method_exists($controller, 'teardown')) $controller->teardown();
		} else {
			$controller = new \Morrow\DefaultController();
			if (method_exists($controller, 'setup')) $controller->setup();
			if (method_exists($controller, 'teardown')) $controller->teardown();
		}

		// Inhalte zuweisen
		$this->view->setContent($this->page->get(), 'page');

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
