<?php
class SessionMemcached implements SessionInterface {
	/**
	 * Memcache based session.
	 *
	 * This class enables saving sessions into a database or memcache.
	 * This can be usefull for multiple server sites, and to have more control over sessions.
	 */
	private static $lifetime = 0;

	public static function open() {
		self :: $lifetime = ini_get('session.gc_maxlifetime');
		return true;
	}

	public static function read($id) {
		return memcached :: get("sessions/{$id}");
	}

	public static function write($id, $data) {
		return memcached :: set("sessions/{$id}", $data, self :: $lifetime);
	}

	public static function destroy($id) {
		return memcached :: delete("sessions/{$id}");
	}

	private function __construct() {
	}

	public static function gc() {
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
