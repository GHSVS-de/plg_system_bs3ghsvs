jQuery(document).ready(function($){
	/* :empty ist unzuverlässig */
	var $blogItem=$(PrependHeaderSelector);
	$blogItem.each(function(){
		var $itemHeader=$(".page-header h2", $(this));
		if($itemHeader.length){
			var Title=jQuery.trim($itemHeader.text());//Nimt allerdings ggf. auch Kind-Tags mit, deshalb ??
			if(Title!=""){
				$TextArea=$(".DIV4BLOGITEMTEXT", $(this))
				if($TextArea.children(":first")[0].nodeName.toLowerCase() !== "p"){
					//alert($TextArea.children(":first")[0].nodeName.toLowerCase());
					$TextArea.prepend("<p></p>");
				}
				Title=jQuery.trim($itemHeader.html());
				//var $FirstP=$(".DIV4BLOGITEMTEXT p:first", $(this));
				var $FirstP=$("p:first", $TextArea);
				//alert($FirstP[0].nodeName);
				if($FirstP.length){
					if(!jQuery.trim($FirstP.text()).match(/^[,;.?!:]/i)){
						Title=Title+" ";
					}
					$FirstP.prepend('<span class="PREPENDEDBLOGITEMTITLE">'+Title+"</span>");
					$itemHeader.remove();
				}
			}
		}
	}); //$blogItem.each
});
