<?php
class AdminController extends ApplicationController {
	public function index() {
		pr(Router :: available_controllers());
	}

	public function login() {
		$path = 'test/test/?test=1#test';
		$path = preg_replace('/\/*(\?|\z)/', '/\1', $path, 1);
		pr($path);
		$path = 'test/test?test=1#test';
		$path = preg_replace('/\/*(\?|\z)/', '/\1', $path, 1);
		pr($path);
		$path = 'test/test/';
		$path = preg_replace('/\/*(\?|\z)/', '/\1', $path, 1);
		pr($path);
		$path = 'test/test';
		$path = preg_replace('/\/*(\?|\z)/', '/\1', $path, 1);
		pr($path);
		$url = Router :: url_for(array (
			'controller' => 'test',
			'action' => 'index',
			'protocol' => 'https',
			'port' => 3000,
			'trailing_slash' => true,
			'only_path' => false,
			'anchor' => 'test',
			'other' => 1,
			'page' => 2,
			'per_page' => 10
		));
		pr($url);
		$rs = RouteSet :: singleton();
		pr($rs->get_named_routes());
		$_SESSION['user'] = 'test';
	}

	public function logout() {
		$_SESSION['user'] = null;
		$this->redirect_to('login');
	}

	public function test() {
		//$this->info("self", __FILE__, __LINE__);
		//$this->redirect_to("/");
		//$this->send_file(__FILE__);
		//$this->render("test string render");
		//$this->render(array('text' => "test string render"));
		//$this->render(array('text' => "test string render", 'layout' => true));
		//$this->render(array('layout' => false));
		//$this->render(array('layout' => 'posts'));
		//$this->render(array('layout' => 'not_exists_layout')); // trigger warning
		//$this->render(array('inline' => "render $this->action"));
		//$this->render(array('inline' => "render $this->action", 'layout' => false));
		//$this->render(array('js' => "alert('__METHOD__')"));
		//$this->render(array('json' => "{name:'$this->action'}"));
		//$this->render(array('json' => "{name:'$this->action'}", 'callback' => 'show'));
		//$this->render(array('nothing' => true));
		//$this->render(array('nothing' => false));
		//$this->render(array('status' => 202));
		//$this->render(array('status' => 202, 'layout' => false));
		//$this->render(array('location' => '/', 'status' => 301)); // move permanently redirection 301
		//$this->render(array('locals' => array('var1' => 'locals_var1', 'var2' => 'locals_var2')));
		//$this->render(array('file' => '/Users/yu/Sites/phpfirefly/app/views/test/test.php'));
		//$this->render('/Users/yu/Sites/phpfirefly/app/views/test/test.php');
		//$this->render('posts/index');
		//$this->render('test');
		//$this->render('test2');
		//$this->render(array('template' => 'posts/index'));
		//$this->render(array('template' => 'posts/index2')); // template not exists.
		//$this->render(array('action' => 'posts/index'));
		//$this->render(array('action' => 'posts/index', 'layout' => false));
		//$this->render(array('action' => 'test'));
		//$this->render(array('partial' => 'form'));
		//$this->render(array('partial' => 'form', 'layout' => false));
		//$this->render(array('partial' => 'posts/form'));
		//$this->render(array('partial' => 'posts/form', 'layout' => 'posts'));
	}

}
?>
