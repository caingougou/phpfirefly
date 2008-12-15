<?php
class TestController extends ApplicationController{
	public function index(){
		$post = new Post();
		$this->set('action', __METHOD__);
	}
}
?>
