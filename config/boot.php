<?php


/**
 * boot.php
 *
 * define application constants
 * include application configure
 * auto load classes
 */

define('DS', DIRECTORY_SEPARATOR);
define('FIREFLY_BASE_DIR', str_replace(DS . 'config' . DS . 'boot.php', '', __FILE__));
define('FIREFLY_LIB_DIR', FIREFLY_BASE_DIR . DS . 'firefly');
define('FIREFLY_APP_DIR', FIREFLY_BASE_DIR . DS . 'app');
define('APP_LIB_DIR', FIREFLY_BASE_DIR . DS . 'lib');

session_start();

set_include_path(get_include_path() . PATH_SEPARATOR . FIREFLY_LIB_DIR);

// globals functions
include_once(FIREFLY_LIB_DIR . DS . 'functions.php');
include_once(FIREFLY_LIB_DIR . DS . 'router.php');
include_once(FIREFLY_BASE_DIR . DS . 'config' . DS . 'routes.php');
include_once(FIREFLY_LIB_DIR . DS . 'controller' . DS . 'controller.php');

/**
 * auto include app controllers/models/helpers class files.
 * auto include firefly controller/model/view and other lib class files.
 */
function __autoload($class_name) {
	if(preg_match('/\w+Controller/', $class_name)) {
		// include app controllers
		include_once(FIREFLY_APP_DIR . DS . 'controllers' . DS . str_replace('Controller', '', $class_name) . '_controller.php');
	}
	elseif(preg_match('/\w+Helper/', $class_name)) {
		// include app helers
		include_once(FIREFLY_APP_DIR . DS . 'helpers' . DS . str_replace('Helper', '', $class_name) . '_helper.php');
	}
	elseif(file_exists(FIREFLY_APP_DIR . DS . 'models' . DS . strtolower($class_name) . '.php')) {
		// include app models
		include_once(FIREFLY_APP_DIR . DS . 'models' . DS . strtolower($class_name) . '.php');
		if($class_name != 'activerecords') {
			//$tmp = new $class_name;
			call_user_func(array($class_name, '_init'), $class_name); // becase of STATIC PROBLEMS in php versions below 5.3.0
		}
	}
	elseif(file_exists(FIREFLY_LIB_DIR . DS . 'controller' . DS . strtolower($class_name) . '.php')) {
		// include firefly controller lib
		include_once(FIREFLY_LIB_DIR . DS . 'controller' . DS . strtolower($class_name) . '.php');
	}
	elseif(file_exists(FIREFLY_LIB_DIR . DS . 'model' . DS . strtolower($class_name) . '.php')) {
		// include firefly model lib
		include_once(FIREFLY_LIB_DIR . DS . 'model' . DS . strtolower($class_name) . '.php');
	}
	elseif(file_exists(FIREFLY_LIB_DIR . DS . 'model' . DS . 'database_adapters' . DS .strtolower($class_name) . '.php')) {
		// include firefly database adapters lib
		include_once(FIREFLY_LIB_DIR . DS . 'model' . DS . 'database_adapters' . DS .strtolower($class_name) . '.php');
	}
	elseif(file_exists(FIREFLY_LIB_DIR . DS . 'view' . DS . strtolower($class_name) . '.php')) {
		// include firefly view lib
		include_once(FIREFLY_LIB_DIR . DS . 'view' . DS . strtolower($class_name) . '.php');
	}
	elseif(file_exists(FIREFLY_LIB_DIR . DS . strtolower($class_name) . '.php')) {
		// include firefly lib
		include_once(FIREFLY_LIB_DIR . DS . strtolower($class_name) . '.php');
	}
	elseif(file_exists(APP_LIB_DIR . DS . strtolower($class_name) . '.php')) {
		// include app libs add by user.
		include_once(APP_LIB_DIR . DS . strtolower($class_name) . '.php');
	}
}
?>