<?php

// If you want to modify defaults for your project (like routing rules), use this file.

return array(
// routing rules
	'routing'					=> array(
		''						=> 'home',
		'class/(?P<path>.+)'	=> 'class',
		'page/(?P<id>.+)'		=> 'page',
	),
);
