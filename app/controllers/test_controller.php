<?php
class TestController extends ApplicationController {
	public function index() {
		$this->action = __METHOD__;

//		$user = User :: find(1);
//		$first_post = Post :: find(1);
//		pr($user);
//		pr($first_post);
//		pr($user->posts[1]->title);
//		$post = new Post();
//		$post->title = 'test';
//		$second_and_third_post = Post::find(array(2,3));
//		$post_count = Post::count();
//		$next_post = Post::create(array('title' => 5, 'content' => 6));
//		$next_post->save();
//		$next_post->save(array('title' => 5, 'content' => 6);
//		pr($post);
//		pr($first_post);
//		pr($second_post);
//		pr($third_post);
//		pr($post_count);
//		pr($next_post);
//		$next_post->update(array('content' => 7));
//		pr($next_post);
//		$post_count2 = Post::count();
//		pr($post_count2);
//		$next_post->delete();
//		$post_count3 = Post::count();
//		pr($post_count3);
//		echo User::count();
//		if(User::count() == 0) {
//		$user = new User();
//		$user->save(array('name' => 'admin', 'password' => md5('admin')));
//		}

//		$this->info("self", __FILE__, __LINE__);

//		$this->redirect_to("/");
//		$this->send_file(__FILE__);
//		$this->render("test string render");
//		$this->render(array('text' => "test string render"));
//		$this->render(array('text' => "test string render", 'layout' => true));
//		$this->render(array('layout' => false));
//		$this->render(array('layout' => 'posts'));
//		$this->render(array('layout' => 'not_exists_layout')); // trigger warning
//		$this->render(array('inline' => "render $this->action"));
//		$this->render(array('inline' => "render $this->action", 'layout' => false));
//		$this->render(array('js' => "alert('__METHOD__')"));
//		$this->render(array('json' => "{name:'$this->action'}"));
//		$this->render(array('json' => "{name:'$this->action'}", 'callback' => 'show'));
//		$this->render(array('nothing' => true));
//		$this->render(array('nothing' => false));
//		$this->render(array('status' => 202));
//		$this->render(array('status' => 202, 'layout' => false));
//		$this->render(array('location' => '/', 'status' => 301)); // move permanently redirection 301
//		$this->render(array('locals' => array('var1' => 'locals_var1', 'var2' => 'locals_var2')));
//		$this->render(array('file' => '/Users/yu/Sites/phpfirefly/app/views/test/test.php'));
//		$this->render('/Users/yu/Sites/phpfirefly/app/views/test/test.php');
//		$this->render('posts/index');
//		$this->render('test');
//		$this->render('test2');
//		$this->render(array('template' => 'posts/index'));
//		$this->render(array('template' => 'posts/index2')); // template not exists.
//		$this->render(array('action' => 'posts/index'));
//		$this->render(array('action' => 'posts/index', 'layout' => false));
//		$this->render(array('action' => 'test'));
//		$this->render(array('partial' => 'form'));
//		$this->render(array('partial' => 'form', 'layout' => false));
//		$this->render(array('partial' => 'posts/form'));
//		$this->render(array('partial' => 'posts/form', 'layout' => 'posts'));
	}

}
?>
