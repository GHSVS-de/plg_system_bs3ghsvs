<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Application\ApplicationHelper;

$com_path = JPATH_SITE . '/components/com_content/';
require_once $com_path . 'helpers/route.php';

JModelLegacy::addIncludePath($com_path . '/models', 'ContentModel');

abstract class Bs3ghsvsArticle
{
	protected static $loaded = array();
	
	/**
	 * E.g. for blog <> list view with footables.
	 */
	public static function getList(&$params)
	{
		// Get an instance of the generic articles model
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = Factory::getApplication();
		$appParams = $app->getParams();
		$articles->setState('params', $appParams);

		// Set the filters based on the module params
		$articles->setState('list.start', 0);
		$articles->setState('list.limit', (int) $params->get('count', 0));
		$articles->setState('filter.published', 1);

		// Access filter
		$access     = !ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$articles->setState('filter.access', $access);

		// Prep for Normal or Dynamic Modes
		$mode = $params->get('mode', 'normal');

		switch ($mode)
		{
			case 'dynamic' :
				$option = $app->input->get('option');
				$view   = $app->input->get('view');

				if ($option === 'com_content')
				{
					switch ($view)
					{
						case 'category' :
							$catids = array($app->input->getInt('id'));
							break;
						case 'categories' :
							$catids = array($app->input->getInt('id'));
							break;
						case 'article' :
							if ($params->get('show_on_article_page', 1))
							{
								$article_id = $app->input->getInt('id');
								$catid      = $app->input->getInt('catid');

								if (!$catid)
								{
									// Get an instance of the generic article model
									$article = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));

									$article->setState('params', $appParams);
									$article->setState('filter.published', 1);
									$article->setState('article.id', (int) $article_id);
									$item   = $article->getItem();
									$catids = array($item->catid);
								}
								else
								{
									$catids = array($catid);
								}
							}
							else
							{
								// Return right away if show_on_article_page option is off
								return;
							}
							break;

						case 'featured' :
						default:
							// Return right away if not on the category or article views
							return;
					}
				}
				else
				{
					// Return right away if not on a com_content page
					return;
				}

				break;

