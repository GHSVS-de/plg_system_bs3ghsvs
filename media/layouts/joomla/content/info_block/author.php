<?php
defined('JPATH_BASE') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

?>
<dt class="sr-only"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_AUTHOR'); ?></dt>
<dd class="createdby">
	<?php $author = ($displayData['item']->created_by_alias ?: $displayData['item']->author); ?>
	<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
		<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', HTMLHelper::_('link', $displayData['item']->contact_link, $author, array())); ?>
	<?php else : ?>
		<?php echo Text::sprintf('COM_CONTENT_WRITTEN_BY', $author); ?>
	<?php endif; ?>
</dd>
