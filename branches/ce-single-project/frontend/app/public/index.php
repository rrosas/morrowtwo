<?php

namespace Morrow;

// define paths
define('PUBLIC_PATH', realpath(__DIR__) . '/');
define('APP_PATH', realpath(__DIR__ . '/..') . '/');
define('FW_PATH', realpath(__DIR__ . '/../..') . '/');

// define the path to vendor dir
// change this if you have to projects which should use the same vendor folder
define('VENDOR_PATH', realpath(__DIR__ . '/../../vendor') . '/');
define('VENDOR_USER_PATH', realpath(__DIR__ . '/../../vendor_user') . '/');

// register the Composer autoloader
require VENDOR_PATH . 'autoload.php';

// execute Morrow framework
new Core\Morrow();
