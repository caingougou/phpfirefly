<?php
class RenderOptions {
	private $request;
	private $response;
	private $controller;
	private $default_template;
	private $template_root;
	private $layout;
	private $options = array();

	public function __construct($request, $response, $controller) {
		$this->request = $request;
		$this->response = $response;
		$this->controller = $controller;
		$this->layout = $this->controller->layout;
		$this->template_root = FIREFLY_APP_DIR . DS . 'views' . DS;
		$this->default_template = FIREFLY_APP_DIR . DS . 'views' . DS . $this->controller->params['controller'] . DS . $this->controller->params['action'] . '.php';
	}

	public function parse($options) {
		$this->options = $this->parse_shortcut_options($options);
		if(empty($this->options)) {
			$this->render_for_file($this->default_template);
		}
		elseif(isset($this->options['text'])) {
			$this->render_for_text($this->options['text']);
		}
		elseif(isset($this->options['file'])) {
			$this->render_for_file($this->options['file'], $this->options);
		}
		elseif(isset($this->options['template'])) {
			$this->render_for_file($this->find_template($this->options['template']), $this->options);
		}
		elseif(isset($this->options['action'])) {
			$this->render_for_file($this->find_template($this->options['action']), $this->options);
		}
		elseif(isset($this->options['xml'])) {
			$this->response->set_content_type_by_extension('xml');
			$this->render_for_text($this->options['xml']);
		}
		elseif(isset($this->options['js'])) {
			$this->response->set_content_type_by_extension('js');
			$this->render_for_text($this->options['js']);
		}
		elseif(isset($this->options['json'])) {
			if(isset($this->options['callback'])) {
				$this->response->set_content_type_by_extension('js');
				$this->options['json'] = $this->options['callback'] . "({$this->options['json']});";
			} else {
				$this->response->set_content_type_by_extension('json');
			}
			$this->render_for_text($this->options['json']);
		}
		elseif(isset($this->options['partial'])) {
			$this->render_for_file($this->find_template($this->options['partial'], true), $this->options);
		}
		elseif(isset($this->options['update'])) {
			include_once('javascript_generator.php');
			$javascript_generator = new JavaScriptGenerator($this->options['update']);
			$javascript = $javascript_generator->generator();
			$this->response->set_content_type_by_extension('js');
			$this->render_for_text($javascript);
		}
		elseif(isset($this->options['nothing'])) {
			if($this->options['nothing']) {
				$this->render_for_text('');
			} else {
				$this->render_for_file($this->default_template, $this->options);
			}
		} else {
			$this->render_for_file($this->default_template, $this->options);
		}

		$this->options['layout'] = Layout :: get_layout($this->layout, $this->options);
		;
		return $this->options;
	}

	private function render_for_file($file) {
		if(file_exists($file) && preg_match('/^' . preg_quote(FIREFLY_BASE_DIR, '/') . '/', $file)) {
			$this->options['template'] = $file;
		} else {
			throw new FireflyException('File: "<b>' . $file . '</b>" is not exists!');
		}
	}

	private function render_for_text($text) {
		$this->options['content'] = $text;
	}

	/**
	 * chooses between file, template, action and text depending on
	 * whether there is a leading slash (file and file must under FIREFLY_APP_DIR),
	 * or an embedded slash (template),
	 * or no slash and no white space at all in whatÕs to be rendered (action),
	 * or render as string (text).
	 */
	private function parse_shortcut_options($options) {
		if(!is_array($options)) {
			if(is_string($options)) {
				if(file_exists($options)) {
					$options = array('file' => $options);
				}
				elseif(strpos($options, '/') > 0 && file_exists($this->template_root . str_replace('/', DS, $options) . '.php')) {
					$options = array('template' => $options);
				}
				elseif(!preg_match('/\s/', $options) && file_exists($this->template_root . $this->controller->params['controller'] . DS . $options . '.php')) {
					$options = array('action' => $options);
				} else {
					$options = array('text' => $options);
				}
			} else {
				$options = array();
			}
		}
		if(isset($options['content_type'])) {
			$this->response->set_content_type($options['content_type']);
		}
		elseif(isset($options['format'])) {
			$this->response->set_content_type_by_extension($options['format']);
		} else {
			$this->response->set_content_type_by_extension($this->request->format);
		}
		if(isset($options['status'])) {
			$this->response->set_header_status($options['status']);
		}
		if(isset($options['location'])) {
			$this->response->redirect_to($options['location']);
		}
		if(empty($options['locals']) || !is_array($options['locals'])) {
			$options['locals'] = array();
		}

		return $options;
	}

	/**
	 * when $partial is true, extract controller and partial action:
	 * action => _action
	 * controller/action => controller/_action
	 * other_prefix_path/controller/action => other_prefix_path/controller/_action
	 */
	private function find_template($action_name, $partial = false) {
		if($action_name === true) {
			return $this->default_template;
		}
		elseif(file_exists($action_name)) {
			return $action_name;
		} else {
			if(strpos($action_name, '/') > 0) {
				if($partial) {
					$parts = explode('/', $action_name);
					$size = count($parts);
					$action_name = $parts[$size -2] . DS . '_' . $parts[$size -1];
				}
				return $this->template_root . $action_name . '.php';
			} else {
				if($partial) {
					$action_name = '_' . $action_name;
				}
				return $this->template_root . $this->controller->params['controller'] . DS . $action_name . '.php';
			}
		}
	}
}
?>
