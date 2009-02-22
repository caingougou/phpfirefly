<?php
class AdminController extends ApplicationController {
	public $layout = 'posts';
	public $helper = array('javascript');

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
		$array = array(0 => 'blue', 1 => 'red', 2 => 'green', 3 => 'red', array(4 => 'yello'));

		$this->test = "test";
		$this->set('text', 'file');
	}

	public function logout() {
		$_SESSION['user'] = null;
		$this->redirect_to('login');
	}

	public function renders() {
		$this->flash->keep('test');
		$this->flash->keep('notice');
		$this->render(array('update' => array('alert' => 'xxxx', 'hide' => 'test', 'show' => 'test2')));
		//		$this->render(array('update' => array('alert' => 'xxxx')));
		//		$this->render(array('js' => "alert('" . __METHOD__ . "')"));
		//		$this->render(array('text' => "alert('" . __METHOD__ . "');\n", 'format' => 'js'));
		//		$this->render(array('nothing' => true));
		//		$this->render('posts/index');
		//		$this->render(array('xml' => '<root><a>test</a><p>paragraph</p><div><b>test</b></div></root>'));
		//		$this->render(array('json' => "{controller_name:'" . $this->params['controller'] . "', action_name: '" . $this->params['action'] . "'}", 'callback' => 'show'));
		//		$this->render(array('json' => "{controller_name:'" . $this->params['controller'] . "', action_name: '" . $this->params['action'] . "'}"));
		//		$this->render(array('file' => '/Users/yu/Sites/phpfirefly/app/views/test/test.php'));
		//		$this->render('/Users/yu/Sites/phpfirefly/app/views/test/test.php');
		//		$this->render('/Users/yu/Sites/phpfirefly/app/views/test/test.phpx');
	}
}
?>
