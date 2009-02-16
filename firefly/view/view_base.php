<?php
class ViewBase {
	private $template;
	private $template_root;
	private $default_template;
	private $response;
	private $controller;
	private $layout;
	private $assigns = array ();
	private $content = null;

	public function __construct($controller, $response) {
		$this->controller = $controller;
		$this->response = $response;
		$this->layout = $this->controller->layout;
		$this->template_root = FIREFLY_APP_DIR . DS . 'views' . DS;
		$this->default_template = FIREFLY_APP_DIR . DS . 'views' . DS . $this->controller->params['controller'] . DS . $this->controller->params['action'] . '.php';
	}

	final public function render($options) {
		$this->render_action($this->parse_render_options($options));
	}

	final private function render_action($options) {
		if (empty ($options)) {
			$this->render_for_file($this->default_template);
		}
		elseif (isset ($options['text'])) {
			$this->render_for_text($options['text']);
		}
		elseif (isset ($options['file'])) {
			$this->render_for_file($options['file'], $options);
		}
		elseif (isset ($options['template'])) {
			$this->render_for_file($this->find_template($options['template']), $options);
		}
		elseif (isset ($options['inline'])) {
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
		$options['layout'] = $this->pick_layout($options);;
		echo $this->output($options);
	}

	final private function render_for_file($file, $options = array ()) {
		// $this->logger->info($file, __FILE__, __LINE__);
		if (file_exists($file) && preg_match('/^' . preg_quote(FIREFLY_BASE_DIR, '/') . '/', $file)) {
			$this->template = $file;
		} else {
			 throw new FireflyException('Template: "' . $file . '" is not exists!');
		}
	}

	final private function render_for_text($text) {
		$this->content = $text;
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
				if (file_exists($options)) {
					$options = array ( 'file' => $options );
				}
				elseif (strpos($options, '/') > 0 && file_exists($this->template_root . str_replace('/', DS, $options) . '.php')) {
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
		$this->set_assigns($options['locals']);
		return $options;
	}

	final private function set_assigns($locals) {
		$vars = array_merge(get_object_vars($this->controller), $locals);
		unset ($vars['view']);
		$this->assigns = $vars;
	}

	/**
	 * when $partial is true, extract controller and partial action:
	 * action => _action
	 * controller/action => controller/_action
	 * other_prefix_path/controller/action => other_prefix_path/controller/_action
	 */
	final private function find_template($action_name, $partial = false) {
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
				return $this->template_root . $this->params['controller'] . DS . $action_name . '.php';
			}
		}
	}

	final private function pick_layout($options) {
		if (isset ($options['layout'])) {
			return $this->active_layout($options['layout']);
		}
		elseif (isset ($options['text']) || isset ($options['partial'])) {
			return $this->active_layout(false);
		} else {
			return $this->active_layout($this->layout, true);
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
			return $this->find_layout($this->layout, true);
		}
		elseif ($layout) {
			return $this->find_layout($layout, $using_default_layout);
		} else {
			return null;
		}
	}

	final private function layout_location($layout) {
		if (file_exists($layout)) {
			return $layout;
		}
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
		return $file;
	}

	final private function output($options) {
		foreach ($this->response->get_headers() as $header) {
			header($header);
		}
		extract($this->assigns, EXTR_SKIP);
		$controller_name = isset ($controller_name) ? $controller_name : $this->controller->params['controller'];
		$action_name = isset ($action_name) ? $action_name : $this->controller->params['action'];

		ob_start();
		if (is_null($this->content)) {
			include ($this->template);
		} else {
			echo $this->content;
		}
		$content_for_layout = ob_get_clean();
		if ($options['layout']) {
			ob_start();
			include ($options['layout']);
			return ob_get_clean();
		} else {
			return $content_for_layout;
		}
	}
}
?>