			case 'normal' :
			default:
				$catids = $params->get('catid');
				$articles->setState('filter.category_id.include', (bool) $params->get('category_filtering_type', 1));
				break;
		}

		// Category filter
		if ($catids)
		{
			if ($params->get('show_child_category_articles', 0) && (int) $params->get('levels', 0) > 0)
			{
				// Get an instance of the generic categories model
				$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$categories->setState('params', $appParams);
				$levels = $params->get('levels', 1) ? $params->get('levels', 1) : 9999;
				$categories->setState('filter.get_children', $levels);
				$categories->setState('filter.published', 1);
				$categories->setState('filter.access', $access);
				$additional_catids = array();

				foreach ($catids as $catid)
				{
					$categories->setState('filter.parentId', $catid);
					$recursive = true;
					$items     = $categories->getItems($recursive);

					if ($items)
					{
						foreach ($items as $category)
						{
							$condition = (($category->level - $categories->getParent()->level) <= $levels);

							if ($condition)
							{
								$additional_catids[] = $category->id;
							}
						}
					}
				}

				$catids = array_unique(array_merge($catids, $additional_catids));
			}

			$articles->setState('filter.category_id', $catids);
		}

		// Ordering
		$articles->setState('list.ordering', $params->get('article_ordering', 'a.ordering'));
		$articles->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

		// New Parameters
		$articles->setState('filter.featured', $params->get('show_front', 'show'));
		$articles->setState('filter.author_id', $params->get('created_by', ""));
		$articles->setState('filter.author_id.include', $params->get('author_filtering_type', 1));
		$articles->setState('filter.author_alias', $params->get('created_by_alias', ""));
		$articles->setState('filter.author_alias.include', $params->get('author_alias_filtering_type', 1));
		$excluded_articles = $params->get('excluded_articles', '');

		if ($excluded_articles)
		{
			$excluded_articles = explode("\r\n", $excluded_articles);
			$articles->setState('filter.article_id', $excluded_articles);

			// Exclude
			$articles->setState('filter.article_id.include', false);
		}

		$date_filtering = $params->get('date_filtering', 'off');

		if ($date_filtering !== 'off')
		{
			$articles->setState('filter.date_filtering', $date_filtering);
			$articles->setState('filter.date_field', $params->get('date_field', 'a.created'));
			$articles->setState('filter.start_date_range', $params->get('start_date_range', '1000-01-01 00:00:00'));
			$articles->setState('filter.end_date_range', $params->get('end_date_range', '9999-12-31 23:59:59'));
			$articles->setState('filter.relative_date', $params->get('relative_date', 30));
		}

		// Filter by language
		$articles->setState('filter.language', $app->getLanguageFilter());

		$items = $articles->getItems();

		// Display options
		$show_date        = $params->get('show_date', 0);
		$show_date_field  = $params->get('show_date_field', 'created');
		$show_date_format = $params->get('show_date_format', 'Y-m-d H:i:s');
		$show_category    = $params->get('show_category', 0);
		$show_hits        = $params->get('show_hits', 0);
		$show_author      = $params->get('show_author', 0);
		$show_introtext   = $params->get('show_introtext', 0);
		$introtext_limit  = $params->get('introtext_limit', 100);

		// Find current Article ID if on an article page
		$option = $app->input->get('option');
		$view   = $app->input->get('view');

		if ($option === 'com_content' && $view === 'article')
		{
			$active_article_id = $app->input->getInt('id');
		}
		else
		{
			$active_article_id = 0;
		}

		// Prepare data for display using display options
		foreach ($items as &$item)
		{
			$item->slug    = $item->id . ':' . $item->alias;
			$item->catslug = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$app       = Factory::getApplication();
				$menu      = $app->getMenu();
				$menuitems = $menu->getItems('link', 'index.php?option=com_users&view=login');

				if (isset($menuitems[0]))
				{
					$Itemid = $menuitems[0]->id;
				}
				elseif ($app->input->getInt('Itemid') > 0)
				{
					// Use Itemid from requesting page only if there is no existing menu
					$Itemid = $app->input->getInt('Itemid');
				}

				$item->link = Route::_('index.php?option=com_users&view=login&Itemid=' . $Itemid);
			}

			// Used for styling the active article
			$item->active      = $item->id == $active_article_id ? 'active' : '';
			$item->displayDate = '';

			if ($show_date)
			{
				$item->displayDate = HTMLHelper::_('date', $item->$show_date_field, $show_date_format);
			}

			if ($item->catid)
			{
				$item->displayCategoryLink  = Route::_(ContentHelperRoute::getCategoryRoute($item->catid));
				$item->displayCategoryTitle = $show_category ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
			}
			else
			{
				$item->displayCategoryTitle = $show_category ? $item->category_title : '';
			}

			$item->displayHits       = $show_hits ? $item->hits : '';
			$item->displayAuthorName = $show_author ? $item->author : '';

			if ($show_introtext)
			{
				$item->introtext = HTMLHelper::_('content.prepare', $item->introtext, '', 'mod_articles_category.content');
				$item->introtext = self::_cleanIntrotext($item->introtext);
			}

			$item->displayIntrotext = $show_introtext ? self::truncate($item->introtext, $introtext_limit) : '';
			$item->displayReadmore  = $item->alternative_readmore;
		}

		return $items;
	}

	/**
	 * Strips unnecessary tags from the introtext
	 *
	 * @param   string  $introtext  introtext to sanitize
	 *
	 * @return mixed|string
	 *
	 * @since  1.6
	 */
	public static function _cleanIntrotext($introtext)
	{
		$introtext = str_replace('<p>', ' ', $introtext);
		$introtext = str_replace('</p>', ' ', $introtext);
		$introtext = strip_tags($introtext, '<a><em><strong>');
		$introtext = trim($introtext);

		return $introtext;
	}

	/**
	 * Method to truncate introtext
	 *
	 * The goal is to get the proper length plain text string with as much of
	 * the html intact as possible with all tags properly closed.
	 *
	 * @param   string   $html       The content of the introtext to be truncated
	 * @param   integer  $maxLength  The maximum number of charactes to render
	 *
	 * @return  string  The truncated string
	 *
	 * @since   1.6
	 */
	public static function truncate($html, $maxLength = 0)
	{
		$baseLength = strlen($html);

		// First get the plain text string. This is the rendered text we want to end up with.
		$ptString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

		for ($maxLength; $maxLength < $baseLength;)
		{
			// Now get the string if we allow html.
			$htmlString = HTMLHelper::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

			// Now get the plain text from the html string.
			$htmlStringToPtString = HTMLHelper::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

			// If the new plain text string matches the original plain text string we are done.
			if ($ptString == $htmlStringToPtString)
			{
				return $htmlString;
			}

			// Get the number of html tag characters in the first $maxlength characters
			$diffLength = strlen($ptString) - strlen($htmlStringToPtString);

			// Set new $maxlength that adjusts for the html tags
			$maxLength += $diffLength;

			if ($baseLength <= $maxLength || $diffLength <= 0)
			{
				return $htmlString;
			}
		}

		return $html;
	}

	/**
	 * Groups items by field
	 *
	 * @param   array   $list                        list of items
	 * @param   string  $fieldName                   name of field that is used for grouping
	 * @param   string  $article_grouping_direction  ordering direction
	 * @param   null    $fieldNameToKeep             field name to keep
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function groupBy($list, $fieldName, $article_grouping_direction, $fieldNameToKeep = null)
	{
		$grouped = array();

		if (!is_array($list))
		{
			if ($list == '')
			{
				return $grouped;
			}

			$list = array($list);
		}

		foreach ($list as $key => $item)
		{
			if (!isset($grouped[$item->$fieldName]))
			{
				$grouped[$item->$fieldName] = array();
			}

			if (is_null($fieldNameToKeep))
			{
				$grouped[$item->$fieldName][$key] = $item;
			}
			else
			{
				$grouped[$item->$fieldName][$key] = $item->$fieldNameToKeep;
			}

			unset($list[$key]);
		}

		$article_grouping_direction($grouped);

		return $grouped;
	}

	/**
	 * Groups items by date
	 *
	 * @param   array   $list                        list of items
	 * @param   string  $type                        type of grouping
	 * @param   string  $article_grouping_direction  ordering direction
	 * @param   string  $month_year_format           date format to use
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function groupByDate($list, $type = 'year', $article_grouping_direction, $month_year_format = 'F Y')
	{
		$grouped = array();

		if (!is_array($list))
		{
			if ($list == '')
			{
				return $grouped;
			}

			$list = array($list);
		}

		foreach ($list as $key => $item)
		{
			switch ($type)
			{
				case 'month_year' :
					$month_year = String::substr($item->created, 0, 7);

					if (!isset($grouped[$month_year]))
					{
						$grouped[$month_year] = array();
					}

					$grouped[$month_year][$key] = $item;
					break;

				case 'year' :
				default:
					$year = String::substr($item->created, 0, 4);

					if (!isset($grouped[$year]))
					{
						$grouped[$year] = array();
					}

					$grouped[$year][$key] = $item;
					break;
			}

			unset($list[$key]);
		}

		$article_grouping_direction($grouped);

		if ($type === 'month_year')
		{
			foreach ($grouped as $group => $items)
			{
				$date                      = new Date($group);
				$formatted_group           = $date->format($month_year_format);
				$grouped[$formatted_group] = $items;

				unset($grouped[$group]);
			}
		}

		return $grouped;
	}

	/**
	 * Get additional article data from #__bs3ghsvs_article.
	 *
	 * @param integer $articleId Value for db column article_id
	 * @param array $keys Values for db column key. If empty request all.
	 * @return array of arrays | boolean false
	*/
	public static function getExtraFields(int $articleId, array $keys = array(), bool $asArray = false)
	{
		if (!$articleId)
		{
			return false;
		}

		$keys = \array_flip($keys);
		ksort($keys);
		
		$sig = md5(serialize(array($articleId, $keys, $asArray)));
		
		if (isset(static::$loaded[__METHOD__][$sig]))
		{
			return static::$loaded[__METHOD__][$sig];
		}
		
		$prefix = 'article';
		$activeXml = Bs3GhsvsFormHelper::getActiveXml(
			$prefix,
			PlgSystemBS3Ghsvs::getPluginParams(),
			array(1) // stati
		);

		if (!$activeXml)
		{
			return false;
		}

		// Get the relevant values for column "key" in db table.
		foreach ($activeXml as $key => $status)
		{
			$keyValue = strtolower(substr_replace($key, '', 0, strlen($prefix)));

			if ($keys && !isset($keys[$keyValue]))
			{
				unset($activeXml[$key]);
				continue;
			}
			
			$activeXml[$key] = $keyValue;
		}

		if (!$activeXml)
		{
			return false;
		}

		// Load the article extra fields from the database.
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('key', 'value')))
			->from($db->qn('#__bs3ghsvs_article'))
			->where($db->qn('article_id') . ' = ' . $articleId)
			->where($db->qn('key') . ' IN('
				. implode(', ', $db->q($activeXml)) . ')')
		;
		$result = $db->setQuery($query)->loadObjectList('key');

		if (!$result)
		{
			return false;
		}

		foreach ($result as $key => $value)
		{
			$result[$key] = json_decode($value->value, true);
			
			if (json_last_error() !== JSON_ERROR_NONE)
			{
				$result[$key] = $value->value;
			}
		}

		if ($asArray === false)
		{ 
			$result = new Registry($result);
		}
		static::$loaded[__METHOD__][$sig] = $result;
		
		return $result;
	}
	
	public static function getExtensionData(int $articleId)
	{
		$extensionData = self::getExtraFields($articleId, array('extension'));

		if (
			!($extensionData instanceof Registry)
			|| self::isExtensionEmpty($extensionData->get('extension'))
		){
			return false;
		}

		return $extensionData->get('extension', new stdClass, 'OBJECT');
	}
	
	public static function getVariousData(int $articleId)
	{
		$data = self::getExtraFields($articleId, array('various'));

		if (
			!($data instanceof Registry)
			|| self::isVariousEmpty($data->get('various')))
		{
			return false;
		}

		return $data->get('various', new stdClass, 'OBJECT');
	}

	public static function getJcfieldsAsRegistry($item) : Registry
	{
		$JcfieldsAsRegistry = new Registry();
		
		if (!empty($item->jcfields) && is_array($item->jcfields))
		{
			$jcfields = array();

			foreach ($item->jcfields as $jcfield)
			{
				$jcfields[$jcfield->name] = $jcfield->rawvalue; 
			}
			
			$JcfieldsAsRegistry = new Registry($jcfields);
		}

		return $JcfieldsAsRegistry;
	}

	public static function buildFlagImages(array $languages)
	{
		$flags = \array_flip($languages);
		
		foreach ($flags as $flag => $key)
		{
			$alt = Text::sprintf('PLG_SYSTEM_BS3GHSVS_EXTENSION_LANGUAGE_X', str_replace('_', '-', $flag));
			$flags[$flag] = HTMLHelper::_('image',
				'mod_languages/' . $flag . '.gif',
				$alt,
				array('class' => 'flag excludevenobox'),
				true
			);
		}
		return $flags;
	}
	
	/**
	 * Check if it's worth to go on with 'extension' datas on display actions.
	 */
	public static function isExtensionEmpty(
		$extensionData,
		array $required = array('name', 'description', 'url')
	){
		if (!is_object($extensionData))
		{
			return true;
		}

		foreach ($required as $key)
		{
			if (empty($extensionData->$key))
			{
				return true;
			}
		}
		return false;
	}
	/**
	 * 
	 */
	public static function isVariousEmpty($various)
	{
		if (!is_object($various))
		{
			return true;
		}
		
		// Nearly impossible that this property exists AND is set to 0.
		// If it exists it should be alwys 1.
		if (empty($various->bs3ghsvs_various_active))
		{
			return true;
		}
		
		foreach ($various as $key => $value)
		{
			if (!empty($value))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * z.B. module-übergreifende id="..." aus JInput-Daten ermitteln.
	 * Beispiel sind modal-Buttons mit Content am Ende der Seite.
	 */
	public static function buildUniqueIdFromJinput($prefix = '')
	{
		$jinput = Factory::getApplication()->input;
		
		$getThis = array(
			'Itemid',
			'option',
			'view',
			'catid',
			'id',
			'task'
		);
		
		$id = '';
		
		foreach ($getThis as $GetMe)
		{
			$id .= '-' . $jinput->get($GetMe);
		}
		
		$id = $prefix . '-' . ApplicationHelper::stringURLSafe(base64_encode($id));
		
		return $id;
	}

###################################### 	 * NUR FÜR Datenbank-Migration! REMOVE LATER - START
	
	/**
	 * NUR FÜR Datenbank-Migration!
	 * Zum Konvertieren alter extension Felder u-ä. nach neue Plugin-Anfordernisse,
	 	 um nicht alles neu speichern zu müssen nach plumpem Import der DB.
	 * Muss explizit irgendwo gestartet werden.
	 * JLoader::register('Bs3ghsvsArticle', JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php');
	 * Bs3ghsvsArticle::convertExtensionFields();
	 */
	public static function convertExtensionFields()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__content');
		$db->setQuery($query);
		$articles = $db->loadObjectList('id');

		foreach ($articles as $key => $article)
		{
			$registry = new Registry($article->attribs);
			$params = $registry->toArray();
			
			$extensionData = array();
			$various = array();
			
			$various['articlesubtitle'] = isset($params['articlesubtitle1'])
				? $params['articlesubtitle1'] : '';

			$extensionData['name'] = isset($params['extensionname'])
				? $params['extensionname'] : '';
			
			$extensionData['description'] = isset($params['extensiondescription'])
				? $params['extensiondescription'] : '';

			$extensionData['inspiredby'] = isset($params['extensioninspiredby'])
				? $params['extensioninspiredby'] : '';

			$extensionData['url'] = isset($params['extensionurl'])
				? trim($params['extensionurl']) : '';

			if ($extensionData['url'] && (strpos($extensionData['url'], '://') === false))
			{
				$download_url = 'https://updates.ghsvs.de/joomla';
				$extensionData['url'] = $download_url . '/' . ltrim($extensionData['url'], '/\\');
			}

			$extensionData['project'] = isset($params['extensionproject'])
				? $params['extensionproject'] : '';

			$extensionData['comment'] = isset($params['extensioncomment'])
				? $params['extensioncomment'] : '';

			$extensionData['languages'] = isset($params['extensionlanguages'])
				? $params['extensionlanguages'] : array();

			$extensionData['history'] = isset($params['extensionhistory'])
				? $params['extensionhistory'] : '';
			
			$extensionData['updateserver'] = '';
			
			if (isset($params['extensionupdateserver']))
			{
				if (!$params['extensionupdateserver'])
				{
					$extensionData['updateserver'] = '';
				}
				else
				{
					$extensionData['updateserver'] = (string) $params['extensionupdateserver'];
				}
			}
			
			/*unset(
				$params['extensionversion'],
				$params['extensiontest'],
				$params['extensionemail'],
				$params['articleshortdescription']
			);*/

			/*if (!empty($params['articleconnect']))
			{
				foreach ($params['articleconnect'] as $k => $v)
				{
					if (empty($v))
					{
						unset($params['articleconnect'][$k]);
					}
				}
			}*/
			
			$query = $db->getQuery(true)
				->delete($db->qn('#__bs3ghsvs_article'))
				->where($db->qn('article_id') . ' = ' . $article->id)
				->where($db->qn('key') . ' IN('
					. implode(',', $db->q(array('extension', 'various'))) . ')'
				)
			;
#
			$db->setQuery($query);
			$db->execute();
			
			$extensionEmpty = $variousEmpty = true;
			$tuples = array();
			
			// All required fields present? Otherwise skip.
			if (!self::isExtensionEmpty((object) $extensionData))
			{
				$extensionData['bs3ghsvs_extension_active'] = 1;
				$tuples[] = $article->id
							. ', ' . $db->q('extension')
							. ', ' . $db->q(json_encode($extensionData))
				;
				
				$extensionEmpty = false;
			}
			
			foreach ($various as $checkValue)
			{
				if (!empty($checkValue))
				{
					$variousEmpty = false;
					break;
				}
			}
			
			if ($variousEmpty === false)
			{
				$various['bs3ghsvs_various_active'] = 1;

				$tuples[] = $article->id
							. ', ' . $db->q('various')
							. ', ' . $db->q(json_encode($various))
				;
			}

			if ($tuples)
			{
				$query = $db->getQuery(true)
					->insert($db->quoteName('#__bs3ghsvs_article'))
					->columns($db->quoteName(array('article_id', 'key', 'value')))
					->values($tuples);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
	
	/**
	 * NUR FÜR Datenbank-Migration!
	 * Zum Konvertieren alter core_params in com_tags,
	 	 um nicht alles neu speichern zu müssen nach plumpem Import der DB.
	 * Wird oben in convertExtensionFields() direkt ausgeführt.
	 */
	protected static function convertTagParams($item)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__ucm_content')
			->where('core_content_item_id = ' . $item->id)
			->where('core_type_alias = "com_content.article"')
		;
		$db->setQuery($query);
		$articles = $db->loadObjectList();

		foreach ($articles as $article)
		{
			$article->core_params = $item->attribs;
			$db->updateObject('#__ucm_content', $article, 'core_content_id');
		}
	}
	
	/**
	 * NUR FÜR Datenbank-Migration!
	 * Zum Konvertieren alter <hr class="system-pagebreak" title="Title" alt="Alt" />
	 * nach neue <p>{pagebreakghsvs-slider title="Title" title2="Title2"}</p>.
	 * Zum Konvertieren alter <hr class="system-pagebreak" title="endepagebreaks" />
	 * nach neue <p>{pagebreakghsvs-slider slidersEnd="1"}</p>.
	 * Muss explizit irgendwo gestartet werden.
	 * JLoader::register(Bs3ghsvsArticle', JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php');
	 * Bs3ghsvsArticle::convertPagebreakghsvs();
	 */
	public static function convertPagebreakghsvs()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__content');
		$db->setQuery($query);
		$articles = $db->loadObjectList('id');

		foreach ($articles as $key => $article)
		{
			$sepa = '[--SEPA--intro-full--Y]';

			$text = $article->introtext . $sepa . $article->fulltext;

			if (strpos($text, 'system-pagebreak') !== false)
			{
				$regex = '#<hr(.*)class="system-pagebreak"(.*)\/>#iU';
				$matches = array();
				preg_match_all($regex, $text, $matches, PREG_SET_ORDER);
				
				foreach ($matches as $match)
				{
					$match[0] = trim($match[0]);
					$match[2] = trim($match[2]);

					if (strpos($match[0], 'endepagebreaks') !== false)
					{
						$new = '<p>{pagebreakghsvs-slider slidersEnd="1"}</p>';
					}
					else if (strpos($match[0], 'alt="') !== false)
					{
						$new = '<p>{pagebreakghsvs-slider '
							. trim(str_replace('alt="', 'title2="', $match[2]))
							. '}</p>';
					}
					else
					{
						$new = '<p>{pagebreakghsvs-slider '
							. $match[2] . ' title2=""'
							. '}</p>';
					}
					$text = str_replace($match[0], $new, $text);
				}
				$texts = explode($sepa, $text);
				$article->introtext = $texts[0];
				$article->fulltext = $texts[1];
				$db->updateObject('#__content', $article, 'id');
			}
		}
	}
	
###################################### REMOVE LATER - END
}
