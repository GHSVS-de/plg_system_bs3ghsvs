;(function($)
{
	$.fn.bloglisttoggle = function(currentClass)
	{
		var buttonClasses = ["SHOWLIST", "SHOWBLOG"];

		if (
			typeof currentClass === "undefined" || currentClass === null ||
			jQuery.inArray(currentClass, buttonClasses) < 0
		){
			alert("$.fn.bloglisttoggle: Parameter currentClass missing or wrong! Exit!");
			return;
		}

		var toggler = "#BLOGLISTTOGGLER";
		
		// ID des umgebenden Container für den Button. "händisch" einzusetzen in PHP/HTML.
		var div4Toggler = $(toggler + "DIV");
		
		if (!div4Toggler.length)
		{
			return;
		}
		
		var KEY = "bloglisttoggler";
		var PLUGIN = "SessionBs3Ghsvs";
		var FORMAT = "raw";
		var OPTION = "com_ajax";
		var CMD = "add";

		// Fallback-Starttext für Button
		var buttonText = 
			'<span class="icon-chevron-right" aria-hidden="true"> </span> '
			+ Joomla.JText._("SINFOTPL_" + KEY + "_" + currentClass);

		div4Toggler.html(
			'<button class="btn btn-catcolor ' + currentClass
			+ '" id="' + toggler.replace("#", "") + '">' + buttonText + '</button>'
		);

		div4Toggler.removeClass("hidden");

		$(toggler).click(function(ef)
		{
			var $this = $(this);

			ef.preventDefault();

			if ($(this).hasClass(buttonClasses[0]))
			{
				currentClass = buttonClasses[0];
				var newClass = buttonClasses[1];
			}
			else if ($(this).hasClass(buttonClasses[1]))
			{
				currentClass = buttonClasses[1];
				var newClass = buttonClasses[0];
			}
			else
			{
				div4Toggler.addClass("hidden");
				return false;
			}

			var systemPaths = Joomla.getOptions('system.paths');
			var Uri = (systemPaths ? systemPaths.root + '/index.php' : window.location.pathname) + '?'
				+ "option=" + OPTION + "&group=system" + "&plugin=" + PLUGIN + "&format=" + FORMAT
				+ "&cmd=" + CMD + "&key=" + KEY + "&data=" + newClass;

			Joomla.request({
				url: Uri,
				// data: newClass,
				onBefore: function(xhr)
				{
					$this.html(Joomla.JText._("PLG_SYSTEM_BS3GHSVS_LOADING"));
					//console.log("onBefore");
				},
				onSuccess: function(response, xhr)
				{
					window.location = window.location.pathname;
					//console.log("onSuccess");
				},
				onError: function(xhr)
				{
					alert(Joomla.JText._("SINFOTPL_ERROR_AJAX"));
					div4Toggler.addClass("hidden");
					//console.log("onError");
				}
			});
		}); // end click
	} //$.fn.bloglisttoggle
})(jQuery);