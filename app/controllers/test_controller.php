<?php
class TestController extends ApplicationController {
	public function index() {
		$this->action = __METHOD__;

		//$user = User::model()->find(1);
		//$post = Post::model()->find(1);
		//pr($user);
		//pr($post);
		//pr($user->posts[0]->title);
		//pr($user->posts[1]->title);
		//$post_two = new Post();
		//$post_two->title = 'test';
		//pr($post_two);
		//$second_and_third_post = Post::model()->find(array(2,3));
		//pr($second_and_third_post);
		//$post_count = Post::model()->count();
		//pr($post_count);
		//$next_post = Post::model()->create(array('title' => 5, 'content' => 6));
		//var_dump($next_post);
		//$next_post->save();
		//$next_post->save(array('title' => 5, 'content' => 6));
		//$post->update(array('content' => 7));
		//pr($next_post);
		//$post_count2 = Post::model()->count();
		//pr($post_count2);
		//$next_post->delete();
		//$post_count3 = Post::model()->count();
		//pr($post_count3);
		//echo User::count();
		//if(User::count() == 0) {
		//$user = new User();
		//$user->save(array('name' => 'admin', 'password' => md5('admin')));
	}

	public function test1() {
		pr(RouteSet :: get_reference()->recognize_path("/2009/01/18"));
		pr(RouteSet :: get_reference()->routes_by_controller("posts"));
		pr(RouteSet :: get_reference()->routes_by_controller_and_action("posts", "find_by_date"));
		pr($this->url_for(array (
			"controller" => "posts",
			"action" => "find_by_date",
			"year" => "2009",
			"month" => "01",
			"day" => "18"
		)));

		pr(RouteSet :: get_reference()->routes_for(array (
			"controller" => "posts",
			"action" => "find_by_date",
			"year" => "2009",
			"month" => "01",
			"day" => "18",
			"page" => 1
		)));

		pr($this->url_for(array (
			'controller' => 'posts',
			'action' => 'show',
			'protocol' => 'https',
			'port' => 3000,
			'trailing_slash' => false,
			'only_path' => false,
			'anchor' => 'test',
			"year" => "2009",
			"month" => "01",
			"day" => "18",
			'other' => 1,
			'page' => 2,
			'per_page' => 10
		)));

		pr($this->url_for(array (
			'controller' => 'posts',
			'action' => 'find_by_date',
			'protocol' => 'https',
			'port' => 3000,
			'trailing_slash' => true,
			'only_path' => false,
			'anchor' => 'test',
			"year" => "2009",
			"month" => "01",
			"day" => "18",
			'other' => 1,
			'page' => 2,
			'per_page' => 10
		)));

		pr(RouteSet :: get_reference()->routes_for(array (
			"controller" => "admin"
		)));

		pr(RouteSet :: get_reference()->routes_for(array (
			"controller" => "admin",
			"action" => "index"
		)));

		$this->render("<p>test routes_for function</p>");
	}

	public function test2() {
		$this->logger->info(Router :: url_rewritable(), __FILE__, __LINE__);
		$this->logger->warn(print_r(Router :: available_controllers(), true), __FILE__, __LINE__);
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
		$url = $this->url_for(array (
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
		$rs = RouteSet :: get_reference();
		pr($rs->get_named_routes());
		$this->render("<p>test url_for function</p>");
	}

	public function test3() {
		$this->redirect_to("/test/index");
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
