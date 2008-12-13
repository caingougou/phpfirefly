<?php
include_once (FIREFLY_BASE_DIR . DS . 'config' . DS . 'router.php');

class Router {
	public static function parse(){
		$params = array();
		foreach($_GET as $key => $value) {
			if($key == 'path') {
				$rs = explode("/", $value);
				$params['controller_name'] = $rs[0];
				$params['action_name'] = $rs[1];
			} else {
				$params[$key] = $value;
			}
		}

		return $params;
	}
}
?>
