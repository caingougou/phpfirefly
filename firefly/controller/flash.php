<?php
class Flash {
	private static $instance = null;
	private $flash = array ();
	private $keys = array ();

	public static function get_reference() {
		if (self :: $instance == null) {
			self :: $instance = new Flash;
		}
		return self :: $instance;
	}

	private function __construct() {
		if (!isset ($_SESSION['flash'])) {
			$_SESSION['flash'] = array ();
		}
		$this->flash = $_SESSION['flash'];
		$_SESSION['flash'] = array ();
	}

	public function get($key) {
		if (isset ($this->flash[$key])) {
			return $this->flash[$key];
		} else {
			return null;
		}
	}

	public function set($key, $value = null) {
		$_SESSION['flash'][$key] = $value;
	}

	/**
	 * This flash $key only access in current page, can't access in next page.
	 */
	public function now($key, $value = null) {
		$this->flash[$key] = $value;
		if (isset ($_SESSION['flash'][$key])) {
			unset ($_SESSION['flash'][$key]);
		}
	}

	/**
	 * Keep flash $key to next page.
	 */
	public function keep($key) {
		$_SESSION['flash'][$key] = isset ($this->flash[$key]) ? $this->flash[$key] : null;
	}

	/**
	 * Remove flash $key.
	 */
	public function discard($key) {
		if (isset ($this->flash[$key])) {
			unset ($this->flash[$key]);
		}
		if (isset ($_SESSION['flash'][$key])) {
			unset ($_SESSION['flash'][$key]);
		}
	}

	/**
	 * return flash keys.
	 */
	public function get_keys() {
		return array_keys($this->flash);
	}

}
?>