<?php
include_once('routing/route_set.php');

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
	private static $controllers = array();

	public static function recognize($request) {
		$route_set = new RouteSet;
		return $route_set->recognize_path($request->path);
	}

	public static function recursive_glob($pattern = '*', $path = '') {
		$path = preg_quote($path, DS); // prevend character '[', ']' etc in $path to cause glob return false
		$paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
		$files = glob($path . $pattern, GLOB_NOSORT);
		foreach($paths as $path) {
			$files = array_merge($files, self :: recursive_glob($pattern, $path));
		}
		return $files;
	}

	/**
	 * Returns the array of controller names currently available to router
	 */
	public static function available_controllers() {
		$app_controllers_path = FIREFLY_APP_DIR . DS . 'controllers';
		if(empty(self :: $controllers)) {
			$files = self :: recursive_glob('*_controller.php', $app_controllers_path);
			$regexp = '/^' . preg_quote($app_controllers_path . DS, DS) . '(.+)_controller.php' . '$/i';
			foreach($files as $file) {
				array_push(self :: $controllers, preg_replace($regexp, '\1', $file));
			}
		}
		return self :: $controllers;
	}

	/**
	 * Returns normalized path, cleaned of double-slashes and relative path references.
	 * "//"  become "/".
	 * remove "/" at begin of path when $path begin with "/".
	 * remove '/' at end of $path if $path end with '/'.
	 * "/foo/bar/../config" becomes "/foo/config".
	 */
	public static function normalize_path($path) {
		// $path = preg_replace('/^\/*(.*?)\/*$/', '/\1', $path);
		$regexp = array('/^[\/]?(.)/', '/\/\//', '/(.)[\/]$/', '/[^\/]+[\/]\.\.[\/]/');
		$replace = array('\1', '/', '\1', '');
		$path = preg_replace($regexp, $replace, $path);
		return $path;
	}

}
?>