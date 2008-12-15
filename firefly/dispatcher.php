<?php
class Dispatcher {

	public function dispatch() {
		// parse router and return params
		$params = $this->get_params();

		$class_name = $params['controller'] . "Controller";
		$controller = new $class_name();
		$controller->params = $params;

		// AOP Interceptor plugins
		$controller->before_filter();
		$controller->view = new View($controller);

		/**
		 * if request action exists in controller
		 * 		invoke controller->action
		 * else if request action file exists under views folder (for controller clean)
		 * 		render action file
		 * else
		 * 		render method_missing template
		 */
		if(in_array($params['action'], get_class_methods($class_name))) {
			call_user_func(array($controller, $params['action']));
		}
		elseif(file_exists(FIREFLY_APP_DIR . DS . 'views' . DS . $params['controller'] . DS . $params['action'] . '.php')) {
			$controller->render(FIREFLY_APP_DIR . DS . 'views' . DS . $params['controller'] . DS . $params['action'] . '.php');
		} else {
			call_user_func(array($controller, 'method_missing'), $params);
		}

		if($controller->auto_render) {
			$controller->render();
		}

		$controller->after_filter();

		// debug controller info
		debug($controller);
	}

	private function get_params() {
		$params = Router :: parse();
		return $params;
	}

}
?>