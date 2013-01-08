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

$time_start = microtime(true);

// compress the output
if(!ob_start("ob_gzhandler")) ob_start();
		
// include E_STRICT in error_reporting
error_reporting(E_ALL | E_STRICT);

define('FW_PATH', __DIR__ .'/');

/* define dump function
********************************************************************************************/
function dump() {
	$debug = Core\Factory::load('Libraries\debug');
	$args = func_get_args();
	echo $debug->dump($args);
}

/* load framework
********************************************************************************************/
require(FW_PATH . '/_core/factory.class.php');
require(FW_PATH . '/_core/loader.class.php');
require(FW_PATH . '/_core/morrow.class.php');

new Core\Morrow();

/*
$time_end = microtime(true);
$time = $time_end - $time_start;
Factory::load('log')->set(round($time*1000, 2).' ms');
*/

