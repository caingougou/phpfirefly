<?php
class Controller {
	protected $logger;
	protected $request;
	protected $response;
	protected $layout;

	public $flash;
	public $params;
	public $helper;
	public $page_title;
	public $default_template;
	public $rendered = false;

	private $template_root;

	public function __construct($request, $response, $params) {
		$this->params = $params;
		$this->request = $request;
		$this->response = $response;
		$this->flash = Flash :: get_reference();
		$this->logger = Logger :: get_reference();
		$this->template_root = FIREFLY_APP_DIR . DS . 'views' . DS;

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
	 * Uses flash_message.php as a layout for the messages.
	 */
	function flash_message($message, $url, $pause = 3) {
		$this->flash->set('message', $message);
		if (FLASH_MESSAGE) {
			$file = FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . 'flash_message.php';
			if (!file_exists($file)) {
				$file = FIREFLY_APP_DIR . DS . 'views' . DS . 'layouts' . DS . 'flash_message.php';
				if(!file_exists($file)) {
					$file = FIREFLY_LIB_DIR . DS . 'view' . DS . 'flash_message.php';
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
	 * patial
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
	 */
	final public function render($options = array ()) {
		$this->layout = is_string($this->layout) ? $this->layout : $this->params['controller'];
		$this->default_template = FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . $this->params['action'] . '.php';
		$this->flash_transform();
		$options = $this->parse_render_options($options);
		$this->rendered = true;
		$this->before_render();
		$this->render_action($options);
		$this->after_render();
	}

	final private function render_action($options) {
		if (empty ($options)) {
			$this->render_for_file($this->default_template);
		}
		elseif (isset ($options['text'])) {
			if (isset ($options['layout']) && $options['layout']) {
				$this->pick_layout($options);
			}
			$this->render_for_text($options['text']);
		}
		elseif (isset ($options['file'])) {
			$this->render_for_file($options['file'], $options);
		}
		elseif (isset ($options['template'])) {
			$this->render_for_file($this->find_template($options['template']), $options);
		}
		elseif (isset ($options['inline'])) {
			$this->pick_layout($options);
			// TODO: render inline php statement
			$this->render_for_text($options['inline']);
		}
		elseif (isset ($options['action'])) {
			$this->render_for_file($this->find_template($options['action']), $options);
		}
		elseif (isset ($options['xml'])) {
			$this->response->set_content_type_by_extension('xml');
			// TODO: $options['xml'] parse to xml.
			$this->render_for_text($options['xml']);
		}
		elseif (isset ($options['js'])) {
			$this->response->set_content_type_by_extension('js');
			$this->render_for_text($options['js']);
		}
		elseif (isset ($options['json'])) {
			$this->response->set_content_type_by_extension('json');
			if (isset ($options['callback'])) {
				$options['json'] = $options['callback'] . "({$options['json']});";
			}
			$this->render_for_text($options['json']);
		}
		elseif (isset ($options['partial'])) {
			$this->render_for_file($this->find_template($options['partial'], true), $options);
		}
		elseif (isset ($options['update'])) {
			$this->response->set_content_type_by_extension('js');
			// TODO: update partial using ajax
			$this->render_for_text($options['update']);
		}
		elseif (isset ($options['nothing'])) {
			if ($options['nothing']) {
				$this->render_for_text('');
			} else {
				$this->render_for_file($this->default_template, $options);
			}
		} else {
			$this->render_for_file($this->default_template, $options);
		}

		$out = $this->response->output();
		echo $out;
		$this->debug_controller();
	}

	/**
	 * Transform Flash object [$this->flash] to array.
	 */
	final private function flash_transform() {
		$flash = array();
		foreach ( $this->flash->get_keys() as $key ) {
			$flash[$key] = $this->flash->get($key);
		}
		$this->flash = $flash;
	}

	final private function render_for_file($file, $options = array ()) {
		// $this->logger->info($file, __FILE__, __LINE__);
		if (file_exists($file) && preg_match('/^' . preg_quote(FIREFLY_BASE_DIR, '/') . '/', $file)) {
			$this->response->template = $file;
		} else {
			$this->response->set_content('Template: "' . $options['template'] . '" is not exists!');
			// throw new FireflyException('Template: "' . $options['template'] . '" is not exists!');
		}
		$this->pick_layout($options);
	}

	final private function render_for_text($text) {
		$this->response->set_content($text);
	}

	/**
	 * chooses between file, template, action and text depending on
	 * whether there is a leading slash (file and file must under FIREFLY_APP_DIR),
	 * or an embedded slash (template),
	 * or no slash and no white space at all in whatÕs to be rendered (action),
	 * or render as string (text).
	 */
	final private function parse_render_options($options) {
		if (!is_array($options)) {
			if (is_string($options)) {
				if (substr($options, 0, 1) == '/' && file_exists($options)) {
					$options = array ( 'file' => $options );
				}
				elseif (strpos($options, '/') > 0 && file_exists($this->template_root . $options . '.php')) {
					$options = array ( 'template' => $options );
				}
				elseif (!preg_match('/\s/', $options) && file_exists($this->template_root . $this->params['controller'] . DS . $options . '.php')) {
					$options = array ( 'action' => $options );
				} else {
					$options = array ( 'text' => $options );
				}
			} else {
				$options = array ();
			}
		}
		if (isset ($options['content_type'])) {
			$this->response->set_content_type($options['content_type']);
		}
		if (isset ($options['status'])) {
			$this->response->set_header_status($options['status']);
		}
		if (isset ($options['location'])) {
			$this->response->redirect_to($this->url_for($options['location']));
		}
		if (empty ($options['locals']) || !is_array($options['locals'])) {
			$options['locals'] = array ();
		}
		$this->response->set_assigns($this, $options['locals']);
		return $options;
	}

	final private function find_template($action_name, $partial = false) {
		// pr($action_name);
		if ($action_name === true) {
			return $this->default_template;
		} else {
			if ($pos = strpos($action_name, '/')) {
				// controller/_action.php
				// TODO: other_prefix_path/controller/_action
				if ($partial) {
					$action_name = substr($action_name, 0, $pos +1) . '_' . substr($action_name, $pos +1);
				}
				return $this->template_root . $action_name . '.php';
			} else {
				// _action.php
				if ($partial) {
					$action_name = '_' . $action_name;
				}
				return $this->template_root . $this->params['controller'] . DS . $action_name . '.php';
			}
		}
	}

	final private function pick_layout($options) {
		if (isset ($options['layout'])) {
			$this->active_layout($options['layout']);
		} else {
			$this->active_layout($this->layout, true);
		}
	}

	/**
	 * layout => false, no layout.
	 * layout => $options['layout'].
	 * layout => $controller->layout.
	 * layout => $controller_name
	 * layout => application.php
	 *
	 * special: render text, using default layout => false.
	 */
	final private function active_layout($layout, $using_default_layout = false) {
		if ($layout === true) {
			$this->find_layout($this->layout, true);
		}
		elseif ($layout) {
			$this->find_layout($layout, $using_default_layout);
		}
	}

	final private function layout_location($layout) {
		return FIREFLY_APP_DIR . DS . 'views' . DS . 'layouts' . DS . $layout . '.php';
	}

	/**
	 * If can not find specific layout, it will trigger a layout missing exception.
	 */
	final private function find_layout($layout, $using_default_layout) {
		$file = $this->layout_location($layout);
		if (!file_exists($file)) {
			if ($using_default_layout) {
				$file = $this->layout_location($this->layout);
				if (!file_exists($file)) {
					$file = $this->layout_location('application');
					if (!file_exists($file)) {
						$file = null;
					}
				}
			} else {
				throw new FireflyException('Specific layout "<b>' . $layout . '</b>" is not exists!');
			}
		}
		$this->response->layout = $file;
	}

	final private function debug_controller() {
		if (DEBUG) {
			$this->logger->debug($this, __FILE__, __LINE__);
			$this->logger->debug($_SERVER);
			$this->logger->output();
		}
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