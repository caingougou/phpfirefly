<?php
include_once ('routing/route_set.php');

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
	private static $controllers = array ();

	public static function recognize($request) {
		$route_set = RouteSet :: get_reference();
		return $route_set->recognize_path($request->path);
	}

	/**
	 * Recursive glob() directory and return matched files.
	 */
	public static function recursive_glob($pattern = '*', $path = '') {
		$paths = glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
		$files = glob($path . $pattern, GLOB_NOSORT);
		foreach ($paths as $path) {
			$files = array_merge($files, self :: recursive_glob($pattern, $path));
		}
		return $files;
	}

	/**
	 * Returns the array of controller names currently available to router
	 */
	public static function available_controllers() {
		$app_controllers_path = FIREFLY_APP_DIR . DS . 'controllers';
		if (empty (self :: $controllers)) {
			$files = self :: recursive_glob('*_controller.php', $app_controllers_path);
			$regexp = '/^' . preg_quote($app_controllers_path . DS, DS) . '(.+)_controller.php' . '$/i';
			foreach ($files as $file) {
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
		$regexp = array (
			'/^[\/]?(.)/',
			'/\/\//',
			'/(.)[\/]$/',
			'/[^\/]+[\/]\.\.[\/]/'
		);
		$replace = array (
			'\1',
			'/',
			'\1',
			''
		);
		$path = preg_replace($regexp, $replace, $path);
		return $path;
	}

	/**
	 * Generate a url based on the options provided, default_url_options and the routes defined in routes.rb.
	 * The following options are supported:
	 * <tt>prefix</tt> - If <tt>prefix</tt> supplied, it will be prefix before <tt>controller</tt> in path.
	 * <tt>only_path</tt> - If true, the relative url is returned. Defaults to +false+.
	 * <tt>use_route</tt> - If this option supplied, it will be merged specified named route options into $options.
	 * <tt>protocol</tt> - The protocol to connect to. Defaults to 'http'.
	 * <tt>host</tt> - Specifies the host the link should be targetted at. Defaults to +localhost+.
	 * <tt>port</tt> - Optionally specify the port to connect to.
	 * <tt>anchor</tt> - An anchor name to be appended to the path.
	 * <tt>trailing_slash</tt> - If true, adds a trailing slash, as in "/archive/2009/"
	 * Any other key (<tt>controller</tt>, <tt>action</tt>, <tt>id</tt> etc.) given to +url_for+ is forwarded to the Routes module.
	 * Router :: url_for(array ( 'prefix' => 'phpfirefly', 'controller' => 'tasks', 'action' => 'testing', 'host' => 'somehost.org', 'port' => '8080', 'extra' => '1234', 'other' => '2345', 'anchor' => 'test2' ))
	 * #=>"http://somehost.org:8080/phpfirefly/tasks/testing/?extra=1234&other=2345#test2"
	 * Router :: url_for(array ( 'controller' => 'test', 'action' => 'index', 'protocol' => 'https', 'port' => 3000, 'trailing_slash' => true, 'only_path' => false, 'anchor' => 'test' ))
	 * #=>"https://www.phpfirefly.com:3000/test/index/#test"
	 */
	public static function url_for($options = array ()) {
		$url = '';
		$only_path = isset ($options['only_path']) ? $options['only_path'] : false;
		if (!$only_path) {
			if (isset ($options['protocol'])) {
				$url .= $options['protocol'];
			} else {
				$url .= 'http';
			}
			if (strpos($url, '://') === false) {
				$url .= '://';
			}
			if (empty ($options['host'])) {
				$options['host'] = isset ($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
			}
			$url .= $options['host'];
			if (isset ($options['port']) && is_numeric($options['port'])) {
				$url .= ':' . $options['port'];
			}
		}
		$trailing_slash = isset ($options['trailing_slash']) ? $options['trailing_slash'] : false;
		$anchor = isset ($options['anchor']) ? '#' . $options['anchor'] : '';
		foreach (array (
				'protocol',
				'host',
				'port',
				'only_path',
				'trailing_slash',
				'anchor'
			) as $k) {
			if (isset ($options[$k])) {
				unset ($options[$k]);
			}
		}
		$route_set = RouteSet :: get_reference();
		$path = $route_set->generate($options);
		$path = $trailing_slash ? preg_replace('/\/*(\?|\z)/', '/\1', $path, 1) : $path;
		$url .= $path . $anchor;
		return $url;
	}

	/**
	 * This method tries to determine if url rewrite is enabled on this server.
	 */
	public static function url_rewritable() {
		$url_rewrite_status = false;
		if (isset ($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == 200 && isset ($_SERVER['REDIRECT_QUERY_STRING']) && strstr($_SERVER['REDIRECT_QUERY_STRING'], 'fireflypath=')) {
			$redirect_url = trim($_SERVER['REDIRECT_URL'], '/');
			$request_path = $_SERVER['REDIRECT_QUERY_STRING'];
			if (strstr($request_path, $redirect_url)) {
				$url_rewrite_status = true;
			}
		}
		elseif (function_exists('apache_get_modules')) {
			$available_modules = apache_get_modules();
			if (in_array('mod_rewrite', $available_modules)) {
				$url_rewrite_status = true;
			}
		}
		return $url_rewrite_status;
	}
}
?>