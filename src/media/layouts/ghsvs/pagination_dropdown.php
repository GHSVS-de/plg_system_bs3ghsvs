<?php
defined('JPATH_BASE') or die;
use Joomla\Registry\Registry;

/*
$displayData = $this->pagination aus View
*/
/*$list = array(
	'prefix'       => $displayData['pagination']->prefix,
	'limit'        => $displayData['pagination']->limit,
	'limitstart'   => $displayData['pagination']->limitstart,
	'total'        => $displayData['pagination']->total,
	'limitfield'   => $displayData['pagination']->getLimitBox(),
	'pagescounter' => $displayData['pagination']->getPagesCounter(),
	'pages'        => $displayData['pagination']->getPaginationPages()
);*/
$pages = $displayData['pagination']->getPaginationPages();
$pagescounter = $displayData['pagination']->getPagesCounter();
if (!isset($displayData['options']))
{
 $displayData['options'] = array();
}
//$pages = $displayData['list']['pages'];
//$pagescounter = $displayData['list']['pagescounter'];
if (isset($displayData['options']))
{
 $options = new Registry($displayData['options']);
}
else
{
	$options = new Registry();
}
/*
$align
center noch nicht eingerichtet.
für left right bringt BS3 bereits mit.
Will man zentrieren braucht das .dropdown
style="margin: 0 auto; display: table;" (das sieht sauberer zentriert aus)
oder etwas in dieser Art
style="margin: 0 auto; width:121px;
Die width könnte man so ermitteln:
<script type="text/javascript">
(function($){
	$(document).ready(function(){
		var buttonWidth = $("#pagination-dropdown-<?php echo $time;?>").outerWidth();
		alert(buttonWidth);
	});
})(jQuery);
</script>
*/
$align = $options->get('align', 'right');
?>
<?php if (!empty($pages['pages']))
{
	$total = count($pages['pages']);
	// GHSVS 2018-06 Use uniqid('', true)
 #$time = '' . str_replace(array('.', ' ', ','), '', microtime());
	$time = '' . str_replace('.', '', uniqid('', true));
?>
<div class="seitenpagination">
 <div class="dropdown text-<?php echo $align;?>" id="dropdown-<?php echo $time;?>">
  <button class="btn dropdown-toggle" type="button" id="pagination-dropdown-<?php echo $time;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   <?php echo $pagescounter;?>
   <span class="caret"></span>
  </button>
  <ul class="dropdown-menu dropdown-menu-<?php echo $align;?>" aria-labelledby="pagination-dropdown-<?php echo $time;?>">
<?php

foreach ($pages['pages'] as $page){
	$counter = JText::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $page['data']->text, $total);
	$class = '';
	if ($page['data']->active)
	{
		$class = ' class="active"';
		$link = '<a>'.$counter.'</a>';
	}
	else
	{
		$class='';
		$link = '<a href="'.$page['data']->link.'">'.$counter.'</a>';
	}?>
   <li<?php echo $class;?>><?php echo $link; ?></li>
<?php }; ?>
  </ul>
</div><!--/dropdown-->
</div>
<?php }; ?>
<script type="text/javascript">
	// 2015-08-04: Wegen Konflikt mit Venobox, muss das in load(). Das pagination wird sonst mehrfach beim Schließen der Venobox erzeugt.
	jQuery(window).on("load", function(){
		// Unter BS3 explizit DIV
		jQuery.fn.paginationClone("div.paginationToClone", "#PAGINATION-CLONE");
		jQuery(".isCloned.paginationToClone .dropdown").removeClass("dropdown").addClass("dropup");
	});
</script>