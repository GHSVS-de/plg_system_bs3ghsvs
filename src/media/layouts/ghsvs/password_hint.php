<?php
defined('JPATH_BASE') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
?>

<p><button type="button" class="btn btn-primary" data-toggle="modal" data-target="#PasswordHintModal">
  <?php echo Text::_('GHSVS_PASSWORD_REQUIREMENTS_BTN'); ?>
	<span class="caret-up" aria-hidden="true"></span>
</button>
</p>

<div
	class="modal"
	id="PasswordHintModal"
	tabindex="-1"
	role="dialog"
	aria-labelledby="PasswordHintModalTitle"
>
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
				<p class="modal-title h3" id="PasswordHintModalTitle">
					<?php echo JText::_('GHSVS_PASSWORD_REQUIREMENTS_HEADLINE'); ?>
				</p>
        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_CLOSE'); ?>">
          {svg{bi/x-circle-fill}class="text-danger"}
        </button>
      </div>
      <div class="modal-body container-fluid">
				<div class="row">
					<div class="col-12">
					<?php
					$userParams = ComponentHelper::getParams('com_users');
					echo Text::sprintf('GHSVS_PASSWORD_REQUIREMENTS',
						$userParams->get('minimum_length'),
						$userParams->get('minimum_integers'),
						$userParams->get('minimum_symbols'),
						$userParams->get('minimum_uppercase'),
						$userParams->get('minimum_lowercase')
					);
					?>
					</div>
				</div>
      </div><!--/modal-body-->
    </div><!--/modal-content-->
  </div><!--/modal-dialog-->
</div>
<?php
if (!empty($displayData['style']))
{
	echo '<style>' . $displayData['style'] . '</style>';
}
