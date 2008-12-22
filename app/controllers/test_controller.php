<?php
class TestController extends ApplicationController {
	public function index() {
//		$user = User :: find(1);
//		$first_post = Post :: find(1);
//		pr($user);
//		pr($first_post);
		//pr($user->posts[1]->title);
		//$post = new Post();
		//$post->title = 'test';
		//$second_and_third_post = Post::find(array(2,3));
		//$post_count = Post::count();
		//$next_post = Post::create(array('title' => 5, 'content' => 6));
		//$next_post->save();
		//$next_post->save(array('title' => 5, 'content' => 6);
		//pr($post);
		//pr($first_post);
		//pr($second_post);
		//pr($third_post);
		//pr($post_count);
		//pr($next_post);
		//$next_post->update(array('content' => 7));
		//pr($next_post);
		//$post_count2 = Post::count();
		//pr($post_count2);
		//$next_post->delete();
		//$post_count3 = Post::count();
		//pr($post_count3);
		//echo User::count();
		//if(User::count() == 0) {
		//$user = new User();
		//$user->save(array('name' => 'admin', 'password' => md5('admin')));
		//}

		$this->set('action', __METHOD__);
		$this->warn("self", __FILE__, __LINE__);
	}
}
?>
