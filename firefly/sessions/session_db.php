<?php
class SessionDb implements SessionInterface {
	// create table for session.
	// CREATE TABLE `ws_sessions` ( `session_id` varchar(255) binary NOT NULL default '', `session_expires` int(10) unsigned NOT NULL default '0', `session_data` text, PRIMARY KEY  (`session_id`) ) TYPE=InnoDB;

	// session-life_time
	private $life_time;
	// mysql-handler
	private $handler;

	public function __construct() {
		session_set_save_handler(array(& $this, 'open'), array(& $this, 'close'), array(& $this, 'read'), array(& $this, 'write'), array(& $this, 'destroy'), array(& $this, 'gc'));
		register_shutdown_function('session_write_close');
		session_start();
	}

	public function open($save_path, $sess_name) {
		// get session-life_time
		$this->life_time = get_cfg_var("session.gc_maxlife_time");
		// open database-connection
		$handler = mysql_connect("server", "user", "password");
		$db_sel = mysql_select_db("database", $handler);
		// return success
		if(!$handler || !$db_sel)
			return false;
		$this->handler = $handler;
		return true;
	}

	public function close() {
		$this->gc(ini_get('session.gc_maxlife_time'));
		// close database-connection
		return @ mysql_close($this->handler);
	}

	public function read($sess_id) {
		// fetch session-data
		$res = mysql_query("SELECT session_data AS d FROM ws_sessions WHERE session_id = '$sess_id' AND session_expires > " . time(), $this->handler);
		// return data or an empty string at failure
		if($row = mysql_fetch_assoc($res))
			return $row['d'];
		return "";
	}

	public function write($sess_id, $sess_data) {
		// new session-expire-time
		$new_exp = time() + $this->life_time;
		// is a session with this id in the database?
		$res = mysql_query("SELECT * FROM ws_sessions WHERE session_id = '$sess_id'", $this->handler); // if yes,
		if(mysql_num_rows($res)) {
			// ...update session-data
			mysql_query("UPDATE ws_sessions SET session_expires = '$new_exp', session_data = '$sess_data' WHERE session_id = '$sess_id'", $this->handler);
			// if something happened, return true
			if(mysql_affected_rows($this->handler))
				return true;
		}
		// if no session-data was found,
		else {
			// create a new row
			mysql_query("INSERT INTO ws_sessions ( session_id, session_expires, session_data) VALUES( '$sess_id', '$new_exp', '$sess_data')", $this->handler);
			// if row was created, return true
			if(mysql_affected_rows($this->handler))
				return true;
		}
		// an unknown error occured
		return false;
	}

	public function destroy($sess_id) {
		// delete session-data
		mysql_query("DELETE FROM ws_sessions WHERE session_id = '$sess_id'", $this->handler);
		// if session was deleted, return true,
		if(mysql_affected_rows($this->handler))
			return true;
		// ...else return false
		return false;
	}

	public function gc($sess_max_life_time) {
		// delete old sessions
		mysql_query("DELETE FROM ws_sessions WHERE session_expires < " . time(), $this->handler);
		// return affected rows
		return mysql_affected_rows($this->handler);
	}
}
?>