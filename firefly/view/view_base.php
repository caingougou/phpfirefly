<?php
include_once ('render_options.php');

class ViewBase {
	private $first_render = true;
	private $request;
	private $response;
	private $controller;

	public function __construct($request, $response, $controller) {
		$this->controller = $controller;
		$this->request = $request;
		$this->response = $response;
	}

	final public function render($options) {
		$render_options = new RenderOptions($this->request, $this->response, $this->controller);
		if ($this->first_render) {
			// render options from controller.
			$this->first_render = false;
		} else {
			if (empty ($options['layout'])) {
				// render options from view.
				$options['layout'] = false;
			}
		}
		$options = $render_options->parse($options);
		$this->response->send_headers();

		$vars = array_merge(get_object_vars($this->controller), $options['locals']);
		extract($vars, EXTR_SKIP);
		$controller_name = isset ($controller_name) ? $controller_name : $this->controller->params['controller'];
		$action_name = isset ($action_name) ? $action_name : $this->controller->params['action'];

		ob_start();
		if (isset ($options['content'])) {
			echo $options['content'];
		} else {
			require $options['template'];
		}
		$content_for_layout = ob_get_clean();
		if ($options['layout']) {
			ob_start();
			require $options['layout'];
			echo ob_get_clean();
		} else {
			echo $content_for_layout;
		}
	}

	final private function url_for($options) {
		return $this->controller->url_for($options);
	}

	final private function debug($object) {
		echo "debug:";
		new Debugger($object);
	}
}
?>
