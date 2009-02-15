<?php

/**
 *
And don't miss the table dump. ^^

CREATE TABLE IF NOT EXISTS `sessions` (
  `session` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  `session_expires` int(10) unsigned NOT NULL default '0',
  `session_data` text collate utf8_unicode_ci,
  PRIMARY KEY  (`session`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
class Session {
	/**
	 * a database connection resource
	 * @var resource
	 */
	private static $_sess_db;

	/**
	 * Open the session
	 * @return bool
	 */
	public static function open() {

		if(self :: $_sess_db = mysql_connect('localhost', 'root', '')) {
			return mysql_select_db('my_application', self :: $_sess_db);
		}
		return false;
	}

	/**
	 * Close the session
	 * @return bool
	 */
	public static function close() {
		return mysql_close(self :: $_sess_db);
	}

	/**
	 * Read the session
	 * @param int session id
	 * @return string string of the sessoin
	 */
	public static function read($id) {
		$id = mysql_real_escape_string($id);
		$sql = sprintf("SELECT `session_data` FROM `sessions` " .
		"WHERE `session` = '%s'", $id);
		if($result = mysql_query($sql, self :: $_sess_db)) {
			if(mysql_num_rows($result)) {
				$record = mysql_fetch_assoc($result);
				return $record['session_data'];
			}
		}
		return '';
	}

	/**
	 * Write the session
	 * @param int session id
	 * @param string data of the session
	 */
	public static function write($id, $data) {
		$sql = sprintf("REPLACE INTO `sessions` VALUES('%s', '%s', '%s')", mysql_real_escape_string($id), mysql_real_escape_string(time()), mysql_real_escape_string($data));
		return mysql_query($sql, self :: $_sess_db);
	}

	/**
	 * Destoroy the session
	 * @param int session id
	 * @return bool
	 */
	public static function destroy($id) {
		$sql = sprintf("DELETE FROM `sessions` WHERE `session` = '%s'", $id);
		return mysql_query($sql, self :: $_sess_db);
	}

	/**
	 * Garbage Collector
	 * @param int life time (sec.)
	 * @return bool
	 * @see session.gc_divisor      100
	 * @see session.gc_maxlifetime 1440
	 * @see session.gc_probability    1
	 * @usage execution rate 1/100
	 *        (session.gc_probability/session.gc_divisor)
	 */
	public static function gc($max) {
		$sql = sprintf("DELETE FROM `sessions` WHERE `session_expires` < '%s'", mysql_real_escape_string(time() - $max));
		return mysql_query($sql, self :: $_sess_db);
	}
}

//ini_set('session.gc_probability', 50);
ini_set('session.save_handler', 'user');

session_set_save_handler(array('Session', 'open'), array('Session', 'close'), array('Session', 'read'), array('Session', 'write'), array('Session', 'destroy'), array('Session', 'gc'));

if(session_id() == "")
	session_start();
//session_regenerate_id(false); //also works fine
if(isset($_SESSION['counter'])) {
	$_SESSION['counter']++;
} else {
	$_SESSION['counter'] = 1;
}
echo '<br/>SessionID: ' . session_id() . '<br/>Counter: ' . $_SESSION['counter'];
?>
