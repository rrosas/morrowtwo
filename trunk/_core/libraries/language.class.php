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




	class Language{
		private $language = null;
		private $default = null;
		private $possible = array();
		private $content_path = null;
		private $locale = array();
		private $content = array();

		public function __construct($settings){
			$required = array("possible","content_path");
			if(count(array_diff($required, array_keys($settings))) > 0){
				user_error("Missing key(s). Required params are : " . implode(", ",$required));
				return;
			}
			if(!is_array($settings['possible'])){
				$settings['default'] = $settings['possible'];
				$settings['possible'] = array($settings['default']);
			}
			else{
				$settings['default'] = $settings['possible'][0];
			}
			
			$this->default = $settings['default'];
			$this->possible = $settings['possible'];
			$this->content_path = HelperFile::cleanPath($settings['content_path']);
			
			#more tests
			if(!is_dir($this->content_path)){
				user_error('Content path ' . $this->content_path . ' does not exist');
				return;
			}
			
			if(!$this->isValid($this->default)) {
				user_error("Default language is not a valid language. Check that the following file exists: " . $this->content_path . $this->default . '.php');
				return;
			}
						
			foreach($this->possible as $pos) {
				if(!$this->isValid($pos)){
					user_error($pos . " is not a valid language. Missing File: " . $this->content_path . $pos . '.php');
					return;
				}
			}
			
			//language was provided
			if(isset($settings['language']) && $this->isValid($settings['language'])){
				$this -> language = $settings['language'];
			}
			
			
			
			#still not defined: default
			if($this->language == null) $this->language = $this->default;
		}

		public function set($lang = null){
			if($this->isValid($lang)){
				$this->language = $lang;
				#clear content because language has changed
				$this->content = array();
				return true;
			}
			return false;
		}

		public function get(){
			if($this->language == null) return $this->default;
			return $this->language;
		}


	public function isValid($lang){
		return (in_array($lang, $this -> possible) && is_file($this->content_path . $lang . '.php'));
	}

	public function getContent($alias){
		$ccontent = array();
		if(!isset($this->content['_global'])){
			$file = $this->content_path . $this->get() . '/_global.php';
			$this->content['_global'] = $this->_getContent($file,'content');
		}
		if(isset($this->content[$alias])) {
			$ccontent = $this->content[$alias];
		}	
		else{
			$file = $this->content_path . $this->get() . '/' . $alias . '.php';
			$ccontent = $this->_getContent($file,'content');
			$this->content[$alias] = $ccontent;
		}
		
		return array_merge($this->content['_global'], $ccontent);
	}
	
	public function getFormContent($alias){
		$file = $this->content_path . $this->get() . '/_global.php';
		$gform = $this->_getContent($file,'form');
		$file = $this->content_path . $this->get() . '/' . $alias . '.php';
		$cform = $this->_getContent($file,'form');
		return array_merge($gform, $cform);
	}
	
	public function getTree(){
		$file = $this->content_path . $this->get() . '/_tree.php';
		return $this->_getContent($file,'tree');
	}


	public function getTranslations($alias){
		$translations = array();
		foreach($this->possible as $availLang){
			if($availLang != $this->get() && $this->translationExists($availLang, $alias)){
				$config = $this->getConfig($availLang);
				$availLangTitle = $availLang;
				if(isset($config['title'])) $availLangTitle = $config['title'];
				$translations[$availLang] = $availLangTitle;
			}
		}
		return $translations;
	}

	public function getConfig($lang = null){
		if(is_null($lang)) $lang = $this->get();
		$file = $this->content_path . $lang . '.php';
		$config = $this->_getContent($file,'config');
		return $config;
	}

	public function translationExists($lang, $alias){
		#where is tree?
		$path = $this->content_path . $lang . '/';
		$global = $path . '_tree.php';
		if(is_file($global)){
			include($global);
			foreach($_TREE as $branch){
				if(isset($branch[$alias])) return true;
			}
		}
		return false;
	}
	
	private function _getContent($file, $key){
		if(!is_file($file)) {
			return array();
		}
		include($file);
		if(!isset($$key)) return array();
		return HelperArray::dotSyntaxExplode($$key);
	}
	
	public function getLocale($lang = null) {
		if (is_null($lang)) $lang = $this->get();
		if (!isset($this->locale[$lang])) {
			$file = $this->content_path . $lang . '.php';
			$config = $this->_getContent($file,'config');
			$this->locale[$lang] = $config;
		}
		return $this->locale[$lang];
	}
	public function setLocale(){
		$lang = $this->get();
		if (!isset($this->locale[$lang])) $this->getLocale();
		if (!setlocale(LC_TIME, $this->locale[$lang]['keys'])){
			exec('locale -a', $locales);
			$locales = implode("<br />", $locales);
			trigger_error(__METHOD__.'<br>setLocale() failed. These are the locales installed on this system:<br />'.$locales, E_USER_NOTICE);
		}
	}

	/*
		if the user has not chosen a language, this method can be used
		to set the language according to the browser settings of the user.
		To make sure that it is only called once (after that the user decides), 
		the variable langcheck is stored in the session.
	*/
	public function setFromClient(){
		$session = Factory::load("session");
		$lang = $this->get();
		if ($session->get("framework.langcheck") !== null) return;

		// get all language keys from language files
		$browser_langs = $this -> _getBrowserLanguages();
		foreach ($browser_langs as $bl) {
			foreach ($this->getPossible() as $possible) {
				$config = $this->getLocale($possible);
				if (in_array($bl, $config['keys'])) { $new = $possible; break(2); }
			}
		}
		
		$session->set("framework.langcheck", "true");
		
		// there was a change so redirect
		if (isset($new)) {
			$url = Factory::load('url');
			$url->redirect('', array('language' => $new));
		}
	}

	public function getPossible(){
		return $this->possible;
	}

	public function getDefault(){
		return $this->default;
	}


	private function _getBrowserLanguages()
		/**
		* Parse the Accept-Language HTTP header sent by the browser. It
		* will return an array with the languages the user accepts, sorted
		* from most preferred to least preferred.
		*
		* @return  Array: key is the importance, value is the language code.
		*/
		{
		$ayLang = array();
		$aySeen = array();
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
			{
			foreach(explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) as $llang)
				{
				preg_match("#^(.*?)([-_].*?)?(\;q\=(.*))?$#i", $llang, $ayM);
				$q = isset($ayM[4]) ? $ayM[4] : '1.0';
				$lang = strtolower(trim($ayM[1]));
				if(!in_array($lang, $aySeen))
					{
					$ayLang[$q] = $lang;
					$aySeen[] = $lang;
					}
				}
			uksort($ayLang, create_function('$a,$b','return ($a>$b) ? -1 : 1;'));
			}
		return $ayLang;
		}
	}
