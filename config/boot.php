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
define('APP_ROOT', FIREFLY_BASE_DIR . DS . 'public');
define('APP_LIB_DIR', FIREFLY_BASE_DIR . DS . 'lib');
define('FIREFLY_PLUGINS_DIR', FIREFLY_BASE_DIR . DS . 'plugins');

set_include_path(get_include_path() . PATH_SEPARATOR . FIREFLY_LIB_DIR . PATH_SEPARATOR . FIREFLY_PLUGINS_DIR);

/**
 * auto include app controllers/models/helpers class files.
 * auto include firefly controller/model/view and other lib class files.
 */
function __autoload($class_name) {
	if (preg_match('/\w+Controller$/', $class_name)) {
		// include app controllers
		// TODO: module controllers include (example: controllers/module/test_controller.php).
		include_once (FIREFLY_APP_DIR . DS . 'controllers' . DS . strtolower(str_replace('Controller', '', $class_name)) . '_controller.php');
	}
	elseif (file_exists(FIREFLY_APP_DIR . DS . 'models' . DS . strtolower($class_name) . '.php')) {
		// include app models
		include_once (FIREFLY_APP_DIR . DS . 'models' . DS . strtolower($class_name) . '.php');
		if ($class_name != 'activerecords') {
			//$tmp = new $class_name;
			//$tmp->_init();
			// because of STATIC PROBLEMS in php versions below 5.3.0
			//call_user_func(array($class_name, '_init'), $class_name);
		}
	}
	elseif (file_exists(FIREFLY_LIB_DIR . DS . 'controller' . DS . strtolower($class_name) . '.php')) {
		// include firefly controller lib
		include_once (FIREFLY_LIB_DIR . DS . 'controller' . DS . strtolower($class_name) . '.php');
	}
	elseif (file_exists(FIREFLY_LIB_DIR . DS . 'model' . DS . strtolower($class_name) . '.php')) {
		// include firefly model lib
		include_once (FIREFLY_LIB_DIR . DS . 'model' . DS . strtolower($class_name) . '.php');
	}
	elseif (file_exists(FIREFLY_LIB_DIR . DS . 'model' . DS . 'database_adapters' . DS . strtolower($class_name) . '.php')) {
		// include firefly database adapters lib
		include_once (FIREFLY_LIB_DIR . DS . 'model' . DS . 'database_adapters' . DS . strtolower($class_name) . '.php');
	}
	elseif (file_exists(FIREFLY_LIB_DIR . DS . 'view' . DS . strtolower($class_name) . '.php')) {
		// include firefly view lib
		include_once (FIREFLY_LIB_DIR . DS . 'view' . DS . strtolower($class_name) . '.php');
	}
	elseif (file_exists(FIREFLY_LIB_DIR . DS . strtolower($class_name) . '.php')) {
		// include firefly lib
		include_once (FIREFLY_LIB_DIR . DS . strtolower($class_name) . '.php');
	}
	elseif (file_exists(APP_LIB_DIR . DS . strtolower($class_name) . '.php')) {
		// include app libs add by user.
		include_once (APP_LIB_DIR . DS . strtolower($class_name) . '.php');
	}

}
?>