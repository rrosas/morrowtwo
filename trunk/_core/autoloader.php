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




// First we try the external libs. That way the user can replace all core libs if he wants to
function __autoload($class_name) {
	$class_name = strtolower(basename($class_name));

	$try[] = FW_PATH.'_libs/'.$class_name.'.class.php';
	
	if (defined('PROJECT_PATH')) {
		$try[] = PROJECT_PATH.'_libs/'.$class_name.'.class.php';
		$try[] = PROJECT_PATH.'_model/'.$class_name.'.class.php';
	}

	$try[] = FW_PATH.'_core/'.$class_name.'.class.php';
	$try[] = FW_PATH.'_core/libraries/'.$class_name.'.class.php';
	$try[] = FW_PATH.'_core/helpers/'.$class_name.'.class.php';
	$try[] = FW_PATH.'_core/view/'.$class_name.'.class.php';
	$try[] = FW_PATH.'_core/filters/'.$class_name.'.class.php';

	foreach($try as $path) { if(is_file($path)) { include ($path); break; } }
}

spl_autoload_register('__autoload');
