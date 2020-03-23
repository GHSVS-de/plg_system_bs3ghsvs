<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

class Bs3GhsvsRegisterBs3ghsvs
{
	protected static $toRegister = array(
		'animsitionghsvs' => array(
			'animsition',
		),
		'bs3ghsvs' => array(
			'layout',
			'rendermodules',
			'templatejs',
			'addsprungmarke',
			// Be aware: 2020-03-10: Removed smoothScrolling() JS in favour of CSS "scroll-behavior: smooth".
			'smoothscroll',
			'slideinpanel',
			'spoiler', // Eigener Bootstrap Spoiler-Button.
			'activeToSession',
			'bloglisttoggle',
			'toTop',
		),
		'slicknavghsvs' => array(
			'slicknav',
			'slicknavcore',
		),
		'footableghsvs' => array(
			'footable', //V3
			'moment', // Date parsing. E.g. in Footables sorting
		),
		'lessghsvs' => array(
			'addLessCss',
		),
	);

	public static function register()
	{
		$prefix = 'JHtml';

		foreach (self::$toRegister as $file => $what)
		{
			$class = $prefix . ucfirst($file);

			JLoader::register($class, __DIR__ . '/../html/' . $file . '.php');
			
			foreach ($what as $method)
			{
				HTMLHelper::register('bs3ghsvs.' . $method, $class . '::' . $method);
			}
		}
		return true;
	}
}