<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$params = $displayData['params'];
$legacy = $displayData['legacy'];

?>
<?php if ($params->get('show_icons')) : ?>
	<?php if ($legacy) : ?>
		<?php echo HTMLHelper::_('image', 'system/printButton.png', Text::_('JGLOBAL_PRINT'), null, true); ?>
	<?php else : ?>
		<span class="icon-print" aria-hidden="true"></span>
		<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_PRINT_POPUP'); ?>
	<?php endif; ?>
<?php else : ?>
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_PRINT_POPUP'); ?>
<?php endif; ?>
