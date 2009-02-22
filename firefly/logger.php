<?php
class Logger {
	private static $logs = array ();
	private static $instance;

	private function __construct() {
		defined('LOG_COLORING') ? null : define('LOG_COLORING', 1); // 0. log disable coloring, 1. log enable coloring.
		defined('DEBUG_LEVEL') ? null : define('DEBUG_LEVEL', 'debug'); // debug, info, warn, error, null.
		defined('LOG_LOCATION') ? null : define("LOG_LOCATION", 'page'); // page, file.
		defined('ENVIRONMENT') ? null : define('ENVIRONMENT', 'development'); // development, production.
	}

	public static function get_reference() {
		if (self :: $instance == null) {
			$class_name = __CLASS__;
			self :: $instance = new $class_name;
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
		$this->log(__FUNCTION__, $info, $file_name, $line, "brown");
	}

	public function error($err, $file_name = null, $line = 0) {
		$this->log(__FUNCTION__, $err, $file_name, $line, "red");
	}

	// 0. log file, 1. append to page footer
	public function output() {
		if (DEBUG_LEVEL) {
			$out = "<br>";
			foreach (self :: $logs as $log) {
				switch (DEBUG_LEVEL) {
					case 'error' :
						$out .= $this->output_error($log);
						break;
					case 'warn' :
						$out .= $this->output_warn($log);
						break;
					case 'debug' :
						$out .= $this->output_debug($log);
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
					file_put_contents($filename, preg_replace(array ( '/\s{2,}|\n/', '/<br>/', '/<\/?b>/' ), array ( " ", "\n", "" ), $out), FILE_APPEND | LOCK_EX);
				} else {
					throw new FireflyException("The file <b>$filename</b> is not writable!");
				}
			}
		}
	}

	public function send_log() {
		// TODO: send log/ENVIRONMENT.log to admin email.
	}

	private function coloring($text, $color = 'normal') {
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

	private function output_error($log) {
		if (in_array('error', $log)) {
			return $log['error'];
		}
	}

	/**
	 * $file_name, which file calls logger.
	 * $line, which line calls logger.
	 */
	private function revoke_from($file_name, $line) {
		if ($file_name) {
			echo "Logger from: <b>" . $file_name . "</b>\n";
		}
		if ($line) {
			echo " and line number is: <b>" . $line . "</b>\n";
		}
	}

	private function dump($object, $file_name, $line) {
		if (LOG_LOCATION == 'page') {
			new Debugger($object, false, true);
		} else {
			print_r($object);
		}
	}

	private function log($level, $msg, $file_name, $line, $color = 'normal') {
		if (DEBUG_LEVEL) {
			ob_start();
			$this->revoke_from($file_name, $line);
			if ($level == 'debug') {
				echo '<br><b>debug</b>: ';
				$this->dump($msg, $file_name, $line);
			} else {
				echo '<br><b>' . $level . '</b>: ' . $msg . '<br>';
			}
			$out = ob_get_clean();
			if (LOG_LOCATION == 'file') {
				$out = $this->coloring($out, $color);
			}
			self :: $logs[] = array ( $level => $out );
		}
	}

	private function output_warn($log) {
		foreach ($log as $k => $v) {
			if (in_array($k, array ( 'error', 'warn' ))) {
				return $v;
			}
		}
	}

	private function output_debug($log) {
		foreach ($log as $k => $v) {
			return $v;
		}
	}

	private function output_info($log) {
		foreach ($log as $k => $v) {
			if (in_array($k, array ( 'error', 'warn', 'info' ))) {
				return $v;
			}
		}
	}
}
?>
