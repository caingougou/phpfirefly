<?php
// globals functions, will be removed.
include_once(FIREFLY_LIB_DIR . DS . 'functions.php');

include_once(FIREFLY_LIB_DIR . DS . 'router.php');
include_once(FIREFLY_LIB_DIR . DS . 'firefly_exception.php');
include_once(FIREFLY_LIB_DIR . DS . 'controller' . DS . 'controller.php');

class Dispatcher {
	private $request;
	private $response;
	private $params;
	private $controller;

	public function dispatch() {
		$this->request = new Request;
		$this->response = new Response;
		$this->process();
	}

	private function process() {
		Session :: start();
		$this->params = $this->request->parameters();
		$class_name = $this->params['controller'] . "Controller";
		$this->controller = new $class_name($this->request, $this->response, $this->params);
		$this->controller->before_filter();
		$this->render();
		$this->controller->after_filter();
	}

	/**
	 * if request action exists in controller
	 * 		invoke controller->action
	 * else if request action file exists under views folder (for controller clean)
	 * 		render action file
	 * else
	 * 		render method_missing template
	 */
	private function render() {
		if(in_array($this->params['action'], get_class_methods(get_class($this->controller)))) {
			call_user_func(array($this->controller, $this->params['action']));
		}
		elseif(file_exists(FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . $this->params['action'] . '.php')) {
			$this->controller->render(FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . $this->params['action'] . '.php');
		} else {
			call_user_func(array($this->controller, 'action_missing'));
		}

		if($this->controller->rendered === false) {
			$this->controller->render();
		}
	}

}
?>