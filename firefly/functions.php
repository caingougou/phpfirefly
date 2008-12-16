<?php
function pr($object) {
	echo '<div style="white-space:pre;">';
	print_r($object);
	echo '</div>';
}

function print_hide($object) {
	echo '<div onclick="javascript:this.childNodes[2].style.display=\'block\'"><a href="#">debug</a><br />';
	echo '<div style="white-space:pre; display:none;">';
	print_r($object);
	echo '</div>';
	echo '</div>';
}

function h($string) {
	return htmlentities($string);
}

/**
 * 0. production
 * 1. monitor sql statements.
 * 2. inspect controller and view object and sql statements
 */
function debug($object) {
	if(DEBUG)
		switch(DEBUG) {
			case 0 :
				break;
			case 1 :
				pr($object);
				break;
			case 2 :
				print_hide($object);
		}
}
?>