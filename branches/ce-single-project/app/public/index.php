<?php

namespace Morrow;

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
new Core\Morrow();
