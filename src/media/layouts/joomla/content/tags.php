<?php
/**
Ghsvs 2015-01-24
Ich will verschachtelte Tags darstellen

 */

defined('JPATH_BASE') or die;

JLoader::register('TagsHelperRoute', JPATH_BASE . '/components/com_tags/helpers/route.php');
?>
<?php if (!empty($displayData)) :

if (!isset($displayData[0]->text))
{
 $tags = new JHelperTags;
 $displayData = $tags->convertPathsToNames($displayData);
}
?>
	<div class="tags itemtags">
		<?php foreach ($displayData as $i => $tag) : ?>
			<?php if (in_array($tag->access, JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id')))) : ?>
				<?php $tagParams = new JRegistry($tag->params); ?>
				<?php $link_class = $tagParams->get('tag_link_class', 'label label-info'); ?>
				<span class="tag-<?php echo $tag->tag_id; ?> tag-list<?php echo $i ?>">
					<a href="<?php echo JRoute::_(TagsHelperRoute::getTagRoute($tag->tag_id . '-' . $tag->alias)) ?>" class="<?php echo $link_class; ?>">
						<?php echo $this->escape($tag->text ? $tag->text : $tag->title); ?>
					</a>
				</span>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
