<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;

class Bs3ghsvsTemplate
{
	protected static $templates = null;
	protected static $templateOptionsFile = '/html/plgSystemBs3Ghsvs.json';
	protected static $loaded;
	
	/**
	 * Return an array of template names (folders) where the plugin specific json configuration file exists.
	 */
	public static function getActiveInTemplates() : array
	{
		if (is_array(self::$templates))
		{
			return self::$templates;
		}
		
		$path = JPATH_SITE . '/templates/';
		self::$templates = Folder::folders($path);

		foreach (self::$templates as $i => $template)
		{
			if (!is_file($path . $template . self::$templateOptionsFile))
			{
				unset(self::$templates[$i]);
			}
		}
		return self::$templates;
	}

	/**
	 * Read options from json file plgSystemBs3GhsvsActive.json.
	 * Expects a $templateFolder (template name) that is already checked by getActiveInTemplates().
	 * Returns json_decoded result.
*
Array
(
    [comment] => See https://hash.online-convert.com/sha384-generator
    [bootstrapJs] => Array
        (
            [media] => plg_system_bs3ghsvs/bootstrap/3.4.1
            [cdnMin] => https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js
            [cdnIntegrityMin] => sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd
            [cdn] => https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.js
            [cdnIntegrity] => sha384-BqgdDRqoSM34U92AkJHjlNmbVsfEZStnqGg4pVMc/AMY0XbNFRu3cO5LfJXXXETD

            [jqueryMax] => 1.9.3
        )

    [jquery] => Array
        (
            [cdnMin] => https://code.jquery.com/jquery-1.9.1.min.js
            [cdnIntegrityMin] => sha256-wS9gmOZBqsqWxgIVgA8Y9WcQOa7PgSIX+rPA0VL2rbQ=
            [cdn] => https://code.jquery.com/jquery-1.9.1.js
            [cdnIntegrity] => sha256-e9gNBsAcA0DBuRWbm0oZfbiCyhjLrI6bmqAl5o+ZjUA=

        )

)
*
	*/
	public static function getTemplateOptionsFromJson($templateFolder)
	{
		$file = JPATH_SITE . '/templates/' . $templateFolder . self::$templateOptionsFile;
		$templateOptions = file_get_contents($file);
		return json_decode(trim($templateOptions), true);
	}
	
	public static function getTemplateByStyleId($id)
	{
		if (empty(static::$loaded[__METHOD__]))
		{
			$db = Factory::getDbo();
	
			$query = $db->getQuery(true)
				->select($db->qn('template'))
				->from($db->qn('#__template_styles'))
				->where($db->qn('id') . ' = ' . (int) $id)
			;
			$db->setQuery($query);
	
			try
			{
				static::$loaded[__METHOD__] = $db->loadResult('field');
			}
			catch (RuntimeException $e)
			{
				return false;
			}
		}
		return static::$loaded[__METHOD__];
	}
	
