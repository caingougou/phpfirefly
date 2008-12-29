<?php
defined('SESSION_STORE_STRATEGY') ? null : define('SESSION_STORE_STRATEGY', 0);

class Controller {
	public $params;
	public $helper;
	public $page_title;
	public $logger;
	public $request;
	public $response;
	public $session;
	public $default_template;
	public $layout = false;
	public $auto_render = true;

	private $template_root;

	public function __construct() {
		$this->logger = new Logger;
		$this->session = Session :: factory(SESSION_STORE_STRATEGY);
		$this->template_root = FIREFLY_APP_DIR . DS . 'views' . DS;

		if(is_subclass_of($this, 'ApplicationController')) {
			$application_vars = get_class_vars('ApplicationController');
			if(!is_array($this->helper)) {
				$this->helper = array($this->helper);
			}
			if(isset($application_vars['helper']) && !empty($application_vars['helper'])) {
				$diff = array_diff($application_vars['helper'], $this->helper);
				$this->helper = array_merge($this->helper, $diff);
			}
		}
	}

	public function debug($object, $file_name = __FILE__, $line = __LINE__) {
		$this->logger->debug($object, $file_name, $line);
	}

	public function warn($warn, $file_name = __FILE__, $line = __LINE__) {
		$this->logger->warn($warn, $file_name, $line);
	}

	public function info($info, $file_name = __FILE__, $line = __LINE__) {
		$this->logger->info($info, $file_name, $line);
	}

	public function redirect_to($url) {
		$this->response->redirect_to($url);
	}

	public function send_file($file) {
		$this->response->send_file($file);
	}

	public function before_filter() {
	}

	public function before_render() {
	}

	public function after_render() {
	}

	public function after_filter() {
	}

	public function method_missing($params) {
		$this->render(array('template' => FIREFLY_LIB_DIR . DS . 'view' . DS . 'method_missing.php'));
	}

	public function __toString() {
		return get_class($this);
	}

	public function __call($method, $args) {
		$this->warn("$method not in this controller: " . __CLASS__, __FILE__, __LINE__);
	}

	public function url_for($info) {
		// TODO: $info is array('controller' => 'test', 'action' => 'index') or $info is url string.
		return $info;
	}

