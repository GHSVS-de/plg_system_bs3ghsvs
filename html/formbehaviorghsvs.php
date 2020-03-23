<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

abstract class JHtmlFormbehaviorghsvs
{
	
	protected static $loaded = array();
	
	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * formbehavior.chosen -> Bs3GhsvsRegisterFormbehavior::chosen -> formbehaviorghsvs.chosen
	 */
	public static function chosen($selector = '.advancedSelect', $debug = null, $options = array())
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "formbehavior.chosen" blocked.';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;

		return;
	}

	/**
	 * formbehavior.ajaxchosen -> Bs3GhsvsRegisterFormbehavior::ajaxchosen -> formbehaviorghsvs.ajaxchosen
	 */
	public static function ajaxchosen(Registry $options, $debug = null)
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "formbehavior.ajaxchosen" blocked.';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;

		return;
	}
}