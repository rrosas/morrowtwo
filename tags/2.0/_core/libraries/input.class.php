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




class Input {
	protected $post;
	protected $get;
	protected $data;
	
	protected $magic_quotes_gpc;
	
	public function __construct() {
		$this->magic_quotes_gpc = get_magic_quotes_gpc();
		
		$this->get   = $this->clean($_GET);
		$this->post  = $this->clean($_POST);
		$this->files = $this->_getFileData($this->clean($_FILES));
		$this->data  = $this->_array_merge_recursive_distinct($this->get, $this->post, $this->files);
	}

	// Bereinigen von user input
	public function clean($value) {
		if (is_array($value)) {
			$value = array_map(array(&$this, 'clean'), $value);
		} else {
			// Folgendermaßen wird bereinigt
			$value = trim($value);
			if ($this->magic_quotes_gpc) $value = stripslashes($value);
			// unify line breaks
			$value = preg_replace("=(\r\n|\r)=", "\n", $value);
			// filter nullbyte
			$value = str_replace("\0", '', $value);
		}
		return $value;
	}

	// Zugriff auf Get-Variablen
	public function get($identifier = null) {
		return helperArray::dotSyntaxGet($this->data, $identifier);
	}

	public function getPost($identifier = null) {
		return helperArray::dotSyntaxGet($this->post, $identifier);
	}

	public function getGet($identifier = null) {
		return helperArray::dotSyntaxGet($this->get, $identifier);
	}

	public function getFiles($identifier = null) {
		return helperArray::dotSyntaxGet($this->files, $identifier);
	}

	// import for URL Routing
	public function set($identifier, $value) {
		helperArray::dotSyntaxSet($this->data, $identifier, $value);
	}

	protected function _array_merge_recursive_distinct () {
		$arrays = func_get_args();
		$base = array_shift($arrays);
		if (!is_array($base)) $base = empty($base) ? array() : array($base);
		foreach($arrays as $append) {
			if (!is_array($append)) $append = array($append);
			foreach($append as $key => $value) {
				if (!array_key_exists($key, $base)) {
					$base[$key] = $append[$key];
					continue;
				}
				if (is_array($value) or is_array($base[$key])) {
					$base[$key] = $this->_array_merge_recursive_distinct($base[$key], $append[$key]);
				} else {
					$base[$key] = $value;
				}
			}
		}
		return $base;
	}

		// if arrays of formdata are used, php rearranges the array format of _FILE.
	// this method puts them back in a more useful format
	protected function _getFileData($_files) {
		$return_files = array();
		if(is_array($_files)) {
			foreach($_files as $fkey => $fvalue) {
				if(is_array($fvalue)) {
					foreach($fvalue as $varname=>$varpair) {
						if(is_array($varpair)) {
							foreach($varpair as $fieldname=>$varvalue) {
								$return_files[$fkey][$fieldname][$varname]=$varvalue;
							}
						} else {
							$return_files[$fkey] = $fvalue;
						}
					}
				}
			}
		}
		return $return_files;
	}

	public function removeXss($var) {
		if (is_array($var)) {
			foreach ($var as $key=>$value) {
				$var[$key] = $this->_removeXss($value);
			}
		} else {
			if (is_scalar($var))
				$var = $this->_removeXss($var);
			}
		return $var;
	}
	
	// http://kallahar.com/smallprojects/php_xss_filter_function.php
	protected function _removeXss($val) {
		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java\0script>
		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
		$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

		// straight replacements, the user should never need these since they're normal characters
		// this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

			// &#x0040 @ search for the hex values
			$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
			// &#00064 @ 0{0,7} matches '0' zero to seven times
			$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
		}

		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true) {
			$val_before = $val;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
				$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
				if ($val_before == $val) {
					// no replacements were made, so exit the loop
					$found = false;
				}
			}
		}
		return $val;
	} 	
}
