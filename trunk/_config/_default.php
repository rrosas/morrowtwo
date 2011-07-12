<?php

/*
 * all config vars in this file are neccessary for the framework to run
 */

// the first project will be the default project. The others reachable through the url
$config['projects'] = array('main');

### locale/timezone
$config['locale.timezone']     = 'Europe/Berlin';

// debug
$config['debug.screen']  		= true;
$config['debug.flatfile']		= false;
$config['debug.die_on_error']	= true;

// session
$config['session.lifetime']		= 0; // Lifetime of the session cookie, defined in seconds.
$config['session.path']			= str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME']).'/'); // Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.
$config['session.domain']		= ''; // Cookie domain, for example 'www.php.net'. To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.php.net'.
$config['session.secure']		= false; // If TRUE cookie will only be sent over secure connections.
$config['session.httponly']		= false; // If set to TRUE then PHP will attempt to send the httponly flag when setting the session cookie.
