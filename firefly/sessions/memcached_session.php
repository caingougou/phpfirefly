<?php
class MemcachedSession implements InterfaceSession {
	/**
	 * Memcache based session.
	 *
	 * This class enables saving sessions into a database or memcache.
	 * This can be usefull for multiple server sites, and to have more control over sessions.
	 */
	private static $lifetime = 0;

	private function __construct() {
		self::$lifetime = ini_get('session.gc_maxlifetime');
		session_set_save_handler(array(& $this, 'open'), array(& $this, 'close'), array(& $this, 'read'), array(& $this, 'write'), array(& $this, 'destroy'), array(& $this, 'gc'));
		register_shutdown_function('session_write_close');
		session_start();
	}

	public static function open($save_path, $sess_name) {
		return true;
	}

	public static function read($id) {
		return memcached :: get("sessions/{$id}");
	}

	public static function write($id, $data) {
		// auto gc
		return memcached :: set("sessions/{$id}", $data, self :: $lifetime);
	}

	public static function destroy($id) {
		return memcached :: delete("sessions/{$id}");
	}

	public static function gc($sess_max_life_time) {
		return true;
	}

	public static function close() {
		return true;
	}

	public function __destruct() {
		session_write_close();
	}
}
?>
