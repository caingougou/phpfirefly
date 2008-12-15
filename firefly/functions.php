<?php
function pr($object) {
	echo '<div style="white-space:pre;">';
	print_r($object);
	echo '</div>';
}

function debug($object) {
	if(DEBUG) {
		echo '<div onclick="javascript:this.childNodes[2].style.display=\'block\'"><a href="#">show debug info</a><br />';
		echo '<div style="white-space:pre; display:none;">';
		print_r($object);
		echo '</div>';
		echo '</div>';
	}
}
?>