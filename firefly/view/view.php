<?php
class View {
	public $helper;
	public $layout;
	public $controller_name;
	public $action_name;
	public $page_title;
	public $tempalte;
	public $params;

	public function __construct($controller) {
		$this->params = $controller->params;
		$this->helper = $controller->helper;
		$this->layout = $controller->layout;
		$this->action_name = $this->params['action_name'];
		$this->controller_name = $this->params['controller_name'];
		$this->page_title = $controller->page_title ? $controller->page_title : $this->controller_name . '::' . $this->action_name;
		$this->template = FIREFLY_APP_DIR . DS . 'views' . DS . strtolower($this->controller_name) . DS . $this->action_name . '.php';
	}

	public function render($view = null) {
		$view_vars = get_object_vars($this);
		foreach ($view_vars as $key => $value) {
			$$key = $value;
		}

		ob_start();
		if (isset ($view)) {
			if (file_exists($view)) {
				include ($view);
			} else {
				echo $view;
			}
		} else {
			include ($this->template);
		}
		$out = ob_get_clean();
		return $out;
	}

}
?>
