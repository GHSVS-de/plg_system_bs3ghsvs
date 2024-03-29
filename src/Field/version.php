<?php
/*
GHSVS 2019-02-01
Usage:
<field name="assetsbe" type="plgSystemHyphenateGhsvs.assetsbe" hidden="true"
	loadjs="false" loadcss="true" />

If attributs loadjs or loadcss are missing their default value is TRUE => Assets will be loaded.

*/
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class plgSystemBs3GhsvsFormFieldVersion extends FormField
{
	protected $type = 'version';

	protected function getInput()
	{

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
		->select($db->qn('manifest_cache'))->from($db->qn('#__extensions'))
		->where($db->qn('extension_id') .'='
		. (int) Factory::getApplication()->input->get('extension_id'))
		;
		$db->setQuery($query);

		try
		{
			$manifest = $db->loadResult();
		}
		catch (Exception $e)
		{
			return '';
		}
		$manifest = @json_decode($manifest);
		$version = isset($manifest->version) ? $manifest->version : Text::_('JLIB_UNKNOWN');
		return $version;
	}
}
