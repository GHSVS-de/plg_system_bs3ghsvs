<?php
class jqueryblocker extends JHtmlJquery
{
	public static function blockCoreJquery()
	{
		if (!empty(parent::$loaded['JHtmlJquery::framework']))
		{
			return false;
		}
		parent::$loaded['JHtmlJquery::framework'] = 13;
		return true;
	}
}