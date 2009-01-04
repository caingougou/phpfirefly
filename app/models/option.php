<?php
class Option extends ActiverRecord {
	public static function model() {
		return parent::model(get_class());
	}
}
?>