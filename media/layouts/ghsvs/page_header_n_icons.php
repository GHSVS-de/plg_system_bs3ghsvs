<?php
/**
page_header_n_icons.php
GHSVS 2014-12-23

Fasst Print-Email-Icons und page-header zusammen, so, dass Icons rechts vom Titel floaten können,
anstatt unterhalb Titel.
Sollte sowohl in Blog, Featured und Article-View funktionieren.
*/
?>
<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;

$params = $displayData['item']->params;
$print = $displayData['print'];

$titletag = $params->get('article_titletag', 'h2');
$microdata = !empty($displayData['microdata']);

$displayData = $displayData['item'];

// com_tags-Views
$typeAlias = isset($displayData->type_alias) ? $displayData->type_alias : false;

$articlesubtitle = '';

if ($params->get('show_title') && $params->get('show_articlesubtitle', 1))
{
	JLoader::register('Bs3ghsvsArticle', JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php');
	$various = new Registry(Bs3ghsvsArticle::getVariousData($displayData->id));
	$articlesubtitle = htmlspecialchars($various->get('articlesubtitle'), ENT_COMPAT, 'utf-8');
}

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

if (!class_exists('ContentHelperRoute'))
{
 require_once JPATH_ROOT . '/components/com_content/helpers/route.php';
}

$linkHeadline = ($params->get('link_titles') && $params->get('access-view'));
$maskHClass = ($params->get('mask_pageheaderclass_ghsvs', false) ? 'Masked' : '');

if ($linkHeadline && empty($displayData->linkGhsvs))
{
 switch($typeAlias){
  case 'com_content.article':
  $displayData->linkGhsvs = Route::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid));
  break;
  case 'com_content.category':
  $displayData->linkGhsvs = Route::_(ContentHelperRoute::getCategoryRoute($displayData->slug));
  break;
  default:
  $displayData->linkGhsvs = Route::_(ContentHelperRoute::getArticleRoute($displayData->slug, $displayData->catid));
 }
}
?>
<?php echo HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.icons',
 array(
  'params' => $params,
  'item' => $displayData,
  'print' => $print,
  'position' => 'above'
 ));
?>
<?php if ($params->get('show_title')) : ?>
<div class="page-header<?php echo $maskHClass;?> state<?php echo $displayData->state ?>">
   <?php 
			$title = $this->escape($displayData->title);

   if ($microdata)
   {
    $title = '<span itemprop="name">' . $title . '</span>';
   }
   ?>
    <?php echo '<' . $titletag ?><?php echo ($microdata ? ' itemprop="headline"' : ''); ?>><?php
    if ($linkHeadline) : ?>
      <a<?php echo ($microdata ? ' itemprop="url"' : ''); ?> href="<?php echo $displayData->linkGhsvs; ?>"><?php echo $title; ?></a>
     <?php else :
      echo $title;
     endif;

     if ($articlesubtitle)
		 { ?>
      <span class="articlesubtitle"><?php echo $articlesubtitle; ?></span>
     <?php } ?><?php echo '</' . $titletag . '>' ?>

  </div><!--/page-header<?php echo $maskHClass;?>-->
<?php endif; ?>
<?php echo HTMLHelper::_('bs3ghsvs.layout', 'ghsvs.icons',
 array(
  'params' => $params,
  'item' => $displayData,
  'print' => $print,
  'position' => 'below'
 ));
?>