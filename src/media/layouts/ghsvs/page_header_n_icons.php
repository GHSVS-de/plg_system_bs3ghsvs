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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

$params = $displayData['item']->params;
$print = $displayData['print'];
$titleclass = isset($displayData['class']) ? ' ' . $displayData['class'] : '';
$titletag = $params->get('article_titletag', 'h2');
$displayData = $displayData['item'];

// com_tags-Views
$typeAlias = isset($displayData->type_alias) ? $displayData->type_alias : false;

$articlesubtitle = '';

if ($params->get('show_title'))
{
	JLoader::register('Bs3ghsvsArticle', JPATH_PLUGINS . '/system/bs3ghsvs/Helper/ArticleHelper.php');
	$various = new Registry(Bs3ghsvsArticle::getVariousData($displayData->id));
	
	if ($params->get('show_articlesubtitle') !== 0)
	{
		$articlesubtitle = htmlspecialchars($various->get('articlesubtitle'), ENT_COMPAT, 'utf-8');
	}
	
	$articleStatus = $various->get('articleStatus', 0);
}

\JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

$linkHeadline = ($params->get('link_titles') && $params->get('access-view'));
$maskHClass = ($params->get('mask_pageheaderclass_ghsvs', 0) ? 'Masked' : '');

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

<?php if ($params->get('show_title'))
{ ?>
<div class="page-header<?php echo $maskHClass;?> state<?php echo $displayData->state ?>">
	<?php
	if ($articleStatus && Factory::getApplication()->input->get('view') === 'article')
	{
		if ($articleStatus > 0)
		{
			Factory::getDocument()->setMetadata('robots', 'noindex, follow');
		}
		?>
	<p class="articleStatus articleStatus_<?php echo $articleStatus; ?> bg-warning p-3">
		<?php echo Text::_('PLG_SYSTEM_BS3GHSVS_ARTICLESTATUS_' . $articleStatus); ?>
	</p>
	<?php
	} ?>
		
	<?php 
		$title = $this->escape($displayData->title);
	?>
	<?php echo '<' . $titletag . ' class="' . $titleclass .' ">'; ?><?php
	if ($linkHeadline) : ?>
		<a href="<?php echo $displayData->linkGhsvs; ?>">
		<?php echo $title; ?>
		</a>
	<?php else :
		echo $title;
	endif; ?>
	<?php
		if ($articlesubtitle)
		{ ?>
		<span class="articlesubtitle">(<?php echo $articlesubtitle; ?>)</span>
		<?php } ?><?php echo '</' . $titletag . '>' ?>

	<?php 
	if (
		!empty($displayData->pagination)
		&& $params->get('ghsvs_show_page_header_pagination', 1) !==0
	){ ?>
	<div class="articlePagination above">
	<?php echo $displayData->pagination; ?>
	</div>
	<?php
	} ?>
  </div><!--/page-header<?php echo $maskHClass;?>-->

<?php
}

