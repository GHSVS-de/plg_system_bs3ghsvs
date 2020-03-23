<?php
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

abstract class JHtmlSelectghsvs
{
	/**
	 * Generates an HTML radio list.
	 *
	 * @param   array    $data       An array of objects
	 * @param   string   $name       The value of the HTML name attribute
	 * @param   string   $attribs    Additional HTML attributes for the `<select>` tag
	 * @param   mixed    $optKey     The key that is selected
	 * @param   string   $optText    The name of the object variable for the option value
	 * @param   string   $selected   The name of the object variable for the option text
	 * @param   boolean  $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True if options will be translated
	 *
	 * @return  string  HTML for the select list
	 *
	 * @since   1.5
	 */
	public static function radiolist(
		$data,
		$name,
		$attribs = null,
		$optKey = 'value',
		$optText = 'text',
		$selected = null,
		$idtag = false,
		$translate = false
	){
		if (is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}

		$id_text = $idtag ?: $name;

		$html = '<div class="controls">';

		foreach ($data as $obj)
		{
			$html .= '<div class="form-check form-check-inline">';

			$k = $obj->$optKey;
			$t = $translate ? Text::_($obj->$optText) : $obj->$optText;
			$id = (isset($obj->id) ? $obj->id : null);

			$extra = '';
			$id = $id ? $obj->id : $id_text . $k;

			if (is_array($selected))
			{
				foreach ($selected as $val)
				{
					$k2 = is_object($val) ? $val->$optKey : $val;

					if ($k == $k2)
					{
						$extra .= ' selected="selected" ';
						break;
					}
				}
			}
			else
			{
				$extra .= ((string) $k === (string) $selected ? ' checked="checked" ' : '');
			}

			$html .= '<input type="radio" class="form-check-input" name="' . $name . '" id="' . $id . '" value="' . $k . '" '
					. $extra . $attribs . '>';
			$html .= '<label for="' . $id . '" class="form-check-label" id="' . $id . '-lbl">' . $t . '</label>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}
