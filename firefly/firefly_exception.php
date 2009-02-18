<?php
class FireflyException extends Exception {
	public function __toString() {
		// debug_print_backtrace();
		print '<div style="white-space:pre; border:#f00 1px solid;">' .
		'Exception message: <b>' . $this->getMessage() . '</b> <br />' .
		'<b>Stack trace</b>:<br />' . $this->getTraceAsString() .
		'</div>';
		return __CLASS__;
	}
}
?>