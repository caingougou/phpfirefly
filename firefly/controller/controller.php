<?php
include_once ('render_options.php');

class Controller {
	protected $view;
	protected $logger;
	protected $request;
	protected $response;

	public $layout;
	public $flash;
	public $params;
	public $helper;
	public $cookies;
	public $sessions;
	public $page_title;
	public $rendered = false;

	public function __construct($request, $response, $params) {
		// 0. log disable coloring, 1. log enable coloring.
		defined('LOG_COLORING') ? null : define('LOG_COLORING', 1);
		defined('DEBUG_LEVEL') ? null : define('DEBUG_LEVEL', 'debug');
		defined('LOG_LOCATION') ? null : define("LOG_LOCATION", 'file');
		defined('ENVIRONMENT') ? null : define('ENVIRONMENT', 'development');
		defined('FLASH_PAGE') ? null : define('FLASH_PAGE', 0);
		defined('VIEW') ? null : define('VIEW', 'php');

		$this->params = $params;
		$this->request = $request;
		$this->response = $response;
		$this->cookies = & $_COOKIE;
		$this->sessions = & $_SESSION;
		$this->flash = Flash :: get_reference();
		$this->logger = Logger :: get_reference();
		$this->view = View :: factory($request, $response, $this, strtolower(VIEW));
		$this->layout = is_string($this->layout) ? $this->layout : $this->params['controller'];

		$this->include_helpers();
	}

	public function before_filter() {
	}

	public function before_render() {
	}

	public function after_render() {
	}

	public function after_filter() {
	}

	public function action_missing() {
		$this->render(array ( 'file' => FIREFLY_LIB_DIR . DS . 'view' . DS . 'action_missing.php' ));
	}

	/**
	 * Shows a message to user $pause seconds, then redirects to $url.
	 * Uses flash_page.php as a layout for the messages.
	 */
	function flash_page($message, $url, $pause = 3) {
		$this->flash->set('message', $message);
		if (FLASH_PAGE) {
			$file = FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . 'flash_page.php';
			if (!file_exists($file)) {
				$file = FIREFLY_APP_DIR . DS . 'views' . DS . 'layouts' . DS . 'flash_page.php';
				if(!file_exists($file)) {
					$file = FIREFLY_LIB_DIR . DS . 'view' . DS . 'flash_page.php';
				}
			}
			$this->render(array (
				'file' => $file,
				'layout' => false,
				'locals' => array ( 'message' => $message, 'redirect_url' => $url, 'pause' => $pause * 1000 )
			));
		} else {
			$this->redirect_to($url);
		}
	}

	public function __toString() {
		return get_class($this);
	}

	public function __call($method, $args) {
		$this->warn("$method not in this controller: " . get_class($this), __FILE__, __LINE__);
	}

	/**
	 * Do not override below final functions in inherited classes.
	 */
	final public function set($key, $value) {
		$this->{$key} = $value;
	}

	/**
	 * Alias method of $this->flash->set($key, $value)
	 */
	final public function flash($key, $value) {
		$this->flash->set($key, $value);
	}

	final public function redirect_to($url) {
		$this->response->redirect_to($url);
	}

	final public function send_file($file) {
		$this->response->send_file($file);
	}

	final public function url_for($options) {
		return Router :: url_for($options);
	}

