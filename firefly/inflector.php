<?php
class Inflector {

	public static function singular($str) {
		$str = strtolower(trim($str));
		$end = substr($str, -3);
		if($end == 'ies') {
			$str = substr($str, 0, strlen($str) - 3) . 'y';
		}
		elseif($end == 'ses') {
			$str = substr($str, 0, strlen($str) - 2);
		} else {
			$end = substr($str, -1);
			if($end == 's') {
				$str = substr($str, 0, strlen($str) - 1);
			}
		}
		return $str;
	}

	public static function plural($str, $force = FALSE) {
		$str = strtolower(trim($str));
		$end = substr($str, -1);
		if($end == 'y') {
			$str = substr($str, 0, strlen($str) - 1) . 'ies';
		}
		elseif($end == 's') {
			if($force == TRUE) {
				$str .= 'es';
			}
		} else {
			$str .= 's';
		}
		return $str;
	}

	public static function camelize($str) {
		$str = 'x' . strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
		return substr(str_replace(' ', '', $str), 1);
	}

	public static function humanize($str) {
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}

}
?>