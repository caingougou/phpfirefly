<?php
class Layout {
	private static $instance = null;
	private $layout;

	public function __construct($layout) {
		$this->layout = $layout;
	}

	public static function get_layout($layout, $options) {
		if (!self :: $instance) {
			$class_name = __CLASS__;
			self :: $instance = new $class_name ($layout);
		}
		return self :: $instance->pick_layout($options);
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
}
?>
