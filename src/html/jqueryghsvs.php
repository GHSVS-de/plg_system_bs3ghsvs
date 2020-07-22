<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Log\Log;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @since  3.0
 */
abstract class JHtmlJqueryghsvs
{
	
	protected static $loaded = array();
	
	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * Method to load the jQuery JavaScript framework into the document head
	 * See https://code.jquery.com/jquery/ for CDN versions and downloads.
	 * See https://github.com/jquery/jquery-migrate/tree/1.x-stable#readme
	 */
	public static function framework($noConflict = true, $debug = null, $migrate = true)
	{
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		$attribs = array();
		$min = JDEBUG ? '' : '.min';
		$suf = $min ? 'Min' : '';
		$version = JDEBUG ? time() : 'auto';;
		$options = PlgSystemBS3Ghsvs::$options['jquery'];
		$Load = $options['Load'];

		if ($Load === 'cdn')
		{
			$file = $options['cdn' . $suf];
			$attribs['crossorigin'] = 'anonymous';
			$attribs['integrity'] = $options['cdnIntegrity' . $suf];
			$version = '';
		}
		elseif ($Load === 'media')
		{
			// B/C
			if (!isset($options['otherFileName']))
			{
				$options['otherFileName'] = '';
			}

			$file = $options['otherFileName'] ? : 'jquery';
			$file = ltrim($options['media'] . '/' . $file . $min . '.js', '/');
		}
		else
		{
			self::$loaded[__METHOD__] = 1;
			return;
		}

		// jquery.slim and migrate are incompatible or you have to load tween.js
		if (strpos($file, '.slim' . $min . '.js') !== false)
		{
			$migrate = false;
		}

		HTMLHelper::_('script', $file,
			array('version' => $version, 'relative' => true),
			$attribs
		);

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': File ' . $file . '. Loaded?';
			Log::add($add, Log::INFO, 'bs3ghsvs');
		}

		if ($noConflict)
		{
			HTMLHelper::_('script', 'jui/jquery-noconflict.js',
				array('version' => $version, 'relative' => true));
		}
		
		$attribs = array();

		if ($migrate)
		{
			$options = PlgSystemBS3Ghsvs::$options['jquery-migrate'];
			$Load = $options['Load'];
	
			if ($Load === 'cdn')
			{
				$file = $options['cdn' . $suf];
				$attribs['crossorigin'] = 'anonymous';
				$attribs['integrity'] = $options['cdnIntegrity' . $suf];
				$version = '';
			}
			elseif ($Load === 'media')
			{
				// B/C
				if (!isset($options['otherFileName']))
				{
					$options['otherFileName'] = '';
				}
	
				$file = $options['otherFileName'] ? : 'jquery-migrate';
				$file = ltrim($options['media'] . '/' . $file . $min . '.js', '/');
			}
			else
			{
				self::$loaded[__METHOD__] = 1;
				return;
			}

			HTMLHelper::_('script', $file,
				array('version' => $version, 'relative' => true),
				$attribs
			);
		}

		static::$loaded[__METHOD__] = true;

		return;
	}
}
