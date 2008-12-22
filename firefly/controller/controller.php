<?php
class Controller {
	public $params;
	public $view;
	public $helper;
	public $layout;
	public $page_title;

	public $logger;
	public $request;
	public $response;
	public $auto_render = true;

	public function __construct() {
		// preg_match('/(\w+)Controller/i', get_class($this), $match); // for check $match[1] == $params['controller']
		// constructor inject
		$this->logger = new Logger();
		$this->request = new Request();
		$this->response = new Response();

		if(is_subclass_of($this, 'ApplicationController')) {
			$application_vars = get_class_vars('ApplicationController');
			if(!is_array($this->helper)) {
				$this->helper = array($this->helper);
			}
			if(isset($application_vars['helper']) && !empty($application_vars['helper'])) {
				$diff = array_diff($application_vars['helper'], $this->helper);
				$this->helper = array_merge($this->helper, $diff);
			}
		}
	}

	public function debug($object, $file_name = __FILE__, $line = __LINE__) {
		$this->logger->debug($object, $file_name, $line);
	}

	public function warn($warn, $file_name = __FILE__, $line = __LINE__) {
		$this->logger->warn($warn, $file_name, $line);
	}

	public function info($info, $file_name = __FILE__, $line = __LINE__) {
		$this->logger->info($info, $file_name, $line);
	}

	public function set($key, $value = null) {
		if(is_array($key)) {
			$this->set_view_vars($key);
		} else {
			$this->set_view_vars(array($key => $value));
		}
	}

	private function set_view_vars($data) {
		foreach($data as $key => $value) {
			if($key == 'title') {
				$this->set_page_title($value);
			} else {
				$this->view-> {
					$key }
				= $value;
			}
		}
	}

	public function set_page_title($title) {
		$this->page_title = $title;
	}

	public function redirect_to($url) {
		header($url);
	}

	/**
	 * ajax
	 * patial
	 * file
	 * inline code
	 * text
	 * 404
	 * blank
	 * layout
	 */
	public function render($info = null) {
		$this->auto_render = false;
		$this->before_render();

		$response = $this->view->render($info);

		$this->after_render();
		echo $response;
	}

	public function before_filter() {
	}

	public function before_render() {
	}

	public function after_render() {
	}

	public function after_filter() {
	}

	public function method_missing($params) {
		$this->render(FIREFLY_LIB_DIR . DS . 'view' . DS . 'method_missing.php');
	}

	public function __toString() {
		return get_class($this);
	}
}
?>