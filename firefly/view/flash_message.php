<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>flash message</title>
		<script type="text/javascript">
			setTimeout(function(){window.location="<?=$redirect_url?>"}, <?= $pause ?>);
		</script>
    </head>
    <body>
		<?php echo $message; ?>
    </body>
</html>