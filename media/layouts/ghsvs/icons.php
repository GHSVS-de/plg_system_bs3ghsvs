<?php


defined('JPATH_BASE') or die;

JHtml::_('bootstrap.framework');

$canEdit = $displayData['params']->get('access-edit');

$position = (!isset($displayData['position']) ? '' : ' '.$displayData['position']);
?>
	<?php if (empty($displayData['print'])) : ?>

		<?php if ($canEdit || $displayData['params']->get('show_print_icon') || $displayData['params']->get('show_email_icon')) : ?>
<div class="icons<?php echo $position; ?>">
			<div class="btn-group">
				<button class="btn dropdown-toggle" type="button" data-toggle="dropdown"> <span class="icon-print"></span><span class="caret"></span> </button>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
					<?php if ($displayData['params']->get('show_print_icon')) : ?>
						<li class="print-icon"> <?php echo JHtml::_('icon.print_popup', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
					<?php if ($displayData['params']->get('show_email_icon')) : ?>
						<li class="email-icon"> <?php echo JHtml::_('icon.email', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<li class="edit-icon"> <?php echo JHtml::_('icon.edit', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
				</ul>
			</div>
</div><!--/icons-->
		<?php endif; ?>

	<?php elseif($position != ' below') : ?>
		<div class="printButton pull-right btn" style="margin-left:10px;">
			<?php echo JHtml::_('icon.print_screen', $displayData['item'], $displayData['params']); ?>
		</div>
	<?php endif; ?>

