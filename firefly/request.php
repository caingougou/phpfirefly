<?php
class Request {
	public $env = array ();
	public $method = 'get';
	public $format = 'html';
	public $path = 'index.php';

	public function __construct() {
		$this->env = & $_SERVER;
		$this->path = $_GET['fireflypath'];
		$this->get_format();
	}

	/**
	* Returns true if the request include (X-Requested-With => XMLHttpRequest).
	* The Prototype Javascript library sends this header with every Ajax request.
	*/
	public function xml_http_request() {
		return !empty ($this->env['HTTP_X_REQUESTED_WITH']) && strstr(strtolower($this->env['HTTP_X_REQUESTED_WITH']), 'xmlhttprequest');
	}

	public function xhr() {
		return $this->xml_http_request();
	}

	// Return true if the request came from localhost, 127.0.0.1
	public function local_request() {
		return ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' && $this->remote_ip() == '127.0.0.1');
	}

	public function remote_ip() {
		if (isset ($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			// remote_addr may be proxy address.
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			return $_SERVER['REMOTE_ADDR'];
		}
	}

	// Firstly merge POST and GET parameters in a single hash, then update hash by path parameters.
	public function parameters() {
		$params = array_merge($_POST, $_GET);
		// hack HTTP PUT/DELETE methods, for http verb request.
		if (isset ($params['_method']) && in_array(strtoupper($params['_method']), array (
				'GET',
				'POST',
				'PUT',
				'DELETE'
			))) {
			$_SERVER['REQUEST_METHOD'] = strtoupper($params['_method']);
			unset ($params['_method']);
		}
		$this->method = strtolower($_SERVER['REQUEST_METHOD']);
		$params = array_merge($params, $this->path_parameters());
		if (empty ($params['action'])) {
			$params['action'] = 'index';
		}
		return $params;
	}

	public function path_parameters() {
		return Router :: recognize($this);
	}

	public function get_format() {
		if (!empty ($_GET['format'])) {
			$this->format = $_GET['format'];
		}
		elseif (!empty ($_POST['format'])) {
			$this->format = $_POST['format'];
		} else {
			$this->format = 'php';
		}
		return $this->format;
	}

}
?>