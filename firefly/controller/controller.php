<?php
class Controller {
	private $action_performed = false;

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
		$this->params = $params;
		$this->request = $request;
		$this->response = $response;
		$this->cookies = & $_COOKIE;
		$this->sessions = & $_SESSION;
		$this->flash = Flash :: get_reference();
		$this->logger = Logger :: get_reference();
		$this->view = View :: factory($request, $response, $this);
		$this->layout = is_string($this->layout) ? $this->layout : $this->params['controller'];

		Helpers :: include_helpers($this->params['controller'], $this->helper);
	}

	public function action_missing() {
		$this->render(array('file' => FIREFLY_LIB_DIR . DS . 'view' . DS . 'action_missing.php'));
	}

	public function before_filter() {
	}

	protected function before_render() {
	}

	protected function after_render() {
	}

	public function after_filter() {
	}

	/**
	 * Shows a message to user $pause seconds, then redirects to $url.
	 * Uses flash_page.php as a layout for the messages.
	 */
	protected function flash_page($message, $url, $pause = 3) {
		defined('FLASH_PAGE') ? null : define('FLASH_PAGE', 0);
		$this->flash->set('message', $message);
		if(FLASH_PAGE) {
			$file = FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . 'flash_page.php';
			if(!file_exists($file)) {
				$file = FIREFLY_APP_DIR . DS . 'views' . DS . 'layouts' . DS . 'flash_page.php';
				if(!file_exists($file)) {
					$file = FIREFLY_LIB_DIR . DS . 'view' . DS . 'flash_page.php';
				}
			}
			$this->render(array('file' => $file, 'layout' => false, 'locals' => array('message' => $message, 'redirect_url' => $url, 'pause' => $pause * 1000)));
		} else {
			$this->redirect_to($url);
		}
	}

	protected function __toString() {
		return get_class($this);
	}

	protected function __call($method, $args) {
		$this->warn("$method not in this controller: " . get_class($this), __FILE__, __LINE__);
	}

	/**
	 * Below functions should not be overrided in inherited classes.
	 */
	final protected function set($key, $value) {
		$this->{$key} = $value;
	}

	/**
	 * Alias method of $this->flash->set($key, $value)
	 */
	final protected function flash($key, $value) {
		$this->flash->set($key, $value);
	}

	final protected function redirect_to($url) {
		$this->response->redirect_to($url);
	}

	final protected function send_file($file) {
		$this->response->send_file($file);
	}

	final protected function url_for($options) {
		return Router :: url_for($options);
	}

	/**
	 * render type:
	 * text (layout default is false)
	 * file	(absolute path)
	 * template (template root app/views/)
	 * action
	 * update (ajax, layout default is false)
	 * xml (layout default is false)
	 * js (layout default is false)
	 * json (callback, layout default is false)
	 * patial (layout default is false)
	 * nothing (layout default is false)
	 *
	 * parameters in render options array:
	 * content_type (render content type)
	 * format (render content type by extension format)
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
	 * $this->render(array('js' => "alert('__METHOD__')"));
	 * $this->render(array('json' => "{name:'$this->action'}"));
	 * $this->render(array('json' => "{name:'$this->action'}", 'callback' => 'show'));
	 * $this->render(array('nothing' => true));
	 * $this->render(array('nothing' => false));
	 * $this->render(array('status' => 202));
	 * $this->render(array('status' => 202, 'layout' => false));
	 * $this->render(array('text' => "alert('" . __METHOD__ . "');\n", 'format' => 'js'));
	 * $this->render(array('location' => '/', 'status' => 301)); // move permanently redirection 301
	 * $this->render(array('locals' => array('var1' => 'locals_var1', 'var2' => 'locals_var2')));
	 * $this->render(array('file' => '/Users/yu/Sites/phpfirefly/app/views/test/test.php'));
	 * $this->render(array('update' => array('alert' => 'xxxx', 'hide' => 'test', 'show' => 'test2')));
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
	final public function render($options = array()) {
		if(!$this->action_performed) {
			$this->action_performed = $this->rendered = true;
			$this->flash = $this->flash->flash_transform();
			$this->before_render();
			$this->view->render($options);
			$this->after_render();
		}
		$this->debug($options);
	}

	/**
	 * Don't output debug messages when action is not rendering text/html content.
	 */
	final private function debug($options) {
		if(DEBUG_LEVEL && $this->response->get_content_type() == 'text/html') {
			$this->logger->debug($this, __FILE__, __LINE__);
			$this->logger->output();
		}
	}

}
?>