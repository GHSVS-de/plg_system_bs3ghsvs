<?php
/**
GHSVS 2015-01-06
page-header
 */

defined('_JEXEC') or die;
$pageheader_suffix_ghsvs = $displayData->params->get('pageheader_suffix_ghsvs', '');

?>
<?php if ($displayData->params->get('show_page_heading')) : ?>
<div class="page-header<?php echo $pageheader_suffix_ghsvs;?>">
 <h1>
  <?php echo $displayData->escape($displayData->params->get('page_heading')); ?>
 </h1>
</div><!--/page-header-->
<?php endif; ?>
<?php if ($displayData->params->get('show_base_description')) : ?>
	<?php if($displayData->params->get('categories_description')) : ?>
		<div class="category-desc base-desc">
		<?php echo JHtml::_('content.prepare', $displayData->params->get('categories_description'), '',  $displayData->get('extension') . '.categories'); ?>
		</div>
	<?php else : ?>
		<?php  if ($displayData->parent->description) : ?>
			<div class="category-desc base-desc">
				<?php echo JHtml::_('content.prepare', $displayData->parent->description, '', $displayData->parent->extension . '.categories'); ?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
<?php endif; ?>
