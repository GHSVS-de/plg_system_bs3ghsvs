<?php
/*
 * Collect configuration infos from plgSystemBs3Ghsvs.json in templates html/ folders.
 * Simple output.
 */
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

JLoader::register('Bs3ghsvsTemplate', __DIR__ . '/../Helper/TemplateHelper.php');

class JFormFieldTemplatesJsonConfigurationInfo extends FormField
{
	protected $type = 'templatesJsonConfigurationInfo';

	protected function getInput()
	{
		$html = array('<h4>' . Text::_('PLG_SYSTEM_BS3GHSVS_TEMPLATES_JSON_CONFIGURATION_INFO') . '</h4>');
		$templates = Bs3ghsvsTemplate::getActiveInTemplates();

		if (!$templates)
		{
			$html[] = Text::_('PLG_SYSTEM_BS3GHSVS_TEMPLATES_JSON_CONFIGURATION_NONE_FOUND');
		}
		else
		{
			foreach ($templates as $template)
			{
				$path = 'templates/' . $template . '/html/plgSystemBs3Ghsvs.json';
				$html[] = '<h5>* Template: ' . $template . '<br>** File: <a href="'
					. Uri::root() . $path . '" target="_blank">'
					. $path . '</a></h5>';
				$options = Bs3ghsvsTemplate::getTemplateOptionsFromJson($template);
				foreach ($options as $key => $dingsbums)
				{
					$html[] = '<h6> &gt;&gt; ' . $key . '</h6>';
					$html[] = '<pre>' . print_r($dingsbums, true) . '</pre>';
				}
			}
		}
		return implode('', $html);
	}
}
