<?php
/**
"Overrides" for JHtml methods of com_content/helpers/icon.php.
See "Redirects" in system plugin bs3ghsvs.
*/
?>
<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

abstract class JHtmlSlicknavghsvs
{
	
	protected static $loaded = array();

	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

 /**
	 * bs3ghsvs.slicknav
	 * Mobil-Menü Slicknav.
	 * https://github.com/ComputerWolf/SlickNav
	 */
 /*
 DONE: 'label' : 'MENU', // Label for menu button. Use an empty string for no label.
 'duplicate': true, // If true, the mobile menu is a copy of the original.
 'duration': 500, // The duration of the sliding animation.
 'easingOpen': 'swing', // Easing used for open animations.
 'easingClose': 'swing' // Easing used for close animations.
 'closedSymbol': '&#9658;', // Character after collapsed parents.
 'openedSymbol': '&#9660;', // Character after expanded parents.
 'prependTo': 'body', // Element, jQuery object, or jQuery selector string to prepend the mobile menu to.
 DONE: 'appendTo': '', // Element, jQuery object, or jQuery selector string to append the mobile menu to. Takes precedence over prependTo.
 'parentTag': 'a', // Element type for parent menu items.
 'closeOnClick': false, // Close menu when a link is clicked.
 'allowParentLinks': false // Allow clickable links as parent elements.
 'nestedParentLinks': true // If false, parent links will be separated from the sub-menu toggle.
 'showChildren': false // Show children of parent links by default.
 'removeIds': false // Remove IDs from all menu elements. Defaults to true if duplicate is true.
 'removeClasses': false // Remove classes from all menu elements.
 'brand': '' // Add branding to menu bar.
 
 $loadCoreCss : lade SlichnavCore-CSS, falls nicht schon geladen?
 */
 public static function slicknav($options = array())
 {
  // Nur, um bei identischen, aber lediglich anders sortierten $params
  // nicht doppelt zu laden
  ksort($options);


		$params = new Registry($options);

  $SlickNavParent = $params->get('SlickNavParent', '.div4menue-oben');
  $SlickNavUL = $params->get('SlickNavUL', '.nav.menu');
  $selector = trim($SlickNavParent . ' ' . $SlickNavUL);

  if (!$selector)
  {
   JFactory::getApplication()->enqueueMessage(__METHOD__ . ': Missing Selektor. Slicknav menu not loaded!', 'error');
   return false;
  }

  $sig = md5(serialize(array($selector, $params)));
  
  if (!isset(static::$loaded[__METHOD__][$sig]))
  {
			// Load Core-JS and -CSS
   self::slicknavcore($params->get('loadCoreCss', 0));
			
			// Add Init-Script.
   // 2017-08 Merge calculated options.
			if ($params->get('prependTo', ''))
			{
				$prependAppend = array('prependTo' => $params->get('prependTo'));
			}
			else
			{
				$prependAppend = array('appendTo' => $params->get('appendTo', $SlickNavParent));
			}
			$options = array_merge(
			 $options,
			 array(
			  'label' => $params->get('label', ''),
			 ),
				$prependAppend
			);

   JFactory::getDocument()->addScriptDeclaration(
    '(function($){
$(document).ready(function(){
 $("' .$selector . '").slicknav(' . json_encode($options) . ');
});
})(jQuery);');
   
			// 2017-07-28
			if($params->get('addSprungmarke', 1))
			{
    HTMLHelper::_('bs3ghsvs.addsprungmarke', $SlickNavParent . ' div.slicknav_menu');
			}

   static::$loaded[__METHOD__][$sig] = true;
  }

  return;
 }
 /*
	 * bs3ghsvs.slicknavcore
		* Load (only) Slicknav core JS and optionally core CSS.
  * $loadCSS : Slicknav-Core-CSS laden? Ich hab das separat in slicknav.less
  */
 public static function slicknavcore($loadCSS = 0)
 {
  if (!empty(static::$loaded[__METHOD__]))
  {
   return;
  }
  JHtml::_('jquery.framework');
  $file = static::$basepath . '/SlickNav/dist/jquery.slicknav.min.js';
		
		JHtml::_('script', // now 3.7.0 ready
			$file,
			array(
				// 'framework' => false,
				'relative' => true, //Nur so weitere Template-Overrides möglich!
				// 'pathOnly' => false,
				// 'detectBrowser' => false,
				// 'detectDebug' => false,
			)
		);

  if ($loadCSS)
  {
   $file = static::$basepath . '/SlickNav/slicknav.min.css';
   HTMLHelper::_('stylesheet', $file, array(), true);
  }
  JFactory::getDocument()->addScriptDeclaration('jQuery("html").removeClass("slicknavNotActive").addClass("slicknavActive");');
  static::$loaded[__METHOD__] = true;
  return;
 }
}