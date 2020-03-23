<?php
class bootstrapblocker extends JHtmlBootstrap
{
	public static function blockCoreBootstrap()
	{
		if (!empty(parent::$loaded['JHtmlBootstrap::framework']))
		{
			return false;
		}
		parent::$loaded['JHtmlBootstrap::framework'] = 13;
		return true;
	}
}