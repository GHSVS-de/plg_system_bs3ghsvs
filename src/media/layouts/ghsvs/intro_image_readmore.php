<?php
// intro_image_readmore
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;

JLoader::register('ContentHelperRoute', JPATH_BASE . '/components/com_content/helpers/route.php');

$item = $displayData['item'];

/* DEBUG. Because print_r(category item) kills memory_limit

file_put_contents(JPATH_SITE . '/blah.txt', '');

foreach($item as $key => $value)
{
	file_put_contents(JPATH_SITE . '/blah.txt', $key . ': ' . $value . "\n\n", FILE_APPEND);
}*/

$images = Bs3ghsvsItem::getItemImagesghsvs($item);

if ($image = $images->get('image_intro'))
{
	if (PluginHelper::isEnabled('system', 'venoboxghsvs'))
	{
		HTMLHelper::_('plgvenoboxghsvs.venobox');
	}

	$pre = Uri::root(true) . '/';
	
	if (empty($displayData['link']))
	{
		$displayData['link'] = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
	}
	
	if ($caption = $images->get('image_intro_caption'))
	{
		$caption = htmlspecialchars($caption, ENT_QUOTES, 'utf-8');
	}
 
	// Entweder explizit gesetzt oder (vielleicht) vom resizer-Plugin gesetzt.
	// Oder durch Bs3ghsvsItem::getItemImagesghsvs (weiß noch nicht, ob das OK ist.
	if ($tmp = $images->get('image_intro_popupghsvs'))
	{
		$imagepopup = $pre . $tmp;
	}
	else
	{
		$imagepopup = false;
	}

	if($imagepopup && ($tmp = $images->get('image_intro_popupghsvs_caption')))
	{
		$imagepopupDescr = $tmp;
	}
	else
	{
		$imagepopupDescr = $caption;
	}
	
	$alt = htmlspecialchars($images->get('image_intro_alt'), ENT_QUOTES, 'UTF-8');
	$imagepopupDescr = htmlspecialchars($imagepopupDescr, ENT_QUOTES, 'UTF-8');

	$picture = array();
	$sources = array();
	
	$picture[] = '<picture>';

	// From plg_system_resizerghsvs.
	$imgs = $images->get('introtext_imagesghsvs');
	
	if (!empty($imgs[0]) && is_array($imgs[0]))
	{
		$imgs = ArrayHelper::getColumn($imgs[0], 'img', 'size');
	
		$sources = array(
			'(max-width: 320px)' => !empty($imgs['_s']) ? $imgs['_s'] : null,
			'(max-width: 360px)' => !empty($imgs['_m']) ? $imgs['_m'] : null,
			'(max-width: 480px)' => !empty($imgs['_l']) ? $imgs['_l'] : null,
			'(max-width: 690px)' => !empty($imgs['_s']) ? $imgs['_s'] : null,
			'(max-width: 768px)' => !empty($imgs['_m']) ? $imgs['_m'] : null,
			'(max-width: 991px)' => !empty($imgs['_l']) ? $imgs['_l'] : null,
			'(max-width: 1380px)' => !empty($imgs['_s']) ? $imgs['_s'] : null,
			'(max-width: 1540px)' => !empty($imgs['_m']) ? $imgs['_m'] : null,
			'(min-width: 1541px)' => !empty($imgs['_l']) ? $imgs['_l'] : null,
		);
	}

	foreach ($sources as $media => $srcset)
	{
		if (!$srcset) continue;
		$picture[] = '<source srcset="' . $pre . $srcset . '" media="' . $media . '">';
	}

	$picture[] = '<source srcset="' . $pre . $image . '">';

	$img = '<img loading="lazy"';

	$img .= ' src="' . $pre . $image . '"';
	$img .= ' alt="' . ($alt ? $alt : $caption) . '"';
	// $img .= ' itemprop="image"';
	$img .= '>';
	$picture[] = $img;

	$picture[] = '</picture>';
	
	$picture = implode('', $picture);
?>
<figure class="item-image image_intro">
	<div class="btnGroupGhsvs">
<?php
if ($imagepopup)
{
?>
		<a class="linkWithIconGhsvs venobox" href="<?php echo $imagepopup?>" data-title="<?php echo $imagepopupDescr; ?>" title="Popup. Größere Auflösung.">{svg{bi/zoom-in}}</a>
	<?php } ?>
		<a class="linkWithIconGhsvs" href="<?php echo $displayData['link']; ?>" title="<?php echo Text::_('COM_CONTENT_READ_MORE');?>">
			{svg{bi/link-45deg}}
		</a>
	</div>
	
<?php echo ($caption ? '<div class="img_caption">':''); ?>
 
	<?php echo $picture; ?>
  
 <?php if ($caption){ ?><figcaption><?php echo $caption; ?></figcaption><?php } ?>
<?php echo ($caption ? '</div>' : ''); ?>
</figure>

<?php
} //not empty image_intro ?>
