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


namespace Morrow;

class Morrow {
	public function __construct() {
		$this->_run();
	}

	public function errorHandler($errno, $errstr, $errfile, $errline) {
		// get actual error_reporting
		$error_reporting = error_reporting();

		// request for @ error-control operator
		if ($error_reporting == 0) return;

		// return if error should not get processed
		if (($errno & $error_reporting) === 0) return;

		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}

	public function ExceptionHandler($exception) {
		try {
			// load errorhandler
			$debug = Factory::load('debug');
			$debug->errorhandler($exception);
		} catch (\Exception $e) {
			// useful if the \Exception handler itself contains errors
			echo "<pre>$e</pre>";
		}
	}
	
	protected function autoload($classname) {
		// First we try the external libs. That way the user can replace all core libs if he wants to
		$classname = preg_replace('=^Morrow=i', '_morrow', $classname);
		$classname = preg_replace('=^User=i', '_user', $classname);
		$classname = str_replace('\\', '/', strtolower($classname));
		
		// first try to find the user replaced or added class
		if (strpos($classname, '_morrow') === 0) {
			$try[] = FW_PATH . str_replace('_morrow/', '_user/', $classname).'.class.php';
		}
		// then the original morrow class
		$try[] = FW_PATH . $classname.'.class.php';

		foreach($try as $path) { if(is_file($path)) { include ($path); break; } }	
	}
	
	// registers the config files in the config class
	protected function _loadConfigVars($path) {
		// load main config
		$config = include ($path.'_configs/_default.php');

		// overwrite with server specific config
		$file1 = $path.'_configs/'.$_SERVER['HTTP_HOST'].'.php';
		$file2 = $path.'_configs/'.$_SERVER['SERVER_ADDR'].'.php';
		if (is_file($file1)) $config = include($file1);
		elseif (is_file($file2)) $config = include($file2);

		return $config;
	}

