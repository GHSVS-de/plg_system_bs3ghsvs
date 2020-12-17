<?php
/*

Modal messages.

Include it via 

defined('JPATH_BASE') or die;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;

// Order main layouts path in template as first. Overrule com_x paths.
echo LayoutHelper::render('joomla.system.message', $displayData, JPATH_THEMES . '/' . Factory::getApplication()->getTemplate() . '/html/layouts', true);

return;

in component message.php overrides of the template. E.g. 
layouts/com_contact/system/message.php

*/
defined('JPATH_BASE') or die;
use Joomla\CMS\Language\Text;

$msgList = $displayData['msgList'];
?>
<style>
div.modalxxx {
 background-color: white;
 border-radius: 6px;
 left: 50%;
 margin-left: -40%;
 position: fixed;
 top: 5%;
 width: 80%;
 z-index: 1050;
}
div.modalxxx .alert{
	margin-bottom:0;
	border:1px solid red;
}
div.modalxxx .close{
	opacity:1;
	font-size:28px;
	color:red;
	background-color:#fff;
}
</style>
<div id="system-message-container" class="modalxxx">
	<?php if (is_array($msgList) && !empty($msgList)) : ?>
		<div id="system-message">
			<?php foreach ($msgList as $type => $msgs) : ?>
				<div class="alert alert-<?php echo $type; ?>">
					<?php // This requires JS so we should add it trough JS. Progressive enhancement and stuff. ?>
					<a class="close" data-dismiss="alert" style="cursor:pointer;">×</a>

					<?php if (!empty($msgs)) : ?>
						<h4 class="alert-heading"><?php echo Text::_($type); ?></h4>
						<div>
							<?php foreach ($msgs as $msg) : ?>
								<div class="alert-message"><?php echo $msg; ?></div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>