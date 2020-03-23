<?php
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;

$items = $displayData['items'];

if (isset($displayData['options']))
{
	$options = new Registry($displayData['options']);
}
else
{
	$options = new Registry();
}
$class = array();

$class[] = 'sprungmarken makeBackdrop';
if (!empty($displayData['bootstrapsize']))
{
	$class[] = 'col-sm-' . $displayData['bootstrapsize'];
}
if ($class = implode(' ', $class))
{
	$class = ' class ="'.$class.'"';
}

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
  <button class="btn dropdown-toggle" type="button" id="<?php echo $time;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   Springe zu
   <span class="caret"></span>
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