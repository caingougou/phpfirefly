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

include_once(FIREFLY_BASE_DIR . DS . 'config' . DS . 'routes.php');

class Router {
	/**
	* routes setting container
	*/
	private static $routes = array();
	private static $map = array();

	public static function parse() {
		$params = array();
		$params['path'] = $_GET['path'];
		$params['form'] = $_POST;

		// file uploader
		foreach($_FILES as $name => $data) {
			$params['form'][$name] = $data;
		}

		// hack HTTP PUT/DELETE methods for restful request.
		if(isset($params['form']['_method'])) {
			$_SERVER['REQUEST_METHOD'] = $params['form']['_method'];
			unset($params['form']['_method']);
		}

		foreach($_GET as $key => $value) {
			if($key == 'path') {
				$parse_path = Router::to_params($params['path']);
				// TODO, false
				$rs = explode("/", $value);
				$params['controller_name'] = $rs[0];
				$params['action_name'] = $rs[1];
			} else {
				$params[$key] = $value;
			}
		}

		if(empty($params['action_name'])) {
			$params['action_name'] = 'index';
		}

		//		pr($params);
		return $params;
	}

	/**
	 * Reloading routes
	 * You can reload routes if you feel you must
	 */
	public static function reload() {
		Router :: $routes = array();
	}

	public static function get_routes() {
		return Router :: $routes;
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

		pr($path);
		return false;
	}

	public static function map($map) {
		//pr($map);
		Router :: $map = $map;
	}

}
?>
