<?php
	echo isset($flash['notice']) ? $flash['notice'] . '<br />' : 'not exists flash notice<br/>';
	echo isset($flash['msg']) ? $flash['msg'] . '<br />' : 'not exists flash msg<br/>';
	echo $test . '<br/>';
	echo $text . '<br/>';
?>
<form action="" method="post" accept-charset="utf-8">
	<p><input type="text" name="username" value="" /></p>
	<p><input type="text" name="password" value="" /></p>
	<p><input type="submit" value="Continue" /></p>
</form>