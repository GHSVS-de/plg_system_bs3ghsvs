<?php
/*
J3.8.9
*/
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

class Bs3GhsvsRegisterJQuery
{
	protected static $jquery = array(
		// 'ui',
		// 'token',
	);

	public static function register()
	{
		require_once(__DIR__ . '/jqueryblocker.php');
		$jqueryblocker = new jqueryblocker;

		if (!$jqueryblocker->blockCoreJquery())
		{
			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': Core "jquery.framework" konnte nicht blockiert werden. Joomla lädt Core-JQuery.';
				Log::add($add, Log::WARNING, 'bs3ghsvs');
			}
			return false;
		}
		elseif (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Core "jquery.framework" successfully blocked.';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		HTMLHelper::register('jquery.framework', 'Bs3GhsvsRegisterJQuery::framework');

		foreach (self::$jquery as $method)
		{
			HTMLHelper::register('jquery.' . $method, 'Bs3GhsvsRegisterJQuery::' . $method);
		}
		return true;
	}

	/**
	 * jquery.framework
	 */
	public static function framework($noConflict = true, $debug = null, $migrate = true)
	{
		HTMLHelper::_('jqueryghsvs.framework');
		return;
	}
}