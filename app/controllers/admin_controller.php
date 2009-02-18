<?php
class AdminController extends ApplicationController {
	public $layout = 'posts';
	public $helper = array ( 'javascript' );

	public function index() {
		$this->flash('test', 'login');
		$this->flash->set('notice', 'user login!');
		$this->flash->now('now_msg', 'user messages');
		$this->action_name = "index of admin controller.";
//		$this->render(array('partial' => 'test/form', 'locals' => array('method' => 'get')));
	}

	public function login() {
		$this->logger->info($this->helper[0], __FILE__, __LINE__);
		$this->logger->info(Router :: url_rewritable(), __FILE__, __LINE__);
		$this->logger->warn(print_r(Router :: available_controllers(), true), __FILE__, __LINE__);

		$this->page_title = "user login";
		$_SESSION['user'] = 'test';
		$array = array ( 0 => 'blue', 1 => 'red', 2 => 'green', 3 => 'red', array ( 4 => 'yello' ) );

		$this->test = "test";
		$this->set('text', 'file');
	}

	public function logout() {
		$_SESSION['user'] = null;
		$this->redirect_to('login');
	}

}
?>
