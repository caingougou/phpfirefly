<?php
include_once ('view_base.php');

class View extends ViewBase {

	public static function factory($request, $response, $controller, $type = 'php') {
		if ($type == 'php') {
			$classname = __CLASS__;
			return new $classname ($request, $response, $controller);
		}
		elseif (include_once (FIREFLY_PLUGINS_DIR . DS . $type . '_view.php')) {
			return new $type ($request, $response, $controller);
		} else {
			throw new FireflyException('Can not find view strategy: ' . $type, E_USER_ERROR);
		}
	}

}
?>