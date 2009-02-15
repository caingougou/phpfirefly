<?php
class View {
	public static function factory($type = 'php') {
		if ($type == 'php') {
			$classname = __CLASS__;
			return new $classname;
		}
		elseif (include_once (FIREFLY_PLUGINS_DIR . DS . $type . '_view.php')) {
			return new $type;
		} else {
			throw new FireflyException('Can not find view strategy: ' . $type, E_USER_ERROR);
		}
	}
}
?>