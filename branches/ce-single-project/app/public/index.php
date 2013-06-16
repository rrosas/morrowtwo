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


// define the app path
define('FW_PATH', realpath('../..') . '/');

// define the app path
define('APP_PATH', realpath('..') . '/');

// define the path to vendor dir
// change this if you have to projects which should use the same vendor folder
define('VENDOR_PATH', realpath('../../vendor') . '/');
define('VENDOR_USER_PATH', realpath('../../vendor_user') . '/');

// register the Composer autoloader
require VENDOR_PATH . 'autoload.php';

// execute Morrow framework
new Morrow\Core\Morrow();
