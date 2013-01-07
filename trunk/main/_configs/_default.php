<?php

return array(
// debug
	'debug.output.screen'	=> true,
	'debug.output.headers'	=> false,
	'debug.output.flatfile'	=> false,
	'debug.password'		=> 'password',

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

// languages
	'languages'    			=> array('en'),

// routing rules
	'routing' => array(
		'/' => 'home/',
	),
);