<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

class Bs3GhsvsRegisterBehavior
{
	protected static $behavior = array(
		// 'framework', # Special handling. See register().
		// 'core', # OK.
		'caption', # No Mootools but KILL! Is not responsive.
		'formvalidation', # Kill! Mootools. Use formvalidator instead.
		// 'formvalidator', # OK
		// 'switcher', # OK. Irgend Hathor Scheiß. Fliegt in Joomla 4.
		// 'combobox',
		'tooltip', # Kill! Mootools. Use bootstrap.tooltip instead.
		'modal', # Kill! Mootools. Use bootstrap.modal instead.
		// 'multiselect', # OK
		'tree', # Kill! Mootools.
		// 'calendar', # OK
		// 'colorpicker', # OK
		// 'simplecolorpicker', # OK
		// 'keepalive', # OK
		// 'highlighter', # OK
		// 'noframes', # OK
		// '_getJSObject', # OK
		// 'tabstate', # OK
		// 'polyfill', # OK
		// 'calendartranslation', # OK
	);

	public static function register()
	{
		require_once(__DIR__ . '/mootoolsblocker.php');
		$mootoolsblocker = new mootoolsblocker;

		if (!$mootoolsblocker->blockCoreMootools())
		{
			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': Core "behavior.framework" (=Mootools) konnte nicht blockiert werden.';
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}

			return false;
		}
		elseif (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Core "behavior.framework" (=Mootools) successfully blocked.';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		HTMLHelper::register('behavior.framework', 'Bs3GhsvsRegisterBehavior::framework');

		foreach (self::$behavior as $method)
		{
			HTMLHelper::register('behavior.' . $method, 'Bs3GhsvsRegisterBehavior::' . $method);
		}
		return true;
	}

	/**
	 * behavior.framework (= Mootools)
	 */
	public static function framework()
	{
		HTMLHelper::_('behaviorghsvs.framework');
		return;
	}

	/**
	 * behavior.caption
	 */
	public static function caption($selector = 'img.caption')
	{
		HTMLHelper::_('behaviorghsvs.caption', $selector);
		return;
	}

	/**
	 * behavior.formvalidation
	 */
	public static function formvalidation()
	{
		HTMLHelper::_('behaviorghsvs.formvalidation');
		return;
	}

	/**
	 * behavior.tooltip
	 */
	public static function tooltip($selector = '.hasTip', $params = array())
	{
		HTMLHelper::_('behaviorghsvs.tooltip');
		return;
	}

	/**
	 * behavior.modal
	 */
	public static function modal($selector = 'a.modal', $params = array())
	{
		HTMLHelper::_('behaviorghsvs.modal');
		return;
	}

	/**
	 * behavior.tree
	 */
	public static function tree($id, $params = array(), $root = array())
	{
		HTMLHelper::_('behaviorghsvs.tree');
		return;
	}
}