	public static function getLessPluginParams()
	{
		if (empty(static::$loaded[__METHOD__]))
		{
			$plugin = PluginHelper::getPlugin('system', 'lessghsvs');
			$pluginPath = 'system/lessghsvs';
			$params = null;
			
			if (!empty($plugin->params))
			{
				$params = new Registry($plugin->params);
				$params->set('isEnabled', 1);
			}
			elseif (is_file(JPATH_PLUGINS . '/' . $pluginPath . '/lessghsvs.php'))
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select($db->qn('params'))
					->from($db->qn('#__extensions'))
					->where($db->qn('type') . ' = ' . $db->q('plugin'))
					->where($db->qn('element') . ' = ' . $db->q('lessghsvs'))
					->where($db->qn('folder') . ' = ' . $db->q('system'))
				;
				$db->setQuery($query);
	
				try
				{
					$params = $db->loadResult();
	
					if ($params)
					{
						$params = new Registry($params);
						$params->set('isEnabled', 0);
					}
				}
				catch (RuntimeException $e)
				{
					// return false;
				}
			}
	
			if (($params instanceof Registry) && $params->get('sitelessc'))
			{
				$lesscPath = $pluginPath . '/lessc/' . $params->get('sitelessc') . '.php';
	
				if (is_file(JPATH_PLUGINS . '/' . $lesscPath))
				{
					$params->set('lesscPath', $lesscPath);
					$params->set('lesscPathAbs', JPATH_PLUGINS . '/' . $lesscPath);
					$params->set('isInstalled', 1);
				}
			}
			else
			{
				$params = new Registry;
				$params->set('isInstalled', 0);
			}
			
			$params->set('checkedPlugin', $pluginPath);
			
			// NO! NO! NO! Too early under some circumstances.
			#### $params->set('tplName', Factory::getApplication()->getTemplate());
			
			static::$loaded[__METHOD__] = $params;
		}
		return static::$loaded[__METHOD__];
	}
	
	public static function getIsFrontpage()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$menu = Factory::getApplication()->getMenu();
	
			if ($menu->getActive() === $menu->getDefault(Factory::getLanguage()->getTag()))
			{
				static::$loaded[__METHOD__] = true;
			}
			else
			{
				static::$loaded[__METHOD__] = false;
			}
		}
		return static::$loaded[__METHOD__];
	}

	/**
	 * Collect additional template params and more (too much?).
	 * Don't call before onAfterRoute!
	 */
	public static function initTemplate()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$app = Factory::getApplication();
			$view = $app->input->getCmd('view', '');
			$option = $app->input->getCmd('option', '');
			$layout = $app->input->getCmd('layout', '');
			$task = $app->input->getCmd('task', '');
			$itemid = $app->input->getInt('Itemid', 0);

			$template = $app->getTemplate(true);
			$tplPath = 'templates/' . $template->template;
			$BodyClasses = array();

			$template->params->set('isFrontpage', static::getIsFrontpage());
			
			if (!$template->params->get('sitetitle'))
			{
				$template->params->set('sitetitle', trim($app->getCfg('sitename')));
			}

			if (!$template->params->get('sitetitle'))
			{
				$template->params->set('sitetitleHide', -1);
			}

			if($menuParams = static::getActiveMenuParams())
			{
				$BodyClasses = static::splitPageClass($menuParams);

				// 2015-12-30: Artikeleinzelansicht innerhalb eines Catgory-Menüs
				if ($view === 'article' && $menuParams->get('query')->get('view') === 'category')
				{
					$BodyClasses[] = 'categoryView-article';
				}
						
				// 2015-12-31
				// "Seitentitel im Browser", der im Menüeintrag eingetragen ist
				$template->params->set('page_titleMenu', trim($menuParams->get('page_title', '')));
			}

			$BodyClasses[] = $option ? 'option-' . $option : 'no-option';
			$BodyClasses[] = $view ? 'view-' . $view : 'no-view';
			
			if ($view === 'article')
			{
				$BodyClasses[] = 'articleId-' . $app->input->getInt('id', 0);
				$BodyClasses[] = 'catId-' . $app->input->getInt('catid', 0);
			}
			elseif (in_array($view, array('category', 'categories')))
			{
				$BodyClasses[] = 'catId-' . $app->input->getInt('id', 0);
			}

			$BodyClasses[] = $layout ? 'layout-' . $layout : 'no-layout';
			$BodyClasses[] = $task ? 'task-' . ApplicationHelper::stringURLSafe($task) : 'no-task';
			$BodyClasses[] = $itemid ? 'itemid-' . $itemid : 'no-itemid';

			// In meinem Templatestil einstellbar
			$BodyClasses[] = $template->params->get('templatestyleclass', '');
			
			$BodyClasses[] = static::getIsFrontpage() ? 'isFrontpage' : 'isNotFrontpage';
			$template->params->set('isRobot', $app->client->robot);
			
			$BodyClasses[] = $app->client->robot ? 'isRobot' : 'isNotRobot';
			
			$template->params->set('BodyClasses', $BodyClasses);

			####START - LOGO, SEITENTITEL, SITEDESCRIPTION, SITENAME 
			#Logo-Bild:
			$logo = $template->params->get('logo', '');
			$path = $tplPath . '/images/logos/';

			if ((int) $logo === -1)
			{
				$logo = trim($template->params->get('logoalternativ', ''));
				$path = '';
			}

			if ($logo !== '' && $logo !== 'JNONE')
			{
				$logo = $path . $logo;

				$logoAlt = Text::_($template->params->get('logoAltText', ''));

				$template->params->set('companylogo', $logo);
				$logoAlt = htmlentities($logoAlt, ENT_QUOTES, 'UTF-8');

				$logo = '<img class="logo" id="SITELOGO" src="' . $logo . '" alt="' .$logoAlt . '"/>';
				$template->params->set('logoimg', $logo);
			}

			$template->params->set('initTemplate', true);
			static::$loaded[__METHOD__] = 1;
		}
	}

	/**
	 * Aus übergebenen $params pageclass_sfx holen.
	 * An Leerzeichen splitten in Einzelklassen.
	 * Ggf. Postfix an alle Klassen.
	 * Als Array zurückgeben.
	 */
	protected static function splitPageClass($params)
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$classes = (string) $params->get('pageclass_sfx');
	
			if ($classes = trim($classes))
			{
				$classes = preg_replace('/\s\s+/', ' ', $classes);
				$classes = str_replace(' ', 'Body' . ' ', $classes) . 'Body';
			}
	
			static::$loaded[__METHOD__] = explode(' ', $classes);
		}
		return static::$loaded[__METHOD__];
	}
	/**
	 * Check 2016-06: Wird verwendet.
	 * Aktive Menüparameter holen
	 * 2015-12-30: ->query hinzugefügt, um ermitteln zu können, ob Artikelansicht unterhalb category-View
	 * 2018-03-03: Beachte! Hier fehlt das merging mit den globalen Einstellungen der com_menus.
	 */
	public static function getActiveMenuParams()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$menu = Factory::getApplication()->getMenu()->getActive();
		
			//Bugfix removed isset($menu->params). Always false.
			if ($menu)
			{
				$menuParams = $menu->params;
				$menuParams->set('query', new Registry($menu->query));
				static::$loaded[__METHOD__] = $menuParams;
			}
			else
			{
				static::$loaded[__METHOD__] = false;
			}
		}
		return static::$loaded[__METHOD__];
	}

	public static function getActiveMenu()
	{
		if (!isset(static::$loaded[__METHOD__]))
		{
			$menu = Factory::getApplication()->getMenu()->getActive();

			//Bugfix removed isset($menu->params). Always false.
			if ($menu)
			{
				static::$loaded[__METHOD__] = $menu;
			}
			else
			{
				static::$loaded[__METHOD__] = false;
			}
		}
		return static::$loaded[__METHOD__];
	}
	/**
	 * Get template name (without params) e.g. in onAfterRoute.
	 * From https://github.com/joomla/joomla-cms/blob/4.0.0-alpha10/libraries/src/Application/SiteApplication.php#L397
	 */
	public static function getTemplateNameEarly($app, $db)
	{
		$menu = $app->getMenu();
		$item = $menu->getActive();

		if (!$item)
		{
			$item = $menu->getItem($app->input->getInt('Itemid', null));
		}
		
		$id = 0;

		if (is_object($item))
		{
			// Valid item retrieved
			$id = $item->template_style_id;
		}

		$tid = $app->input->getUint('templateStyle', 0);

		if (is_numeric($tid) && (int) $tid > 0)
		{
			$id = (int) $tid;
		}

		if ($app->getLanguageFilter())
		{
			$tag = $app->getLanguage()->getTag();
		}
		else
		{
			$tag = '';
		}

		// Load styles
		$query = $db->getQuery(true)
			->select('id, home, template, s.params')
			->from('#__template_styles as s')
			->where('s.client_id = 0')
			->where('e.enabled = 1')
			->join('LEFT', '#__extensions as e ON e.element=s.template AND e.type=' . $db->quote('template') . ' AND e.client_id=s.client_id');

		$db->setQuery($query);
		$templates = $db->loadObjectList('id');

		foreach ($templates as &$template)
		{
			// Create home element
			if ($template->home == 1 && !isset($template_home) || $app->getLanguageFilter() && $template->home == $tag)
			{
				$template_home = clone $template;
			}
		}

		// Unset the $template reference to the last $templates[n] item cycled in the foreach above to avoid editing it later
		unset($template);

		// Add home element, after loop to avoid double execution
		if (isset($template_home))
		{
			$templates[0] = $template_home;
		}

		if (isset($templates[$id]))
		{
			$template = $templates[$id];
		}
		else
		{
			$template = $templates[0];
		}

		// Allows for overriding the active template from the request
		$template_override = $app->input->getCmd('template', '');

		// Only set template override if it is a valid template (= it exists and is enabled)
		if (!empty($template_override))
		{
			if (file_exists(JPATH_THEMES . '/' . $template_override . '/index.php'))
			{
				foreach ($templates as $tmpl)
				{
					if ($tmpl->template === $template_override)
					{
						$template = $tmpl;
						break;
					}
				}
			}
		}

		// Need to filter the default value as well
		$template->template = InputFilter::getInstance()->clean($template->template, 'cmd');
		
		// Fallback template
		if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
		{
			$this->enqueueMessage(Text::_('JERROR_ALERTNOTEMPLATE') . ' in __METHOD__', 'error');

			// Try to find data for 'cassiopeia' template
			$original_tmpl = $template->template;

			foreach ($templates as $tmpl)
			{
				if ($tmpl->template === 'protostar')
				{
					$template = $tmpl;
					break;
				}
			}

			// Check, the data were found and if template really exists
			if (!file_exists(JPATH_THEMES . '/' . $template->template . '/index.php'))
			{
				throw new \InvalidArgumentException(Text::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $original_tmpl . ' in __METHOD__'));
			}
		}
		return $template->template;
	}
}