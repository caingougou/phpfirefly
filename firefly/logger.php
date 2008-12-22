<?php
if(!defined('DEBUG')) {
	define('DEBUG', 0);
}

if(!defined('LOG_LOCATION')) {
	define("LOG_LOCATION", 1);
}

if(!defined('ENVIRONMENT')) {
	define('ENVIRONMENT', 'development');
}

if(!defined('LOG_COLORING')) {
	defined('LOG_COLORING', 1);
}

class Logger {

	/**
	* 0. production
	* 1. monitor sql statements, warn and info.
	* 2. inspect controller and view object and sql statements
	*/
	public function debug($object, $file_name = null, $line = 0) {
		switch(DEBUG) {
			case 0 :
				break;
			case 1 :
				$this->pr($object, $file_name, $line);
				break;
			case 2 :
				$this->ps($object, $file_name, $line);
		}
	}

	public function warn($warning, $file_name = null, $line = 0) {
		ob_start();
		if(DEBUG) {
			$this->caller($file_name, $line);
			echo "WARN: " . $warning;
		}
		$out = ob_get_clean();
		if(!LOG_LOCATION){
			$out = $this->formater("\n" . $out . "\n", 'yello');
		}
		$this->output($out);
	}

	public function info($info, $file_name = null, $line = 0) {
		ob_start();
		if(DEBUG) {
			$this->caller($file_name, $line);
			echo "INFO: " . $info;
		}
		$out = ob_get_clean();
		if(!LOG_LOCATION){
			$out = $this->formater("\n" . $out . "\n", 'magenta');
		}
		$this->output($out);
	}

	/**
	 * $file_name, which file calls logger.
	 * $line, which line calls logger.
	 */
	private function caller($file_name, $line) {
		if($line) {
			echo "Revoke from: <strong>" . $file_name . "</strong> and line number is: <strong>" . $line . "</strong>";
		}
	}

	private function pr($object, $file_name, $line) {
		ob_start();
		$this->caller($file_name, $line);
		if(!LOG_LOCATION){
			print_r($object);
			$out = ob_get_clean();
			$this->output($this->formater("\n" . $out . "\n", 'red'));
		} else {
			echo '<div style="white-space:pre;">';
			print_r($object);
			echo '</div>';
			$out = ob_get_clean();
			$this->output($out);
		}
	}

	private function ps($object, $file_name, $line) {
		ob_start();
		$this->caller($file_name, $line);
		if(!LOG_LOCATION){
			print_r($object);
			$out = ob_get_clean();
			$this->output($this->formater("\n" . $out . "\n", 'green'));
		} else {
			echo '<div onclick="javascript:this.childNodes[2].style.display=\'block\'"><a href="#">debug</a><br />';
			echo '<div style="white-space:pre; display:none;">';
			print_r($object);
			echo '</div>';
			echo '</div>';
			$out = ob_get_clean();
			$this->output($out);
		}
	}

	public function formater($text, $color = 'normal') {
		if(!LOG_COLORING) {
			return $text;
		}

		$colors = array('light_red ' => '[1;31m', 'light_green' => '[1;32m', 'yellow' => '[1;33m', 'light_blue' => '[1;34m', 'magenta' => '[1;35m', 'light_cyan' => '[1;36m', 'white' => '[1;37m', 'normal' => '[0m', 'black' => '[0;30m', 'red' => '[0;31m', 'green' => '[0;32m', 'brown' => '[0;33m', 'blue' => '[0;34m', 'cyan' => '[0;36m', 'bold' => '[1m', 'underscore' => '[4m', 'reverse' => '[7m');

		return "\033" . (isset($colors[$color]) ? $colors[$color] : '[0m') . $text . "\033[0m";
	}

	private function output($out) {
		// 0. log file, 1. append to page footer
		if(LOG_LOCATION) {
			echo $out;
		} else {
			// write to log/ENVIRONMENT.log file
			$filename = FIREFLY_BASE_DIR . DS . 'log' . DS . ENVIRONMENT . '.log';
			if(is_writable($filename)) {
				if(!$handle = fopen($filename, 'a')) {
					exit;
				}
				if(fwrite($handle, $out) === FALSE) {
					exit;
				}
				fclose($handle);
			} else {
				echo "The file $filename is not writable";
			}
		}
	}
}
?>
