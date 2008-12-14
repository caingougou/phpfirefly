<?php

// current environment: development, test and production
define('ENVIRONMENT', 'development');


include_once ("environments" . DIRECTORY_SEPARATOR . ENVIRONMENT . ".php");

// application boot setting.
include_once ("boot.php");
?>