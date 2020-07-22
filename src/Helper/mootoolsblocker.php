<?php
class mootoolsblocker extends JHtmlBehavior
{
	/**
		Array
		(
			[JHtmlBehavior::core] => 1
			[JHtmlBehavior::framework] => Array
			(
				[core] => 1
				[more] => 1
			)
		)
	 */
	public static function blockCoreMootools()
	{
		if (
			!empty(parent::$loaded['JHtmlBehavior::framework'])
			|| !empty(parent::$loaded['JHtmlBehavior::core'])
		){
			return false;
		}
		
		// Nein! Das ist core.js, nicht Mootools.
		#parent::$loaded['JHtmlBehavior::core'] = 1;
		
		parent::$loaded['JHtmlBehavior::framework'] = array(
			'core' => 1,
			'more' => 1
		);
		return true;
	}
}