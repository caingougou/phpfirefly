<?php
include_once('controller' . DS . 'mime_type.php');

class Response {
	private $headers = array();
	private $content_type = 'text/html';

	public function __construct() {
	}

	public function add_header($header) {
		array_push($this->headers, $header);
	}

	public function send_headers() {
		foreach($this->headers as $header) {
			header($header);
		}
	}

	public function set_header_status($status_code) {
		$status_codes = array (
			100 => 'HTTP/1.1 100 Continue',
			101 => 'HTTP/1.1 101 Switching Protocols',
			200 => 'HTTP/1.1 200 OK',
			201 => 'HTTP/1.1 201 Created',
			202 => 'HTTP/1.1 202 Accepted',
			203 => 'HTTP/1.1 203 Non-Authoritative Information',
			204 => 'HTTP/1.1 204 No Content',
			205 => 'HTTP/1.1 205 Reset Content',
			206 => 'HTTP/1.1 206 Partial Content',
			300 => 'HTTP/1.1 300 Multiple Choices',
			301 => 'HTTP/1.1 301 Moved Permanently',
			302 => 'HTTP/1.1 302 Found',
			303 => 'HTTP/1.1 303 See Other',
			304 => 'HTTP/1.1 304 Not Modified',
			305 => 'HTTP/1.1 305 Use Proxy',
			307 => 'HTTP/1.1 307 Temporary Redirect',
			400 => 'HTTP/1.1 400 Bad Request',
			401 => 'HTTP/1.1 401 Unauthorized',
			402 => 'HTTP/1.1 402 Payment Required',
			403 => 'HTTP/1.1 403 Forbidden',
			404 => 'HTTP/1.1 404 Not Found',
			405 => 'HTTP/1.1 405 Method Not Allowed',
			406 => 'HTTP/1.1 406 Not Acceptable',
			407 => 'HTTP/1.1 407 Proxy Authentication Required',
			408 => 'HTTP/1.1 408 Request Time-out',
			409 => 'HTTP/1.1 409 Conflict',
			410 => 'HTTP/1.1 410 Gone',
			411 => 'HTTP/1.1 411 Length Required',
			412 => 'HTTP/1.1 412 Precondition Failed',
			413 => 'HTTP/1.1 413 Request Entity Too Large',
			414 => 'HTTP/1.1 414 Request-URI Too Large',
			415 => 'HTTP/1.1 415 Unsupported Media Type',
			416 => 'HTTP/1.1 416 Requested range not satisfiable',
			417 => 'HTTP/1.1 417 Expectation Failed',
			500 => 'HTTP/1.1 500 Internal Server Error',
			501 => 'HTTP/1.1 501 Not Implemented',
			502 => 'HTTP/1.1 502 Bad Gateway',
			503 => 'HTTP/1.1 503 Service Unavailable',
			504 => 'HTTP/1.1 504 Gateway Time-out'
		);
		array_push($this->headers, empty($status_codes[$status_code]) ? false : $status_codes[$status_code]);
	}

	public function set_content_type($content_type) {
		$this->content_type = $content_type;
		array_push($this->headers, 'Content-Type: ' . $content_type);
	}

	public function set_content_type_by_extension($extension) {
		$find = false;
		foreach(MimeType :: get_mime_types() as $key => $value) {
			if($extension == $key) {
				$this->content_type = $value;
				array_push($this->headers, 'Content-Type: ' . $value);
				$find = true;
				break;
			}
		}
		if(!$find) {
			array_push($this->headers, 'Content-Type: application/force-download');
		}
	}

	public function get_content_type() {
		return $this->content_type;
	}

	public function redirect_to($url) {
		header('Location: ' . $url);
	}

	public function send_file($file) {
		if(!is_file($file)) {
			$this->set_header_status(404);
			exit;
		}

		$len = filesize($file);
		$info = pathinfo($file);
		$extension = strtolower($info['extension']);
		$filename = strtolower($info['basename']);

		header('Pragma: no-cache');
		header('Expires: Thu, 25 Dec 2008 23:17:14 GMT');
		header('Last-Modified: ' . date('r'));
		header('Cache-Control: no-store, no-cache, must-revalidate');
		// header("Cache-control: private"); // fix a bug for IE 6.x
		header('Content-Description: File Transfer');
		header('Content-Disposition: attachment; filename=' . $filename . ';');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . $len);
		$this->set_content_type_by_extension($extension);
		readfile($file);
	}

}
?>
