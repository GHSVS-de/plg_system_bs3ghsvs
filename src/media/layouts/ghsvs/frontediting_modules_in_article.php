<?php
defined('JPATH_BASE') or die;

/**
 * $module mandatory
 * $params optional
*/
extract($displayData);

$user = \JFactory::getUser();

if ($user->authorise('module.edit.frontend', 'com_modules.module.' . $module->id))
{
	$parameters   = JComponentHelper::getParams('com_modules');
	$redirectUri  = '&return=' . urlencode(base64_encode(JUri::getInstance()->toString()));
	$target       = '_blank';
	$itemid       = JFactory::getApplication()->input->get('Itemid', '0', 'int');

	$editUrl = JUri::base() . 'administrator/index.php?option=com_modules&task=module.edit&id='
		. (int) $module->id;

	if ($parameters->get('redirect_edit', 'site') === 'site')
	{
		$editUrl = JUri::base() . 'index.php?option=com_config&controller=config.display.modules&id='
			. (int) $module->id . '&Itemid=' . $itemid . $redirectUri;
		$target  = '_self';
	}
	echo '<a class="btn btn-frontediting btn-sm mb-2" href="' . $editUrl . '" target="' . $target . '">Modul "' . $module->title . '" bearbeiten (Modul-Id: ' . $module->id . ')</a>';
}
