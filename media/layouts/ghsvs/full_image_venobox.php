<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;

$images = Bs3ghsvsItem::getItemImagesghsvs($displayData['item']);

if ($image = $images->get('image_fulltext'))
{
	$venobox = 0;
	$aTitle = 'GHSVS_HIGHER_RESOLUTION_1';
	
	if (PluginHelper::isEnabled('system', 'venoboxghsvs'))
	{
		$venobox = 1;
		HTMLHelper::_('plgvenoboxghsvs.venobox');
		$aTitle = 'GHSVS_HIGHER_RESOLUTION_0';
	}

	$alt = $images->get('image_fulltext_alt');
	$caption = $images->get('image_fulltext_caption');
	
	if (!($imagepopupDescr = $images->get('image_popupghsvs_caption')))
	{
		$imagepopupDescr = $caption;
	}
	
	// Explizit gesetzt?
	if (!($imagepopup = $images->get('image_fulltext_popupghsvs')))
	{
		// Nope. Use main image as popup.
		$imagepopup = $image;
	}

	$alt = htmlspecialchars(($alt ? $alt : $caption), ENT_QUOTES, 'UTF-8');
	$caption = htmlspecialchars($caption, ENT_QUOTES, 'UTF-8');
	$imagepopupDescr = htmlspecialchars($imagepopupDescr, ENT_QUOTES, 'UTF-8');

	$picture = array('<picture>');

	/* $imgs is something like
	Array
	(
			[_u] => images/logos/PLG.png
			[_x] => cache/images/logos/PLG_f_x.png
			[_l] => cache/images/logos/PLG_f_l.png
			[_m] => cache/images/logos/PLG_f_m.png
			[_s] => cache/images/logos/PLG_f_s.png
			[_og] => cache/images/logos/PLG_f_og.png
	)
	*/
	// Wird im Resizer-Part vom Plugin dynamisch gesetzt.
	$imgs = $images->get('fulltext_imagesghsvs');

	if (!empty($imgs[0]))
	{
		$imgs = ArrayHelper::getColumn($imgs[0], 'img', 'size');
/*
_u 700
_l 400
_m 360
_s 320
_og min 310
*/

		$sources = array(
		 '(max-width: 320px)' => !empty($imgs['_s']) ? $imgs['_s'] : null,
		 '(max-width: 360px)' => !empty($imgs['_m']) ? $imgs['_m'] : null,
		 '(max-width: 480px)' => !empty($imgs['_l']) ? $imgs['_l'] : null,
			// halbe Größe
			'(max-width: 640px)' => !empty($imgs['_s']) ? $imgs['_s'] : null,
			'(max-width: 768px)' => !empty($imgs['_m']) ? $imgs['_m'] : null,
			'(min-width: 769px)' => !empty($imgs['_l']) ? $imgs['_l'] : null,
		);

		foreach ($sources as $media => $srcset)
		{
			if (!$srcset) continue;
			$picture[] = '<source srcset="' . $srcset . '" media="' . $media . '">';
		}
	}

	$picture[] = '<source srcset="' . $image . '">';

	$picture[] = '<img loading="lazy"'
		. ' src="' . $image . '"'
		. ' alt="' . $alt . '"'
		. '>';
	
	$picture[] = '</picture>';
	
	$picture = implode('', $picture);

	// 2017-10-03. B\C.
	// Kein Bindestrich! Sonst funktioniert :not() nicht
	$align = 'imgAlign'
		. (!empty($displayData['use-float_fulltext']) ? $images->get('float_fulltext') : '');
?>
<figure class="item-image image_fulltext <?php echo $align?>">
	<a href="<?php echo $imagepopup; ?>" title="<?php echo $imagepopupDescr; ?>"
		data-title="<?php echo $imagepopupDescr; ?>" class="<?php echo ($venobox ? 'venobox' : ''); ?>">
		<?php echo $picture; ?>
		<div class="iconGhsvs text-right">
			<div class="btn btn-default btn-sm">
				<span class="sr-only"><?php echo Text::_($aTitle); ?></span>
				<span class="icon-zoom-in" aria-hidden="true"></span>
			</div>
		</div>
	</a>
	<?php if ($caption)
	{ ?>
	<figcaption><?php echo $caption; ?></figcaption>
	<?php
	} ?>
</figure>
<?php
} // if image_fulltext ?>
