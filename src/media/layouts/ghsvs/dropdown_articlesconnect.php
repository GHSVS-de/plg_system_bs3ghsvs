<?php
defined('_JEXEC') or die;
use Joomla\Registry\Registry;
if (!JPluginHelper::isEnabled('system', 'articleconnectghsvs'))
{
	#JFactory::getApplication()->enqueueMessage('Layout - dropdown_articleconnect by GHSVS: Plugin plg_system_articleconnectghsvs not enabled or not installed', 'error');
	return '';
}

JLoader::register('PlgArticleConnectHelper', JPATH_SITE . '/plugins/system/articleconnectghsvs/helper.php');

if (isset($displayData['options']))
{
 $params = new Registry($displayData['options']);
}
else
{
	$params = new Registry();
}

$params->set('article_ordering', 'a.title');
$params->set('article_ordering_direction', 'ASC');
$params->set('show_front', 'show');
$params->set('count', 999);
$params->set('mode', 'bothallsuperheavy');

$items = PlgArticleConnectHelper::getList($params);

if (empty($items))
{
 return '';
}

JHtml::_('bs3ghsvs.addsprungmarke', '.articleconnect ul.dropdown-menu');

$class = array();
$class[] = 'articleconnect makeBackdrop';
if (!empty($displayData['bootstrapsize']))
{
	$class[] = 'col-sm-' . $displayData['bootstrapsize'];
}
if ($class = implode(' ', $class))
{
	$class = ' class ="'.$class.'"';
}

$dropdownHeader = '<li class="dropdown-header">Verknüpfte Artikel</li>';
$close = '<li class="dropdown-header"><span class="close glyphicon glyphicon-remove"></span></li>';
$cnt = 0;
?>
<?php if (!empty($items))
{
	// GHSVS 2018-06 Use uniqueid()
 #$time = 'articleconnect-dropdown-' . str_replace(array('.', ' ', ','), '', microtime());
	$time = 'articleconnect-dropdown-' . str_replace('.', '', uniqid('', true));
?>
<div<?php echo $class;?>>
 <div class="dropdown">
  <button class="btn dropdown-toggle" type="button" id="<?php echo $time;?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
   Verknüpfte Artikel
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
   <li><a href="<?php echo $item->link; ?>"><?php echo $title;?></a></li>
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


<?php
};