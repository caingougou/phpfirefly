<?php
class PostsController extends ApplicationController {
	public function index() {
		$test = __FUNCTION__;
		$this->logger->info('index', __FILE__, __LINE__);
		$this->render('posts of index');
	}

	// post
	public function create_comment() {
		$this->logger->info('create_comment', __FILE__, __LINE__);
		$this->render('posts of create_comment');
	}

	// get
	public function show() {
		$this->page_title = __METHOD__;
		$this->logger->info('show', __FILE__, __LINE__);
		$this->render(array('text' => 'posts of show', 'layout' => true));
	}

	// put
	public function update() {
		$this->logger->info('update', __FILE__, __LINE__);
		$this->render('posts of update');
	}

	// delete
	public function destroy() {
		$this->logger->info('destroy', __FILE__, __LINE__);
		$this->render('posts of destroy');
	}

	public function find_by_date() {
		$this->logger->info('find_by_date', __FILE__, __LINE__);
		pr($this->params);
		$this->render('posts of find_by_date()');
	}
}
?>
