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

/* define dump function
********************************************************************************************/
function dump() {
	$debug = Factory::load('debug');
	$args = func_get_args();
	echo $debug->dump($args);
}

/* the autoloader for all classes
********************************************************************************************/
function __autoload($classname) {
	print_r($classname);

	// First we try the external libs. That way the user can replace all core libs if he wants to
	$classname = preg_replace('=^Morrow=i', '_core', $classname);
	$classname = preg_replace('=^User=i', '_libs', $classname);
	$classname = str_replace('\\', '/', strtolower($classname));
	
	// first try to find the user class, then the morrow class
	$try[] = FW_PATH.$classname.'.class.php';
	if (strpos($classname, '_core') === 0) {
		$try[] = FW_PATH.str_replace('_core/', '_libs/', $classname).'.class.php';
	}

	print_r($try);
	
	/*
	if (defined('PROJECT_PATH')) {
		$try[] = PROJECT_PATH.'_libs/'.$classname.'.class.php';
		$try[] = PROJECT_PATH.'_model/'.$classname.'.class.php';
	}

	$try[] = FW_PATH.'_core/'.$classname.'.class.php';
	$try[] = FW_PATH.'_core/libraries/'.$classname.'.class.php';
	$try[] = FW_PATH.'_core/helpers/'.$classname.'.class.php';
	$try[] = FW_PATH.'_core/view/'.$classname.'.class.php';
	$try[] = FW_PATH.'_core/filters/'.$classname.'.class.php';
	*/

	foreach($try as $path) { if(is_file($path)) { include ($path); break; } }
}

spl_autoload_register('Morrow\__autoload');

/* load framework
********************************************************************************************/
require(FW_PATH . '_core/factory.class.php');
require(FW_PATH . '_core/loader.class.php');
require(FW_PATH . '_core/morrow.class.php');

new Morrow();
