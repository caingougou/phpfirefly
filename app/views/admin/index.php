<?php
	$this->render(array('partial' => 'test/form', 'locals' => array('method' => 'get')));
	$this->debug(array('partial' => 'test/form', 'locals' => array('method' => 'get')), __FILE__, __LINE__);
	pr($controller_name);
//	new Debugger(array('a', 'b', $this), true);
//	pr(debug_backtrace());
//	pr($flash);
//	pr(Router :: available_controllers());
//	phpinfo();
?>
