<?php
class JavaScriptGenerator {

	private $options;

	/**
	 * TODO: update page using ajax, options['update'] is an array with below keys:
	 * alert, hide, insert_html, remove, replace, replace_html, select, show, toggle.
	 */
	public function __construct($options) {
		$this->options = $options;
	}

	public function generator() {
		if (!is_array($this->options)) {
			throw new FireflyException('$options is expected to Array');
		}
		$js = "";
		if (isset ($this->options['alert'])) {
			$js .= $this->alert();
		}
		if (isset ($this->options['hide'])) {
			$js .= $this->hide();
		}
		if (isset ($this->options['show'])) {
			$js .= $this->show();
		}
		if (isset ($this->options['insert_html'])) {
			$js .= $this->insert_html();
		}
		if (isset ($this->options['remove'])) {
			$js .= $this->remove();
		}
		if (isset ($this->options['replace'])) {
			$js .= $this->replace();
		}
		if (isset ($this->options['replace_html'])) {
			$js .= $this->replace_html();
		}
		if (isset ($this->options['select'])) {
			$js .= $this->select();
		}
		if (isset ($this->options['toggle'])) {
			$js .= $this->toggle();
		}
		if (DEBUG_LEVEL) {
			$js .= "\n" . "/*" . "\n";
			$js .= var_export($this->options, true);
			$js .= "\n" . "*/" . "\n";
		}
		return $js;
	}

	private function alert() {
		$message = $this->options['alert'];
		return "alert('" . $message . "');" . "\n";
	}

	private function hide() {
		$element = $this->options['hide'];
		$js = "$('" . $element . "').style.display = 'none';" . "\n";
		return $js;
	}

	private function show() {
		$element = $this->options['show'];
		$js = "$('" . $element . "').style.display = 'block';" . "\n";
		return $js;
	}

	private function remove() {
		return "";
	}

	private function replace() {
		return "";
	}

	private function select() {
		return "";
	}

	private function toggle() {
		return "";
	}

	private function insert_html() {
		return "";
	}

	private function replace_html() {
		return "";
	}
}
?>
