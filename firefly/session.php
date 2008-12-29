<?php
require 'sessions/session_interface.php';

class Session implements SessionInterface {
	/**
	 * Memcache based session.
	 *
	 * This class enables saving sessions into a database or memcache.
	 * This can be usefull for multiple server sites, and to have more control over sessions.
	 */
	private function __construct() {
	}

	public static function factory($type = 'default') {
		if($type == 'default') {
			$classname = __CLASS__;
			return new $classname; // $_SESSION;
		} else {
			if(include_once 'sessions/session_' . $type . '.php') {
				$classname = 'session' . $type;
				return new $classname;
			} else {
				trigger_error('Can not find session strategy: ' . $type, E_USER_WARNING);
			}
			$class = __CLASS__;
			$session = new $class($type);
			$session->type = $type;
			return $session;
		}
	}

	public function get($key) {
		return $_SESSION[$key];
	}

	public function set($key, $value) {
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
