<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Plugin\PluginHelper;

class plgSystemBs3ghsvsFormFieldEnabledChecker extends FormField
{
	protected $type = 'enabledchecker';

	protected function getInput()
	{
		$html = array('<ul>');

		$needExtensions = array(
		);

		$needPlugins = array(
			array('venoboxghsvs' => 'system'),
			array('scsscompiler' => 'system'),
			//array('lessghsvs' => 'system'),
			array('redirecttrashto404ghsvs' => 'system'),
			// 'joomla' => 'osmap',
		);

		$recommendedPlugins = array(
			array('lessghsvs' => 'system'),
			array('importfontsghsvs' => 'system'),
			array('syntaxhighlighterghsvs' => 'content'),
			array('syntaxhighlighterghsvs' => 'editors-xtd'),
			array('pagebreakghsvs' => 'editors-xtd'),
			array('hyphenateghsvs' => 'system'),
			//array('articleconnectghsvs' => 'system'),
			array('jooag_shariff' => 'system'),
			array('easycalccheckplus' => 'system'),
			array('jch_optimize' => 'system'),
		);

		foreach ($needExtensions as $extension => $path)
		{
			if (!file_exists($path))
			{
				$html[] = '<li>'
					. Text::sprintf(
						'PLG_SYSTEM_BS3GHSVS_MISSING_EXTENSIONS',
						$extension,
						str_replace(JPATH_SITE, 'JROOT', $path)
					)	. '</li>';
			}
		}

		foreach ($needPlugins as $item)
		{
			$name = key($item);
			$group = $item[$name];

			if (!PluginHelper::isEnabled($group, $name))
			{
				$html[] = '<li>'
					. Text::sprintf(
						'PLG_SYSTEM_BS3GHSVS_MISSING_PLUGIN',
						$name,
						$group
					)	. '</li>';
			}
			else
			{
				$html[] = '<li>'
					. Text::sprintf(
						'PLG_SYSTEM_BS3GHSVS_FOUND_PLUGIN',
						$name,
						$group
					)	. '</li>';
			}
		}

		foreach ($recommendedPlugins as $item)
		{
			$name = key($item);
			$group = $item[$name];

			if (!PluginHelper::isEnabled($group, $name))
			{
				$html[] = '<li>'
					. Text::sprintf(
						'PLG_SYSTEM_BS3GHSVS_RECOMMENDED_PLUGIN',
						$name,
						$group
					)	. '</li>';
			}
			else
			{
				$html[] = '<li>'
					. Text::sprintf(
						'PLG_SYSTEM_BS3GHSVS_FOUND_PLUGIN',
						$name,
						$group
					)	. '</li>';
			}
		}
		$html[] = '<li>mod_teaserghevs</li>';

		$html[] = '</ul>';
		return implode('', $html);
	}
}
