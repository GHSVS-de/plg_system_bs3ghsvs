<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

abstract class JHtmlBehaviorghsvs
{
	
	protected static $loaded = array();
	
	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
   * behavior.framework -> Bs3GhsvsRegisterBehavior::frameworkMt -> behaviorghsvs.framework
   */
	public static function framework()
	{
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		static::$loaded[__METHOD__] = 1;
		return;
	}

	/**
	 * behavior.caption -> Bs3GhsvsRegisterBehavior::caption -> behaviorghsvs.caption
	 */
	public static function caption($selector = 'img.caption')
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "behavior.caption" blocked because lack of responsiveness.
				Use native HTML figure tags instead!';
			Log::add($add, Log::WARNING, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;
	}

	/**
	 * behavior.formvalidation -> Bs3GhsvsRegisterBehavior::formvalidation
	   -> behaviorghsvs.formvalidation -> behavior.formvalidator
	 * Here no Mootools loading.
	 */
	public static function formvalidation()
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}
		HTMLHelper::_('behavior.formvalidator');

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': "behavior.formvalidation" redirected to "behavior.formvalidator" (without load of Mootools).';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;
	}

	/**
	 * behavior.tooltip -> Bs3GhsvsRegisterBehavior::tooltip -> behaviorghsvs.tooltip
	 */
	public static function tooltip($selector = '.hasTip', $params = array())
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "behavior.tooltip" blocked because it needs Mootools.
				Use "bootstrap.tooltip"!';
			Log::add($add, Log::WARNING, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;

		return;
	}

	/**
	 * behavior.modal -> Bs3GhsvsRegisterBehavior::modal -> behaviorghsvs.modal
	 */
	public static function modal($selector = 'a.modal', $params = array())
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "behavior.modal" blocked because it needs Mootools.
				Use "bootstrap.modal"!';
			Log::add($add, Log::WARNING, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;

		return;
	}

	/**
	 * behavior.tree -> Bs3GhsvsRegisterBehavior::tree -> behaviorghsvs.tree
	 */
	public static function tree($id, $params = array(), $root = array())
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "behavior.tree" blocked because it needs Mootools.';
			Log::add($add, Log::WARNING, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;

		return;
	}
}