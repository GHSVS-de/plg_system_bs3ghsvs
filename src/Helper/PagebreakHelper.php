<?php
/**
 *
JLoader::register('Bs3ghsvsPagebreak',JPATH_PLUGINS . '/system/bs3ghsvs/Helper/PagebreakHelper.php');
*/

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Utility\Utility;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Image\Image;
use Joomla\CMS\Uri\Uri;

class Bs3ghsvsPagebreak
{
	protected static $loaded = array();

	/**
	* @var Registry|array|null null will load bs3ghsvs-Params.
	*/
	public static function buildSliders(
		&$theText,
		$id = null,
		$params = null
	){
		if (is_null($params))
		{
			$params = PlgSystemBS3Ghsvs::getPluginParams();
		}
		elseif (is_array($params))
		{
			$params = new Registry($params);
		}
		// something strange.
		else
		{
			$params = new Registry;
		}
		
		if (is_null($id))
		{
			$id = uniqid();
		}
		
		$app = Factory::getApplication();
 		$print = $app->input->getBool('print');
		$isRobot = (int) $app->client->robot;

		// JCE removes <p> around SHORTCODEs if setting in JCE yes. Therefore new regex since 2020-09.
		# Old: $regex = '#<p[^>]*>\s*{pagebreakghsvs-slider\s+(.+?)}\s*</p>#i';
		$regex = '#(<p[^>]*>\s*){0,1}{pagebreakghsvs-slider\s+([^}]+?)}(\s*</p>){0,1}#i';
		$regexEnd = '#(<p[^>]*>\s*){0,1}{pagebreakghsvs-slider[^}]*slidersEnd[^}]*}(\s*</p>){0,1}#iU';
		$toggleContainer = $params->get('toggleContainer', 'div');
		$headingTagGhsvs = $params->get('headingTagGhsvs', 'h4');
		$collector = $endedTextBlocks = array();

		// Array mit mindestens 1 Text-Element, egal, ob slidersEnd gefunden oder nicht.
		$endedTextBlocks = preg_split($regexEnd, $theText);
		
		// Finde falsch platzierte Eingaben von slidersEnd.
		foreach ($endedTextBlocks as $i => $endedText)
		{
			if (!trim($endedText))
			{
				unset($endedTextBlocks[$i]);
			}
		}

		// Now find embedded slides in each block.
		foreach ($endedTextBlocks as $i => $endedText)
		{
			preg_match_all($regex, $endedText, $matches, PREG_SET_ORDER);
			$text = preg_split($regex, $endedText);

			// Teil vor erstem slide, der aber ggf. auch leer sein kann,
			//  falls erstes regex ganz am Anfang im Beitrag.
			$collector[] = $text[0];
			
			// Es wurden weitere Panel-Regexe gefunden. Dann Accordion aufbauen
			if (count($text) > 1)
			{
				$selector = $dataParent = 'pagebreakghsvs' . $id . '-' . $i;
				
				if (!$print && !$isRobot)
				{
					$collector[] = HTMLHelper::_('bootstrap.startAccordion',
							$selector,
							array(
								// Damit mehrere geöffnet werden können auf FALSE!
								'parent' => false,
							)
						);
				}

				// Panels.
				foreach ($text as $key => $subtext)
				{
					// Ignore $matches[0]. Already collected above.
					if ($key)
					{
						$match = $matches[$key - 1];
						$match = (array) Utility::parseAttributes($match[2]);
						$title = $title2 = $class = '';
						$title = htmlspecialchars_decode($match['title'], ENT_COMPAT);
						$title = htmlspecialchars($title, ENT_COMPAT, 'utf-8');
						$title2 = @htmlspecialchars_decode($match['title2'], ENT_COMPAT);
						$title2 = htmlspecialchars($title2, ENT_COMPAT, 'utf-8');

						$href = $selector . '-' . $key;
						
						if (!$print && !$isRobot)
						{
							$collector[] = HTMLHelper::_('bootstrap.addSlide',
										$selector,
										$title,
										$href,
										$class,
										$toggleContainer,
										$title2
									);
						}

						$collector[] = '<' . $headingTagGhsvs . ' class="headAutoByPagebrekghsvs">'
							. $title . ($title2 ? ' - ' . $title2 : '') . '</' . $headingTagGhsvs . '>';

						$collector[] = $subtext;
						
						if (!$print && !$isRobot)
						{
							$collector[] = HTMLHelper::_('bootstrap.endSlide');
						}
						$collector[] = "\n<!--endSlide-->\n";
					}
				}

				if (!$print && !$isRobot)
				{
					$collector[] = HTMLHelper::_('bootstrap.endAccordion');
					
					// Aktive IDs in Session schreiben mit Ajax-Plugin.
					if ($params->get('activeToSession', 1) === 1)
					{
						$collector[] = HTMLHelper::_('bs3ghsvs.activeToSession', $dataParent);
					}
				}
				
				$collector[] = "\n<!--endAccordion-->\n";
			}
		}
		
		if ($collector)
		{
			$theText = implode('', $collector);
		}
	}
}
