<?php
class RouteSet {
	private $map = array();
	private $configure_file;

	public $routes = array();
	public $named_routes = array();

	public function __construct() {
		$this->configure_file = FIREFLY_BASE_DIR . DS . 'config' . DS . 'routes.php';
		// default route, can be overrided in config/routes.php
		$map = array();
		$map['/:controller/:action/:id'] = array();
		if(include($this->configure_file)) {
			$this->map = $map;
		} else {
			trigger_error($this->configure_file . ' is not exists!', E_USER_WARNING);
		}
	}

	public function add($path, $route = array()) {

	}

	public function clear() {
		$this->routes = array();
	}

	public function load($routes_configure_file) {

	}

	/**
	 * Reloading routes
	 * You can reload routes if you feel you must
	 */
	public function reload() {
		$this->load($this->configure_file);
	}

	public function generate($options = array()) {
		$path = '/';
		return $path;
	}

	public function recognize($request) {
		return 'TestController';
	}

	public function routes_by_controller($controller) {

	}

	public function routes_for($options = array()) {

	}

	public function routes_for_controller_and_action($controller, $action) {

	}

	public function matches_controller_and_action($controller, $action) {
		return true;
	}

	public function get_routes() {
		return $this->routes;
	}

	/**
	* Router::url_for(array('controller' => 'user','action' => 'list','id' => '12'));
	* Produces: /user/list/12
	*/
	public function url_for($params = array()) {
		$cache_key = md5(serialize($params));
		if(!isset($routes[$cache_key])) {
			$parsed = isset($params['prefix']) ? '/' . $params['prefix'] : '';
			$parsed .= '/' . $params['controller_name'] . '/' . $params['action_name'];
			$parsed .= isset($params['id']) ? '/' . $params['id'] : '';
			$routes[$cache_key] = $parsed;
		}
		return $routes[$cache_key];
	}

	/**
	 * for performance to find exact route in route sets.
	 */
	private function filter($path, $route, $array) {
		// TODO: glob * path
		if(substr_count($path, '/') == substr_count($route, '/')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * http://yuweijun.blogspot.com/2007/08/rails-routes.html
	 * Router::to_params('/blog/view/10/');
	 * Produces: array('controller'=>'post','action'=>'view','id'=>'10');
	 *
	 * This function returns false in case no rule is found for selected URL
	 */
	public function recognize_path($path) {
		$path = preg_replace('/^\/*(.*?)\/*$/', '/\1', $path);

		foreach($this->map as $key => $value) {
			if($key == 'resources') {
				// resources parse
				$resources = new Resources($path, $value);
				$params = $resources->parse();
			}
			elseif(preg_match('/^\*\w*$/', $key)) {
				// globbing route
				$params = $this->globbing($path, $key, $value);
			}
			elseif(in_array('get', array_keys($value)) || in_array('post', array_keys($value)) || in_array('put', array_keys($value)) || in_array('delete', array_keys($value))) {
				// restful conditions route
				$params = $this->restful($path, $key, $value);
			}
			elseif(preg_match('/^\w+$/', $key)) {
				// named route
				$params = $this->named($path, $key, $value);
			} else {
				// variables route
				$params = $this->routing($path, $key, $value);
			}
			if($params) {
				// TODO: append defaults parameters to params.
				$params = array_merge($params, $this->default_params());
				return $params;
			}
		}

		return array();
	}

	public function globbing($path, $key, $value) {
		return false;
	}

	public function restful($path, $key, $value) {
		return false;
	}

	/**
	 * $map['logout'] = array('controller' => 'page', 'action' => 'logout');
	 */
	public function named($path, $key, $value) {
		if($path == '/' . $key || $path == '/' . $key . '/') {
			return $value;
		} else {
			return false;
		}
	}

	/**
	 * if matched any rule, return params array()
	 * else return false
	 */
	public function routing($path, $key, $value) {
		//				pr($value);
		if(true) {
			return array('controller' => 'test');
		}
		return false;
	}

	public function default_params() {
		return array();
	}
}
?>
