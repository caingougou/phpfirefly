<?php
class Helpers {
	private function __construct() {
	}

	public static function include_helpers($controller_name, $helpers) {
		$application_vars = get_class_vars('ApplicationController');
		self :: include_helper($controller_name);
		self :: include_helper($helpers);
		self :: include_helper($application_vars['helper']);
	}

	private static function include_helper($helpers) {
		if ($helpers && !is_array($helpers)) {
			self :: include_helper_file($helpers);
		}
		elseif (is_array($helpers)) {
			foreach ($helpers as $helper) {
				self :: include_helper_file($helper);
			}
		}
	}

	private static function include_helper_file($helper_file) {
		$file_name = strtolower($helper_file) . '_helper.php';
		$firefly_helpers_path = FIREFLY_LIB_DIR . DS . 'helpers' . DS . $file_name;
		$app_helers_path = FIREFLY_APP_DIR . DS . 'helers' . DS . $file_name;
		$plugins_helpers_path = FIREFLY_PLUGINS_DIR . DS . $file_name;
		if (file_exists($firefly_helpers_path)) {
			include_once $firefly_helpers_path;
		}
		elseif (file_exists($app_helers_path)) {
			include_once $app_helers_path;
		}
		elseif (file_exists($plugins_helpers_path)) {
			include_once $plugins_helpers_path;
		}
	}

}
?>
