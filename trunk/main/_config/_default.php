<?php

### url
$config['url.modrewrite']   = 1;

### mailer
$config['mail.Mailer']			= 'mail';
$config['mail.From']			= 'test@morrowtest.com';
$config['mail.FromName']		= 'MorrowTwo';
$config['mail.WordWrap']		= 0;
$config['mail.Encoding']		= 'quoted-printable';
$config['mail.CharSet']			= 'utf-8';
$config['mail.SMTPAuth']		= false;
$config['mail.Username']		= '';
$config['mail.password']		= '';
$config['mail.Host']			= '';

### db
$config['db.driver']  			= 'mysql';
$config['db.host']  			= 'localhost';
$config['db.db']  				= 'database';
$config['db.user']  			= 'user';
$config['db.pass']  			= 'pass';
$config['db.encoding']  		= 'utf8';

### languages
$config['languages']    = array('en', 'de');

### session
$config['session.handler']   = '';

### routing rules
$config['routing'] = array(
	'/' => 'home/',
);
