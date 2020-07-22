<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('bootstrap.framework');

$canEdit = $displayData['params']->get('access-edit');

$position = (!isset($displayData['position']) ? '' : ' '.$displayData['position']);
?>
<?php if (empty($displayData['print']))
{ ?>
	<?php if ($canEdit) : ?>
	<div class="icons<?php echo $position; ?>">
		<p class="edit-icon">
			<?php echo HTMLHelper::_('iconghsvs.edit', $displayData['item'], $displayData['params']); ?>
		</p>
	</div><!--/icons-->
	<?php endif; ?>
<?php
}
elseif($position != ' below')
{ ?>
	<div class="printButton pull-right btn" style="margin-left:10px;">
		<?php echo JHtml::_('icon.print_screen', $displayData['item'], $displayData['params']); ?>
	</div>
<?php } ?>