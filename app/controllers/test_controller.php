<?php
class TestController extends ApplicationController{
	public function index(){
		$test = __METHOD__;
		$params = array();
		$params['test'] = $test;

		$template = FIREFLY_APP_DIR . DS . 'views' . DS . 'test' . DS . 'index.php';
		$view = new View();
		$view->render($template, $params);
	}
}
?>
