<?php
include_once('sessions/session_interface.php');

class Session implements SessionInterface {
	/**
	 * Memcache based session.
	 *
	 * This class enables saving sessions into a database or memcache.
	 * This can be usefull for multiple server sites, and to have more control over sessions.
	 */
	private function __construct() {
		session_set_save_handler(array(& $this, 'open'), array(& $this, 'close'), array(& $this, 'read'), array(& $this, 'write'), array(& $this, 'destroy'), array(& $this, 'gc'));
		register_shutdown_function('session_write_close');
		session_start();
	}

	public static function start($type = 'default') {
		if($type == 'default') {
			$classname = __CLASS__;
			$session = new $classname;
		}
		elseif($type != 'none') {
			if(include_once('sessions/session_' . strtolower($type) . '.php')) {
				$classname = 'session' . $type;
				new $classname;
			}
			elseif(include_once('plugins' . DS . 'sessions' . DS . $type . '.php')) {
				new $type;
			} else {
				trigger_error('Can not find session strategy: ' . $type, E_USER_ERROR);
			}
		}
	}

	public function open() {
	}

	public function close() {
	}

	public function read() {
	}

	public function write() {
	}

	public function destroy() {
	}

	public function gc() {
	}

	public function __clone() {
		trigger_error('Clone session is not allowed.', E_USER_ERROR);
	}
}
?>
