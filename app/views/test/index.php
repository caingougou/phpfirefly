this file under app/views/test/index.php.
<br />
<?php
pr($params['action']);
pr($action);
pr($controller_name);
pr($action_name);

if (isset ($var1) && isset ($var2)) {
	pr($var1);
	pr($var2);
}
?>