<?php
defined('JPATH_BASE') or die;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

abstract class JHtmlAnimsitionghsvs
{
	protected static $loaded = array();
	
	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * bs3ghsvs.animsition
	 */
	public static function animsition
	(
		$selectors = array('.item-page'),
		$options = array()
	){
		$app = Factory::getApplication();
		
		if (
			$app->client->robot
			|| ($app->client->browser === WebClient::IE
			&& 	explode('.', $app->client->browserVersion)[0] < 10)
		){
			return;
		}
		
		if (is_string($selectors))
		{
			if (!trim($selectors))
			{
				return;
			}

			$selectors = (array) $selectors;
		}

		if (!is_array($selectors) || !is_array($options))
		{
			return;
		}

		$default_options = array(
			'inDuration' => 800,
			'outDuration' => 0,
			'inClasses' => array(
				"flip-in-x-fr",
				"flip-in-y-fr",
				"flip-in-y",
				"flip-in-x",
				"zoom-in",
				"fade-in-right",
				"fade-in-right-lg",
				"fade-in-left",
				"fade-in-left-lg",
				"zoom-in",
				"zoom-in-lg",
				"rotate-in",
				"rotate-in-lg"
			)
		);
		$options = array_merge($default_options, $options);
		
		$chkInt = array('inDuration', 'outDuration');

		foreach ($chkInt as $chck)
		{
		 if ((int) $options[$chck] <= 0)
		 {
			 $options[$chck] = $default_options[$chck];
		 }
		}

		if (!is_array($options['inClasses']))
		{
			$options['inClasses'] = $default_options['inClasses'];
		}
  
		// Um nach Möglichkeit doppeltes Laden zu verhindern, wenn nur Reihenfolge anders:
		sort($selectors);
		$sig = md5(serialize(array($selectors)));

		if (!empty(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		$attribs = array();
		$min = JDEBUG ? '' : '.min';
		$suf = $min ? 'Min' : '';
		$version = JDEBUG ? time() : 'auto';
		HTMLHelper::_('jquery.framework');

		$tmplOptions = PlgSystemBS3Ghsvs::$options['animsitionJs'];

		if ($tmplOptions['cdnLoad'])
		{
			$file = $tmplOptions['cdn' . $suf];
			$attribs['crossorigin'] = 'anonymous';
			$attribs['integrity'] = $tmplOptions['cdnIntegrity' . $suf];
			#??? $version = '';
		}
		else
		{
			$file = $tmplOptions['media'] . '/animsition' . $min . '.js';
		}

		HTMLHelper::_('script', $file,
			array('version' => $version, 'relative' => true),
			$attribs
		);

		$tmplOptions = PlgSystemBS3Ghsvs::$options['animsitionCss'];

		if ($tmplOptions['cdnLoad'])
		{
			$file = $tmplOptions['cdn' . $suf];
			$attribs['crossorigin'] = 'anonymous';
			$attribs['integrity'] = $tmplOptions['cdnIntegrity' . $suf];
			#??? $version = '';
		}
		else
		{
			$file = $tmplOptions['media'] . '/animsition' . $min . '.css';
		}

		HTMLHelper::_('stylesheet', $file,
			array('version' => $version, 'relative' => true),
			$attribs
		);

		$css = array();

		foreach ($selectors as $selector)
		{
			$css[] = '.jsActive ' . $selector;
		}

		$selectors = implode(',', $css);
		
		$doc = Factory::getDocument();

		$doc->addScriptDeclaration('
(function($){$(document).ready(function(){
	var $inClasses = ["' . implode('", "', $options['inClasses']) . '"];
 var randomInClass = $inClasses[Math.floor(Math.random() * $inClasses.length)];
 $("' . $selectors . '").animsition({
		inClass: randomInClass,
		outClass: "",
		inDuration: ' . $options['inDuration'] . ',
		outDuration: ' . $options['outDuration'] . ',
		linkElement:".animsition-link",
		// die Loading-Grafik verschwindet gelegentlich nicht mehr
  loading: false,
  loadingParentElement: "body",
  //loadingClass: "animsition-loading",
  unSupportCss: ["animation-duration", "-webkit-animation-duration","-o-animation-duration"],
  overlay: false,
  //overlayClass: "animsition-overlay-slide",
  //overlayParentElement: "body",
	timeout: true,
	timeoutCountdown: 5,
 });
})})(jQuery);');

		$doc->addStyleDeclaration($selectors . '{opacity:0; position:relative; -webkit-animation-fill-mode: both; -o-animation-fill-mode:both; animation-fill-mode:both;} .page-headerHandschrift{position:relative; z-index:5;}');

		static::$loaded[__METHOD__][$sig] = 1;
		return;
	}
}