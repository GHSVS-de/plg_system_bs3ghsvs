<?php
defined('JPATH_BASE') or die;
?>

<p class="p4password_hint_button"><a class="btn btn-primary btn-xs" data-toggle="modal" data-target=".bs-example-modal-sm">
 <?php echo JText::_('GHSVS_PASSWORD_REQUIREMENTS_BTN'); ?>
</a></p>

<div class="modal bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">
         <?php echo JText::_('GHSVS_PASSWORD_REQUIREMENTS_HEADLINE'); ?>
        </h4>
      </div>
      <div class="modal-body">
<?php
    $userParams = JComponentHelper::getParams('com_users');
    echo JText::sprintf('GHSVS_PASSWORD_REQUIREMENTS',
			 $userParams->get('minimum_length'),
				$userParams->get('minimum_integers'),
				$userParams->get('minimum_symbols'),
				$userParams->get('minimum_uppercase')
			); ?>
      </div>
    </div>
  </div>
</div>
<?php
if (!empty($displayData['style']))
{
	echo '<style>' . $displayData['style'] . '</style>';
}
?>