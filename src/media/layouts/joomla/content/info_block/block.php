<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
?>
<dl class="article-info">
	<?php if ($displayData['params']->get('show_author') && !empty($displayData['item']->author ))
	{ ?>
		<?php echo HTMLHelper::_('bs3ghsvs.layout', 'joomla.content.info_block.author', $displayData); ?>
	<?php
	} ?>

<?php
$show_parent_category = $displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_slug);
$show_category = $displayData['params']->get('show_category');
$showDate = $displayData['params']->get('show_publish_date') || $displayData['params']->get('show_create_date') || $displayData['params']->get('show_modified_date');
?>

<?php
	if (
	 !isset($displayData['item']->combinedCatsGhsvs) ||
		(!$displayData['params']->get('ghsvs_combine_categories', 0) &&
		!$displayData['item']->params->get('ghsvs_combine_categories', 0))
	){
	?>
		<?php if ($show_parent_category || $show_category)
		{ ?>
			<dt class="sr-only"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_CATEGORIES'); ?></dt>
		<?php
		} ?>
	
	
		<?php if ($show_parent_category) : ?>
			<?php echo HTMLHelper::_('bs3ghsvs.layout', 'joomla.content.info_block.parent_category', $displayData); ?>
		<?php endif; ?>

		<?php if ($show_category) : ?>
			<?php echo HTMLHelper::_('bs3ghsvs.layout', 'joomla.content.info_block.category', $displayData); ?>
		<?php endif; ?>
	
	<?php
	}
	else
	{
	?>
		<dt class="sr-only"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_CATEGORIES'); ?></dt>
		<?php
		$displayData['item']->params->set('ghsvs_combine_categories', 0);
		echo HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.combine_categories', $displayData);
	}
?>

		<?php if ($showDate)
		{ ?>
			<dt class="sr-only"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_DATE_INFO'); ?></dt>
		<?php
		} ?>

	<?php if ($displayData['params']->get('show_publish_date')) : ?>
	<dd class="published">
		<?php echo Text::sprintf('COM_CONTENT_PUBLISHED_DATE_ON', HTMLHelper::_('date', $displayData['item']->publish_up, Text::_('DATE_FORMAT_LC3'))); ?>
	</dd>
	<?php endif; ?>
	
	<?php if ($displayData['params']->get('show_create_date')) : ?>
	<dd class="create">
		<?php echo Text::sprintf('COM_CONTENT_CREATED_DATE_ON', HTMLHelper::_('date', $displayData['item']->created, Text::_('DATE_FORMAT_LC3'))); ?>
	</dd>
	<?php endif; ?>
	
	<?php if ($displayData['params']->get('show_modify_date')) : ?>
	<dd class="modified">
		<?php echo Text::sprintf('COM_CONTENT_LAST_UPDATED', HTMLHelper::_('date', $displayData['item']->modified, Text::_('DATE_FORMAT_LC3'))); ?>
	</dd>
	<?php endif; ?>
</dl>
