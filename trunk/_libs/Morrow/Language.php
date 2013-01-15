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

class Language {
	protected $language = null;
	protected $default = null;
	protected $possible = array();
	protected $language_path = null;
	protected $locale = null;
	protected $content = null;
	protected $i18n_checked = false;
	protected $i18n_path = null;

	public function __construct($settings) {
		// check for required setting keys
		$required = array("possible", "language_path", "i18n_path");
		if (count(array_diff($required, array_keys($settings))) > 0) {
			throw new \Exception("Missing key(s). Required params are : " . implode(", ", $required));
		}
		if (!is_array($settings['possible'])) {
			$settings['default'] = $settings['possible'];
			$settings['possible'] = array($settings['default']);
		} else {
			$settings['default'] = $settings['possible'][0];
		}

		// set default parameters
		$this->default = $settings['default'];
		$this->possible = $settings['possible'];
		$this->language_path = Helpers\General::cleanPath($settings['language_path']);
		$this->i18n_path = $settings['i18n_path'];
		
		// check if there is a valid language file for the possible languages
		foreach ($this->possible as $pos) {
			if (!$this->isValid($pos)) {
				throw new \Exception($pos . " is not a valid language. Missing File: " . $this->language_path . $pos . '/l10n.php');
			}
		}
		
		// language was provided
		if (isset($settings['language']) && $this->isValid($settings['language'])) {
			$this->language = $settings['language'];
		}
		
		// still not defined: default
		if ($this->language == null) $this->language = $this->default;

		// now load l10n
		$this->locale = $this->getLocale();
	}

	public function set($lang = null) {
		if ($this->isValid($lang)) {
			$this->language = $lang;
			$this->locale = $this->getLocale();

			// clear content because language has changed
			$this->content = null;
			return true;
		}
		return false;
	}

	public function get() {
		return $this->language;
	}

	public function getPossible() {
		return $this->possible;
	}

	public function getDefault() {
		return $this->default;
	}

	public function isValid($lang) {
		return in_array($lang, $this->possible);
	}

	public function getConfig($lang = null) {
		if (is_null($lang)) $lang = $this->get();

		$file = $this->language_path . $lang . '/l10n.php';
		$config = $this->_loadFile($file);
		return $config;
	}

	public function getContent() {
		if ($this->content == null) {
			$file = $this->language_path . $this->language . '/i18n.php';
			$this->content = $this->_loadFile($file, false);
		}

		return $this->content;
	}

	public function getFormContent() {
		$file = $this->language_path . $this->get() . '/forms.php';
		return $this->_loadFile($file);
	}

	public function getTree() {
		$file = $this->language_path . $this->get() . '/tree.php';
		return $this->_loadFile($file);
	}

	protected function _loadFile($file, $dotSyntaxExplode = true) {
		if(!is_file($file)) return array();
		if ($dotSyntaxExplode) return Helpers\General::array_dotSyntaxExplode(include($file));
		return include($file);
	}

	public function getLocale($lang = null) {
		if ($lang == null) $lang = $this->language;
		if ($lang == $this->language && $this->locale != null) return $this->locale;

		$file = $this->language_path . $lang . '/l10n.php';
		$locale = $this->_loadFile($file);
		return $locale;
	}

	public function setLocale() {
		if (!setlocale(LC_TIME, $this->locale['keys'])) {
			exec('locale -a', $locales);
			$locales = implode("<br />", $locales);
			throw new \Exception(__METHOD__.'<br>setLocale() failed. These are the locales installed on this system:<br />'.$locales);
		}
	}

	public function getTranslations($alias) {
		$translations = array();
		foreach ($this->possible as $availLang) {
			if ($availLang != $this->get() && $this->translationExists($availLang, $alias)) {
				$config = $this->getConfig($availLang);
				$availLangTitle = $availLang;
				if (isset($config['title'])) $availLangTitle = $config['title'];
				$translations[$availLang] = $availLangTitle;
			}
		}
		return $translations;
	}

	public function translationExists($lang, $alias) {
		// where is tree?
		$path = $this->language_path . $lang . '/';
		$global = $path . '_tree.php';
		if (is_file($global)) {
			include($global);
			foreach ($tree as $branch) {
				if (isset($branch[$alias])) return true;
			}
		}
		return false;
	}

