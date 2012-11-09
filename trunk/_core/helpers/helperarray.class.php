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





class HelperArray {
	// get key of an array or object
	// useful to access keys of arrays returned from functions
	public static function extract($data, $key, $default = null) {
		$data = (array)$data;
		
		if (is_scalar($key)) {
			if (!isset($data[$key])) return $default;
			return $data[$key];
		} else if (is_array($key)) {
			return array_intersect_key($data, array_flip($key));
		} else {
			return null;
		}
	}
	
	// This function orders an array like in a sql query
	public static function orderBy($data, $orderby) {
		// all the references are part of a workaround which only occurs with array_multisort and call_user_func_array in PHP >= 5.3
		$asc = SORT_ASC; 
		$desc = SORT_DESC;
		
		// the array we pass to array_multisort at the end
		$params = array();

		// explode the orderby to use it for array_multisort
		$orderbys = explode(',', $orderby);
		$orderbys = array_map('trim', $orderbys);
		foreach ($orderbys as &$orderby) {
			$parts = explode(" ", $orderby);
			if (!isset($parts[1])) $parts[1] = 'asc';
			$parts[1] = strtolower($parts[1]);
			if (!in_array($parts[1], array('asc', 'desc'))) $parts[1] = 'asc';
			
			// add field name
			$params[] = $parts[0];

			// add sort flag
			if ($parts[1] == 'asc') $params[] =& $asc;
			else $params[] =& $desc;
		}

		// create temp arrays for multisort
		$temp = array();
		$count = count($params)-1;
		for ($i=0; $i<=$count; $i=$i+2) {
			$field = $params[$i];
			$params[$i] = array();
			foreach ($data as $ii=>$row) {
				$temp[$field][] = strtolower($row[$field]);
			}
			
			$params[$i] =& $temp[$field];
		}
		
		//now sort
		$params[] =& $data;
		call_user_func_array('array_multisort', $params);
		return $data;
	}

	// This function does a array_diff_key but recursive
	public static function array_diff_key_recursive ($a1, $a2) {
		if (is_array($a2)) {
			$r = array_diff_key($a1, $a2);
		} else {
			$r = $a1;
		}

		foreach($a1 as $k => $v) {
			if (is_array($v)) {
				$temp = self::array_diff_key_recursive($a1[$k], $a2[$k]);
				if (count($temp) > 0) {
					$r[$k]=$temp;
				}
			}
		}
		return $r;
	}

	// This function does a correct array_merge_recursive
	public static function array_merge_recursive() {
		$_return    = false;
		$parameters = func_get_args();

		// Validation
		if(count($parameters) >= 2) {
			$_return = true;

			foreach($parameters as $parameter) {
				if(!is_array($parameter)) {
					$_return = false;
					throw new Exception(__METHOD__ . ': Only arrays can be merged', E_USER_ERROR);
					break;
				}
			}
		} else {
			throw new Exception(__METHOD__ . ': Two or more arrays needed to be merged', E_USER_ERROR);
		}

		if($_return = true) {
			$_return = array_shift($parameters);

			foreach($parameters as $parameter) {
				foreach($parameter as $key => $value) {
					$type = gettype($value);
					switch($type) {
						case 'array':
							if(isset($_return[$key]) && is_array($_return[$key])) {
								$_return[$key] = self::array_merge_recursive($_return[$key], $parameter[$key]);
							} else {
								$_return[$key] = $parameter[$key];
							}
							break;
						default:
							$_return[$key] = $parameter[$key];
							break;
					}
				}
			}
		}

		return $_return;
	}

	public static function dotSyntaxGet(&$array, $identifier) {
		// Validierung
		if (!is_array($array)) { trigger_error(__CLASS__.': first parameter has to be of type "array".', E_USER_ERROR); return; }
		if (!is_string($identifier) AND !is_null($identifier)) { trigger_error(__CLASS__.': second parameter has to be of type "string".', E_USER_ERROR); return; }
		if (empty($identifier)) return $array;

		// Referenz erstellen
		$parts = explode('.', $identifier);
		$returner =& $array;
		foreach ($parts as $part) {
			// Wenn es den Array-Schlüssel gibt, Referenz erweitern
			if (isset($returner[$part])) {
				$returner =& $returner[$part];
			}
			// ansonsten Referenz löschen
			else {
				unset($returner);
				break;
			}
		}

		if (isset($returner)) return $returner;
		else return null;
	}

	public static function dotSyntaxSet(&$array, $identifier, $value) {
		// Validierung
		if (!is_array($array)) { trigger_error(__CLASS__.': first parameter has to be of type "array".', E_USER_ERROR); return false; }
		if (!is_string($identifier) OR empty($identifier)) { trigger_error(__CLASS__.': identifier has to be of type "string" and must not be empty.', E_USER_ERROR); return false; }

		// Referenz erstellen
		$parts = explode('.', $identifier);
		$returner =& $array;
		
		foreach ($parts as $part) {
			if (strlen($part) === 0) { trigger_error(__CLASS__.': a key must not be empty.', E_USER_ERROR); return false; }
			if (!isset($returner[$part])) {
				$returner[$part] = '';
			}
			$returner =& $returner[$part];
		}

		if (is_array($value)) {
			$returner = self::dotSyntaxExplode($value);
		}
		else $returner = $value;
		return true;
	}

	public static function dotSyntaxDelete(&$array, $identifier) {
		// Validierung
		if (!is_array($array)) { trigger_error(__CLASS__.': first parameter has to be of type "array".', E_USER_ERROR); return; }
		if (!is_string($identifier)) { trigger_error(__CLASS__.': second parameter has to be of type "string".', E_USER_ERROR); return; }

		// Referenz erstellen
		$parts = explode('.', $identifier);
		$returner =& $array;
		$parent =& $array;
		foreach ($parts as $part) {
			// Wenn es den Array-Schlüssel gibt, Referenz erweitern
			if (isset($returner[$part]) && !empty($part)) {
				$parent =& $returner;
				$rkey = $part;
				$returner =& $returner[$part];
			}
			// ansonsten Referenz löschen
			else {
				unset($returner);
				break;
			}
		}

		if (isset($returner)) {
		    unset($parent[$rkey]);
			return true;
		} else {
			trigger_error(__CLASS__.': identifier "'.$identifier.'" does not exist.', E_USER_ERROR); return false;
		}
	}

	public static function dotSyntaxExplode($array) {
		$data = array();

		// Iterate keys
		foreach ($array as $rkey=>$row) {
			$parent =& $data;
			$parts = explode('.',$rkey);

			// Iterate key parts
			foreach ($parts as $part) {
				// build values
				if(!isset($parent[$part]) || !is_array($parent[$part])) {
					if ($part === end($parts)) {
						if (!is_array($row)) $parent[$part] = $row;
						else $parent[$part] = self::dotSyntaxExplode($row);
					}
					else $parent[$part] = array();
				}
				$parent = &$parent[$part];
			}
		}
		return $data;
	}

	public static function setKey($data, $field) {
		$returner = array();
		foreach ($data as $row) {
			$returner[$row[$field]] = $row;
		}
		return $returner;
	}
}
