<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$params = $displayData['params'];
$item = $displayData['item'];

if ($params->get('readmore_inline_ghsvs', false))
{
	$containerTag = 'span';
	$aClass = 'btnMasked';
}
else
{
 $containerTag = 'p';
	$aClass = 'btn';
}
?>

<<?php echo $containerTag; ?> class="readmore">
	<a class="<?php echo $aClass;?>" href="<?php echo $displayData['link']; ?>" itemprop="url">
		<span class="icon-chevron-right"></span>
		<?php if (!$params->get('access-view')) :
			echo JText::_('COM_CONTENT_REGISTER_TO_READ_MORE');
		elseif ($readmore = $item->alternative_readmore) :
			echo $readmore;
			if ($params->get('show_readmore_title', 0) != 0) :
				echo JHtml::_('string.truncate', ($item->title), $params->get('readmore_limit'));
			endif;
		elseif ($params->get('show_readmore_title', 0) == 0) :
			echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
		else :
			echo JText::_('COM_CONTENT_READ_MORE');
			echo JHtml::_('string.truncate', ($item->title), $params->get('readmore_limit'));
		endif; ?>
	</a>
</<?php echo $containerTag; ?>>
