<?php
defined('JPATH_BASE') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

/**
 * $displayData is item->id
*/

JLoader::register(
	'Bs3ghsvsArticle',
	JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php'
);

JLoader::register(
	'Bs3ghsvsItem',
	JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ItemHelper.php'
);

$displayData = Bs3ghsvsArticle::getExtensionData($displayData);

if (false === $displayData)
{
	return '';
}

$displayData = new Registry($displayData);
?>
<div class="block-download" aria-labelledby="EXTENSION_INFO">
<h3 id="EXTENSION_INFO"><?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_INFO'); ?></h3>

<p class="breakall" aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_NAME'); ?>">
	<?php echo Text::_($displayData->get('name')); ?>
</p>

<p aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_DESCRIPTION'); ?>">
	<?php echo nl2br($displayData->get('description')); ?>
</p>

<?php
if (($out = trim($displayData->get('inspiredby'))))
{ 
	if (strpos($out, ' ') === false && Bs3ghsvsItem::hasScheme($out))
	{
		$out = '<a href="' . $out . '">' . $out . '</a>';
	}
?>
<p>
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_INSPIREDBY'); ?>:
	<?php echo $out; ?>
</p>
<?php
} ?>

<p aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_URL_DESC'); ?>">
	<a href="<?php echo $displayData->get('url'); ?>" class="btn spoilerButton"
		title="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_URL_DESC')?>">
		<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_URL'); ?>
		<span class="glyphicon glyphicon-download-alt" aria-hidden="true"> </span>
	</a>
</p>

<?php
if ($displayData->get('updateserver'))
{ ?>
<p aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_UPDATESERVER'); ?>" >
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_UPDATESERVER_'
		. $displayData->get('updateserver')); ?>:
</p>
<?php
} ?>

<?php
if ($displayData->get('languages'))
{
	$flags = Bs3ghsvsArticle::buildFlagImages($displayData->get('languages'));	
?>
<p>
	<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_LANGUAGES'); ?>:
	<?php echo implode(' ', $flags); ?>:
</p>
<?php
} ?>

<?php
if (($out = trim($displayData->get('project'))))
{ ?>
<p aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_PROJECT'); ?>">
	<a href="<?php echo $out; ?>" class="btn spoilerButton">
		<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_PROJECT'); ?>
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"> </span>
	</a>
</p>
<?php
} ?>

<?php
if (($out = trim($displayData->get('comment'))))
{ ?>
<p class="block" aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_COMMENT'); ?>">
	<?php echo nl2br($out); ?>
</p>
<?php
} ?>

<?php
if (($out = trim($displayData->get('history'))))
{ ?>
<p>
	<a href="<?php echo $out; ?>" class="btn spoilerButton">
		<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_EXTENSION_HISTORY'); ?>
		<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"> </span>
	</a>
</p>
<?php
} ?>

<?php echo Text::_('GHSVS_MODULES_SCRIPT_HINT'); ?>
</div><!--/block-download-->