	public function reset_session() {
		$this->session->reset();
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
	public function render($options = array()) {
		$this->layout = $this->params['controller'];
		$this->default_template = FIREFLY_APP_DIR . DS . 'views' . DS . $this->params['controller'] . DS . $this->params['action'] . '.php';
		$options = $this->parse_render_options($options);
		$this->auto_render = false;
		$this->before_render();
		$this->render_action($options);
		$this->after_render();
	}

	public function render_action($options) {
		if(empty($options)) {
			$this->render_for_file($this->default_template);
		}
		elseif(isset($options['text'])) {
			if(isset($options['layout']) && $options['layout']) {
				$this->pick_layout($options);
			}
			$this->render_for_text($options['text']);
		}
		elseif(isset($options['file'])) {
			$this->render_for_file($options['file'], $options);
		}
		elseif(isset($options['template'])) {
			$this->render_for_file($this->find_template($options['template']), $options);
		}
		elseif(isset($options['inline'])) {
			$this->pick_layout($options);
			// TODO: render inline php statement
			$this->render_for_text($options['inline']);
		}
		elseif(isset($options['action'])) {
			$this->render_for_file($this->find_template($options['action']), $options);
		}
		elseif(isset($options['xml'])) {
			$this->response->set_content_type_by_extension('xml');
			// TODO: $options['xml'] parse to xml.
			$this->render_for_text($options['xml']);
		}
		elseif(isset($options['js'])) {
			$this->response->set_content_type_by_extension('js');
			$this->render_for_text($options['js']);
		}
		elseif(isset($options['json'])) {
			$this->response->set_content_type_by_extension('json');
			if(isset($options['callback'])) {
				$options['json'] = $options['callback'] . "({$options['json']});";
			}
			$this->render_for_text($options['json']);
		}
		elseif(isset($options['partial'])) {
			$this->render_for_file($this->find_template($options['partial'], true), $options);
		}
		elseif(isset($options['update'])) {
			$this->response->set_content_type_by_extension('js');
			// TODO: update partial using ajax
			$this->render_for_text($options['update']);
		}
		elseif(isset($options['nothing'])) {
			if($options['nothing']) {
				$this->render_for_text('');
			} else {
				$this->render_for_file($this->default_template, $options);
			}
		} else {
			$this->render_for_file($this->default_template, $options);
		}

		$out = $this->response->output();
		echo $out;
	}

	private function render_for_file($file, $options = array()) {
		if(file_exists($file)) {
			$this->response->template = $file;
		} else {
			$this->response->content = 'Template: "' . $options['template'] . '" does not exists!';
		}
		$this->pick_layout($options);
	}

	private function render_for_text($text) {
		$this->response->content = $text;
	}

	/**
	 * chooses between file, template, action and text depending on
	 * whether there is a leading slash (file),
	 * or an embedded slash (template),
	 * or no slash and no white space at all in what’s to be rendered (action),
	 * or render as string (text).
	 */
	private function parse_render_options($options) {
		if(!is_array($options)) {
			if(is_string($options)) {
				if(substr($options, 0, 1) == '/' && file_exists($options)) {
					$options = array('file' => $options);
				}
				elseif(strpos($options, '/') > 0 && file_exists($this->template_root . $options . '.php')) {
					$options = array('template' => $options);
				}
				elseif(!preg_match('/\s/', $options) && file_exists($this->template_root . $this->params['controller'] . DS . $options . '.php')) {
					$options = array('action' => $options);
				} else {
					$options = array('text' => $options);
				}
			} else {
				$options = array();
			}
		}
		if($options['content_type']) {
			$this->response->set_content_type($options['content_type']);
		}
		if($options['status']) {
			$this->response->set_header_status($options['status']);
		}
		if($options['location']) {
			$this->response->redirect_to($this->url_for($options['location']));
		}
		if(empty($options['locals']) || !is_array($options['locals'])) {
			$options['locals'] = array();
		}
		$this->set_response_assigns($options['locals']);
		return $options;
	}

	private function find_template($action_name, $partial = false) {
		if($action_name === true) {
			return $this->default_template;
		} else {
			if($pos = strpos($action_name, '/')) {
				// controller/_action.php
				if($partial) {
					$action_name = substr($action_name, 0, $pos +1) . '_' . substr($action_name, $pos +1);
				}
				return $this->template_root . $action_name . '.php';
			} else {
				// _action.php
				if($partial) {
					$action_name = '_' . $action_name;
				}
				return $this->template_root . $this->params['controller'] . DS . $action_name . '.php';
			}
		}
	}

	private function set_response_assigns($locals) {
		$vars = array_merge(get_object_vars($this), $locals);
		$vars['response'] = null;
		$this->response->assigns = $vars;
	}

	private function pick_layout($options) {
		if(isset($options['layout'])) {
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
	 * special: render text, default layout => false.
	 */
	private function active_layout($layout, $default = false) {
		if($layout === true) {
			$this->find_layout($this->layout, true);
		}
		elseif($layout) {
			$this->find_layout($layout, $default);
		}
	}

	private function layout_location($layout) {
		return FIREFLY_APP_DIR . DS . 'views' . DS . 'layouts' . DS . $layout . '.php';
	}

	/**
	 * if not to find default layout and can not find specific layout
	 * will trigger an layout missing warning.
	 */
	private function find_layout($layout, $default) {
		$file = $this->layout_location($layout);
		if(!file_exists($file)) {
			if($default) {
				$file = $this->layout_location($this->layout);
				if(!file_exists($file)) {
					$file = $this->layout_location('application');
					if(!file_exists($file)) {
						$file = null;
					}
				}
			} else {
				trigger_error('layout "' . $file . '" is not exists!', E_USER_WARNING);
				$file = null;
			}
		}
		$this->response->layout = $file;
	}

}
?>