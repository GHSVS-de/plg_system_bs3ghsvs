<?php
/*
GHSVS Bootstrap 3
Baut auf Modul mod_articles_categories auf.
BEACHTE ALSO ZUSAMMENHANG MIT MODULPOSITION buttonGruppeGhsvs: Sowie Alternatives Layout dropdown.php
*/
defined('_JEXEC') or die;
$items = $displayData['items'];
$module = $displayData['module'];

$class = array();
$class[] = 'dropdown-categories makeBackdrop';
if (!empty($displayData['bootstrapsize']))
{
	$class[] = 'span' . $displayData['bootstrapsize'];
}
if ($class = implode(' ', $class))
{
	$class = ' class ="'.$class.'"';
}
$dropdownHeader = '<li class="dropdown-header">Kategorie wechseln</li>';
$close = '<li class="dropdown-header"><span class="close glyphicon glyphicon-remove"></span></li>';
$cnt = 0;

if (!($buttonTitle = trim($module->title)))
{
	$buttonTitle = JText::_('JCATEGORY');
}
else
{
	$buttonTitle = JText::_($buttonTitle);
}
?>
<?php if (!empty($items))
{
	// GHSVS 2018-06 Use uniqueid()
 #$time = 'categories-dropdown-' . str_replace(array('.', ' ', ','), '', microtime());
	$time = 'categories-dropdown-' . str_replace('.', '', uniqid('', true));
?>
<div<?php echo $class;?>>
 <div class="dropdown">
  <button class="btn dropdown-toggle" type="button" id="<?php echo $time;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   <?php echo $buttonTitle; ?>
   <span class="caret"></span>
  </button>
  <ul class="dropdown-menu controlMaxWidth" aria-labelledby="<?php echo $time;?>">
		 <?php echo $dropdownHeader; ?>
			<?php echo $close; ?>
<?php
foreach ($items as $item){
	$liclass = '';
	$cnt++;
	$link = JRoute::_(ContentHelperRoute::getCategoryRoute($item->id));
 if ($_SERVER['REQUEST_URI'] == $link)
	{
		$liclass = ' class="active"';
	}
	
 // Vorsicht mit $item->title. $items wird referenziert übergeben, also
	// auch die Blogitem-Überschrift geändert!
 $title = str_replace(array('"', "'", '-', '«', '»'), ' ', $item->title);
?>
   <li<?php echo $liclass; ?>><a href="<?php echo $link; ?>"><?php echo $title;?></a></li>
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