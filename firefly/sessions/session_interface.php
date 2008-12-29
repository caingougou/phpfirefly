<?php
interface SessionInterface {
	function get($key);
	function set($key, $value);
	function reset();
}
?>
