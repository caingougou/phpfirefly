<?php
class Session {
	/**
	 * Memcache based session.
	 *
	 * This class enables saving sessions into a database or memcache.
	 * This can be usefull for multiple server sites, and to have more control over sessions.
	 */
	private $type;

	private function __construct($type) {
	}

	public static function factory($type = 0) {
		if($type) {
			$class = __CLASS__;
			$session = new $class($type);
			$session->type = $type;
			return $session;
		} else {
			return $_SESSION;
		}
	}

	public function get($key) {
		// TODO
		return $_SESSION[$key];
	}

	public function set($key, $value) {
		// TODO
		$_SESSION[$key] = $value;
	}

	public function reset() {
		$_SESSION = array();
	}

	public function __clone() {
		trigger_error('Clone session is not allowed.', E_USER_ERROR);
	}
}
?>
