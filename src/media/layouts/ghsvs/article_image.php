<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

echo '<!--' . basename(__DIR__) . '/' . basename(__FIlE__) . '-->';

/**
$attributes: Registry. All found attributes like class, alt ... of img tag.
$imgs: Array[0] of arrays. Relevant collected resized images with size keys like _u, _l, _m and so on.
$image: The origImage path.
*/
extract($displayData);

if (empty($imgs) && empty($image))
{
	return;
}

$mediaQueries = array();
$picture      = array('<picture>');
$venobox      = 0;
$attrString   = '';
$aTitle       = 'GHSVS_HIGHER_RESOLUTION_1';
$alt          = $attributes->get('alt', '');
$title        = $attributes->get('title', '');
$data_title   = $attributes->get('data-title', ($alt ? $alt : $title));

// Because editors encode already quotes.
$alt          = htmlspecialchars_decode($alt, ENT_QUOTES);
$alt          = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
$title        = htmlspecialchars_decode($title, ENT_QUOTES);
$title        = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$data_title   = htmlspecialchars_decode($data_title, ENT_QUOTES);
$data_title   = htmlspecialchars($data_title, ENT_QUOTES, 'UTF-8');
$caption      = $alt;
$attrArray    = $attributes->toArray();

if (PluginHelper::isEnabled('system', 'venoboxghsvs'))
{
	$imgClasses = explode(' ', $attributes->get('class', ''));
	$imgClasses = array_map('trim', $imgClasses);	

	if (!in_array('EXCLUDEVENOBOX', $imgClasses) && !in_array('excludevenobox', $imgClasses))
	{
		HTMLHelper::_('plgvenoboxghsvs.venobox', '.venobox', array('arrowsColor' => "#ffffff"));
		$venobox  = 1;
		$aTitle   = 'GHSVS_HIGHER_RESOLUTION_0';
	}
}

$aTitle = htmlspecialchars(Text::_($aTitle), ENT_QUOTES, 'UTF-8');

if (!$title)
{
	$title = $aTitle;
}

if (!empty($imgs[0]) && is_array($imgs[0]))
{
	$mediaQueries = array(
		'(max-width: 340px)' => '_s',
		'(max-width: 420px)' => '_m',
		'(min-width: 421px)' => '_l',
		
		// Largest <source> without mediaQuery. Also for fallback <img> src, width and height calculation.
		// Value only if you want to force one. Otherwise _x or fallback _u is used.
		'srcSetKey' => '',
	);
}
else
{
	$imgs  = array();
}

// Use $imgs not $imgs[0] because of ['order'] index.
// And because other $imgs collections can contain more than just 1 image.
$sources              = Bs3ghsvsItem::getSources($imgs, $mediaQueries, $image);
$sources              = $sources[0];
$attrArray['loading'] = 'lazy';
$attrArray['width']   = $sources['assets']['width'];
$attrArray['height']  = $sources['assets']['height'];
$attrArray['class']   = isset($attrArray['class']) ? $attrArray['class'] . ' h-auto' : 'h-auto';

foreach ($attrArray as $k => $v)
{
	$attrString .= ' ' . $k . '="' . $v . '"';
}

$picture[] = $sources['sources'];

$picture[] = '<img'
	. ' src="' . $sources['assets']['img'] . '"'
	. $attrString
	. '>';
$picture[] = '</picture>';
$picture   = implode('', $picture);
?>
<figure class="item-image-in-article">
	<a data-gall="myGallery" href="<?php echo $image; ?>" title="<?php echo $title; ?>"
		data-title="<?php echo $data_title; ?>" class="<?php echo ($venobox ? 'venobox' : ''); ?>">
		<?php echo $picture; ?>
		<div class="iconGhsvs text-right">
			<div class="btn btn-default btn-sm">
				<span class="sr-only"><?php echo $aTitle; ?></span>
				{svg{bi/zoom-in}}
			</div>
		</div>
	</a>
	<?php if ($caption)
	{ ?>
	<figcaption><?php echo $caption; ?></figcaption>
	<?php
	} ?>
</figure>