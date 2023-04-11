<?php
/*
J3.8.9
*/
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

class Bs3GhsvsRegisterBootstrap
{
	protected static $bootstrap = array(
		// 'affix',
		// 'alert',
		// 'button',
		'carousel',
		// 'dropdown',
		// 'modal',
		// 'renderModal',
		'popover',
		// 'scrollspy',
		'tooltip',
		'tooltipExtended',
		// 'typeahead',
		'startAccordion',
		// 'endAccordion',
		'addSlide',
		// 'endSlide',
		// 'startTabSet',
		// 'endTabSet',
		// 'addTab',
		// 'endTab',
		// 'startPane',
		// 'endPane',
		// 'addPanel',
		// 'endPanel',
		'loadCss',
	);

	public static function register()
	{
		require_once(__DIR__ . '/bootstrapblocker.php');
		$bootstrapblocker = new bootstrapblocker;

		if (!$bootstrapblocker->blockCoreBootstrap())
		{
			if (PlgSystemBS3Ghsvs::$log)
			{
				$add = __METHOD__ . ': Core "bootstrap.framework" konnte nicht blockiert werden.';
				Log::add($add, Log::CRITICAL, 'bs3ghsvs');
			}
			return false;
		}
		elseif (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Core "bootstrap.framework" successfully blocked.';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		HTMLHelper::register('bootstrap.framework', 'Bs3GhsvsRegisterBootstrap::framework');

		foreach (self::$bootstrap as $method)
		{
			HTMLHelper::register('bootstrap.' . $method, 'Bs3GhsvsRegisterBootstrap::' . $method);
		}
		return true;
	}

	/**
	 * bootstrap.framework
	 */
	public static function framework()
	{
		HTMLHelper::_('bootstrapghsvs.framework');
		return;
	}

	/**
	 * bootstrap.loadCss
	 */
	public static function loadCss(
		$includeMainCss = true,
		$direction = 'ltr',
		$attribs = array()
	){
		HTMLHelper::_('bootstrapghsvs.loadCss', $includeMainCss, $direction, $attribs);
		return;
	}

	/**
	 * bootstrap.startAccordion
	 */
	public static function startAccordion($selector = 'myAccordian', $params = array())
	{
  	return HTMLHelper::_('bootstrapghsvs.startAccordion', $selector, $params);
	}

	/**
	 * bootstrap.addSlide
	 */
	public static function addSlide(
		$selector,
		$text,
		$id,
		$class = '',
		$headingTagGhsvs = '',
		$title = ''
	){
		return HTMLHelper::_('bootstrapghsvs.addSlide',
			$selector, $text, $id, $class, $headingTagGhsvs, $title
		);
	}

	/**
	 * bootstrap.carousel
	 */
	public static function carousel($selector = 'carousel', $params = array())
	{
  	return HTMLHelper::_('bootstrapghsvs.carousel', $selector, $params);
	}

	/**
	 * bootstrap.popover
	 */
	public static function popover($selector = '.hasPopover', $params = array())
	{
  	return;
	}

	/**
	 * bootstrap.tooltip
	 */
	public static function tooltip($selector = '.hasTooltip', $params = array())
	{
  	return;
	}

	/**
	 * bootstrap.tooltipExtended
	 */
	public static function tooltipExtended($extended = false)
	{
  	return;
	}
}
