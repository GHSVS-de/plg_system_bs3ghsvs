<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

class Bs3GhsvsRegisterFormbehavior
{
	protected static $formbehavior = array(
		'chosen',
		'ajaxchosen',
  );

	public static function register()
	{
		require_once(__DIR__ . '/formbehaviorblocker.php');
		$formbehaviorblocker = new formbehaviorblocker;

		if (!$formbehaviorblocker->checkChosenLoaded())
		{
			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': Core "formbehavior.chosen" or "formbehavior.chosenajax" is already loaded. I can\'t suppress it.';
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}
			return false;
		}
		elseif (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Core "formbehavior.chosen" or "formbehavior.chosenajax" not loaded yet. I can suppress it.';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		foreach (self::$formbehavior as $method)
		{
			HTMLHelper::register('formbehavior.' . $method, 'Bs3GhsvsRegisterFormbehavior::' . $method);
		}
		return true;
	}

	/**
	 * formbehavior.chosen
	 */
	public static function chosen($selector = '.advancedSelect', $debug = null, $options = array())
	{
		HTMLHelper::_('formbehaviorghsvs.chosen');
		return;
	}

	/**
	 * formbehavior.chosenajax
	 */
	public static function ajaxchosen($options, $debug = null)
	{
		HTMLHelper::_('formbehaviorghsvs.chosenajax');
		return;
	}
}