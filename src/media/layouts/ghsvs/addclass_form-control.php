<?php
defined('JPATH_BASE') or die;
?>
<?php
$formSelector = $displayData['formSelector'];
$additionalScript = empty($displayData['additionalScript']) ? '' : $displayData['additionalScript'];
?>
<script>
;(function($){
$(document).ready(function()
{		
	$("<?php echo $formSelector?> input, <?php echo $formSelector?> select").not("[type=\"hidden\"]").not("[type=\"checkbox\"]").addClass("form-control");
	<?php echo $additionalScript;?>
}); //document ready
})(jQuery);
</script>