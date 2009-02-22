<?php
defined('ENVIRONMENT') ? null : define('ENVIRONMENT', 'development'); // development, production.

include_once ("environments" . DIRECTORY_SEPARATOR . ENVIRONMENT . ".php");
include_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'boot.php');
?>