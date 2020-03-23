<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

abstract class JHtmlFootableghsvs
{
	protected static $loaded = array();
	
	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * bs3ghsvs.footable
	 * $moment: erzwingt moment.js für Datumsparsing laden. Zusätzlich findet aber eine Prüfung statt, ob im columns-Array ein type=date enthalten ist. Dann wird moment immer geladen.
	*/
	public static function footable($selector = '.table', $options = array(), $moment = 0)
	{
		if (!($selector = trim($selector)))
		{
			return;
		}

		if (!is_array($options))
		{
			$options = array();
		}

		ksort($options);
		$sig = md5(serialize(array($options, $selector)));
		
		if (!empty(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		$attribs = array();
		$min = JDEBUG ? '' : '.min';
		$version = JDEBUG ? time() : 'auto';

		
		if (empty($options['breakpoints']))
		{
			// Du musst z.B. xxs eingeben, damit Spalte bei *xs* *verschwindet*!
			// Die Breakpoints orientieren sich quasi an der Tabellenbreite, wenn 'useParentWidth' => true.
			// Siehe dazu auch https://github.com/GHSVS-de/GHSVSThings/issues/41
			$options['breakpoints'] = array(
				'xxxs' => 320,
				'xxs' => 480,
				'xs' => 620,
				'sm' => 768,
				'md' => 992,
				'md2' => 1024,
				'lg' => 1200,
			);
		}
		
		HTMLHelper::_('bootstrap.framework');
		
		// JQuery lines to add before or after init lines of footable
		$beforeInit = $afterInit = array();
		$checkEnabled = array('sorting', 'filtering', 'paging', 'editing', 'state');
		$files = array('footable.core');
		
		foreach ($checkEnabled as $check)
		{
			if (!empty($options[$check]['enabled']))
			{
				$files[] = 'footable.' . $check;

				if ($check == 'paging')
				{
					if (empty($options[$check]['countFormat']))
					{
						$options[$check]['countFormat'] =
							Text::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', '{CP}', '{TP}');
					}

					if (empty($options[$check]['limit']))
					{
						$options[$check]['limit'] = 10;
					}
				}
    
				if ($check == 'filtering')
				{
					if (empty($options['empty']))
					{
						$options['empty'] = Text::_('GHSVS_MODULES_FOOTABLE_EMPTY');
					}
				}
			}
		}

		// Datums-Parsing benoetigt moment.js. Notfalls erzwingen.
		if (!$moment && !empty($options['columns']))
		{
			$found = ArrayHelper::getColumn($options['columns'], 'type');

			if (in_array('date', $found))
			{
				$moment = 1;
			}
		}
  
		if ($moment)
		{
			HTMLHelper::_('bs3ghsvs.moment');
		}

		foreach ($files as $file)
		{
			$file = self::$basepath . '/footable/' . $file . $min . '.js';
			HTMLHelper::_('script', $file,
				array('version' => $version, 'relative' => true),
				$attribs
			);
		}

		$files[0] = 'footable.core.bootstrap';

		foreach ($files as $file)
		{
			$file = self::$basepath . '/footable/' . $file . $min .  '.css';
			HTMLHelper::_('stylesheet', $file, array('relative' => true, 'version' => $version), $attribs);
		}

		Factory::getDocument()->addScriptDeclaration(';(function($){$(document).ready(function(){'
		. implode('', $beforeInit)
		. '$("' . $selector . '").footable(' . json_encode($options) . ');'
		. implode('', $afterInit) . '});})(jQuery);'
		);
		
		static::$loaded[__METHOD__][$selector] = 1;
		return;
	}

	/**
	 *	moment.js für Datumsparsing z.B. in Footable laden.
	 */
	public static function moment()
	{
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		$attribs = array();
		$min = JDEBUG ? '' : '.min';
		$version = JDEBUG ? time() : 'auto';

		$file = ($min ? 'min/' : '') . 'moment';
		
		$file = self::$basepath . '/moment/' . $file . $min . '.js';
		HTMLHelper::_('script', $file,
			array('version' => $version, 'relative' => true),
			$attribs
		);
		
		static::$loaded[__METHOD__] = 1;
		return;
	}
}