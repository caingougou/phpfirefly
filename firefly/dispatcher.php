<?php
include_once ('router.php');

class Dispatcher {

	public function dispatch() {
		$params = $this->get_params();
		$class_name = $params['controller_name'] . "Controller";
		$controller = new $class_name();
		$controller->params = $params;
		$controller->before_filter();
		$controller->view = new View($controller);
		call_user_func(array ( $controller, $params['action_name'] ));
		if($controller->auto_render) $controller->render();
		$controller->after_filter();
	}

	private function get_params() {
		$params = array ();
		$params['path'] = $_GET['path'];
		$params['form'] = $_POST;

		// file uploader
		foreach ($_FILES as $name => $data) {
			$params['form'][$name] = $data;
		}

		// hack HTTP PUT/DELETE methods for restful request.
		if (isset ($params['form']['_method'])) {
			$_SERVER['REQUEST_METHOD'] = $params['form']['_method'];
			unset ($params['form']['_method']);
		}

		// parse router, strategy pattern.
		$params = array_merge(Router :: parse(), $params);

		if (empty ($params['action_name'])) {
			$params['action_name'] = 'index';
		}

		return $params;
	}
}
?>