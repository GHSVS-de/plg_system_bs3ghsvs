<?php
/**
Ghsvs 2015-01-24
Ich will verschachtelte Tags darstellen
2015-08-02: Wenn $displayData (also das $item) eine Kategorie ist, jetzt auch im Categories-View Ermittlung der Kategorie-Schlagworte.
2015-08-23: Neuer Parameter not_linked_ghsvs.
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');

$tagsCatGhsvs = $tags = $microdata = false;

// itemprop="keywords"
$keywords = array();

if (is_array($displayData))
{
	$microdata = !empty($displayData['microdata']);
	$displayData = $displayData['item'];
}

// Verlinkte Schlagworte oder nicht?
$linkTags = $displayData->params->get('show_tags') !== 'not_linked_ghsvs';

/*/* DEBUG. Because print_r(category item) kills memory_limit
foreach($displayData as $key => $value)
{
	file_put_contents(JPATH_SITE . '/blah.txt', $key . ': ' . $value . "\n\n", FILE_APPEND);
}*/

Bs3ghsvsItem::setCatTagsToItem(
	$displayData,
	$typeAlias = 'com_content.category',
	$catKey = $displayData->params->get('itemIsCatGhsvs') ? 'id' : 'catid'
);

$tagsCatGhsvs = $displayData->tagsCatGhsvs->itemTags;

if (!empty($displayData->tags->itemTags))
{
	$tags = $displayData->tags->itemTags;
	if (!isset($tags[0]->text))
	{
		$tags_ = new TagsHelper;
		$tagsCatGhsvs = $tags_->convertPathsToNames($tagsCatGhsvs);
	}
}
?>
<?php if ($tagsCatGhsvs || $tags) :?>
<div class="tags">
<?php if ($tags)
{ ?>
<div aria-label="<?php echo Text::_('GHSVS_TAGS_ITEM'); ?>">
	<?php foreach ($tags as $i => $tag) : ?>
		<?php if (in_array($tag->access, Access::getAuthorisedViewLevels(Factory::getUser()->get('id')))) :
			// Fix 2016-01-17 bei deaktiv. Plugin articlesubtitleghsvs
			$tagtxt = (!empty($tag->text) ? $tag->text : $tag->title);
			$keywords[] = $tagtxt;
			?>
<?php
if ($linkTags)
{
	$link_class = 'label label-tag';
?>
	<a href="<?php echo Route::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)) ?>" class="<?php echo $link_class; ?>">
		<?php echo $tagtxt; ?>
	</a>
<?php
}
else
{
	$link_class = 'label label-default';
?>
	<span class="<?php echo $link_class; ?>"><?php echo $tagtxt; ?></span>
<?php
} ?>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php
	if ($microdata && count($keywords))
	{
		echo '<span itemprop="keywords" class="hidden">' . implode(',', $keywords) . '</span>';
	}
?>
</div><!--/aria-label="<?php echo Text::_('GHSVS_TAGS_ITEM'); ?>"-->
<?php
} // end if ($tags) ?>
	
<?php if ($tagsCatGhsvs)
{ ?>
<div aria-label="<?php echo Text::_('GHSVS_TAGS_CATEGORY'); ?>">
<?php foreach ($tagsCatGhsvs as $i => $tag) :

$collect = array();
$spanClass = 'label label-categorytag categorytag';

if ($linkTags)
{
	$collect[1] = '<a href="'
	. Route::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias))
	. '" class="' . $spanClass . '" title="Kategorien-Schlagwort">';
	$collect[3] = '</a>';
	
	$spanClass = '';
}

$spanClass .= ' tag-' . $tag->tag_id . ' tag-list' . $i;

$collect[0] = '<span class="' . trim($spanClass) . '">';
$collect[2] = htmlspecialchars($tag->text ? $tag->text : $tag->title, ENT_COMPAT, 'utf-8');
$collect[] = '</span>';
ksort($collect);
?>
<?php
if (in_array($tag->access, Access::getAuthorisedViewLevels(Factory::getUser()->get('id'))))
{
	echo implode('', $collect);
} ?>
		<?php endforeach; ?>
</div><!--/aria-label="<?php echo Text::_('GHSVS_TAGS_CATEGORY'); ?>"-->
<?php
} // end if ($tagsCatGhsvs) ?>
</div><!--/tags-->
<?php endif; 
?>
