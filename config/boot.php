<?php

/**
 * Boot.php
 *
 * define application constants
 */

define('DS', DIRECTORY_SEPARATOR);
define('FIREFLY_BASE_DIR', str_replace(DS . 'config' . DS . 'boot.php', '', __FILE__));
define('FIREFLY_LIB_DIR', FIREFLY_BASE_DIR . DS . 'firefly');
define('FIREFLY_APP_DIR', FIREFLY_BASE_DIR . DS . 'app');

function __autoload($class_name) {
    require_once FIREFLY_BASE_DIR . DS . 'app' . DS . 'controllers' . DS . str_replace('Controller', '', $class_name) . '_controller.php';
}

?>

