<?php
/**
JHTML::_('bs3ghsvs.something'...);
*/
?>
<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;


abstract class JHtmlBs3ghsvs
{
	protected static $loaded = array();

	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * bs3ghsvs.layout
	 *
	 * 2016-05-29: Überarbeitung wegen jetzt einfacherer Abwicklung mit file.php aus Joomla 3.6.0.
	 * Helferklasse layoutfileghsvs.php nicht mehr nötig!
	 *
	 * Aufrufe von JLayouts der Art JHtml::_('bs3ghsvs.layout', 'ghsvs.irgendwas'
	 * Joomlas JLayoutFile fügt immer selbst hinzugefügten basePath als ersten ein.
	 * Dann werden die existierenden JLayouts im media-ordner zuerst genommen.
	 * Hier geht es um Wiederverwendbarkeit für mehrere Templates.
	 * Bei joomla.irgendwas sucht das JLayout also erst im media-Ordner, falls kein anderer basePath
	 * definiert ist.
	 *
	 * Ausnahmen sind layouts-Ordner, die in §newSortIf hinterlegt sind, bspw. ghsvs. .
	 * Dann wird $basePath ans Ende sortiert, damit Overrides in Template, Komponente etc.
	 * zuerst berücksichtigt werden. Und erst dann JLayout im media-Ordner.
	 * Vorher
				[0] => .../media/plg_system_bs3ghsvs/layouts
				[1] => .../templates/protostarbs3ghsvs/html/layouts/com_content
				[2] => .../components/com_content/layouts
				[3] => .../templates/protostarbs3ghsvs/html/layouts
				[4] => .../layouts

				Nachher
				[0] => .../templates/protostarbs3ghsvs/html/layouts/com_content
				[1] => .../components/com_content/layouts
				[2] => .../templates/protostarbs3ghsvs/html/layouts
				[3] => .../media/plg_system_bs3ghsvs/layouts
				[4] => .../layouts
	 */
	public static function layout($layoutFile, $displayData = null, $basePath = null, $options = null)
	{
		$isBs3ghsvs = false;

		$newSortIf = array(
			'ghsvs.'
		);

		foreach ($newSortIf as $folder)
		{
			if (strpos($layoutFile, $folder) === 0)
			{
				$isBs3ghsvs = true;
				break;
			}
		}

		if ($isBs3ghsvs && empty($basePath))
		{
			$layout = new FileLayout($layoutFile, $basePath = null, $options);
		
			// Liefert an dieser Stelle die Defaultwerte seitens Joomla.
			## $renderer->getDefaultIncludePaths();
			$includePaths = $layout->getIncludePaths();
			
			// Mutterpfad raus.
			$includePaths = array_diff($includePaths, array(JPATH_SITE . '/layouts'));
			
			// media-basePath und Mutterpfad danach wieder rein.
			array_push($includePaths,
				JPATH_SITE . '/media/' . static::$basepath . '/layouts',
				JPATH_SITE . '/layouts'
			);
			
			// Default-Pfade überschreiben.
			$layout->setIncludePaths($includePaths);
			
			return $layout->render($displayData);
		}
		elseif (empty($basePath))
		{
			$basePath = JPATH_SITE . '/media/' . static::$basepath . '/layouts';
		}
		return LayoutHelper::render($layoutFile, $displayData, $basePath, $options);
	}

	/**
	 * bs3ghsvs.rendermodules
	 * Gruppenrendering von Modulen in einer Ghost-Position.
	 */
	public static function rendermodules($position = null, $attribs = array())
	{
		if (empty($position))
		{
			return '';
		}

		$output = array();
		$modules = ModuleHelper::getModules($position);

		foreach ($modules as $module)
		{
			$output[] = ModuleHelper::renderModule($module, $attribs);
		}
		return implode('', $output);
	}

	/**
	 * bs3ghsvs.templatejs
	 * Load js file from plugin media folder (self::$basepath).
	 */
	public static function templatejs($file = 'template', $jquery = true)
	{
		if ($jquery)
		{
			HTMLHelper::_('jquery.framework');
		}

		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		$attribs = array();
		$min = JDEBUG ? '' : '.min';
		$version = JDEBUG ? time() : 'auto';

  	$file = static::$basepath . '/' . $file . $min . '.js';
		
		HTMLHelper::_('script', $file,
			array('version' => $version, 'relative' => true),
			$attribs
		);

		static::$loaded[__METHOD__] = 1;
		return;
	}

	/**
	 * 
	 */
	public static function addsprungmarke($selector, $sprungmarke = '#BELOWHEADER')
	{
		if (Factory::getApplication()->client->robot)
		{
			return;
		}
		
		$selector = trim($selector);
		$sprungmarke = trim($sprungmarke);

		$sig = md5(serialize(array($selector, $sprungmarke)));

		if (!empty(static::$loaded[__METHOD__][$sig]))
		{
			return;
		}

		if (!empty(static::$loaded[__METHOD__]['core']))
		{
			$min = JDEBUG ? '' : '.min';
			$version = JDEBUG ? time() : 'auto';
			HTMLHelper::_('bs3ghsvs.templatejs');
			static::$loaded[__METHOD__]['core'] = 1;
		}

		Factory::getDocument()->addScriptDeclaration(
			';(function($){$(document).ready(function(){'
			. '$.fn.addSprungmarkeToUrl("' . $selector . '", "' . $sprungmarke . '");'
			. '})})(jQuery);'
		);
		static::$loaded[__METHOD__][$sig] = 1;
		return;
	}

	/**
	 * bs3ghsvs.slideinpanel
	 * CSS ist in LESS.
	 */
	public static function slideinpanel()
	{
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		$attribs = array();
		$min = JDEBUG ? '' : '.min';
		$suf = $min ? 'Min' : '';
		$version = JDEBUG ? time() : 'auto';
		HTMLHelper::_('jquery.framework');
		$file = static::$basepath . '/slide-in-panel/js/main' . $min . '.js';

		HTMLHelper::_('script', $file,
			array('version' => $version, 'relative' => true),
			$attribs
		);

		static::$loaded[__METHOD__] = 1;
		return;
	}

	/**
	 * Eigener Bootstrap-Spoiler.
	 * See https://getbootstrap.com/docs/3.4/javascript/#collapse-usage
	 * $in = false => closed
	 */
	public static function spoiler(
		$text,
		$options = array()
	){
		if (!trim($text))
		{
			return '';
		}
		
		$defaultOptions = 		array(
			'buttontext' => 'GHSVS_MODULES_SPOILER_BTN_TEXT_SHOW_HIDE',
			'in' => 0,
			'spoilerclass' => '',
			'buttonclass' => 'btn btn-primary',
			'role' => ''
		);

		$options = array_merge($defaultOptions, $options);
		HTMLHelper::_('bootstrap.framework');
		$html = array();
		$id = 'spoiler' . str_replace('.', '', uniqid('', true));

		$buttonclass = trim('accordion-toggle ' . $options['buttonclass']);
		$spoilerclass = trim('spoilerghsvs ' . $options['spoilerclass']);
		$role = $options['role'] ? ' role="' . $options['role'] . '"' : '';

		$html[] = '<div class="' . $spoilerclass . '"' . $role . '>';
		$html[] = '<button class="' . $buttonclass . '" type="button" data-toggle="collapse" data-target="#' . $id 
			. '" aria-expanded="' . ($options['in'] ? 'true' : 'false') . '" aria-controls="' . $id . '">';
		$html[] = '{svg{solid/plus-square}} ';
		$html[] = Text::_($options['buttontext']);
  	$html[] = '</button>';
		$html[] = '<div class="collapse spoilerbody' . ($options['in'] ? ' in show' : '') . '" id="' . $id . '">';
		$html[] = $text;
		$html[] = '</div><!--/spoilerbody-->';
		$html[] = '</div><!--/spoilerghsvs-->';
		return implode('', $html);
	}

	/**
	 * bs3ghsvs.smoothscroll
	 * 2020-03-10: Remove smoothScrolling() in favour of CSS "scroll-behavior: smooth".
	 * We only need smoothscroll() for closing modals now.
	 */
	public static function smoothscroll($params = array())
	{
		// Nur, um bei identischen, aber lediglich anders sortierten $params
		// nicht doppelt zu laden
		ksort($params);
		$params = new Registry($params);
		$scrollParent = trim($params->get('scrollParent', '.SMOOTHSCROLL'));
		$isAModal = $params->get('isAModal', false);

		$sig = md5(serialize(array($scrollParent, $params)));

		if ($isAModal && !isset(static::$loaded[__METHOD__][$sig]))
		{
			Factory::getDocument()->addScriptDeclaration(
				'jQuery(function(){'
				. 'jQuery("' . $scrollParent . ' a[href*=\"#\"]").not("[href=\"#\"]").not("[href=\"#0\"]")'
				. '.on("click", function(event){'
				. 	'jQuery("' . $scrollParent . '").modal("hide");'
				. 	'jQuery("' . $scrollParent . ' .dropdown").removeClass("open");'
				. '});'
				. '});'
			);
			static::$loaded[__METHOD__][$sig] = true;
		}
		return;
	}

	/**
	 * Bei Klick auf Accordion-Slides Status in Session schreiben,
	 * In Session gespeicherte, aktive Slides öffnen.
	 */
	public static function activeToSession($selector = 'myAccordian')
	{
		$key = '#' . $selector . '.accordion';

		if (!isset(static::$loaded[__METHOD__][$key]))
		{
			$sessionData = Factory::getSession()->get(static::$basepath);
			$IDs = array();

			if (!empty($sessionData[$key]))
			{
				$IDs = explode('|', $sessionData[$key]);
			}

			$js = array();
			$js[] = '
(function($)
{
	$(document).ready(function()
	{
		if (! $("' . $key . '").length)
		{
			return;
		}';

			// Open active slides in session.
			foreach ($IDs as $id)
			{
				$js[] = '$("#' . $id . '").collapse("show");';
			}
			
			// AJAX-save status of open slides in session if any slide clicked.
			$js[] = '
		$("' . $key . '").on("shown.bs.collapse hidden.bs.collapse", function (event)
		{
			$actives = $("' . $key . ' .show");
			
			var activeIds = [];

			$actives.each(function()
			{
				activeIds.push($(this).attr("id")); 
			});

			var activeIds = activeIds.join("|");

			var KEY = "' . urlencode($key) . '"; //Key in der Session
			var PLUGIN = "SessionBs3Ghsvs";
			var GROUP = "system";
			var FORMAT = "raw";
			var OPTION = "com_ajax";
			var CMD = "add";
			var DATA = activeIds;
			
			var systemPaths = Joomla.getOptions("system.paths");
			var Uri = (systemPaths ? systemPaths.root + "/index.php" : window.location.pathname) + "?"
				+ "option=" + OPTION + "&group=" + GROUP + "&plugin=" + PLUGIN + "&format=" + FORMAT
				+ "&cmd=" + CMD + "&key=" + KEY + "&data=" + DATA;

			Joomla.request({
				url: Uri,
				method: "POST",
				onError: function(xhr)
				{
					console.log("error: " + activeIds);
				}
			});
		});
	});
})(jQuery);';
			
			// Wegen Umstellung von $.ajax auf Joomla.request.
			HTMLHelper::_('behavior.core');
			Factory::getDocument()->addScriptDeclaration(implode('', $js));
		}
	}

	/**
	 * 2015-11-02
	 * Lädt category-blog-list-toggle.js für den Blog-Listen-Toggler, das Button-Status in Session schreibt.
	 */
	public static function bloglisttoggle()
	{
		if (empty(static::$loaded[__METHOD__]))
		{
			$attribs = array();
			$min = JDEBUG ? '' : '.min';
			$version = JDEBUG ? time() : 'auto';
			
			Factory::getDocument()->addScriptOptions('category-blog-list-toggle',
				array(
					'chevronRight' => '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-chevron-right" fill="currentColor" xmlns="http://www.w3.org/2000/svg">  <path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>',
					// Spinner:
					'arrowRepeat' => '<svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-repeat" fill="currentColor" xmlns="http://www.w3.org/2000/svg">  <path fill-rule="evenodd" d="M2.854 7.146a.5.5 0 0 0-.708 0l-2 2a.5.5 0 1 0 .708.708L2.5 8.207l1.646 1.647a.5.5 0 0 0 .708-.708l-2-2zm13-1a.5.5 0 0 0-.708 0L13.5 7.793l-1.646-1.647a.5.5 0 0 0-.708.708l2 2a.5.5 0 0 0 .708 0l2-2a.5.5 0 0 0 0-.708z"/>  <path fill-rule="evenodd" d="M8 3a4.995 4.995 0 0 0-4.192 2.273.5.5 0 0 1-.837-.546A6 6 0 0 1 14 8a.5.5 0 0 1-1.001 0 5 5 0 0 0-5-5zM2.5 7.5A.5.5 0 0 1 3 8a5 5 0 0 0 9.192 2.727.5.5 0 1 1 .837.546A6 6 0 0 1 2 8a.5.5 0 0 1 .501-.5z"/></svg>',
				));
			
			// Wegen Bootstrap-Button
			HTMLHelper::_('bootstrap.framework');
			
			// Ajax teils umgeschrieben. Noch nicht komplett Vanilla!!
			HTMLHelper::_('behavior.core');
			
			$file = self::$basepath . '/category-blog-list-toggle' . $min . '.js';
		
			HTMLHelper::_('script', $file,
				array('version' => $version, 'relative' => true),
				$attribs
			);

			static::$loaded[__METHOD__] = 1;
		}
		return;
	}

	public static function toTop()
	{
		if (empty(static::$loaded[__METHOD__]))
		{
			$attribs = array('defer' => 'defer');
			$min = JDEBUG ? '' : '.min';
			$version = JDEBUG ? time() : 'auto';

			$file = self::$basepath . '/toTop' . $min . '.js';
		
			HTMLHelper::_('script', $file,
				array('version' => $version, 'relative' => true),
				$attribs
			);

			$file = self::$basepath . '/toTop' . $min . '.css';
		
			HTMLHelper::_('stylesheet', $file,
				array('version' => $version, 'relative' => true),
				$attribs
			);
			static::$loaded[__METHOD__] = 1;
		}
		return '<button onclick="topFunction()" id="toTop" aria-hidden="true" tabindex="-1" aria-label="' . Text::_('PLG_SYSTEM_BS3GHSVS_TO_TOP') . '"></button>';
	}
}