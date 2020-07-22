<?php
/**
Ghsvs 2017-08-13
1-Seite-vor-zurück-Navigation.
Anlässlich Virtuemart für hypnoseteam.de.
Kann aber auch für andere verwendet werden.
*/
?>
<?php
defined('JPATH_BASE') or die;
use Joomla\Registry\Registry;

$options = new Registry($displayData);

$prev_link = $options->get('prev_link', false);
$next_link = $options->get('next_link', false);

if (!$prev_link && !$next_link)
{
 return;
}
?>
<ul class="pager pagenav">
<?php if ($prev_link)
{
 $prev_title = $options->get('prev_title', '');
?>
 <li class="previous">
  <a class="hasTooltip" href="<?php echo $prev_link ?>" rel="prev" title="<?php echo $prev_title ?>">
   <span class="icon-chevron-left"></span>
   <span class="pageprevnextLabel">
    <?php echo $prev_title ?>
   </span>
  </a>
 </li>
<?php
}?>
<?php if ($next_link)
{
 $next_title = $options->get('next_title', '');
?>
 <li class="next">
  <a class="hasTooltip" href="<?php echo $next_link ?>" rel="next" title="<?php echo $next_title ?>">
   <span class="pageprevnextLabel">
    <?php echo $next_title ?>
   </span>
   <span class="icon-chevron-right"></span>
  </a>
 </li>
<?php
}?>
</ul><!--/pager pagenav-->