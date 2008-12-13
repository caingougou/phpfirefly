<?php
class View {

	public function render($template, $params) {
		ob_start();

		$test = $params['test'];

		include($template);

		$out = ob_get_clean();

		echo $out;
	}

}
?>
