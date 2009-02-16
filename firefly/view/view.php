<?php
include_once ('view_base.php');

class View extends ViewBase {

	public static function factory($controller, $response, $type = 'php') {
		if ($type == 'php') {
			$classname = __CLASS__;
			return new $classname ($controller, $response);
		}
		elseif (include_once (FIREFLY_PLUGINS_DIR . DS . $type . '_view.php')) {
			return new $type ($controller, $response);
		} else {
			throw new FireflyException('Can not find view strategy: ' . $type, E_USER_ERROR);
		}
	}

}
?>