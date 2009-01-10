<?php
class Mysql {

	private static $instance = null;
	private static $conn = null;

	private function __construct() {
	}

	/**
	 * Singleton constructor
	 */
	public static function establish_connection($host, $username, $password, $database) {
		if (self :: $instance == null) {
			self :: $instance = new Mysql();
			self :: $conn = mysql_connect($host, $username, $password);
			mysql_select_db($database);
		}
		return self :: $instance;
	}

	public function execute($sql) {
		//debug_print_backtrace();
		echo $sql . '<br/>';
		$rs = mysql_query($sql) or die(mysql_error());
		return $rs;
	}

	public function fetch($rs) {
		if (is_string($rs)) {
			echo $rs . '<br/>';
			$rs = mysql_query($rs) or die(mysql_error());
		}
		return mysql_fetch_assoc($rs);
	}

	public function fetch_rows($rs) {
		if (is_string($rs)) {
			$rs = mysql_query($rs) or die(mysql_error());
		}
		$rows = array ();
		while (false !== $row = mysql_fetch_assoc($rs)) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function last_insert_id() {
		return mysql_insert_id();
	}

	public function __clone() {
		trigger_error('clone is not allowed.', E_USER_ERROR);
	}
}
?>