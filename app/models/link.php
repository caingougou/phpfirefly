<?php
class Link extends ActiverRecord {
	public static function model() {
		return parent::model(get_class());
	}
}
?>