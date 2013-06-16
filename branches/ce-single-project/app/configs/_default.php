<?php

return array(
// languages
	'languages'    				=> array('en'),

// locale/timezone
	'locale.timezone'			=> 'Europe/Berlin',

// session
	'session.handler'			=> 'Session', // The class name that should be used to handle the session
	'session.lifetime'			=> 0, // Lifetime of the session cookie, defined in seconds.
	'session.path'				=> str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME']).'/'), // Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.
	'session.domain'			=> '', // Cookie domain, for example 'www.php.net'. To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.php.net'.
	'session.secure'			=> false, // If TRUE cookie will only be sent over secure connections.
	'session.httponly'			=> false, // If set to TRUE then PHP will attempt to send the httponly flag when setting the session cookie.

// debug
	'debug.output.screen'		=> true,
	'debug.output.headers'		=> false,
	'debug.output.logfile'		=> false,
	'debug.password'			=> 'password',

// routing rules
	'routing' => array(
		'/' => 'home/',
	),

// OPTIONAL: the following config vars are NOT neccessary for the framework to run
// mailer
	'mail.Mailer'			=> 'mail',
	'mail.From'				=> 'test@morrowtest.com',
	'mail.FromName'			=> 'MorrowTwo',
	'mail.WordWrap'			=> 0,
	'mail.Encoding'			=> 'quoted-printable',
	'mail.CharSet'			=> 'utf-8',
	'mail.SMTPAuth'			=> false,
	'mail.Username'			=> '',
	'mail.password'			=> '',
	'mail.Host'				=> '',

// db
	'db.driver'  			=> 'mysql',
	'db.host'  				=> 'localhost',
	'db.db'  				=> 'database',
	'db.user'  				=> 'user',
	'db.pass'  				=> 'pass',
	'db.encoding'  			=> 'utf8',
);
