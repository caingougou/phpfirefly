<?php
class PostsController extends ApplicationController {
	public function index() {
		$test = __FUNCTION__;
		$this->info('index', __FILE__, __LINE__);
		$this->render('posts of index');
	}

	// post
	public function create_comment() {
		$this->info('create_comment', __FILE__, __LINE__);
		$this->render('posts of create_comment');
	}

	// get
	public function show() {
		$this->page_title = __METHOD__;
		$this->info('show', __FILE__, __LINE__);
		$this->render(array('text' => 'posts of show', 'layout' => true));
	}

	// put
	public function update() {
		$this->info('update', __FILE__, __LINE__);
		$this->render('posts of update');
	}

	// delete
	public function destroy() {
		$this->info('destroy', __FILE__, __LINE__);
		$this->render('posts of destroy');
	}

	public function find_by_date() {
		$this->info('find_by_date', __FILE__, __LINE__);
		$this->render('posts of find_by_date()');
	}
}
?>
