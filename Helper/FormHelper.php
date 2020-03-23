<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

class Bs3GhsvsFormHelper
{
	protected static $loaded = array();
	
	public static function getActiveXml($key, $paras, $status = array(1, 2))
	{
		$params = clone $paras;

		$key = 'XmlActive' . ucfirst($key);
		$XmlActive = $params->get($key);

		if (!is_object($XmlActive) || !count(get_object_vars($XmlActive)))
		{
			return array();
		}

		foreach ($XmlActive as $file => $active)
		{
			if (!in_array($active, $status) || strpos($file, 'xml_') !== 0)
			{
				unset($XmlActive->$file);
				continue;
			}
		}
		$XmlActive = (array) $XmlActive;

		foreach ($XmlActive as $file => $active)
		{
			unset($XmlActive[$file]);
			$file = substr($file, 4);
			$XmlActive[$file] = $active;
		}
		return $XmlActive;
	}
	
	/**
	 * Placeholder einsetzen statt Labels. Labels erhalten sr-only, damit Validierung noch klappt.
	 * Klasse form-control einsetzen für BS 3.
	 */
	public static function prepareFormFields(
		&$form,
		$hintSource = 'description',
		$overrides = array()
	){
		$fields = $form->getGroup('');

		// Für welche Feldtypen keine Placeholder/Hints einsetzen:
		$excludeTypes  = array(
		 'checkbox',
		 'radio',
		 'submit',
		 'radio',
		 'spacer',
		 'captcha',
		);
		
		foreach ($fields as $field)
		{
			if (in_array($field->getAttribute('type'), $excludeTypes))
			{
				continue;
			}
			
			$class = $field->getAttribute('class');

			if (strpos($class, 'form-control') === false)
			{
				$form->setFieldAttribute(
					$field->fieldname,
					'class',
					$class . ' form-control',
					$field->group
				);
			}
#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($field->fieldname, true) . '</pre>';#exit;
#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($field->group, true) . '</pre>';#exit;
			if (
				!empty($overrides[$field->fieldname])
				&& $overrides[$field->fieldname]['group'] === $field->group
			){
				$hint = $overrides[$field->fieldname]['str'];
			}
			elseif (!empty($field->getAttribute($hintSource)))
			{
				$hint = $field->getAttribute($hintSource);
			}
			else
			{
				$hint = $field->getAttribute('label');
			}
#echo ' 4654sd48sa7d98sD81s8d71dsa <pre>' . print_r($hint, true) . '</pre>';
			if ($hint)
			{
				$hint = Text::_($hint);
				
				if (!$field->required)
				{
					$hint .= ' ' . Text::_('COM_CONTACT_OPTIONAL');
				}
				else
				{
					$hint .= ' *';
				}
		
				$form->setFieldAttribute($field->fieldname, 'hint', $hint, $field->group);
				$form->setFieldAttribute($field->fieldname, 'translateHint', true, $field->group);
		
				$class = $field->getAttribute('labelclass');
#echo ' 4654sd48sa7d98sD81s8d71dsa class<pre>' . print_r($class, true) . '</pre>';
				if (strpos($class, 'sr-only') === false)
				{
					$form->setFieldAttribute(
						$field->fieldname,
						'labelclass',
						$class . ' sr-only',
						$field->group
					);
				}
			}
		}
	}
}