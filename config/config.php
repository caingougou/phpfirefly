<?php
error_reporting(E_ALL);

define('DEBUG', 1); // 0. production, 1. development.

define('LOG_COLORING', 1); // 0. log disable coloring, 1. log enable coloring.

define('LOG_LOCATION', 'page'); // append logs to: file, page.

define('ENVIRONMENT', 'development'); // current environment: development, test and production

define('SESSION_STORE_STRATEGY', 'default'); //session store: default, db, memcached, none
?>
