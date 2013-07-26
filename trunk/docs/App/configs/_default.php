<?php

// DO NOT CHANGE THIS FILE
// CREATE AN EXTRA FILE FOR YOUR HOST/IP

// the session should always be valid only for the current project
$session_path = str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])).'/');

// we have to take care of the basehref depth
// if the projects public folder is not the document root
if (isset($_GET['morrow_basehref_depth'])) {
	$session_path = preg_replace('|([^/]+/){'.intval($_GET['morrow_basehref_depth']).'}$|', '', $session_path); // Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.
}

return array(
// debug
	'debug.output.screen'		=> true,
	'debug.output.file'			=> true,
	'debug.file.path'			=> APP_PATH .'logs/error_'. date('Y-m-d') .'.txt',
	
// languages
	'languages'					=> array('en'),
	
// locale/timezone
	'locale.timezone'			=> 'Europe/Berlin',
	
// routing rules
	'routing'					=> array(
		'/'						=> 'home/',
		'class/*path'			=> 'class/',
	),
	
// log
	'log.file.path'				=> APP_PATH .'logs/log_'. date('Y-m-d') .'.txt',
	
// session
	'session.save_path'			=> APP_PATH . 'temp/sessions/', // The path where all sessions are stored (it is also possible to use stream wrappers)
	'session.gc_probability'	=> 1, // In conjunction with gc_divisor it is used to manage probability that the gc (garbage collection) routine is started.
	'session.gc_divisor'		=> 100, // session.gc_divisor coupled with session.gc_probability defines the probability that the gc (garbage collection) process is started on every session initialization. The probability is calculated by using gc_probability/gc_divisor, e.g. 1/100 means there is a 1% chance that the GC process starts on each request.
	'session.gc_maxlifetime'	=> 1440, // Specifies the number of seconds after which data will be seen as 'garbage' and potentially cleaned up. 
	'session.cookie_lifetime'	=> 0, // Lifetime of the session cookie, defined in seconds.
	'session.cookie_path'		=> $session_path, // Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.
	'session.cookie_domain'		=> '', // Cookie domain, for example 'www.php.net'. To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.php.net'.
	'session.cookie_secure'		=> false, // If TRUE cookie will only be sent over secure connections.
	'session.cookie_httponly'	=> true, // If set to TRUE then PHP will attempt to send the httponly flag when setting the session cookie.
	
// OPTIONAL: the following config vars are NOT neccessary for the framework to run
// mailer
	'mail.Mailer'				=> 'mail',
	'mail.From'					=> 'test@example.com',
	'mail.FromName'				=> 'John Doe',
	'mail.WordWrap'				=> 0,
	'mail.Encoding'				=> 'quoted-printable',
	'mail.CharSet'				=> 'utf-8',
	'mail.SMTPAuth'				=> false,
	'mail.Username'				=> '',
	'mail.password'				=> '',
	'mail.Host'					=> '',
	
// db
	'db.driver'					=> 'mysql',
	'db.host'					=> 'localhost',
	'db.db'						=> 'database',
	'db.user'					=> 'user',
	'db.pass'					=> 'pass',
	'db.encoding'				=> 'utf8',
);
