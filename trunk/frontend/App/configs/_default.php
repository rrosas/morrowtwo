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
		'/'							=> 'home/',
	),
	
// log
	'log.file.path'				=> APP_PATH .'logs/log_'. date('Y-m-d') .'.txt',
	
// session
	'session.save_path'			=> 'file://' . APP_PATH . 'temp/sessions/', // The path where all sessions are stored (it is also possible to use stream wrappers)
	'session.cookie_lifetime'	=> 0, // Lifetime of the session cookie, defined in seconds.
	'session.cookie_path'		=> $session_path, // Path on the domain where the cookie will work. Use a single slash ('/') for all paths on the domain.
	'session.cookie_domain'		=> '', // Cookie domain, for example 'www.php.net'. To make cookies visible on all subdomains then the domain must be prefixed with a dot like '.php.net'.
	'session.cookie_secure'		=> false, // If TRUE cookie will only be sent over secure connections.
	'session.cookie_httponly'	=> true, // If set to TRUE then PHP will attempt to send the httponly flag when setting the session cookie.
	
// OPTIONAL: the following config vars are NOT neccessary for the framework to run
// mailer
	'mail.Mailer'				=> 'mail',
	'mail.From'					=> 'test@morrowtest.com',
	'mail.FromName'				=> 'MorrowTwo',
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
