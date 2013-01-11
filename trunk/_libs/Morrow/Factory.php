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

// This class manages all classes the framework is working with
class Factory {
	public static $instances;
	protected $_params = array();
	
	public static function load($params) {
		// get factory config
		$params = explode(':', $params);
		
		// if there was passed an relative path add the current namespace
		$classname = $params[0];
		if ($params[0]{0} != '\\') {
			$classname = 'Morrow\\' . $classname;
		}
		
		$instancename = (isset($params[1])) ? strtolower($params[1]) : $classname;
		
		// all other args are arguments for the new class
		$factory_args = func_get_args();
		if (count($factory_args) > 1)  $args = array_slice($factory_args, 1);
		else                           $args = null;
		
		// Wenn sie schon instanziert wurde, zurückgeben, ansonsten anlegen
		$instance =& self::$instances[$instancename];
		
		if (isset($instance)) {
			if ($instance instanceof $classname) return $instance;
			else {
				throw new \Exception('instance "'.$instancename.'" already defined of class "'.get_class($instance).'"');
				return false;
			}
		}

		// create object
		if (is_null($args)) {
			$instance = new $classname;
		} else {
			// use reflection class to inject the args as single parameters
			$ref = new \ReflectionClass($classname);
			$instance = $ref->newInstanceArgs($args);
		}
		return $instance;
	}

	public static function debug() {
		$returner = array();
		foreach (self::$instances as $class=>$value) {
			$returner[$class] = get_class($value);
		}
		\Morrow\dump($returner);
	}

	protected function prepare() {
		$args = func_get_args();
		
		// get instance name in params string
		$params = explode(':', $args[0]);
		$classname = strtolower($params[0]);
		$instancename = (isset($params[1])) ? strtolower($params[1]) : $classname;
		
		// save params for later
		$this->_params[$instancename] = $args;
	}

	public function __get($instancename) {
		// get arguments
		$factory_args = (isset($this->_params[$instancename])) ? $this->_params[$instancename] : array(ucfirst($instancename));
		
		// assign the new class
		$this->$instancename = call_user_func_array( array(__NAMESPACE__ . '\\Factory','load'), $factory_args );
		return $this->$instancename;
	}
}
