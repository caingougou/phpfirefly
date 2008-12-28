<?php


/**
 * The routing module provides URL rewriting.
 * It's a way to redirect incoming requests to controllers and actions.
 * This replaces mod_rewrite rules. This routing works with any web server.
 * Routes are defined in routes.php in your FIREFLY_APP_DIR/config directory.
 * Rules that are defined first take precedence over the rest.
 *
 * A RESTful resource, in basic terms, is something that can be pointed at and it will respond with a representation of the data requested.
 * In real terms this could mean a user with a browser requests an HTML page, or that a desktop application requests XML data.
 * RESTful design is based on the assumption that there are four generic verbs that a user of an application can request from a resource (the noun).
 * Resources can be requested using four basic HTTP verbs (GET, POST, PUT, DELETE), the method used denotes the type of action that should take place.
 *
 * The Different Methods and their Usage:
 * GET Requests for a resource, no saving or editing of a resource should occur in a GET request.
 * POST Creation of resources.
 * PUT Editing of attributes on a resource.
 * DELETE Deletion of a resource
 */

class Router {
	/**
	* routes setting container
	*/
	private static $routes = array();
	private static $map = array();
	private static $regexp = '/^\/.+\/[imsxe]*$/';

	public static function recognize($path = '') {
		$path = $path ? $path : $_GET['path'];
		return self :: to_params($path);
	}

	/**
	 * Reloading routes
	 * You can reload routes if you feel you must
	 */
	public static function reload() {
		self :: $routes = array();
	}

	public static function get_routes() {
		return self :: $routes;
	}

	/**
	* Router::to_url(array('controller' => 'user','action' => 'list','id' => '12'));
	* Produces: /user/list/12
	*/
	public static function to_url($params = array()) {
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
	* Router::to_params('/blog/view/10/');
	* Produces: array('controller'=>'post','action'=>'view','id'=>'10');
	*
	* This function returns false in case no rule is found for selected URL
	*/
	public static function to_params($path) {
		$path = $path == '/' || $path == '' ? '/' : '/' . $path;
		foreach(self :: $map as $key => $value) {
			if($key == 'resources') {
				// resources parse
				$resources = new Resources($path, $value);
				$params = $resources->parse();
			}
			elseif(preg_match('/^\*\w*$/', $key)) {
				// globbing route
				$params = self :: globbing($path, $key, $value);
			}
			elseif(in_array('get', array_keys($value)) || in_array('post', array_keys($value)) || in_array('put', array_keys($value)) || in_array('delete', array_keys($value))) {
				// restful conditions route
				$params = self :: restful($path, $key, $value);
			}
			elseif(preg_match('/^\w+$/', $key)) {
				// named route
				$params = self :: named($path, $key, $value);
			} else {
				// variables route
				$params = self :: routing($path, $key, $value);
			}
			if($params) {
				// TODO: append defaults parameters to params.
			    $params = array_merge($params, self :: defaults_params());
				return $params;
			}
		}

		return array();
	}

	public static function globbing($path, $key, $value) {
		return false;
	}

	public static function restful($path, $key, $value) {
		return false;
	}

	/**
	 * $map['logout'] = array('controller' => 'page', 'action' => 'logout');
	 */
	public static function named($path, $key, $value) {
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
	public static function routing($path, $key, $value) {
		//		pr($value);
		if(true) {
			return array('controller' => 'test');
		}
		return false;
	}

	public static function map($map) {
		self :: $map = $map;
	}

	public static function defaults_params(){
		return array();
	}

}
?>
