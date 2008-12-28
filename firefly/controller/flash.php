<?php
class Flash {
	protected $flash = null;

	public function __construct() {

	}

	public function set($flash) {
		$this->flash = $flash;
	}

	public function update($flash) {
		$this->flash = $flash;
	}

	public function replace($flash) {
		$this->flash = $flash;
	}

	public function now() {

	}

	public function keep($flash) {

	}

	public function discard() {

	}

	public function sweep() {

	}
}
?>