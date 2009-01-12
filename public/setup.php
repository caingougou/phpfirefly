<?php
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'environment.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'boot.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'firefly' . DIRECTORY_SEPARATOR . 'dispatcher.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'firefly' . DIRECTORY_SEPARATOR . 'functions.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'firefly' . DIRECTORY_SEPARATOR . 'router.php');
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'firefly' . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'controller.php');

// 1. Check url rewrite enabled.
$url_rewritable = Router :: url_rewritable();
if (!$url_rewritable) {
	if (function_exists('apache_get_modules')) {
		$htaccess_file = APP_ROOT . DS . '.htaccess';
		$contents = "\tRewriteEngine on \n\n\tRewriteCond %{REQUEST_FILENAME} !-f \n\tRewriteCond %{REQUEST_FILENAME} !-d \n\tRewriteRule ^(.*)$ dispatch.php?fireflypath=$1 [QSA,L]\n";
		if (is_writable($htaccess_file)) {
			file_put_contents($htaccess_file, $contents);
		} else {
			echo "The file <b>$htaccess_file</b> is not writable, add below content<pre>$contents</pre> to <b>$htaccess_file</b>";
		}
	} else {
		echo "please include web server url rewrite module!";
	}
} else {
	echo "URL rewrite is enabled!";
}
echo "<br />";

// 2. Check database connection config.
// TODO:
echo "<br />";

// 3. Check log file writable.
// TODO:
echo "<br />";
?>