<?php
/*
 * Collect configuration infos from plgSystemBs3Ghsvs.json in templates html/ folders.
 * Simple output.
 */
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

JLoader::register('Bs3ghsvsArticle', __DIR__ . '/Helper/ArticleHelper.php');

class JFormFieldArticlesWithExtrafieldsInfo extends FormField
{
	protected $type = 'ArticlesWithExtrafieldsInfo';

	protected function getInput()
	{
		$html = array('<h4>' . Text::_('PLG_SYSTEM_BS3GHSVS_TEMPLATES_JSON_CONFIGURATION_INFO') . '</h4>');
		
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->qn(array('article_id', 'key', 'value')))
			->from($db->qn('#__bs3ghsvs_article'))
			#->where($db->qn('article_id') . ' = ' . $articleId)
			#->where($db->qn('key') . ' IN('
			#	. implode(', ', $db->q($activeXml)) . ')')
		;
		$results = $db->setQuery($query)->loadObjectList();
		
		$collect = array();
		
		foreach ($results as $result)
		{
//administrator/index.php?option=com_content&task=article.edit&id=262
			$link = '<a href=index.php?option=com_content&task=article.edit&id=' . $result->article_id . ' target=_blank>edit</a>';
			$collect[$result->key][$result->article_id] = $link;
		}
		
		return '<pre>' . print_r($collect, true) . '</pre>';
	}
}
