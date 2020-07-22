/**
main-uncompressed.js
Diese uncompressed-Datei dient der Bearbeitung. Verwende Plugin System - JsCssMinifyGhsvs, um daraus minifiziertes JS zu erzeugen, das dann in Plugin bs3ghsvs oder anderswo geladen wird.
So sparst alles umschreiben in .min.

//2017-06-28
Erweiterte Version für hypnoseteam.de

*/
;jQuery(document).ready(function($){
	//open the lateral panel
	$('.cd-btn').on('click', function(event)
	{
		event.preventDefault();
		
		// Im Idealfall etwas in der Art ".div4irgendwas"
		var showthis = $(this).attr("data-showthis");
		if (typeof showthis != "undefined" && showthis != "" && $(".cd-panel-content " + showthis).length)
		{
			$(".cd-panel-content .showthis").hide();
			$(".cd-panel-content " + showthis).show();
		}

		$('.cd-panel').addClass('is-visible');
		$('.cd-panel-container').fadeIn("slow");
// GHSVS 2016-04-16
// 2017-08: Bugfix, weil ID neuerdings mit angehängter Modul-ID.
  jQuery("input.search-query").focus();
	});
	//clode the lateral panel
	$('.cd-panel').on('click', function(event){
		if( $(event.target).is('.cd-panel') || $(event.target).is('.cd-panel-close') ) {
			event.preventDefault();
			$('.cd-panel').removeClass('is-visible');
			$('.cd-panel-container').fadeOut("slow");
		}
	});
});