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


/*
This class allows lazy loading and registers classes as members
Will be used by the controller and may be used by other classes for example models
*/

namespace Morrow;

class Loader {
	protected $_params = array();
	
	protected function load() {
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
