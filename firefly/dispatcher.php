<?php
class Dispatcher {
	private $request;
	private $response;
	private $params;
	private $controller;

	public function dispatch() {
		//ob_start('ob_gzhandler');
		ob_start();
		$this->request = new Request();
		$this->response = new Response();
		$this->process();
		$body = ob_get_clean();
		echo $body;
	}

	private function process() {
		// parse router and request
		$this->params = $this->request->parameters();

		$class_name = $this->params['controller'] . "Controller";
		$this->controller = new $class_name();
		$this->controller->params = $this->params;
		$this->controller->request = $this->request;
		$this->controller->response = $this->response;

		// TODO: revoke AOP Interceptor plugins here.
		$this->controller->before_filter();
		$this->render();
		$this->controller->after_filter();
		$this->debug();
	}

	private function render() {
		/**
		 * if request action exists in controller
		 * 		invoke controller->action
		 * else if request action file exists under views folder (for controller clean)
		 * 		render action file
		 * else
		 * 		render method_missing template
		 */
		if(in_array($this->params['action'], get_class_methods(get_class($this->controller)))) {
			call_user_func(array($this->controller, $this->params['action']));
		}
		elseif(file_exists(FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . $this->params['action'] . '.php')) {
			$this->controller->render(FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . $this->params['action'] . '.php');
		} else {
			call_user_func_array(array($this->controller, 'method_missing'), $this->params);
		}

		if($this->controller->auto_render) {
			$this->controller->render();
		}
	}

	private function debug() {
		if(DEBUG) {
			$this->controller->debug($this->controller);
		}
	}

}
?>