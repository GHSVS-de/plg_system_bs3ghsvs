<?php
/**
"Overrides" for HTMLHelper methods of com_content/helpers/icon.php.
See "Redirects" in system plugin bs3ghsvs.
*/
?>
<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

abstract class JHtmlIconghsvs
{
	
	protected static $loaded = array();
	
	// media-Ordner:
	protected static $basepath = 'plg_system_bs3ghsvs';

	/**
	 * iconghsvs.create
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object    $category  The category information
	 * @param   Registry  $params    The item parameters
	 * @param   array     $attribs   Optional attributes for the link
	 * @param   boolean   $legacy    True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the create item link
	 */
	public static function create($category, $params, $attribs = array(), $legacy = false)
	{
		$uri = Uri::getInstance();

		$url = 'index.php?option=com_content&task=article.add&return=' . base64_encode($uri) . '&a_id=0&catid=' . $category->id;

		$text = LayoutHelper::render('joomla.content.icons.create', array('params' => $params, 'legacy' => $legacy));

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] = 'btn btn-primary ' . $attribs['class'];
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		$output = '<span class="hasTooltip" title="' . HTMLHelper::_('tooltipText', 'COM_CONTENT_CREATE_ARTICLE') . '">' . $button . '</span>';

		return $output;
	}

	/**
	 * iconghsvs.email
	 * Method to generate a link to the email item page for the given article
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the email item link
	 */
	public static function email($article, $params, $attribs = array(), $legacy = false)
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		if (PlgSystemBS3Ghsvs::$log)
		{
			$add = __METHOD__ . ': Load of "iconghsvs.email" blocked. Not supported.';
			Log::add($add, Log::WARNING, 'bs3ghsvs');
		}

		static::$loaded[__METHOD__] = 1;
		return '';
	}

	/**
	 * iconghsvs.print_popup
		* Override ermöglicht Verwendung einer print.php anstatt component.php, d.h. einfach eine print.php im Templateordner anlegen, wenn man component.php nicht mag.
		* Und location=yes.
	 *
	 * Method to generate a popup link to print an article
	 *
	 * @param   object    $article  The article information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_popup(
		$article,
		$params,
		$attribs = array(),
		$legacy = false,
		$tmpl = 'print',
		$iconClass = ''
	){
		$app = Factory::getApplication();
		$input = $app->input;
		$request = $input->request;
		$template = $app->getTemplate();

		if (!is_file(JPATH_THEMES . '/' . $template . '/' . $tmpl . '.php'))
		{
			$tmpl = 'component';
		}

		$url  = ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language);
		$url .= '&tmpl=' . $tmpl . '&print=1&layout=default';

		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=yes';

		$text = HTMLHelper::_('bs3ghsvs.layout',
			'joomla.content.icons.print_popup',
			array('params' => $params, 'legacy' => $legacy)
		);

		$attribs['title']   = htmlspecialchars(
			Text::sprintf('PLG_SYSTEM_BS3GHSVS_PRINT', $article->title, ENT_QUOTES, 'UTF-8')
		);
		#$attribs['onclick'] = "window.open(this.href,'win2','" . $status . "'); return false;";
		$attribs['rel']     = 'nofollow noopener noreferrer';
		
		$attribs['target'] = '_blank';
		
		
		$attribs = ArrayHelper::toString($attribs);
		
		if ($iconClass)
		{
			if (strpos($iconClass, '{') === false)
			{
				$iconClass = '<span class="' . $iconClass . '" aria-hidden="true"></span>';
			}
			$text = '<span class="sr-only">' . trim($text) . '</span>'; 
		}
		return '<a href="' . Route::_($url) . '" ' . $attribs . '>' . $iconClass . $text . '</a>';
		# return '<a href="' . Route::_($url) . '" target="_blank">' . $iconClass . $text . '</a>';
	}

	/**
	 * iconghsvs.print_screen
	 * Method to generate a link to print an article
	 *
	 * @param   object    $article  Not used, @deprecated for 4.0
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Not used, @deprecated for 4.0
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string  The HTML markup for the popup link
	 */
	public static function print_screen($article, $params, $attribs = array(), $legacy = false)
	{

		$text = HTMLHelper::_('bs3ghsvs.layout',
			'joomla.content.icons.print_screen',
			array('params' => $params, 'legacy' => $legacy)
		);

		return '<a href="#" onclick="window.print();return false;">' . $text . '</a>';
	}

	public static function edit($article, $params, $attribs = array(), $legacy = false)
	{
		$user = Factory::getUser();
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return;
		}

		// Ignore if the state is negative (trashed).
		if ($article->state < 0)
		{
			return;
		}

		// Show checked_out icon if the article is checked out by a different user
		if (property_exists($article, 'checked_out')
			&& property_exists($article, 'checked_out_time')
			&& $article->checked_out > 0
			&& $article->checked_out != $user->get('id'))
		{
			$checkoutUser = Factory::getUser($article->checked_out);
			$date         = HTMLHelper::_('date', $article->checked_out_time);
			$tooltip      = Text::sprintf('COM_CONTENT_CHECKED_OUT_BY', $checkoutUser->name)
				. ', ' . $date;
			
			$text = Text::_('JLIB_HTML_CHECKED_OUT');

			$output = '<span class="text-red">{svg{regular/edit}}</span>'
				. Text::_('COM_CONTENT_EDIT_ITEM') . ' (' .$tooltip . ')</span>';

			return $output;
		}

		$contentUrl = ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language);
		$url        = $contentUrl . '&task=article.edit&a_id=' . $article->id . '&return=' . base64_encode($uri);

		if ($article->state == 0)
		{
			$overlib = JText::_('JUNPUBLISHED');
		}
		else
		{
			$overlib = JText::_('JPUBLISHED');
		}

		$text = '{svg{regular/edit}} ' . Text::_('COM_CONTENT_EDIT_ITEM');

		$attribs['title']   = Text::_('JGLOBAL_EDIT_TITLE');
		$output = HTMLHelper::_('link', JRoute::_($url), $text, $attribs);

		return $output . ' (' . $overlib . ')';
	}
}