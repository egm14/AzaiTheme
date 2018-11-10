 
/*=========== Fancy spinner - Windows ==========*/
$(window).on('ready', function () {
	      setTimeout(function () {
		    //$(".loader-page").css({visibility:"hidden",opacity:"0"})
		    $(".loader-page").hide();
		  }, 300);
		  console.log("Spinner fuera luego del documento estar ready");
		});

$(document).ready(function(){
	/*==================== SECTION PRODUCT - AJAX BLOCK CART =======================*/

	//for product page 'add' button...
     $('#add_to_cart').on("click", function(ee){
     	ee.preventDefault();
     	$('#header .cart_block')
            .stop(true, true)
            .slideDown(450)
            .prev()
           .addClass('active');
    	setTimeout(function(){
          $('#header .cart_block')
            .stop(true, true)
            .slideUp(450)
            .prev()
            .removeClass('active');
    	},3000);
     });

     var loader = $(".loader-page");
    

     /*==================== SPINNER ELEMENT LINK A TO MOBILE  =======================*/

if(window.innerWidth < 768) {

     //Hammer library for mobile
		var aElem = document.getElementsByTagName('a');
		//console.log("esto es aElement: " + aElem);

	//if(aElemn < 0){
		//var mc2 = new Hammer(aElem[0]);
     	//console.log("Esto es mc2: " + mc2);

     	//aElem.on("tap", function(event){
     	Hammer(aElem[0]).on("tap", function(event){
     		console.log("Se ha hecho: "+ ev.type);
     	/*-------- Depurando variables --------------*/
     	var aL = event.currentTarget;
     	var alName = event.handleObj.selector;
     	var aParentClass = aL.parentNode.className;
     	var aLink = aL.href;
     	var aLinksplit = aLink.split(":")[0];
     	var aBaseUri = aL.baseURI;
     	var BaseUriD = aBaseUri + "#";
     	
     	console.log("Selector " + alName);	

     	console.log("Elemento href: " + aLink);
     	console.log("data-image: " + aL.dataset.zoomId)
     	console.log("aBaseUri: " + aBaseUri);
     	console.log("location.href: " + location.href);
     	console.log("Split element: " + aLinksplit);
     	console.log(aParentClass);
     	console.log(event);

  		/*--------- REcorriendo URL SPLIT - comprobando query ----------*/
		if(aLink.indexOf('?') != -1){
			aLinksplit = aLink.split("?")[1].split("=")[0];
			console.log("Delete link:" + aLinksplit);
			console.log("Simbolo de: ?");
		}

		/*--------Recorriendo URL SPLIT - comprobar si contiene pagination --------*/
		var aLinksplit2 = aLink.split("/");
		var abSplit = aLinksplit2.length - 1;
		var aLinksplit2b = aLinksplit2[abSplit].split("-")[0];
		//console.log(aLinksplit2);
		console.log(aLinksplit2b);

		/*-------- Condiciones para aplicar spinner loader -------------*/
		if((aL.localName = "a") && (aLinksplit == "http" || aLinksplit == "https")){
		  	if((aLinksplit !="delete" || aLinksplit != "add") && (aL.dataset.zoomId != "MagicZoomPlusImageMainImage") && (aParentClass != "shopping_cart") && ("remove_link") && (aLink != aBaseUri) && (aLink !== null) && (aLink != "javascript:;") && (aLink != BaseUriD) && (aL.classList[0] !== "add_to_compare") && (aL.offsetParent.localName !== "h4")){
	      			setTimeout(function () {openLoader() }, 300);
	      			console.log("Spinner a mostrar");
	      			//elimando spinner si hacen scroll
				     	 /* $(document).on('scroll', function(){
				     	  	closeLoader();});*/

			      	if((aLinksplit2b == "page")){
			      		setTimeout(function () {closeLoader() }, 300);
			      		console.log("spinner loader out 2");
		     		}else{}
	     	}else{
		     	console.log("No se puede mostrar spinner")}
		  }
		  console.log("Spinner in/out");
		});
	//}
}
 

        // $('.shopping_cart').find('a').on("click tap", setTimeout(closeLoader(),300));
     	//$('#button_order_cart').on("click tap", setTimeout(openLoader(),900));

     	 function openLoader(e){
			//loader.css({visibility:"visibe",opacity:"100"});
			loader.show();
			console.log("Estoy abriendo loader");
    	 }
	     function closeLoader(e){
			//loader.css({visibility:"hidden",opacity:"0"});
			loader.hide();
			console.log("Estoy cerrando loader");
	     }
     /*==================== SIZE CHART  =======================*/

     /* SIZE - CHART- FANCYBOX*/
	  $('.sizes-chart').on("click", function(e){
	  	e.preventDefault();
	  	console.log("Size-chart: me hicieron click");

	  	if (window.innerWidth > 768) {
	  		var wid = 500;
	  		var hei = 750;
	  	}else if(window.innerWidth < 768){
	  		var wid = 500;
	  		var hei = 500;
	  	}else if(window.innerWidth > 400){
	  		var wid = 320;
	  		var hei = 500;
	  	}

	  $('.sizes-chart').fancybox({
	  	 'type': 'iframe',
		 'width': wid,
		 'max-height': hei,
		 'fitToView' : true,
		 'scrolling' : 'auto',
		 //'autoScale': true,
		 'autoSize' : true
	  	});
	  });

     /*sSIZE-CHART-CONTENT*/

     	$('div.container-woman-size-chart').find('ul.tabs li').click(function(e){
		      e.preventDefault();

		    var tab_id = $(this).find("a").attr("href");;

		    $('ul.tabs li').removeClass('current');
		    $('.tab-content').removeClass('current');

		    $(this).addClass('current');
		    $(tab_id).addClass('current');
		  });
	   
  /*========= END SIZE CHART ==============*/

  /*================== SECTION PRODUCT VIEW - PINCH PAN =================*/
	// General Mobile Events
	/*if (window.innerWidth < 768) {
		console.log("pantalla menor de 768px");

		//Fancybox mobile "Pinch Pan"
		var elemet1 = ($('#image-block'))[0]; 
		var mc1 = new Hammer(elemet1);

		mc1.on("tap", function(ev){
			console.log("Se ha hecho: "+ ev.type);

		  if($('.fancybox-opened').length < 0){
		  	console.log("No se ha creado el div.fancybox-opened");
	      }else{
	      	setTimeout(function(){
	      		console.log("Añadiendo div after");
	      		var zoomdiv = $('.fancybox-skin');
	     		//zoomdiv.css("background-color","blue");
	     	    zoomdiv.after("<div class=" +"pinch-zoom"+">Pinch To Zoom</div>");
	      	
		      	//Desaparecer Pinch-zoom al tocar
				var element2 = ($('.fancybox-inner'))[0];
				var mc2 = new Hammer.Manager(element2);

				var pinch = new Hammer.Pinch();
    			var pan = new Hammer.Pan();

    			pinch.recognizeWith(pan);
    			mc2.add([pinch, pan]);
				

				mc2.on("pinchstart panstart", function (ev) {
				   	console.log("Clase deshabilitada por: " + ev.type);
						$('.pinch-zoom').addClass('disable');
			    });
	      	},100);
	      }
		 console.log("img.fancybox-inner está aquí");
		});
	}*/

	/*==================== LOGIN-CONTENT DISPLAY WHEN PRESS MENU =======================*/
	// LOGIN FORM
	var mobilmenu = $('.top_menu').find('.menu-title');
	var menulogin = $('#header-login');
	
	mobilmenu.on("touchstart", function(e){
		$('.top_menu').find('.iconos-menu-mobile').toggleClass('active-imb');
		console.log('click creado para abrir nuevo menu');
	});

	$('.icon-menu-login').on("touchstart", function(e){
		var menuloginul = menulogin.find('ul.header-login-content');
		menulogin.find('ul.header-login-content').toggleClass("active-b");
		menulogin.find('.tm_header_user_info').toggleClass("active");
		/*---------- SCROLL TO BOTTOM WHEN OPEN DE LOGIN MENU-----------*/
		/*$('ul.top-level-menu').animate({ scrollTop: $(document).height() }, 'slow');*/
		setTimeout($('ul.top-level-menu').animate({ scrollTop: $(document).height() }, 'slow'), 600);
      	console.log('slow down icon menu');
      	
      });
    /*==================== MENU LOGIN SESIÓN - MOBILE =======================*/
	
/*==================== SECTION CHECKOUT - FORM SLIDEDOWN =======================*/
	// LOGIN FORM
    $(document).on('click', '#openLoginFormBlock', function(e) {
      e.preventDefault();
      $('#new_account_form').slideDown('slow');
    });

    /* Esta sección se quedará oculta ---*/

 /*==================== SECTION REGISTER - FORM =======================*/
 	//FUNCTION UTILIZED ON CUSTOMER_ACCOUNT_FORM.TPL DELUXES
 	// $('input:radio[name="id_customertype"]').change(function(){
  //               console.log($(this).val());

  //               if($(this).val() === 'wholesale'){
  //                  	$('div#js_wholesale_form').show();
  //                   $('p#js_wholesale_msg').show();
  //                   $('input#uploadBtn').attr("required", true);
  //                   console.log("Seleccion: wholesale");
  //               }else{
  //                   $('div#js_wholesale_form').hide();
  //                   $('p#js_wholesale_msg').hide();
  //                   $('input#uploadBtn').attr("required", false);
  //                   console.log("Selección:Retail");
  //               }
  //           });
});