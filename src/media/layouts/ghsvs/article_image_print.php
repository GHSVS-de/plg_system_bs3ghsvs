<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Language\Text;

echo '<!--' . basename(__DIR__) . '/' . basename(__FIlE__) . '-->';

/**
$attributes: Registry. All found attributes like class, alt ... of img tag.
$pre: <img> attributes before src
$post: <img> attributes after src inclusive closing tag "/>"
$images: Array of arrays. Collected resized images with size keys like _u, _l, _m and so on.
*/
extract($displayData);

$aClass = $figcaption = $venobox = '';
$sources = array();

$aTitle = 'GHSVS_HIGHER_RESOLUTION_1';

$imagepopup = $images['_u']['img'];

$alt = $attributes->get('alt', '');
$title = $attributes->get('title', '');
$data_title = $attributes->get('data-title', ($alt ? $alt : $title));

// Because editors encode already quotes.
$alt = htmlspecialchars_decode($alt, ENT_QUOTES);
$alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
$title = htmlspecialchars_decode($title, ENT_QUOTES);
$title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
$data_title = htmlspecialchars_decode($data_title, ENT_QUOTES);
$data_title = htmlspecialchars($data_title, ENT_QUOTES, 'UTF-8');

$caption = $alt;

$venobox = 0;

if (PluginHelper::isEnabled('system', 'venoboxghsvs'))
{
	$imgClasses = explode(' ', $attributes->get('class', ''));
	$imgClasses = array_map('trim', $imgClasses);	

	if (!in_array('EXCLUDEVENOBOX', $imgClasses) && !in_array('excludevenobox', $imgClasses))
	{
		HTMLHelper::_('plgvenoboxghsvs.venobox');
		$venobox = 1;
		$aTitle = 'GHSVS_HIGHER_RESOLUTION_0';
	}
}

$picture = array('<picture>');

// Wir sind auf einer Seite, die explizit angefordert hat, dass Bilder für Print nicht verkleinert werden.
/*$sources = array(
	'(max-width: 340px)' => !empty($images['_s']['img']) ? $images['_s']['img'] : null,
	'(max-width: 420px)' => !empty($images['_m']['img']) ? $images['_m'] ['img']: null,
	'(min-width: 421px)' => !empty($images['_l']['img']) ? $images['_l']['img'] : null,
);*/

$image = !empty($images['_x']['img']) ? $images['_x']['img'] : $imagepopup;

foreach ($sources as $media => $srcset)
{
	if (!$srcset) continue;
	$picture[] = '<source srcset="' . $srcset . '" media="' . $media . '">';
}

$picture[] = '<source srcset="' . $image . '">';

$picture[] = '<img loading="lazy"'
	. $pre
	. ' src="' . $image . '"'
	. $post;

$picture[] = '</picture>';
$picture = implode('', $picture);

$aTitle = htmlspecialchars(Text::_($aTitle), ENT_QUOTES, 'UTF-8');

if (!$title)
{
	$title = $aTitle;
}
?>
<figure class="item-image-in-article mw-100 clearfix">
	<a data-gall="myGallery" href="<?php echo $imagepopup; ?>" title="<?php echo $title; ?>"
		data-title="<?php echo $data_title; ?>" class="<?php echo ($venobox ? 'venobox' : ''); ?>">
		<?php echo $picture; ?>
		<div class="iconGhsvs text-right">
			<div class="btn btn-default btn-sm">
				<span class="sr-only"><?php echo $aTitle; ?></span>
				{svg{bi/zoom-in}}
			</div>
		</div>
	</a>
	<?php #if ($caption)
	{ ?>
	<figcaption>
		<?php echo ($caption ? $caption . '<br />' : ''); ?>
		<a href="<?php echo $imagepopup; ?>" download class="noprint">Download "<?php echo basename($imagepopup); ?>"</a>
	</figcaption>
	<?php
	} ?>
</figure>