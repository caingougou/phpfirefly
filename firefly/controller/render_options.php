<?php
class RenderOptions {
	private $controller;
	private $default_template;
	private $template_root;
	private $layout;
	private $options = array();

	public function __construct($controller) {
		$this->controller = $controller;
		$this->layout = $this->controller->layout;
		$this->template_root = FIREFLY_APP_DIR . DS . 'views' . DS;
		$this->default_template = FIREFLY_APP_DIR . DS . 'views' . DS . $this->controller->params['controller'] . DS . $this->controller->params['action'] . '.php';
	}

	public function parse($options) {
		$this->options = $options;
		$this->parse_shortcut_options();
		if (empty ($this->options)) {
			$this->render_for_file($this->default_template);
		}
		elseif (isset ($this->options['text'])) {
			$this->render_for_text($this->options['text']);
		}
		elseif (isset ($this->options['file'])) {
			$this->render_for_file($this->options['file'], $this->options);
		}
		elseif (isset ($this->options['template'])) {
			$this->render_for_file($this->find_template($this->options['template']), $this->options);
		}
		elseif (isset ($this->options['inline'])) {
			// TODO: render inline php statement
			$this->render_for_text($this->options['inline']);
		}
		elseif (isset ($this->options['action'])) {
			$this->render_for_file($this->find_template($this->options['action']), $this->options);
		}
		elseif (isset ($this->options['xml'])) {
			$this->response->set_content_type_by_extension('xml');
			// TODO: $this->options['xml'] parse to xml.
			$this->render_for_text($this->options['xml']);
		}
		elseif (isset ($this->options['js'])) {
			$this->response->set_content_type_by_extension('js');
			$this->render_for_text($this->options['js']);
		}
		elseif (isset ($this->options['json'])) {
			$this->response->set_content_type_by_extension('json');
			if (isset ($this->options['callback'])) {
				$this->options['json'] = $this->options['callback'] . "({$this->options['json']});";
			}
			$this->render_for_text($this->options['json']);
		}
		elseif (isset ($this->options['partial'])) {
			$this->render_for_file($this->find_template($this->options['partial'], true), $this->options);
		}
		elseif (isset ($this->options['update'])) {
			$this->response->set_content_type_by_extension('js');
			// TODO: update partial using ajax
			$this->render_for_text($this->options['update']);
		}
		elseif (isset ($this->options['nothing'])) {
			if ($this->options['nothing']) {
				$this->render_for_text('');
			} else {
				$this->render_for_file($this->default_template, $this->options);
			}
		} else {
			$this->render_for_file($this->default_template, $this->options);
		}

		$this->options['layout'] = Layout :: get_layout($this->layout, $this->options);;
		return $this->options;
	}

	private function render_for_file($file) {
		// $this->logger->info($file, __FILE__, __LINE__);
		if (file_exists($file) && preg_match('/^' . preg_quote(FIREFLY_BASE_DIR, '/') . '/', $file)) {
			$this->options['template']= $file;
		} else {
			 throw new FireflyException('Template: "' . $file . '" is not exists!');
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
	private function parse_shortcut_options() {
		if (!is_array($this->options)) {
			if (is_string($this->options)) {
				if (file_exists($this->options)) {
					$this->options = array ( 'file' => $this->options );
				}
				elseif (strpos($this->options, '/') > 0 && file_exists($this->template_root . str_replace('/', DS, $this->options) . '.php')) {
					$this->options = array ( 'template' => $this->options );
				}
				elseif (!preg_match('/\s/', $this->options) && file_exists($this->template_root . $this->controller->params['controller'] . DS . $this->options . '.php')) {
					$this->options = array ( 'action' => $this->options );
				} else {
					$this->options = array ( 'text' => $this->options );
				}
			} else {
				$this->options = array ();
			}
		}
		if (isset ($this->options['content_type'])) {
			$this->response->set_content_type($this->options['content_type']);
		}
		if (isset ($this->options['status'])) {
			$this->response->set_header_status($this->options['status']);
		}
		if (isset ($this->options['location'])) {
			$this->response->redirect_to($this->url_for($this->options['location']));
		}
		if (empty ($this->options['locals']) || !is_array($this->options['locals'])) {
			$this->options['locals'] = array ();
		}

		return $this->options;
	}

	/**
	 * when $partial is true, extract controller and partial action:
	 * action => _action
	 * controller/action => controller/_action
	 * other_prefix_path/controller/action => other_prefix_path/controller/_action
	 */
	private function find_template($action_name, $partial = false) {
		if ($action_name === true) {
			return $this->default_template;
		} else {
			if (strpos($action_name, '/') > 0) {
				if ($partial) {
					$parts = explode('/', $action_name);
					$size = count($parts);
					$action_name = $parts[$size - 2] . DS . '_' . $parts[$size - 1];
				}
				return $this->template_root . $action_name . '.php';
			} else {
				if ($partial) {
					$action_name = '_' . $action_name;
				}
				return $this->template_root . $this->controller->params['controller'] . DS . $action_name . '.php';
			}
		}
	}
}
?>
