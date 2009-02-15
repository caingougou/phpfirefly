<?php
interface InterfaceSession {
	public function open($save_path, $session_name);
	public function close();
	public function read($id);
	public function write($id, $sess_data);
	public function destroy($id);
	public function gc($maxlifetime);
}
?>