	/*
		if the user has not chosen a language, this method can be used
		to set the language according to the browser settings of the user.
		To make sure that it is only called once (after that the user decides), 
		the variable langcheck is stored in the session.
	*/
	public function setFromClient() {
		$session = Factory::load("Session");
		$lang = $this->get();
		if ($session->get("framework.langcheck") !== null) return;

		// get all language keys from language files
		$browser_langs = $this -> _getBrowserLanguages();
		foreach ($browser_langs as $bl) {
			foreach ($this->getPossible() as $possible) {
				$config = $this->getLocale($possible);
				if (in_array($bl, $config['keys'])) {
					$new = $possible;
					break(2);
				}
			}
		}
		
		$session->set("framework.langcheck", "true");
		
		// there was a change so redirect
		if (isset($new)) {
			$url = Factory::load('Url');
			$url->redirect('', array('language' => $new));
		}
	}

	/*
		Parse the Accept-Language HTTP header sent by the browser. It
		will return an array with the languages the user accepts, sorted
		from most preferred to least preferred.

		@return  Array: key is the importance, value is the language code.
	*/
	protected function _getBrowserLanguages() {
		$ayLang = array();
		$aySeen = array();
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $llang) {
				preg_match("#^(.*?)([-_].*?)?(\;q\=(.*))?$#i", $llang, $ayM);
				$q = isset($ayM[4]) ? $ayM[4] : '1.0';
				$lang = strtolower(trim($ayM[1]));
				if (!in_array($lang, $aySeen)) {
					$ayLang[$q] = $lang;
					$aySeen[] = $lang;
				}
			}
			uksort($ayLang, create_function('$a,$b', 'return ($a>$b) ? -1 : 1;'));
		}
		return $ayLang;
	}

	public function _($string) {
		if ($this->language == $this->possible[0]) return $string;

		// search in language file
		if (isset($this->content[$string]) && !empty($this->content[$string])) {
			return $this->content[$string];
		}

		// oh not found, better check all languages
		if (!$this->i18n_checked) {
			$this->check_language_files();
		}

		return $string;
	}

	public function check_language_files() {
		$this->i18n_checked = true;

		// search for all translation patterns
		$files = array();
		foreach ($this->i18n_path as $path) {
			$files = array_merge($files, $this->_glob_recursive($path));
		}
		
		$catalog = array();
		foreach ($files as $file) {
			$content = file_get_contents($file);

			// handle double quotes
			preg_match_all('-_\(("(\\\.|[^"])*")\)-', $content, $matches);
			$catalog = array_merge($catalog, $matches[1]);

			// handle single quotes
			preg_match_all("-_\(('(\\\.|[^'])*')\)-", $content, $matches);
			$catalog = array_merge($catalog, $matches[1]);
		}

		
		// handle string escape sequences
		foreach ($catalog as $i => $v) {
			eval("\$catalog[\$i] = ".$v.";");
		}

		$catalog = array_fill_keys($catalog, '');
		ksort($catalog);

		// check the difference between the catalog and the existing language files
		foreach ($this->possible as $i => $al) {
			if ($i === 0) continue;

			$path = $this->language_path . $al . '/i18n.php';
			
			$current = include($path);
			// only keep not empty values
			if (!is_array($current)) $current = array();
			$current = array_filter($current);
			ksort($current);

			// new entries
			$new = array_diff_key($catalog, $current);
			
			// old entries
			$old = array_diff_key($current, $catalog);

			// valid entries
			$valid = array_intersect_key($current, $catalog);

			$export = "<?php\n/* This is an automatically created file */\n\nreturn array(";
			$export .= $this->_var_export($new, 'entries to translate');
			$export .= $this->_var_export($old, 'unknown entries');
			$export .= $this->_var_export($valid, 'translated entries');
			$export .= ");";

			file_put_contents($path, $export);
		}
	}

	protected function _var_export($array, $section) {
		if (empty($array)) return '';

		$returner = "\n\t/* $section */\n";
		foreach ($array as $k => $v) {
			$key = str_replace("'", "\'", $k);
			$value = str_replace("'", "\'", $v);

			$returner .= str_pad("\t'$key'", 40, ' ');
			$returner .= '=> ';
			$returner .= "'$value',\n";
		}

		return $returner;
	}

	protected function _glob_recursive($pattern, $flags = 0) {
		$files = glob($pattern, $flags);
		foreach (glob(dirname($pattern).'/*', GLOB_NOSORT) as $dir) {
			$files = array_merge($files, $this->_glob_recursive($dir.'/'.basename($pattern), $flags));
		}

		return $files;	
	}
}
