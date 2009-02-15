<?php
error_reporting(E_ALL);

define('DEBUG', 1); // 0. no debug, 1. debug.

define('DEBUG_LEVEL', 'debug'); // debug, info, warn, error.

define('LOG_COLORING', 1); // 0. log disable coloring, 1. log enable coloring.

define('LOG_LOCATION', 'page'); // file, page.

define('FLASH_MESSAGE', 1); // 0. show flash message in next page, 1. show flash message in a redirect page.

define('ENVIRONMENT', 'development'); // development, test, production

define('SESSION_STORE_STRATEGY', 'default'); // default, db, memcached, none
?>