	// This function contains the main application flow
	protected function _run() {
		/* register autoloader
		********************************************************************************************/
		spl_autoload_register(array($this, 'autoload'));
		
		/* register main config in the config class
		********************************************************************************************/
		$this->config = Factory::load('config'); // config class for config vars
		// load vars
		$config = $this->_loadConfigVars(FW_PATH);

		// register config in class
		foreach ($config as $key=>$array) {
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

		/* load classes
		********************************************************************************************/
		$this->page		= Factory::load('page'); // config class for page vars
		$this->url		= Factory::load('url'); // url class
		$this->input	= Factory::load('input'); // input class for all user input

		/* define project
		********************************************************************************************/
		$url			= Helpers\General::url_trimSlashes($this->input->get('morrow_content'));
		$url_nodes		= explode('/', $url);
		$this->page->set('nodes', $url_nodes);

		$inpath			= $this->config->get('projects');
		$project_folder	= array_shift($inpath);
		$this->config->set('default_project', $project_folder);

		$possible_project_path = $url_nodes[0];

		// set standard
		$project_relpath = $project_folder;

		// search for projects in splitted request
		foreach($inpath as $project_url) {
			if($project_url == $possible_project_path) {
				$project_relpath = $project_url;
				 // reset splitted nodes in config
				array_shift($url_nodes);
				$this->page->set('nodes', $url_nodes);
			}
		}

		// define project constants
		define ('PROJECT_PATH', FW_PATH . $project_relpath . '/');
		define ('PROJECT_RELPATH', $project_relpath);


		/* register project config in the config class
		********************************************************************************************/
		// load vars
		$config = $this->_loadConfigVars(PROJECT_PATH);

		// register project config in config class
		foreach ($config as $key=>$array) {
			$this->config->set($key, $array);
		}

		/* get domain
		********************************************************************************************/
		$domain = $this->url->getDomain();
		$this->page->set('base_href', $domain);

		/* define project paths
		********************************************************************************************/
		$this->page->set('project_path', $domain.$project_relpath.'/');
		$this->page->set('project_relpath', $project_relpath.'/');

		/* load session
		********************************************************************************************/
		$sessionHandler = $this->config->get('session.handler');
		if($sessionHandler == '') $sessionHandler = 'session';
		$session = Factory::load($sessionHandler.':session', $this->input->get());
		
		/* load languageClass and define alias
		********************************************************************************************/
		$lang_settings['possible'] = $this->config->get('languages');
		$lang_settings['language_path'] = PROJECT_PATH . '_i18n/';
		$lang_settings['i18n_path'] = array(
			FW_PATH . '_morrow/*.php',
			FW_PATH . '_user/*.php',
			PROJECT_PATH . '_templates/*',
			PROJECT_PATH . '*.php'
		);
		$this->language = Factory::load('language', $lang_settings);

		// language via path
		$nodes = $this->page->get('nodes');
		if(isset($nodes[0]) && $this->language->isValid($nodes[0])) {
			$input_lang_nodes = array_shift($nodes);
			$this->page->set('nodes', $nodes);
		}
		
		#$alias = implode('_', $nodes);
		#$this->page->set('alias', $alias);

		// language via input
		$input_lang = $this->input->get('language');

		if($input_lang === null && isset($input_lang_nodes)) {
			$input_lang = $input_lang_nodes;
		}

		if ($input_lang !== null) $this->language->set($input_lang);
		$this->language->setLocale();

		/* url routing
		********************************************************************************************/
		$routes	= $this->config->get('routing');
		$url	= implode('/',$this->page->get('nodes')); #$this->input->get('morrow_content');
		$url	= Helpers\General::url_trimSlashes($url);
	
		// iterate all rules
		foreach ($routes as $rule=>$new_url) {
			$rule		= Helpers\General::url_trimSlashes($rule);
			$new_url	= Helpers\General::url_trimSlashes($new_url);

			// rebuild route to a preg pattern
			$preg_route	= preg_replace('=\\:[a-z0-9_]+=i', '([^/]+)', $rule); // match parameters. the four backslashes match just one
			$preg_route	= preg_replace('=/\\*[a-z0-9_]+=i', '(.*)', $preg_route); // match asterisk.

			$pattern	= '=^'.$preg_route.'$=i';

			// does this rule match?
			preg_match($pattern, $url, $hits);

			// no hits? then goto next rule
			if (count($hits) == 0) continue;
						
			// skip first entry because it contains the complete string and not only the search result
			array_shift($hits);
			
			// if there is an asterisk the last entry is for the params
			if (strpos($rule, '*') !== false) {
				$blind_parameters = array_pop($hits);
				$blind_parameters = Helpers\General::url_trimSlashes($blind_parameters);
				$blind_parameters = explode('/', $blind_parameters);
				
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
				foreach ($params as $key=>$param) {
					$new_url = str_replace(":$key", $params[$key], $new_url);
				}

				// register new params in the input class
				foreach ($params as $key=>$param) {
					$this->input->set($key, $param);
				}
			}

			$url = $new_url;
		}

		// set nodes in page class
		if($this->config->get('url.case_insensitive')) $url = strtolower($url);
		$url_nodes = explode('/', $url);
		$this->page->set('nodes', $url_nodes);

		/* set alias
		********************************************************************************************/
		
		$alias = implode('_', $url_nodes);
		$this->page->set('alias', $alias);

		/* load controller and render page
		********************************************************************************************/
		$this->view = Factory::load('view');
				
		// declare the controller to be loaded
		$controller_path		= PROJECT_PATH.'_controllers/';
		$global_controller_file	= $controller_path.'_default.class.php';
		$page_controller_file	= $controller_path.$alias.'.class.php';

		$this->page->set('controller', $page_controller_file);
		$this->page->set('path', implode('/', $nodes).'/');
		
		$query = $this->input->getGet();
		unset($query['morrow_content']);
		//$fullpath = $this->url->makeUrl($this->page->get('path'), $query);
		if (count($query) === 0)
			$fullpath = $this->page->get('path');
		else
			$fullpath = $this->page->get('path').'?'.http_build_query($query, '', '&');
		$this->page->set('fullpath', $fullpath);

		// make sure to get language content for page alias
		$this->language->getContent($this->page->get('alias'));

		// include global controller class
		include($global_controller_file);

		// include page controller class
		if (is_file($page_controller_file)) {
			include($page_controller_file);
			$controller = new PageController();
			if (method_exists($controller, 'setup')) $controller->setup();
			$controller->run();
			if (method_exists($controller, 'teardown')) $controller->teardown();
		} else {
			$controller = new DefaultController();
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
