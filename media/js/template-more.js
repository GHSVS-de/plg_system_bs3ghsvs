/**
Ausgelagertes aus diversen Templates.
Achte darauf, dass das dann hier auch verschwindet,
wenn in andere Dateien kopiert!
Siehe Wo-sind-welche-functions-JQuery.xlsx
*/


(function($){
 /**
 Im Bootstrap-Carousel gibt es Slides (.item), die display:none haben.
 Dadurch lässt sich für diese height nicht für Höhenangleichung bestimmen.
 Setzt man sie zwischenzeitlich auf visibility:hidden, geht das aber.
 myCarousel: z.B. ".blogcarousel162", wobei 162 Joomla-Modul-Id
 */
 $.fn.bootstrapCarouselResize = function(myCarousel, isCycling){
  // falls man zuvor zum ersten Slide gehen möchte, muss das vor pause!
  //$(myCarousel).carousel(0);
  $(myCarousel).carousel("pause");
  $hiddenRows = $(myCarousel + " .carousel-inner > .item:hidden");
  if ($hiddenRows.length)
  {
   $hiddenRows.css("visibility", "hidden").css("display", "block");
   $.fn.autoheightghsvs(
    myCarousel + " .row-fluid",
    ".BOOTSTRAPCAROUSELRESIZE",
    ".CAROUSELLIMITER",
    50
   );
   $(myCarousel + " .carousel-inner > .item").css("visibility", "").css("display", "");
   if (isCycling) $(myCarousel).carousel("cycle");
  }
 } //$.fn.bootstrapCarouselResize










 /*
 Benötigt vorbereitenden Code in blogghsvs.php.
 Dort wird Session initialisiert. Eine aktuelle Klasse bestimmt und gesetzt.
 var blogListToggler = "#BLOGLISTTOGGLER";
 */
 $.fn.sessionListOrBlog = function(blogListToggler, currentClass){
  
  if (
   typeof currentClass === "undefined" ||
   currentClass === null || jQuery.inArray(currentClass, ["listeghsvs", "blogghsvs"]) < 0
  )
  {
   return false;
  }
  
  if (typeof blogListToggler === "undefined" || blogListToggler === null)
  {
   var blogListToggler = "#BLOGLISTTOGGLER";
  }
  
  // Starttext für Button
  var TEXT = Joomla.JText._('SINFOTPL_STARTTEXT');

  // key für Session
  var KEY = blogListToggler.replace("#", "");
  
  // Umgebender Container für den Button
  var div4blogListToggler = blogListToggler + "DIV";

  $(div4blogListToggler).html('<a class="btn ' +currentClass+ '" id="' +KEY+ '" href="#">' + TEXT + '</a>');
  
  // Der eben gesetzte Button:
  var $this = $(blogListToggler);
 
  if ($this.length)
  {
   // Jetzt erst den DIV mit Button drin anzeigen
   $(div4blogListToggler).removeClass("hide");
   
   // com_ajax-Defaults für folgende Abfragen
   var NODE = "jshopghsvs";
   var FORMAT = "raw";
   var OPTION = "com_ajax";
   var PLUGIN = "session";

   // Buttontexte und ...
   var LISTTEXT = Joomla.JText._('SINFOTPL_LISTTEXT');
   var BLOGTEXT = Joomla.JText._('SINFOTPL_BLOGTEXT');
   var AJAXLOADING = "<img src='"+JURIROOT+"/images/ajax-loader.gif'/>";
   var FEHLER = "Entschuldigung! Ein Fehler in der Abfrage ist aufgetreten.";
   
   // Sessionabfrage und Init habe ich bereits in PHP erledigt (currentClass)
   var response = currentClass;
   
   // Dann bin ich in Liste und will bei nächstem Klick zurück in Blog:
   if(response == "blogghsvs"){
    $this.html(BLOGTEXT);
   }else if(response == "listeghsvs"){
    $this.html(LISTTEXT);
   }else{
    $(div4blogListToggler).addClass("hide");
    return false;
   }

   $this.click(function(ef){
    ef.preventDefault();
    if ($this.hasClass("blogghsvs"))
    {
     currentClass = "blogghsvs";
     var newClass = "listeghsvs";
     //var btnText = LISTTEXT;
    }
    else if ($this.hasClass("listeghsvs"))
    {
     currentClass = "listeghsvs";
     var newClass = "blogghsvs";
     //var btnText = BLOGTEXT;
    }
    else
    {
     $(div4blogListToggler).addClass("hide");
     return false;
    }
    
    //Session schreiben
    var request = {
     "option": OPTION,
     "plugin": PLUGIN,
     "cmd": "add",
     "key": KEY, //Key in der Session
     "data": newClass, //Der Wert zum Key show|hide 
     "node": NODE, //Bereich in der Session
     "format": FORMAT
    };
    $.ajax({
     type: "POST",
     data: request,
     beforeSend: function(){
      $this.html(AJAXLOADING);
     },
     success: function(response){
      /*
      $this.removeClass("listeghsvs blogghsvs").addClass(newClass);
      $this.html(btnText);
      */
      location.reload();
     },
     error: function(response){
      alert(FEHLER);
      $(div4blogListToggler).addClass("hide");
     }
    });// end $.ajax
   });
  }; // end if $this.length
 }; // end $.fn.sessionListOrBlog






 // Im Unterschied zu autoheightghsvs, werden die höhenanzupassenden Container
 // pro Reihe angepasst, also nicht alle auf der Seite mit ident. Höhe
 // Neu: substract: Ein weiterer Container (z.B. readmore), der von errechneter Höhe abgezogen wird
 // Neu: substract2: Da mit row-Höhe gerechnet wird, weitere Korrektur, z.B. für .item mit Padding und Margin
 // Siehe z.B. adveoneu
 $.fn.autoheightghsvsPerRow = function(
  outer,row,inner,substract,substract2,limitBreite
 ){
  /*var outer = ".jshopListCategory";
  var row = ".row-fluid";
  var inner = ".item";*/
  if(typeof substract === "undefined" || substract === null){
   substract = false;
  }
  if(typeof substract2 === "undefined" || substract2 === null){
   substract2 = false;
  }
  var substractHeight = 0;
  $rows = $(row, outer);
  if($rows.length && $(outer + " " + row + " " + inner).length){
   
   $("html").addClass("autoheightPerRow");
   $.each($rows, function(indexRow, ROW){
    var $this = $(this);
    $items = $(inner, ROW);
    var countItems = $items.length;
    if(countItems > 1){
     $items.height("auto");
     $this.height("auto");
     
     // alert(limitBreite);
     if(typeof limitBreite === "undefined" || limitBreite === null){
      // Keine Ahnung, warum hier kein var davorstehen darf. Sonst undefined ba vorhergehendem Alert.
      limitBreite = (100 / countItems);
     }
     
     //Prozentuale Breite des regulierenden Selectors
     limiterSelectorWidth = ($items.width() * 100) / $rows.width();
     //So lange diese Breite unterhalb gewähltem Wert (default:50%), gleiche aller Boxen Höhen an:
     if(limiterSelectorWidth <= limitBreite){
      var rowHeight = $this.height(); //inklusive paddings
      if (substract && $(substract, ROW).length){
       substractHeight = $(substract, ROW).max(function(){
        return $(this).outerHeight(true);
       }); 
      }
      if (substract2 && $(substract2, ROW).length){
       substractHeight = substractHeight + $(substract2, ROW).max(function(){
        return $(this).outerHeight(true);
       }) - $(substract2, ROW).max(function(){
        return $(this).height();
       });
      }
      $items.outerHeight(
       rowHeight - substractHeight
      );
      $("html").addClass("autoheightPerRow");
     }else{
      $("html").removeClass("autoheightPerRow");
     }
    }
   });
  }
 } //$.fn.autoheightghsvsPerRow



	$.fn.smoothScrolling = function(myThis, event, duration)
	{
		
		return;
		
		if (isNaN(duration))
		{
			duration = 300;
		}
		
    if (
      location.pathname.replace(/^\//, '') == myThis.pathname.replace(/^\//, '') 
      && 
      location.hostname == myThis.hostname
    ) {
      // Figure out element to scroll to
      var target = $(myThis.hash);
      target = target.length ? target : $('[name=' + myThis.hash.slice(1) + ']');
      // Does a scroll target exist?
      if (target.length) {
        // Only prevent default if animation is actually gonna happen
        event.preventDefault();
        $('html, body').animate({
          scrollTop: target.offset().top
        }, duration, function() {
          // Callback after animation
          // Must change focus!
          var $target = $(target);
          $target.focus();
          if ($target.is(":focus")) { // Checking if the target was focused
            return false;
          } else {
            $target.attr('tabindex','-1'); // Adding tabindex for elements not focusable
            $target.focus(); // Set focus again
          };
        });
      }
    }
	};


 //Aufruf(!) dieser Funktion muss in load wegen Safari-Bug:
 $.fn.autoheightghsvs=function(
  mainSelector, //.MAINSELECTOR Boxen umgebender .row-fluid
  innerSelector, //.INNERSELECTOR Höhenanzupassende Boxen selbst. Können auch innerhalb nested .row-fluid sein. 
  limiterSelector, //.LIMITERSELECTOR Die Box, deren prozentuale Minimal-Breite (limitWidth) über Höhenanpassung entscheidet.
  limitWidth, //50 [%] Bei Werten darunter findet keine Anpassung mehr statt.
  ignoreLimitWidth // Falls auch bei 100% Breite oder nicht floaten Höhe angepasst werden soll.
 ){
  if(
   typeof mainSelector === "undefined"
   || mainSelector === null
   || !jQuery.trim(mainSelector)
  ){
   alert("mainSelector missing in $.fn.autoheightghsvs");
   return false;
  }
  if(
   typeof innerSelector === "undefined"
   || innerSelector === null
   || !jQuery.trim(innerSelector)
  ){
   alert("innerSelector missing in $.fn.autoheightghsvs");
   return false;
  }
  //Falls keine Box gefüllt, wird height IMMER auf 0 gesetzt.
  if($(mainSelector+" " + innerSelector).children().length < 1){
   $(mainSelector+" " + innerSelector).addClass("emptyBox").height("0");
   return false;
  }
  if(
   typeof limiterSelector === "undefined"
   || limiterSelector===null
   || !jQuery.trim(limiterSelector)
  ){
   var limiterSelector = ".DUMMYLIMITERSELECTOR";
   //alert("limiterSelector missing in $.fn.autoheightghsvs");
   //return false;
  }
  if(typeof ignoreLimitWidth === "undefined" || limitWidth === null){
   var ignoreLimitWidth = true;
  }
  if(typeof limitWidth === "undefined" || limitWidth === null){
   var limitWidth = 50;
  }
  //Korrektur, falls sowieso nicht gefloatet wird.
  //limitWidth kann glaub ich ersetzt werden(?)
  if($(limiterSelector).css("float") === "none"){
   var limitWidth = 1;
  }
  //Prozentuale Breite des regulierenden Selectors
  limiterSelectorWidth = ($(mainSelector+" "+limiterSelector).width()*100) / $(mainSelector).width();
  //So lange diese Breite unterhalb gewähltem Wert (default:50%), gleiche aller Boxen Höhen an:
  if((limiterSelectorWidth < limitWidth) || ignoreLimitWidth){
   // alert(limitWidth - limiterSelectorWidth);
   $(mainSelector + " " + innerSelector).height("auto");
   var maxHeight = $(mainSelector + " " + innerSelector).max(function(){
    return $(this).outerHeight(true);
    //return $(this).height();
   });
   $(mainSelector + " " + innerSelector).removeClass("emptyBox").height(maxHeight);
  
  //Falls diese Breite oberhalb gewähltem Wert (default:50%), gleiche Höhen nicht an, außer bei leeren, die dann 0-Height:
  }else{
   $(mainSelector + " " + innerSelector).each(function(){
    if($(this).children().length == 0){
     $(this).addClass("emptyBox").height("0");
    }else{
     //alert("removeClass");
     $(this).height("auto");
    }
   });
  }
 }; //jQuery.fn.autoheightghsvs




 $.fn.updateHint = function(messagepage){
		var container = "#" + messagepage;
		var url = JURIROOT2 + "/" + messagepage + ".html";
		var hidebutton = ".btn." + messagepage;
		
  //Session abfragen
  var request = {
   "option": "com_ajax",
   "plugin": "session",
   "cmd": "get",
   "key": "updateHint", //Key in der Session
   "node": "jshopghsvs", //Bereich in der Session
   "format": "raw"
  };
  $.ajax({
   type: "POST",
   data: request,
   success: function(response){
    if(response != "hide"){
					
     $(container).load(url);
    }
   }
  });
  // Hide-Button
  $(document).on("click", hidebutton, function(e){
   //Session schreiben
   var request = {
    "option": "com_ajax",
    "plugin": "session",
    "cmd": "add",
    "key": "updateHint", //Key in der Session
    "data": "hide", //Der Wert zum Key show|hide 
    "node": "jshopghsvs", //Bereich in der Session
    "format": "raw"
   };
   $.ajax({
    type: "POST",
    data: request,
    success: function(response){
     $(container).remove();
     
     /* DEBUG: braucht oben format:debug
     
     if(response.data){
      var result = "";
      $.each(response.data, function (index, value) {
       result = result + ' ' + value;
      });
      alert("aaaaaaaaa "+result);
     } else {
      alert("bbbbbbbbb "+response);
     }
     */
    },
    error: function(response){
    }
   });
  });
	} //$.fn.updateHint






 $(document).ready(function(){
 
  //START - ToggleButtons GHSVS.
  /*
  Init-Teil:
  - Erwartet einen oder mehrere
   <a class="btn hide" href="#TOGGLER">Beschreibung ausblenden</a>, die am
   href="#TOGGLER erkannt werden.
   (Die class=hide ist im Button nur, um per JS einzublenden.)
  - Macht dann Session-Abfrage, ob ein Show-Hide-Status hinterlegt ist
  - Sucht dann nach DIV id="TOGGLERCatDescr" und setzt gefundenen Status
  .hide oder .show, falls nicht schon gesetzt.
  */
  // Nachtrag 2015-01: Ist doch komplett bescheuert/statisch, der Aufbau:
  var $ToggleButtons = $('a[href^="#TOGGLER"]');
  $.each($ToggleButtons,
   function(){
    //Buttons, A-Tags
    var targetId = $(this).attr("href"); //z.B. #TOGGLERCatDescr

    //replace, weil ich von id auf class umgestellt habe.
    var $target=$(targetId.replace("#", "."));
    
    if($target.length && jQuery.trim($target.text()) != ""){ //Gibt es einen oder mehrere DIV mit class .TOGGLERCatDescr? Und nicht alle leer?
     var $this = $(this);
     //Session abfragen
     var request = {
      "option": "com_ajax",
      "plugin": "session",
      "cmd": "get",
      "key": targetId.replace("#", ""), //Key in der Session
      "node": "jshopghsvs", //Bereich in der Session
      "format": "raw"
     };
     $.ajax({
      type: "POST",
      data: request,
      beforeSend: function(){
       $this.html("<img src='"+JURIROOT+"/images/ajax-loader.gif'/>").removeClass("hide");
      },
      success: function(response){
       // DEBUG:: response="hide";
       if(response && !$target.hasClass(response)){
        $target.removeClass("hide show").addClass(response);
        if(response=="hide"){
         var btnText = "Beschreibung einblenden";
        }else{
         var btnText = "Nur Produkte zeigen";
        }
       }else{
        var btnText = "Nur Produkte zeigen";
        $target.removeClass("hide").addClass("show");
       }
       $this.html(btnText);
      },
      error: function(response){
       //alert("Error while loading Cat-Description.");
      }
     });
    }
   }
  );
  
  $(document).on("click", 'a[href^="#TOGGLER"]', function(e){
   e.preventDefault();
   var targetId = $(this).attr("href"); //z.B. #TOGGLERCatDescr
   //replace, weil ich von id auf class umgestellt habe.
   $target=$(targetId.replace("#", ".")); 
   if($target.length && jQuery.trim($target.text()) != ""){ //Gibt es einen oder mehrere DIV mit class .TOGGLERCatDescr? Und nicht alle leer?
    if($target.hasClass("show")){
     var newClass = "hide";
     var btnText = "Beschreibung einblenden";
    }else{
     var newClass = "show";
     var btnText = "Nur Produkte zeigen";
    }
    $target.removeClass("hide show").addClass(newClass);
    $(this).html(btnText);
    //Session schreiben
    var request = {
     "option": "com_ajax",
     "plugin": "session",
     "cmd": "add",
     "key": targetId.replace("#", ""), //Key in der Session
     "data": newClass, //Der Wert zum Key show|hide 
     "node": "jshopghsvs", //Bereich in der Session
     "format": "raw"
    };
    $.ajax({
     type: "POST",
     data: request,
     success: function(response){
      /* DEBUG: braucht oben format:debug
      alert("Success");
      if(response.data){
       var result = "";
       $.each(response.data, function (index, value) {
        result = result + ' ' + value;
       });
       alert("aaaaaaaaa "+result);
      } else {
       alert("bbbbbbbbb "+response);
      }
      */
     },
     error: function(response){
     }
    });
   }
  });
  //ENDE - ToggleButtons GHSVS.
 }); //document ready


})(jQuery);