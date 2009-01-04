<?php
class RouteSet {
	private $map = array();
	private $configure_file;

	public $routes = array();
	public $named_routes = array();
	public $available_controllers = array();

	public function __construct() {
		$routes_configure_file = FIREFLY_BASE_DIR . DS . 'config' . DS . 'routes.php';
		$this->load($routes_configure_file);
		$this->available_controllers = Router :: available_controllers();
	}

	public function add($path, $route = array()) {
		if(empty($route['controller'])) {
			throw new FireflyException('please specified controller name in route');
		}
		$this->map[$path] = $route;
	}

	public function clear() {
		$this->map = array();
	}

	public function load($routes_configure_file) {
		$this->configure_file = $routes_configure_file;
		// default route, can be overrided in config/routes.php
		$map = array();
		$map['/:controller/:action/:id'] = array();
		$map['*path'] = array('location' => '/404.html');
		if(include($this->configure_file)) {
			$this->map = $map;
		} else {
			throw new FireflyException($this->configure_file . ' is not exists!');
		}
	}

	/**
	 * Reloading routes
	 * You can reload routes if you feel you must
	 */
	public function reload() {
		$this->load($this->configure_file);
	}

	/**
	 * route options convert to firefly path
	 */
	public function generate($options = array()) {
		return $this->url_for($options);
	}

	public function recognize_controller($request) {
		$params = $this->recognize_path($request->path);
		if($params) {
			return $params['controller'];
		} else {
			return false;
		}
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

	public function get_map() {
		return $this->map;
	}

	public function get_routes() {
		return $this->routes;
	}

	public function get_named_routes() {
		return array();
	}

	public function get_resources() {
		return array();
	}

	/**
	* Router::url_for(array('controller' => 'user','action' => 'list','id' => '12'));
	* Produces: /user/list/12
	*/
	public function url_for($params = array()) {
		// TODO: routes cached
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
	 * http://yuweijun.blogspot.com/2007/08/rails-routes.html
	 * This function must return array() in case of no rule is found for selected URL
	 */
	public function recognize_path($path) {
		$path = Router :: normalize_path($path);
		foreach($this->map as $key => $options) {
			pr($key);
			if($key == 'resources') {
				// TODO
				// resources route parse
				$resources = new Resources($this, $path, $options);
				$params = $resources->parse();
			}
			elseif(preg_match('/^\w+$/', $key)) {
				// named route
				$params = $this->named_route($path, $key, $options);
			} else {
				// normal route, glob route and http verb request route.
				$params = $this->routing($path, $key, $options);
			}

			if($params) {
				return $this->check_params($params);
			}
		}
		return array();
	}

	public function check_params($params) {
		//		pr($params);
		if(empty($params['controller'])) {
			if(isset($params['location'])) {
				// temporary redirect route
				header("Location: " . $params['location']);
			} else {
				throw new FireflyException('No controller setting in route config');
			}
		}
		if(in_array($params['controller'], $this->available_controllers)) {
			// append defaults parameters to params, and remove 'defaults' and 'conditions' from $params.
			$params = array_merge($params, $this->default_params($params));
			unset($params['conditions']);
			unset($params['defaults']); // FIXME: maybe defaults params should be overrided by $_REQUEST params.
			return $params;
		} else {
			// non exists controller, skip current route rule.
			return array();
		}
	}

	/**
	 * if matched any rule, return $params array()
	 * else return false
	 */
	public function routing($path, $key, $options) {
		$params = array();
		$key = Router :: normalize_path($key);
		$key_segments = explode('/', $key);
		$path_segments = explode('/', $path);

		// filter: $key_segments size >= $path_segments size && not glob route.
		if(substr_count($key, '/') < substr_count($path, '/') && strpos($key, '*') === false) {
			return false;
		}
		// TODO: resources prefix when nested resources.
		foreach($key_segments as $k => $key_segment) {
			$path_segment = $path_segments[$k];
			if($path_segment == $key_segment) {
				continue;
			}
			elseif(strpos($key_segment, ':') === 0) {
				$symbol = substr($key_segment, 1);
				$symbol_param = $this->parse_symbol_options($symbol, $options, $path_segment);
				if($symbol_param === false) {
					return false;
				}
				elseif(is_array($symbol_param)) {
					// path matched conditions route.
					return $params = array_merge($params, $symbol_param);
				} else {
					$params[$symbol] = $symbol_param;
				}
			}
			elseif(strpos($key_segment, '*') === 0) {
				// glob route parsing
				$symbol = substr($key_segment, 1);
				$params[$symbol] = implode('/', array_slice($path_segments, $k));
			} else {
				return false;
			}
		}
		return array_merge($options, $params);
	}

	public function parse_symbol_options($symbol, $options, $path_segment) {
		if(isset($options[$symbol])) {
			return $this->parse_symbol($symbol, $options, $path_segment);
		}
		elseif(isset($options[0])) {
			// conditions route for http verb request
			return $this->conditions_route($symbol, $options, $path_segment);
		} else {
			// no $options[$symbol] exists, for :controller/:action/:id and other ':' prefix params
			return $path_segment;
		}
	}

	public function parse_symbol($symbol, $options, $path_segment) {
		$route_value = $options[$symbol];
		if(preg_match('/^\w+$/', $route_value)) {
			// :controller, :action, :id and other non condition params
			return $route_value;
		}
		elseif($this->match_conditions_regexp($route_value)) {
			// requirements route check
			if(preg_match($route_value, $path_segment)) {
				return $path_segment;
			} else {
				return false;
			}
		} else {
			throw new FireflyException("routes rule has error nearby '$route_value'");
		}
	}

	public function conditions_route($symbol, $options, $path_segment) {
		foreach($options as $option) {
			if(isset($option[$symbol])) {
				$option[$symbol] = $this->parse_symbol($symbol, $option, $path_segment);
				if($option[$symbol] === false)
					return false;
			}
			if(isset($option['conditions'])) {
				if(empty($option['conditions']['method'])) {
					// GET act as default http verb request
					$option['conditions']['method'] = 'get';
				}
				if(strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($option['conditions']['method'])) {
					if(empty($option[$symbol]))
						$option[$symbol] = $path_segment;
					return $option;
				}
			} else {
				throw new FireflyException('conditions route rule error');
			}
		}
	}

	/**
	 * $map['logout'] = array('controller' => 'page', 'action' => 'logout');
	 */
	public function named_route($path, $key, $options) {
		$key = Router :: normalize_path($key);
		if($path == $key) {
			return $options;
		} else {
			return false;
		}
	}

	public function default_params($options) {
		if(empty($options['defaults'])) {
			return array();
		}
		elseif(is_array($options['defaults'])) {
			return $options['defaults'];
		} else {
			throw new FireflyException('"defaults" must be array in routes map.');
		}
	}

	private function match_conditions_regexp($route_value) {
		$regexp = '/^\/.+\/[imsxe]*$/';
		return preg_match($regexp, $route_value);
	}

}
?>
