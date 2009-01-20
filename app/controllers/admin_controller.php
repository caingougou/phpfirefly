<?php
class AdminController extends ApplicationController {
	public $layout = 'posts';

	public function index() {
		pr(Router :: available_controllers());
	}

	public function login() {
		$this->logger->info(Router :: url_rewritable(), __FILE__, __LINE__);
		$this->logger->warn(print_r(Router :: available_controllers(), true), __FILE__, __LINE__);
		$_SESSION['user'] = 'test';
		$array = array(0 => 'blue', 1 => 'red', 2 => 'green', 3 => 'red', array(4 => 'yello'));

		$key = array_search('green', $array); // $key = 2;
		pr($key);
		$key = array_search('red', $array);   // $key = 1;
		pr($key);
		$key = array_search('yello', $array);   // $key = 1;
		pr($key);
	}

	public function logout() {
		$_SESSION['user'] = null;
		$this->redirect_to('login');
	}

}
?>
