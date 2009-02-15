<?php
include_once ('sessions/interface_session.php');

class Session implements InterfaceSession {

	private $lifetime = 1440;

	// session.save_path, '/tmp' for linux.
	private static $sess_save_path = '/tmp';

	private function __construct() {
		self :: $sess_save_path = FIREFLY_BASE_DIR . DS . 'tmp' . DS . 'sessions';
		$this->lifetime = ini_get('session.gc_maxlifetime');
		session_set_save_handler(array(& $this, 'open'), array(& $this, 'close'), array(& $this, 'read'), array(& $this, 'write'), array(& $this, 'destroy'), array(& $this, 'gc'));
		register_shutdown_function('session_write_close');
		session_start();
	}

	public static function start($type = 'default') {
		if ($type == 'default') {
			$classname = __CLASS__;
			new $classname;
		}
		elseif ($type != 'none') {
			if (include_once ('sessions/' . strtolower($type) . '_session.php')) {
				$classname = 'session' . $type;
				new $classname;
			}
			elseif (include_once (FIREFLY_PLUGINS_DIR . DS . $type . '_session.php')) {
				new $type;
			} else {
				throw new FireflyException('Can not find session strategy: ' . $type, E_USER_ERROR);
			}
		}
	}

	public function open($save_path, $session_name) {
		return true;
	}

	public function close() {
		return true;
	}

	public function read($id) {
		$sess_file = self :: $sess_save_path . DS . "sess_" . $id;
		return (string) @ file_get_contents($sess_file);
	}

	public function write($id, $sess_data) {
		$sess_file = self :: $sess_save_path . DS . "sess_" . $id;
		if ($fp = @ fopen($sess_file, "w")) {
			$return = fwrite($fp, $sess_data);
			fclose($fp);
			return $return;
		} else {
			return false;
		}

	}

	public function destroy($id) {
		$sess_file = self :: $sess_save_path . DS . "sess_" . $id;
		return @ unlink($sess_file);
	}

	public function gc($maxlifetime) {
		foreach (glob(self :: $sess_save_path . DS . "sess_*") as $filename) {
			if (filemtime($filename) + $maxlifetime < time()) {
				@ unlink($filename);
			}
		}
		return true;
	}

	public function __clone() {
		throw new FireflyException('Clone session is not allowed.', E_USER_ERROR);
	}
}
?>
