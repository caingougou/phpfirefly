<?php
defined('DEBUG') ? null : define('DEBUG', 0);
defined('DEBUG_LEVEL') ? null : define('DEBUG_LEVEL', 'info');
defined('LOG_COLORING') ? null : defined('LOG_COLORING', 1);
defined('LOG_LOCATION') ? null : define("LOG_LOCATION", 'file');
defined('ENVIRONMENT') ? null : define('ENVIRONMENT', 'development');

class Logger {
	private static $logs = array ();
	private static $instance;

	private function __construct() {
	}

	public static function singleton() {
		if (self :: $instance == null) {
			self :: $instance = new Logger;
		}
		return self :: $instance;
	}

	/**
	* 0. production, without debug info.
	* 1. inspect controller and view object and sql statements.
	*/
	public function debug($object, $file_name = null, $line = 0) {
		$this->log(__FUNCTION__, $object, $file_name, $line, "green");
	}

	public function warn($msg, $file_name = null, $line = 0) {
		$this->log(__FUNCTION__, $msg, $file_name, $line, "light_red");
	}

	public function info($info, $file_name = null, $line = 0) {
		$this->log(__FUNCTION__, $info, $file_name, $line, "normal");
	}

	public function error($err, $file_name = null, $line = 0) {
		$this->log(__FUNCTION__, $err, $file_name, $line, "red");
	}

	public function coloring($text, $color = 'normal') {
		if (!LOG_COLORING) {
			return $text;
		}

		$colors = array (
			'light_red' => '[1;31m',
			'light_green' => '[1;32m',
			'yellow' => '[1;33m',
			'light_blue' => '[1;34m',
			'magenta' => '[1;35m',
			'light_cyan' => '[1;36m',
			'white' => '[1;37m',
			'normal' => '[0m',
			'black' => '[0;30m',
			'red' => '[0;31m',
			'green' => '[0;32m',
			'brown' => '[0;33m',
			'blue' => '[0;34m',
			'cyan' => '[0;36m',
			'bold' => '[1m',
			'underscore' => '[4m',
			'reverse' => '[7m'
		);
		return "\033" . (isset ($colors[$color]) ? $colors[$color] : '[0m') . $text . "\033[0m";
	}

	/**
	 * $file_name, which file calls logger.
	 * $line, which line calls logger.
	 */
	private function caller($file_name, $line) {
		if ($file_name) {
			echo "Revoke from: " . $file_name;
		}
		if ($line) {
			echo " and line number is: " . $line;
		}
	}

	private function log_start($level) {
		if (LOG_LOCATION == 'page') {
			if ($level == 'debug') {
				echo '<div><a href="#" onclick="javascript:(function(elem){elem.childNodes[2].style.display=(elem.childNodes[2].style.display==\'block\'?\'none\':\'block\')})(this.parentNode);">debug</a><br />';
				echo '<div style="white-space:pre; display:none; color:magenta; border:#ddd 1px solid;">';
			} else {
				echo '<div style="color:red; font-weight:bold;"><div>';
			}
		}
	}

	private function log_end($level) {
		if (LOG_LOCATION == 'page') {
			echo "</div></div>";
		}
	}

	private function dump($object, $file_name, $line) {
		if (LOG_LOCATION == 'page') {
			return '<div style="white-space:pre;">' . print_r($object, true) . '</div>';
		} else {
			return print_r($object, true);
		}
	}

	private function log($level, $msg, $file_name, $line, $color = 'normal') {
		ob_start();
		if (DEBUG) {
			$this->log_start($level);
			$this->caller($file_name, $line);
			if ($level == 'debug') {
				echo $this->dump($msg, $file_name, $line);
			} else {
				echo $level . ': ' . $msg;
			}
			echo "<br/>";
			$this->log_end($level);
		}
		$out = ob_get_clean();
		if (LOG_LOCATION == 'file') {
			$out = $this->coloring($out, $color);
		}
		self :: $logs[] = array ( $level => $out );
	}

	// 0. log file, 1. append to page footer
	public function output() {
		$out = "\n";
		foreach (self :: $logs as $log) {
			switch (DEBUG_LEVEL) {
				case 'error' :
					$out .= $this->output_error($log);
					break;
				case 'warn' :
					$out .= $this->output_warn($log);
					break;
				case 'debug' :
					$out .= $this->output_bug($log);
					break;
				default :
					$out .= $this->output_info($log);
			}
		}
		if (LOG_LOCATION == 'page') {
			echo $out;
		} else {
			// write to log/ENVIRONMENT.log file
			$filename = FIREFLY_BASE_DIR . DS . 'log' . DS . ENVIRONMENT . '.log';
			if (is_writable($filename)) {
				file_put_contents($filename, preg_replace(array('/\s+/', '/<br\/>/'), array('', "\n"), $out), FILE_APPEND | LOCK_EX);
			} else {
				echo "The file <b>$filename</b> is not writable!";
			}
		}
	}

	public function send_log() {
		// send log/ENVIRONMENT.log to admin email.
	}

	private function output_error($log) {
		if (array_key_exists('error', $log)) {
			return $log['error'];
		}
	}

	private function output_warn($log) {
		foreach ($log as $k => $v) {
			if (array_key_exists($k, array ( 'error', 'warn' ))) {
				return $v;
			}
		}
	}

	private function output_debug($log) {
		foreach ($log as $k => $v) {
			if (array_key_exists($k, array ( 'error', 'warn', 'debug' ))) {
				return $v;
			}
		}
	}

	private function output_info($log) {
		foreach ($log as $k => $v) {
			return $v;
		}
	}
}
?>
