<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$list = $displayData['items'];

$jinput = Factory::getApplication()->input;
$view = $jinput->get('view');

$views = array('category');

// Wenn empty($list) kommen wir hier gar nicht an. Muss also nicht prüfen.
if (empty($list) || !in_array($view, $views))
{
	return '';
}

$uri = Uri::getInstance();

JLoader::register('Bs3ghsvsArticle', JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php');
$id = Bs3ghsvsArticle::buildUniqueIdFromJinput('scrollToArticleModal');

HTMLHelper::_('bs3ghsvs.smoothscroll', array(
	'scrollParent' => '#' . $id,
	'isAModal' => true
));
?>
<div>
	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#<?php echo $id; ?>">
		 <?php echo Text::_('PLG_SYSTEM_BS3GHSVS_SCROLL_TO'); ?>
		 <span class="caret-down" aria-hidden="true"></span>
	</button>
<?php # Keine fade class, wenn Smoothscroll im Spiel!! ?>
	<div id="<?php echo $id; ?>"
		class="modal"
		tabindex="-1"
		role="dialog"
		aria-labelledby="<?php echo $id; ?>Title"
		aria-hidden="true"
	>
	
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<p class="modal-title h3" id="<?php echo $id; ?>Title">
						<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_SCROLL_TO_ARTICLE'); ?>
					</p>
					<button type="button" class="close" data-dismiss="modal"
								aria-label="<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_CLOSE'); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div><!--/modal-header-->
				<div class="modal-body container-fluid">
					<div class="row">
						<div class="col-12">
							<ul class="list-group text-left">
							<?php
							foreach ($list as $item)
							{
								$uri->setFragment('blogitem-anker-' . $item->id);
								$link = $uri->toString(array('path', 'query', 'fragment'));
								$liclass = 'list-group-item';
								?>
								<li class="<?php echo $liclass; ?>">
									<a href="<?php echo $link;?>"><?php echo $item->title;?></a>
								</li>
							<?php
							} ?>
							</ul>
						</div>
					</div>
				</div><!--/modal-body-->
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_CLOSE'); ?>
					</button>
				</div><!--/modal-footer-->
			</div><!--/modal-content-->
		</div><!--/modal-dialog-->
	</div><!--/#<?php echo $id; ?>-->

</div>



<?php
return;
if (isset($displayData['options']))
{
	$options = new Registry($displayData['options']);
}
else
{
	$options = new Registry();
}
$class = array();
if (!empty($displayData['smoothscroll']))
{
	
	$class[] = 'SMOOTHSCROLL';
}

$class[] = 'sprungmarken makeBackdrop';


$dropdownHeader = '<li class="dropdown-header">Hüpfen auf dieser Seite</li>';
$close = '<li class="dropdown-header"><span class="close glyphicon glyphicon-remove"></span></li>';
$cnt = 0;
?>
<?php if (!empty($items))
{
	$time = 'blogitem-ankers-dropdown-' . str_replace('.', '', uniqid('', true));
?>
<div<?php echo $class;?>>
 <div class="dropdown">
  <button class="btn btn-primary" type="button" id="<?php echo $time;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   <?php echo Text::_('PLG_SYSTEM_BS3GHSVS_SCROLL_TO'); ?>
   <span class="caret-down" aria-hidden="true"></span>
  </button>
  <ul class="dropdown-menu controlMaxWidth " aria-labelledby="<?php echo $time;?>">
		 <?php echo $dropdownHeader; ?>
			<?php echo $close; ?>
<?php
foreach ($items as $item){ $cnt++;
 // Vorsicht mit $item->title. $items wird referenziert übergeben, also
	// auch die Blogitem-Überschrift geändert!
 $title = str_replace(array('"', "'", '-', '«', '»'), ' ', $item->title);
?>
   <li><a href="#blogitem-anker-<?php echo $item->id;?>"><?php echo $title;?></a></li>
<?php
 if (!($cnt % 10))
	{
		#echo $close;
	}
}; ?>
<?php #echo $close; ?>
  </ul>
</div>
</div>
<?php }; ?>