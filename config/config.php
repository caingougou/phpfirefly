<?php
error_reporting(E_ALL);

define('DEBUG_LEVEL', 'debug'); // debug, info, warn, error, null.

define('LOG_LOCATION', 'page'); // file, page.

define('FLASH_PAGE', 1); // 0. show flash message in next page, 1. show flash message in a redirect page.

define('ENVIRONMENT', 'development'); // development, test, production

define('SESSION_STORE_STRATEGY', 'default'); // default, db, memcached, none

define('VIEW', 'php'); // view template name, default is php.
?>
