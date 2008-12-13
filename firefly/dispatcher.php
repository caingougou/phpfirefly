<?php

//require_once(FIREFLY_LIB_DIR . S . 'router.php');

class Dispatcher {
	private $controller;
	private $action;

	public function dispatch() {
		$this->recognize();
		$this->controller = $this->get_controller();
		call_user_func_array(array($this->controller, $this->action), array());
		$response = "xx";
//		echo $response;
	}

	private function get_controller(){
		$class_name = $this->controller . "Controller";
		return new $class_name();
	}

	private function recognize(){
		$this->controller = "test";
		$this->action = "index";
	}

}
?>