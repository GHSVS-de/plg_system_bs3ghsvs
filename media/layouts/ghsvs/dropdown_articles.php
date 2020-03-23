<?php
/*
GHSVS Bootstrap 3
Baut auf Modul mod_articles_category auf.
BEACHTE ALSO ZUSAMMENHANG MIT MODULPOSITION buttonGruppeGhsvs: Sowie Alternatives Layout dropdown.php
*/
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

JHtml::_('bs3ghsvs.addsprungmarke', '.dropdown-articles ul.dropdown-menu');

$options = new Registry($displayData);

$items = (array) $options->get('items');
if (!$items) return '';

$views = (array) $options->get('views');
if ($views && ! in_array(JFactory::getApplication()->input->get('view'), $views))
{
	return '';
}

$module = new Registry($options->get('module', new stdClass));

$class = array();
$class[] = 'dropdown-articles makeBackdrop';
if (!empty($displayData['bootstrapsize']))
{
	$class[] = 'span' . $displayData['bootstrapsize'];
}
if ($class = implode(' ', $class))
{
	$class = ' class ="' . $class . '"';
}

$cats = array_flip(array_flip(Joomla\Utilities\ArrayHelper::getColumn($items, 'category_title')));
$cats = '<span class="text-mini text-kursiv">' . JText::_(count($cats) > 1 ? 'JCATEGORIES' :  'JCATEGORY') . ': ' . implode(', ', $cats) . '</span>';

$dropdownHeader = '<li class="dropdown-header">Beitrag öffnen ' . $cats . '</li>';
$close = '<li class="dropdown-header"><span class="close glyphicon glyphicon-remove"></span></li>';
$close2 = '<li><span class="close glyphicon glyphicon-remove"></span>&nbsp;</li>';
$cnt = 0;

$buttonTitle = JText::_($module->get('title', 'JGLOBAL_ARTICLES'));
?>
<?php
 // Nicht $module->id falls Modul mehrfach angezeigt wird.
	// GHSVS 2018-06 Use uniqueid()
 #$time = 'articles-dropdown-' . str_replace(array('.', ' ', ','), '', microtime());
	$time = 'articles-dropdown-' . str_replace('.', '', uniqid('', true));
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
foreach ($items as $item)
{
	$cnt++;
	$link = $item->link;
	$liclass = $item->active ? ' class="active"' : '';
	
 // Vorsicht mit $item->title direkt. $items wird referenziert übergeben, also
	// auch die Blogitem-Überschrift geändert!
 $title = str_replace(array('"', "'", '-', '«', '»'), ' ', $item->title);
?>
   <li<?php echo $liclass; ?>><a href="<?php echo $item->link; ?>"><?php echo $title;?></a></li>
<?php
 if (!($cnt % 10))
	{
		echo $close2;
	}
}; ?>
  </ul>
</div>
</div>
