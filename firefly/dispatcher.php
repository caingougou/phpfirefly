<?php
include_once('router.php');

class Dispatcher {

	public function dispatch() {
		$params = $this->get_params();
		$class_name = $params['controller_name'] . "Controller";
		$controller = new $class_name();
		$controller->params = $params;

		$controller->before_filter();
		$controller->view = new View($controller);
		call_user_func(array($controller, $params['action_name']));
		if($controller->auto_render) {
			$controller->render();
		}
		$controller->after_filter();
	}

	private function get_params() {
		$params = Router :: parse();
		return $params;
	}

}
?>