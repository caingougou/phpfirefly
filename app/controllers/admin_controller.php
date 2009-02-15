<?php
class AdminController extends ApplicationController {
	public $layout = 'posts';
	public $helper = array ( 'javascript' );

	public function index() {
		pr(FIREFLY_BASE_DIR . DS . 'tmp' . DS . 'sessions');
		$this->flash('test', 'login');
		$this->flash->set('notice', 'user login!');
		$this->flash->now('now_msg', 'user messages');
	}

	public function login() {
		$this->logger->info($this->helper[0], __FILE__, __LINE__);
		$this->logger->info(Router :: url_rewritable(), __FILE__, __LINE__);
		$this->logger->warn(print_r(Router :: available_controllers(), true), __FILE__, __LINE__);

		$this->page_title = "user login";
		$_SESSION['user'] = 'test';
		$array = array (
			0 => 'blue',
			1 => 'red',
			2 => 'green',
			3 => 'red',
			array (
				4 => 'yello'
			)
		);

		$key = array_search('green', $array); // $key = 2;
		pr($key);
		$key = array_search('red', $array); // $key = 1;
		pr($key);
		$key = array_search('yello', $array); // false;
		pr($key);
		$this->test = "test";
		$this->set('text', 'file');
		if (isset ($this->helper['txt'])) {
			pr($this->helper['txt']);
		}
	}

	public function logout() {
		$_SESSION['user'] = null;
		$this->redirect_to('login');
	}

}
?>
