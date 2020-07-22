<?php
/**
2015-06-01
für about-africa.
Wenn ein Kategoriebild existiert und ein weiteres mit Endung -thumb, also z.B.
KK042012-Seite01.jpg und KK042012-Seite01-thumb.jpg
wird thumb dargestellt, bei Klick Venobox.
Thumb sollte 320Pixel sein.
Wenn kein Thumbbild, wird halt großes als Thumb genommen.

2015-08-01: Abwandlung von joomla.content.category_default. Versuch einen universellen kategorie-Header zu bauen, also sowohl für Liste als auch Blog. Dafür müssen paar load-Aufrufe wie subtemplate und children hier verhindert werden und in aufrufender Datei verbleiben.

*/

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

$params  = $displayData->params;
$canEdit = $params->get('access-edit');
$catImage = $title = '';
$pageheader_suffix_ghsvs = $params->get('pageheader_suffix_ghsvs', '');

if ($params->get('show_description_image'))
{
 $catImage = $displayData->get('category')->getParams()->get('image');
	if ($catImage)
	{
		HTMLHelper::_('plgvenoboxghsvs.venobox');
		$imgfloat = 'left';
		$caption = $displayData->get('category')->getParams()->get('image_alt');
		if ($caption)
		{
			$title = ' data-title="' . $this->escape($caption) . '"';
			$caption = ' class="caption"' . $title;
		}
		$parts = explode('.', $catImage);
		$ext = array_pop($parts);
		$thmb = implode('.', $parts).'-thumb.'.$ext;
		if (!JFile::exists(JPATH_SITE.'/'.$thmb))
		{
			$thmb = $catImage;
		}
	}
}
?>
<?php
 echo HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.page_heading',
		array('params' => $params)
	); ?>
<?php if($params->get('show_category_title', 1)) : ?>
<div class="page-header">
	<h2><?php
			echo HTMLHelper::_(
				'content.prepare',
				$displayData->get('category')->title,
				'',
				$displayData->get('category')->extension.'.category.title'
			); ?></h2>
</div><!--/page-header h2-->
<?php endif; ?>
<?php if ($params->get('show_cat_tags', 1)) :
	echo HTMLHelper::_('bs3ghsvs.layout','joomla.content.tags', $displayData->get('category')->tags->itemTags);
endif; ?>
<?php if ($params->get('show_description', 1) || $catImage) : ?>
<div class="category-desc">
	<?php if ($catImage) : ?>
	<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image">
		<a href="<?php echo $catImage; ?>" class="venobox"<?php echo $title; ?>>
			<img<?php echo $caption;?>	src="<?php echo $thmb; ?>" alt="" />
		</a>
	</div><!--/item-image-->
	<?php endif; ?>
	<?php if ($params->get('show_description') && $displayData->get('category')->description) : ?>
		<?php
			echo HTMLHelper::_(
				'content.prepare',
				$displayData->get('category')->description,
				'',
				$displayData->get('category')->extension . '.category'
			); ?>
	<?php endif; ?>
	<div class="clr"></div>
</div><!--/category-desc-->
<?php endif;?>