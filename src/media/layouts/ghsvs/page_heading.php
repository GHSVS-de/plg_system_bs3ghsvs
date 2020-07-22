<?php
/**
page_heading.php
2015-12-22
H1-Seitenüberschrift.
Sowohl für Kategorien als auch Einzelbeiträge.
2017-08 Um Module per bs3ghsvs.rendermodules unterhalb des page_headers setzen zu können.
*/
?>
<?php
defined('JPATH_BASE') or die;

$params  = $displayData['params'];

$page_subheading = trim($params->get('page_subheading')) ? '<span class="articlesubtitle">'.$params->get('page_subheading').'</span>' : '';

// 2017-08-11
// Möglichkeit einen alternativen page_handing zu übergeben.
// Falls keine Seitenüberschrift im Menü aktiviert.
$page_heading = $params->get('show_page_heading') ? $params->get('page_heading') : '';
if (!$page_heading && !empty($displayData['ifNoPage_heading']))
{
	$page_heading = $displayData['ifNoPage_heading'];
}

if (
 $page_heading
	|| $page_subheading
) :
 $position = !empty($displayData['bs3ghsvs.rendermodules-position']) ? 
	 $displayData['bs3ghsvs.rendermodules-position'] :'';
 
	$class = $position ? ' rendermodules-position ' . $position :'';
?>
<div class="page-header<?php echo $params->get('pageheader_suffix_ghsvs', ''); ?><?php echo $class ?>">
	<h1><?php echo $page_heading ?><?php echo $page_subheading; ?></h1>
 
	<?php
 if ($position)
 {
  echo JHtml::_('bs3ghsvs.rendermodules', $position);
 }
 ?>
 
</div><!--/page-header h1-->
<?php endif; ?>