	/**
	 * render type:
	 * text (layout default is false)
	 * file	(absolute path)
	 * template (template root app/views/)
	 * inline
	 * action
	 * update (ajax/rjs)
	 * xml
	 * js
	 * json (callback)
	 * patial (layout default is false)
	 * nothing
	 * default
	 *
	 * parameters in render options array:
	 * status (404/301/200 etc.)
	 * location (redirect_to)
	 * locals (must be array, act as view variable)
	 * layout
	 *
	 * examples:
	 * $this->redirect_to("/");
	 * $this->send_file(__FILE__);
	 * $this->render("test string render");
	 * $this->render(array('text' => "test string render"));
	 * $this->render(array('text' => "test string render", 'layout' => true));
	 * $this->render(array('layout' => false));
	 * $this->render(array('layout' => 'posts'));
	 * $this->render(array('layout' => 'not_exists_layout')); // trigger warning
	 * $this->render(array('inline' => "render $this->action"));
	 * $this->render(array('inline' => "render $this->action", 'layout' => false));
	 * $this->render(array('js' => "alert('__METHOD__')"));
	 * $this->render(array('json' => "{name:'$this->action'}"));
	 * $this->render(array('json' => "{name:'$this->action'}", 'callback' => 'show'));
	 * $this->render(array('nothing' => true));
	 * $this->render(array('nothing' => false));
	 * $this->render(array('status' => 202));
	 * $this->render(array('status' => 202, 'layout' => false));
	 * $this->render(array('location' => '/', 'status' => 301)); // move permanently redirection 301
	 * $this->render(array('locals' => array('var1' => 'locals_var1', 'var2' => 'locals_var2')));
	 * $this->render(array('file' => '/Users/yu/Sites/phpfirefly/app/views/test/test.php'));
	 * $this->render(array('template' => 'posts/index'));
	 * $this->render(array('template' => 'posts/index2')); // template not exists.
	 * $this->render(array('action' => 'posts/index'));
	 * $this->render(array('action' => 'posts/index', 'layout' => false));
	 * $this->render(array('action' => 'test'));
	 * $this->render('/Users/yu/Sites/phpfirefly/app/views/test/test.php');
	 * $this->render('posts/index');
	 * $this->render('test');
	 * $this->render(array('partial' => 'form'));
	 * $this->render(array('partial' => 'form', 'layout' => false));
	 * $this->render(array('partial' => 'posts/form'));
	 * $this->render(array('partial' => 'posts/form', 'layout' => 'posts'));
	 * action can render only once, partial is rendered by view can more one time.
	 */
	final public function render($options = array ()) {
		if (!$this->rendered) {
			$this->rendered = true;
			$this->flash_transform();
			$this->before_render();
			$this->view->render($options);
			$this->after_render();
		}
		$this->debug();
	}

	/**
	 * Transform Flash object [$this->flash] to array.
	 */
	final private function debug() {
		if (DEBUG_LEVEL) {
			$this->logger->debug($this, __FILE__, __LINE__);
			$this->logger->output();
		}
	}

	final private function flash_transform() {
		$flash = array();
		foreach ( $this->flash->get_keys() as $key ) {
			$flash[$key] = $this->flash->get($key);
		}
		$this->flash = $flash;
	}

	final private function include_helpers() {
		if (is_subclass_of($this, 'ApplicationController')) {
			$application_vars = get_class_vars('ApplicationController');
			$this->include_helper($this->params['controller']);
			$this->include_helper($this->helper);
			$this->include_helper($application_vars['helper']);
		}
	}

	final private function include_helper($helpers) {
		if ($helpers && !is_array($helpers)) {
			$this->include_helper_file($helpers);
		}
		elseif (is_array($helpers)) {
			foreach ($helpers as $helper) {
				$this->include_helper_file($helper);
			}
		}
	}

	final private function include_helper_file($helper_file) {
		$file_name = strtolower($helper_file) . '_helper.php';
		$firefly_helpers_path = FIREFLY_LIB_DIR . DS . 'helpers' . DS . $file_name;
		$app_helers_path = FIREFLY_APP_DIR . DS . 'helers' . DS . $file_name;
		$plugins_helpers_path = FIREFLY_PLUGINS_DIR . DS . $file_name;
		if (file_exists($firefly_helpers_path)) {
			include_once ($firefly_helpers_path);
		}
		elseif (file_exists($app_helers_path)) {
			include_once ($app_helers_path);
		}
		elseif (file_exists($plugins_helpers_path)) {
			include_once ($plugins_helpers_path);
		}
	}

}
?>