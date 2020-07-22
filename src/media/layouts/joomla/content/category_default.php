<?php
/**
2015-06-01
für about-africa.
Wenn ein Kategoriebild existiert und ein weiteres mit Endung -thumb, also z.B.
KK042012-Seite01.jpg und KK042012-Seite01-thumb.jpg
wird thumb dargestellt, bei Klick Venobox.
Thumb sollte 320Pixel sein.
Wenn kein Thumbbild, wird halt großes als Thumb genommen.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

// Note that this layout opens a div with the page class suffix. If you do not use the category children
// layout you need to close this div either by overriding this file or in your main layout.
$params  = $displayData->params;
$extension = $displayData->get('category')->extension;
$canEdit = $params->get('access-edit');
$className = substr($extension, 4);

$catImage = $title = '';
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
			HTMLHelper::_('behavior.caption');
			$title = ' title="' . $this->escape($caption) . '"';
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

// This will work for the core components but not necessarily for other components
// that may have different pluralisation rules.
if (substr($className, -1) == 's')
{
	$className = rtrim($className, 's');
}
$tagsData  = $displayData->get('category')->tags->itemTags;
?>
<div class="<?php echo $className .'-category' . $displayData->pageclass_sfx;?>">
	<?php if ($params->get('show_page_heading')) : ?>
	<div class="page-headerHandschrift">
		<h1>
			<?php echo $displayData->escape($params->get('page_heading')); ?>
		</h1>
	</div><!--/page-header h1-->
	<?php endif; ?>
	<?php if($params->get('show_category_title', 1)) : ?>
	<div class="page-header">
		<h2>
			<?php echo HTMLHelper::_('content.prepare', $displayData->get('category')->title, '', $extension.'.category.title'); ?>
		</h2>
	</div><!--/page-header h2-->
	<?php endif; ?>
	<?php if ($params->get('show_cat_tags', 1)) : ?>
		<?php echo HTMLHelper::_('bs3ghsvs.layout', 'joomla.content.tags', $tagsData); ?>
	<?php endif; ?>
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
			<?php echo HTMLHelper::_('content.prepare', $displayData->get('category')->description, '', $extension .'.category'); ?>
		<?php endif; ?>
		<div class="clr"></div>
	</div><!--/category-desc-->
	<?php endif; ?>
	<?php echo $displayData->loadTemplate($displayData->subtemplatename); ?>
	<?php if ($displayData->get('children') && $displayData->maxLevel != 0) : ?>
	<div class="cat-children">
		<?php if ($params->get('show_category_heading_title_text', 1) == 1) : ?>
		<h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
		<?php endif; ?>
		<?php echo $displayData->loadTemplate('children'); ?>
	</div>
	<?php endif; ?>
</div>