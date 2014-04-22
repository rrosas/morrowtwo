<!doctype html>
<html>
<head>
	<title></title>
</head>
<body>	

<pre>
<?php

ob_start();

// init Codesniffer
echo "Running CodeSniffer\n-------------------\n";
echo shell_exec('vendor/bin/phpcs --version');
echo shell_exec("vendor/bin/phpcs --standard=".__DIR__."/Ministry ../trunk/main/vendor/Morrow 2>&1");

// init Mess Detector
echo "\nRunning Mess Detector\n-------------------\n";
//echo "\nNaming Rules\n-------------------";
//echo shell_exec("vendor/bin/phpmd ../trunk/main/vendor/Morrow text naming 2>&1");
echo "\nUnused Rules\n-------------------";
echo shell_exec("vendor/bin/phpmd ../trunk/main/vendor/Morrow text unusedcode 2>&1");
echo "\nCodesize Rules\n-------------------";
echo shell_exec("vendor/bin/phpmd ../trunk/main/vendor/Morrow text codesize 2>&1");
echo "\nDesign Rules\n-------------------";
echo shell_exec("vendor/bin/phpmd ../trunk/main/vendor/Morrow text design 2>&1");

echo 'Done: ' . date('Y-m-d H:i:s');

$content = ob_get_clean();
$content = str_replace(realpath('../trunk/main/vendor/Morrow') . '/', '', $content);
echo $content;
?>
</pre>

</body>
</html>
