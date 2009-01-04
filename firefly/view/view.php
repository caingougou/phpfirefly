<?php
class View {
	public static function factory($type = 'php') {
		if($type == 'php') {
			$classname = __CLASS__;
			return new $classname;
		}
		elseif(include_once('view_' . strtolower($type) . '.php')) {
			$classname = 'view' . $type;
			return new $classname;
		}
		elseif(include_once('plugins' . DS . 'views' . DS . $type . '.php')) {
			return new $type;
		} else {
			trigger_error('Can not find view strategy: ' . $type, E_USER_ERROR);
		}
	}
}
?>