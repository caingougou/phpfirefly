<?php
class Controller {
	public $params;
	public $view;
	public $helper;
	public $layout;
	public $page_title;
	public $auto_render = true;

	public function __construct() {
		preg_match('/(\w+)Controller/i', get_class($this), $match);
		$this->controller_name = $match[1];

		if (is_subclass_of($this, 'ApplicationController')) {
			$application_vars = get_class_vars('ApplicationController');

			if (isset ($application_vars['helper']) && !empty ($application_vars['helper']) && is_array($this->helper)) {
				$diff = array_diff($application_vars['helper'], $this->helper);
				$this->helper = array_merge($this->helper, $diff);
			}
		}
	}

	public function set($key, $value = null) {
		if (is_array($key)) {
			$this->set_view_vars($key);
		} else {
			$this->set_view_vars(array ( $key => $value ));
		}
	}

	private function set_view_vars($data) {
		foreach ($data as $key => $value) {
			if ($key == 'title') {
				$this->set_page_title($value);
			} else {
				$this->view->{$key} = $value;
			}
		}
	}

	public function set_page_title($title) {
		$this->page_title = $title;
	}

	public function render($info = null) {
		$this->auto_render = false;
		$this->before_render();

		$response = $this->view->render($info);
		echo $response;
	}

	function before_filter() {
	}

	function before_render() {
	}

	function after_filter() {
	}
}
?>
