<?php
class formbehaviorblocker extends JHtmlFormbehavior
{
	public static function checkChosenLoaded()
	{
		if (
			!empty(parent::$loaded['JHtmlFormbehavior::chosen'])
			|| !empty(parent::$loaded['JHtmlFormbehavior::ajaxchosen'])
		){
			return false;
		}
		return true;
	}
}