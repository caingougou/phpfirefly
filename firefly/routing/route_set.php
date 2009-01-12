<?php
class RouteSet {
	private static $instance = null;
	private $map = array ();
	private $configure_file;

	public $routes = array ();
	public $named_routes = array ();
	public $available_controllers = array ();

	private function __construct() {
		$routes_configure_file = FIREFLY_BASE_DIR . DS . 'config' . DS . 'routes.php';
		$this->load($routes_configure_file);
		$this->available_controllers = Router :: available_controllers();
	}

	public static function singleton() {
		if (self :: $instance == null) {
			self :: $instance = new RouteSet;
		}
		return self :: $instance;
	}

	public function add($path, $route = array ()) {
		if (empty ($route['controller'])) {
			throw new FireflyException('please specified controller name in route');
		}
		$this->map[$path] = $route;
	}

	public function clear() {
		$this->map = array ();
	}

	/**
	 * routes map config.
	 * defaults:	default params append to request params array.
	 * collection:  for resources routes.
	 * conditions:  fro http verb request methods(get/post/put/delete).
	 * resources:	resources name used as key name.
	 * location:	url for temporary route redirect.
	 * status:		header status for this route.
	 * symbol:		symbols in key can be used in array as key name(such as :controller, :action, :id).
	 */
	public function load($routes_configure_file) {
		$this->configure_file = $routes_configure_file;
		$map = array ();
		if (include ($this->configure_file)) {
			if (empty ($map['/:controller/:action/:id'])) {
				// default route, can be overrided in config/routes.php
				$map['/:controller/:action/:id'] = array ();
			}
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
	 * TODO: reverse route map.
	 * $map('/:year/:month/:day', array('controller' => 'posts','action' => 'threads','year' => /\d{4}/) , array('year'=>'/(20){1}\d{2}/','month'=>'/((1)?\d{1,2}){2}/','day'=>'/(([1-3])?\d{1,2}){2}/'));
	 * $this->generate(array('controller' => 'posts','action' => 'threads','year' => '2005','month' => '10'));
	 * Produces: /2005/10/
	 * route options convert to firefly path
	 * $this->generate(array('controller' => 'user','action' => 'list','id' => '12'));
	 * Produces: /user/list/12
	 */
	public function generate($options = array ()) {
		$cache_key = md5(serialize($options));
		if (!isset ($this->routes[$cache_key])) {
			if (isset ($options['use_route'])) {
				$named_route_name = $options['use_route'];
				unset ($options['use_route']);
				$options = array_merge($this->map[$named_route_name], $options);
			}
			$options = $this->options_as_params($options);
			$prefix = isset ($options['prefix']) ? '/' . $options['prefix'] : '';
			$controller = $options['controller'];
			$action = $options['action'];
			$id = isset ($options['id']) ? $options['id'] : '';
			$path = implode('/', array (
				$prefix,
				$controller,
				$action,
				$id
			));
			$this->routes[$cache_key] = $this->append_query_string($path, $options);
		}
		return $this->routes[$cache_key];
	}

	/**
	 * Generate the query string with any extra keys in the $options and append it to the given path, returning the new path.
	 */
	public function append_query_string($path, $options) {
		foreach (array (
				'controller',
				'action',
				'id',
				'use_route',
				'prefix'
			) as $k) {
			if (isset ($options[$k])) {
				unset ($options[$k]);
			}
		}
		return $path . $this->build_query_string($options);
	}

	// Build a query string from the keys of the given $options.
	public function build_query_string($options) {
		$elem = array ();
		foreach ($options as $key => $value) {
			$elem[] = $this->to_query($key, $value);
		}
		if (empty ($elem)) {
			return '';
		} else {
			return '?' . implode('&', $elem);
		}
	}

	public function to_query($key, $value) {
		return urlencode($key) . '=' . urlencode($value);
	}

	public function recognize_controller($request) {
		$params = $this->recognize_path($request->path);
		if ($params) {
			return $params['controller'];
		} else {
			return false;
		}
	}

	public function routes_by_controller($controller) {

	}

	public function routes_for($options = array ()) {

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
		foreach ($this->map as $key => $options) {
			if ($key != 'resources' && preg_match('/^\w+$/', $key)) {
				$this->named_routes[$key] = $options;
			}
		}
		return $this->named_routes;
	}

	public function get_resources() {
		return $this->map['resources'];
	}

	public function recognize_path($path) {
		$path = Router :: normalize_path($path);
		foreach ($this->map as $key => $options) {
			// pr($key);
			if ($key == 'resources') {
				// TODO
				// resources route parse
				$resources = new Resources($this, $path, $options);
				$params = $resources->parse();
			}
			elseif (preg_match('/^\w+$/', $key)) {
				// named route
				$params = $this->named_route($path, $key, $options);
			} else {
				// normal route, glob route and http verb request route.
				$params = $this->routing($path, $key, $options);
			}

			if ($params) {
				// append defaults parameters to params, and remove 'defaults' and 'conditions' from $params.
				$params = array_merge($params, $this->default_params($params));
				unset ($params['conditions']);
				unset ($params['defaults']);
				// FIXME: maybe defaults params should be overrided by $_REQUEST params.
				return $params;
			}
		}
		throw new FireflyException("No route matches '$path' with request method => {$_SERVER['REQUEST_METHOD']}");
	}

	/**
	 * If matched any rule, return $params array()
	 * else return false
	 */
	public function routing($path, $key, $options) {
		$params = array ();
		$key = Router :: normalize_path($key);
		$key_segments = explode('/', $key);
		$path_segments = explode('/', $path);

		// filter: $key_segments size >= $path_segments size && not glob route.
		if (substr_count($key, '/') < substr_count($path, '/') && strpos($key, '*') === false) {
			return false;
		}
		// TODO: resources prefix when nested resources.
		foreach ($key_segments as $k => $key_segment) {
			$path_segment = isset ($path_segments[$k]) ? $path_segments[$k] : null;
			if ($path_segment == $key_segment) {
				continue;
			}
			elseif (strpos($key_segment, ':') === 0) {
				$symbol = substr($key_segment, 1);
				$symbol_param = $this->parse_symbol_options($symbol, $options, $path_segment);
				if ($symbol_param === false) {
					return false;
				}
				elseif (is_array($symbol_param)) {
					// path matched conditions route.
					return $params = array_merge($params, $symbol_param);
				} else {
					$params[$symbol] = $symbol_param;
				}
			}
			elseif (strpos($key_segment, '*') === 0) {
				// glob route parsing
				$symbol = substr($key_segment, 1);
				$params[$symbol] = implode('/', array_slice($path_segments, $k));
			} else {
				return false;
			}
		}
		return $this->check_params(array_merge($options, $params));
	}

	public function check_params($params) {
		if (empty ($params['controller'])) {
			if (isset ($params['location'])) {
				// temporary redirect route for test
				header("Location: " . $params['location']);
			} else {
				throw new FireflyException('No controller setting in route config');
			}
		}
		elseif (in_array($params['controller'], $this->available_controllers)) {
			return $params;
		} else {
			// non exists controller, skip current route rule.
			return false;
		}
	}

	public function parse_symbol_options($symbol, $options, $path_segment) {
		if (isset ($options[$symbol])) {
			return $this->parse_symbol($symbol, $options, $path_segment);
		}
		elseif (isset ($options[0])) {
			// conditions route for http verb request
			return $this->conditions_route($symbol, $options, $path_segment);
		} else {
			// no $options[$symbol] exists, for :controller/:action/:id and other ':' prefix params
			return $path_segment;
		}
	}

	public function parse_symbol($symbol, $options, $path_segment) {
		$route_value = $options[$symbol];
		if (preg_match('/^\w+$/', $route_value)) {
			// :controller, :action, :id and other non condition params
			return $route_value;
		}
		elseif ($this->match_requirements_regexp($route_value)) {
			// requirements route check
			if (preg_match($route_value, $path_segment)) {
				return $path_segment;
			} else {
				return false;
			}
		} else {
			throw new FireflyException("routes rule has error nearby '$route_value'");
		}
	}

	public function conditions_route($symbol, $options, $path_segment) {
		foreach ($options as $option) {
			if (isset ($option[$symbol])) {
				$option[$symbol] = $this->parse_symbol($symbol, $option, $path_segment);
				if ($option[$symbol] === false) {
					return false;
				}
			}
			if (isset ($option['conditions'])) {
				if (empty ($option['conditions']['method'])) {
					// GET act as default http verb request
					$option['conditions']['method'] = 'get';
				}
				if (strtoupper($_SERVER['REQUEST_METHOD']) == strtoupper($option['conditions']['method'])) {
					if (empty ($option[$symbol])) {
						$option[$symbol] = $path_segment;
					}
					return $option;
				}
			} else {
				throw new FireflyException('conditions route rule error');
			}
		}
	}

	/**
	 * $map['logout'] = array('controller' => 'admin', 'action' => 'logout');
	 */
	public function named_route($path, $key, $options) {
		$key = Router :: normalize_path($key);
		if ($path == $key) {
			return $this->check_params($options);
		} else {
			return false;
		}
	}

	public function default_params($options) {
		if (empty ($options['defaults'])) {
			return array ();
		}
		elseif (is_array($options['defaults'])) {
			return $options['defaults'];
		} else {
			throw new FireflyException('"defaults" must be array in routes map.');
		}
	}

	/**
	 * Check route value whether match requirements regualr expression.
	 */
	public function match_requirements_regexp($route_value) {
		$regexp = '/^\/.+\/[imsxe]*$/';
		return preg_match($regexp, $route_value);
	}

	/**
	 * Check controller name and action name in $options.
	 * If no controller supplied, throw exception.
	 * If no action supplied, set default action name "index" to $options.
	 */
	public function options_as_params($options, $only_current_app = false) {
		if (isset ($options['controller'])) {
			if ($only_current_app && !in_array($options['controller'], $this->available_controllers)) {
				throw new FireflyException('Controller name in options is not avaliable!');
			}
			if (empty ($options['action'])) {
				$options['action'] = 'index';
			}
		} else {
			throw new FireflyException('Need controller and action in options!');
		}
		return $options;
	}
}
?>
