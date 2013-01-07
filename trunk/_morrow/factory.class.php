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
	
	public static function load($params) {
		// get factory config
		$params = explode(':', $params);
		$classname = __NAMESPACE__ . '\\Libraries\\' . strtolower($params[0]);
		$instancename = (isset($params[1])) ? strtolower($params[1]) : $classname;
		$namespace = (isset($params[2])) ? $params[2] : 'user';
		
		// all other args are arguments for the new class
		$factory_args = func_get_args();
		if (count($factory_args) > 1)  $args = array_slice($factory_args, 1);
		else                           $args = null;
		
		// Wenn sie schon instanziert wurde, zurückgeben, ansonsten anlegen
		$instance =& self::$instances[$namespace][$instancename];
		
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
			$reflectionObj = new \ReflectionClass($classname);
			$instance = $reflectionObj->newInstanceArgs($args);
		}
		return $instance;
	}

	public static function debug() {
		$returner = array();
		foreach (self::$instances['internal'] as $class=>$value) {
			$returner['internal'][$class] = get_class($value);
		}
		foreach (self::$instances['user'] as $class=>$value) {
			$returner['user'][$class] = get_class($value);
		}
		dump($returner);
	}
}
