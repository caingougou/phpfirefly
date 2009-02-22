<script type="application/javascript">
	function show (json) {
		alert(json.action_name);
	}
	function $(element) {
		return document.getElementById(element);
	}
</script>
<div style="border: #ddd 1px solid; height: 100px; width: 100px; display: none;" id="test2">test2 div</div>
<div style="border: #ddd 1px solid; height: 100px; width: 100px;" id="test">test div</div>
<?php
//	$this->render(array('partial' => 'test/form', 'locals' => array('method' => 'get')));
//	$this->render(array('action' => 'admin/login', 'locals' => array('text' => 'text in admin index page', 'test' => 'test in admin index page')));
//	$this->debug(array('partial' => 'test/form', 'locals' => array('method' => 'get')), __FILE__, __LINE__);
//	pr($controller_name);
//	new Debugger(array('a', 'b', $this), true);
//	pr(debug_backtrace());
//	pr($flash);
//	pr(Router :: available_controllers());
//	phpinfo();
?>
<script type="application/javascript" src="/admin/renders"></script>