<?php
class TestController extends ApplicationController{
	public function index(){
		$this->set('action', __METHOD__);
	}
}
?>
