<?php
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class plgSystemBs3GhsvsFormFieldLessenabled extends FormField
{
	protected $type = 'Lessenabled';

	protected function getInput()
	{
		JLoader::register('Bs3ghsvsTemplate', __DIR__ . '/../Helper/TemplateHelper.php');
		$params = Bs3ghsvsTemplate::getLessPluginParams();

		$txt = Text::sprintf(
		 'GHSVS_LESSPLUGIN_INFO',
			$params->get('checkedPlugin'),
			$params->get('lesscPath') ? $params->get('lesscPath') : Text::_('JNONE'),
			$params->get('isEnabled') ? Text::_('JYES') : Text::_('JNO') . ' (nicht unbedingt nötig)',
			$params->get('isInstalled') ? Text::_('JYES') : Text::_('JNO') . ' (keinerlei LESS-Kompilierung möglich)'
		);

		return $txt;
	}
}
