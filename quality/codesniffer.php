<!doctype html>
<html>
<head>
	<style>
	body { white-space: pre; }
	</style>

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
	<script>
	$(function(){
		$('a').click(function(e){
			e.preventDefault();
			$.get($(this).attr('href'));
		});
	});
	</script>
</head>
<body>	

<?php

if (isset($_GET['file'])) {
	$command = 'export DISPLAY=:0; geany '.$_GET['file'].':'.$_GET['line'].' &';
	shell_exec($command);
	die();
}

header('Content-Type: text/html; charset=utf-8');

$root = realpath(__DIR__ . '/../trunk');
$command = "cd $root; ../quality/PHP_CodeSniffer-1.4.3/scripts/phpcs --standard=Ministry --ignore=_libs/PHPMailer_v5.0.2,_libs/Zend,_libs/serpent_1.3,main/_libs --extensions=php .";
//print_r($command);
//die();
preg_match_all("|FILE: (.+?)\n.+?\n\n|is", `$command`, $matches, PREG_SET_ORDER);

foreach ($matches as $block) {
	$file = $block[1];
	$full = preg_replace("_ (\d+) \|_s", ' <a href="?file='.urlencode($file).'&amp;line=$1">$1</a> |', $block[0]);
	echo $full;
}
?>

</body>
</html>
