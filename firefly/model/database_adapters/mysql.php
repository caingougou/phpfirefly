<?php

class Mysql {

	static $instance = null;
	static $conn = null;

	private function __construct() {}

	public static function conn($host, $user, $pass, $db) {
		if(self::$instance == null) {
			self::$instance = new Mysql();
			self::$conn = mysql_connect($host, $user, $pass);
			mysql_select_db($db);
		}
		return self::$instance;
	}

	public function execute($sql) {
//		echo '<!--small>'.$sql.'</small><br/-->';
//		echo $sql.'<br/>';
		$rs = mysql_query($sql) or die(mysql_error());
		return $rs;
	}

	public function fetch($rs) {
		return mysql_fetch_assoc($rs);
	}

	public function fetch_rows($rs) {
//		echo $rs.'<br/>';
		if(is_string($rs)) {
			$rs = mysql_query($rs);
		}
		$rows = array();
		while(false !== $row = mysql_fetch_assoc($rs)) {
			$rows[] = $row;
		}
		return $rows;
	}

	public function last_insert_id() {
		return mysql_insert_id();
	}
}

?>