<?php
interface SessionInterface {
	public function open();
	public function close();
	public function read();
	public function write();
	public function destroy();
	public function gc();
}
?>
