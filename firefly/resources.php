<?php
class Resources {
	private $map = array();
	private $route_set;

	public function __construct($route_set, $path, $value) {
		$this->route_set = $route_set;
		$this->map = $value;
//		pr($map);
	}

	public function parse(){
		// parse $this->map;
		return false;
	}

}
?>