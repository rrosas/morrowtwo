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

class Url {

	public function __construct() {
		ini_set('arg_separator.output', '&amp;');
		$this->page = Factory::load('Page');
	}

	// wie parse_url, gibt jedoch immer den kompletten Satz an Schlüsseln zurück
	public function parse($url) {
		// Diese Schlüssel können alle vorkommen
		$template = array('scheme'=>'','host'=>'','port'=>'','user'=>'','pass'=>'','path'=>'','query'=>'','fragment'=>'');

		// URL zerschneiden
		$parts = parse_url($url);

		// Es sollen alle Schlüssel vorkommen, auch wenn sie leer sind
		$parts = array_merge($template, $parts);

		return $parts;
	}

	// Ersetzt Fragmente der URL durch neue und gibt die umgebaute URL zurück
	public function rewrite($url, $replacements) {
		// URL zerschneiden
		$parts = $this->parse($url);

		// Zusammenfügen
		$parts = array_merge($parts, $replacements);

		// URL zusammenbauen
		$url = '';
		if (!empty($parts['scheme'])) $url .= $parts['scheme'].'://';
		$url .= $parts['user'];
		if (!empty($parts['pass'])) $url .= ':'.$parts['pass'];
		if (!empty($parts['user']) OR !empty($parts['pass'])) $url .= '@';
		$url .= $parts['host'];
		if (!empty($parts['port'])) $url .= ':'.$parts['port'];
		$url .= $parts['path'];
		if (!empty($parts['query'])) $url .= '?'.$parts['query'];
		if (!empty($parts['fragment']))	$url .= '#'.$parts['fragment'];

		return $url;
	}

	// Führt ein sauberes HTTP-Redirect aus
	public function redirect($path, $query=array(), $replacements=array(), $http_response_code=302) {
		$url = $this->makeUrl($path, $query, $replacements, true, '&');
		header('Location: '.$url, true, $http_response_code);
		die('');
	}


	public function makeUrl($path, $query=array(), $replacements=array(), $rel2abs=false, $sep=null) {
		if ($sep !== null) {
			ini_set('arg_separator.output', $sep);
		}
		
		// remember whether a slash was at the beginning
		$absolute = false;
		if (isset($path[0]) && $path[0] == '/') $absolute = true;

		// take path apart
		$parts		= $this->parse($path);
		$scheme		= ( !empty($parts['scheme']) ) ? $parts['scheme'] . '://':'';
		$host		= $parts['host'];
		$port		= ( !empty($parts['port']) ) ? ':'.$parts['port'] : '';
		$auth		= ( !empty($parts['user']) ) ? $parts['user'].':'.$parts['pass'].'@' : '';
		$path		= $parts['path'];
		$querystring= $parts['query'];
		$fragment	= ( !empty($parts['fragment']) ) ? '#' . $parts['fragment'] :'';
		
		// combine query in path with query-array
		if (!empty($querystring)) {
			parse_str($querystring, $query_array);
			$query = array_merge($query_array, $query);
		}

		// only for internal:
		if (empty($scheme)) {
			// load only once
			if (!isset($this->config)) {
				$this->config = Factory::load('Config');
				$this->config_all = $this->config->get();
			}
			$config =& $this->config;
			
			if (!isset($this->page_get)) {
				$this->page_get = $this->page->get();
			}

			if (!isset($this->language)) {
				$this->language = Factory::load('Language');
				$this->language_possible = $this->language->getPossible();
				$this->language_default = $this->language->getDefault();
			}
			
			if (empty($path)) {
				// path empty?
				$path = $this->page->get('fullpath');
				$parts	= $this->parse($path);
				$path = $parts['path'];
				parse_str($parts['query'], $fullpathquery);
				$query = array_merge($fullpathquery, $query);
			}

			// path: trim slashes
			if ($path != '') $path = trim($path, '/');

			// project  && lang handling
			$pathparts = explode('/', $path);
			$lang = '';
			$project = "";
			// for use with files
			$project_folder = $this->config_all['projects'][0] . '/';

			// project from context
			if (!$absolute) {
				$project = $this->page_get['project_relpath']; 
				$project_folder = $project;
				// remove it if it is the default folder
			} elseif (isset($pathparts[0]) && (in_array($pathparts[0], $this->config_all['projects'])) || $pathparts[0] == $this->config_all['projects'][0]) {
				// project explicitly named - so switch
				$project = array_shift($pathparts) . "/";
				$project_folder = $project;
			}
			if ($project == $this->config_all['projects'][0] . '/') $project = '';

			// language in path?
			if (isset($pathparts[0]) && in_array($pathparts[0], $this->language_possible)) {
				$lang = array_shift($pathparts);
			}

			// put it back together
			$path = implode('/', $pathparts);

			// if lang as get-param, it has precedence
			// load language
			if (!isset($this->language_get)) $this->language_get = $this->language->get();
			
			if (isset($query['language'])) {
				$lang = $query['language'];
				unset($query['language']);
			} else if ($this->language_get != $this->language_default) {
				$lang = $this->language_get;
			}

			if (!empty($lang)) $lang .= '/';

			// check if it is a file
			if (is_file(FW_PATH . $project_folder . $path)) {
				// add project_folder
				$path = $project_folder . $path;
			} else {
				// otherwise add lang and project path and slash
				if (!empty($path)) $path .= '/';
				$path = $project . $lang . $path;
			}

			// create complete url with domain
			if ($rel2abs) {
				$domain_parts = $this->parse($this->page_get['base_href']);
				$domain_parts['path'] = trim($domain_parts['path'], '/');
				$host = $domain_parts['host'] . '/';
				if (!empty($domain_parts['path'])) $host .= $domain_parts['path'] . '/';
				if (empty($scheme)) $scheme = $domain_parts['scheme'].'://';
			}
		}

		$qstring = '';
		if (count($query) > 0) {
			$qstring = '?' . http_build_query($query);
		}

		$url =  $scheme . $auth . $host . $port . $path . $qstring . $fragment;
		
		if (count($replacements) > 0) {
			$url = $this->rewrite($url, $replacements);
		}
		
		return $url;
	}

	// get actual request
	public function getDomain() {
		$path = dirname($_SERVER['SCRIPT_NAME']).'/';
		
		// If it is the root the return value of dirname is slash
		if ($path == '//') $path = '/';
		
		$host = isset($_SERVER['HTTPS']) || (isset($_SERVER['SSL_PROTOCOL']) && !empty($_SERVER['SSL_PROTOCOL'])) ? 'https://' : 'http://';
		return $host.$_SERVER['HTTP_HOST'].$path;
	}
}
