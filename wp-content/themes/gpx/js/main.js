$(function(){
	//instancias
	var home = $('.home');
	var slider_home  = $('#slider-home'); 
	var modal_login  = $('#modal-login');
	var modal_filter = $('#modal-filter');
	var modal_filter_resort = $('#modal-filter-resort');
	var modal_alert  = $('#modal-alert');
	var title_acordeon = $('.w-list-availables .title');
	//mask scroll
	//$('body').mCustomScrollbar();
	//mask for selects
	$('select.dgt-select').SumoSelect();
	//mask for form
	$('form.material').materialForm();


	//$('#table2').DataTable();
	$('table')
		.addClass( 'nowrap' )
		.dataTable({
			responsive: true,
			columnDefs: [
				{ 
					targets: [-1, -3], 
					className: 'dt-body-right' 
				}
			]
		});

    $(".mCustomScrollBox").hover(function() {
		$('.mCSB_dragger').addClass('mCSB_dragger_onDrag'); 
	}, function() {
		$('.mCSB_dragger').removeClass('mCSB_dragger_onDrag'); 
	});
	/*Royal Slider*/
    $(slider_home).royalSlider({
        autoHeight: false,
        autoScaleSlider:false,
        navigateByClick: false,
        sliderTouch: true,
        startSlideId: 0,
        controlNavigation: 'bullets',
        keyboardNavEnabled: true,
        imageScaleMode: 'fill',
        minSlideOffset: 0,
        slidesSpacing: 0,
        arrowsNav: true,
        transitionType: 'fade',
    	autoPlay: {
    		// autoplay options go gere
    		enabled: true,
    		//pauseOnHover: true,
    		delay: 8500
    	}
    });
	/* Data preview */
	var availableLocation = [
		"Anywhere",
		"California",
		"Hawaii",
		"Las Vegas, NV",
		"Location Four",
		"Location Five",
		"Location Six",
		"Location Seven"
	];
	var availableautocomplete = [
		"Top Resort 1",
		"Top Resort 2",
		"Top Resort 3",
		"Top Resort 4"
	];
	/* autocomplete */
	$( "#location_autocomplete" ).autocomplete({
		source: availableLocation,
		minLength: 0,
	}).focus(function () {
	    $(this).autocomplete("search");
	});
	$( "#resort_autocomplete" ).autocomplete({
		source: availableautocomplete,
		minLength: 0,
	}).focus(function () {
	    $(this).autocomplete("search");
	});
	/* FECHA FOOTER */
	var fecha = new Date();
	var year =fecha.getFullYear();
	$('#id_year').text(year);
	/* MENU RESPONSIVE */
	//se clona el navlist para poder tener mas flexibilidad y control
	$('.nav-list').clone().appendTo('body').insertAfter('footer').addClass('menu-responsive').removeClass('nav-list');
	$('.menu-mobile').click(function(event){
		event.preventDefault();
		$(this).addClass('active-menu-mobile');
		$('.menu-mobile-close').addClass('active-menu-mobile-close');
		$('.menu-responsive').addClass('active-menu');
		$('.r-overlay').addClass('active-overlay');
		$('.cnt-wrapper').addClass('active-cnt-wrapper');
		$('.footer').addClass('active-footer');
	}); 
	// js submenu 2do nivel
	function cerrar_submenu(){
		$('.menu-responsive .u-submenu').stop(false).slideUp();
	}
	$('.menu-responsive .abre-submenu').click(function(e) {
		e.preventDefault();
  		$('.menu-responsive .abre-submenu').removeClass('active');
  		$(this).addClass('active');
  		cerrar_submenu();
  		$(this).parent().find('.u-submenu').stop(false).slideToggle();
	});
	// fin js submenu 2do nivel
	// funcion  para cerrar menu responsive	
	function cerrar_nav() {
		$('.menu-responsive').removeClass('active-menu');
		$('.r-overlay').removeClass('active-overlay');
		$('.menu-mobile-close').removeClass('active-menu-mobile-close');
		$('.menu-mobile').removeClass('active-menu-mobile');
		$('.cnt-wrapper').removeClass('active-cnt-wrapper');
		$('.footer').removeClass('active-footer');
	};
	//click en boton cerrar y overlay
	$('.w-nav').on('click', '.menu-mobile-close', function(event) {
		event.preventDefault();
		cerrar_nav();
		cerrar_submenu();
	});
	$('.r-overlay').click(function() {
		cerrar_nav();
		cerrar_submenu();
	});
	$(window).scroll(function(){
		var preview = $(this).scrollTop();		
		if (preview > 120) {
			$(".scrolltop").addClass("active");
		}
			else if (preview < 120) {
			$(".scrolltop").removeClass("active");
		}
	});
	$('.scrolltop').click(function(event) {
		$('html, body').animate({scrollTop: 0}, 900);
	});
	function active_modal( $modal ){
		$modal.removeClass('desactive-modal');
		$modal.addClass('active-modal');
	}
	function close_modal( $obj ){
		var $this = $obj;
		var $modal = $this.closest('.dgt-modal');
		$modal.removeClass('active-modal');
		$modal.addClass('desactive-modal');
	}
	$('.close-modal').click(function(event){
		close_modal(  $(this) );
	});
	$('.call-modal-login').click(function(event){
		event.preventDefault();
		active_modal( modal_login );
	});
	$('.call-modal-filter').click(function(event){
		event.preventDefault();
		active_modal( modal_filter );
	});
	$('.call-modal-filter-resort').click(function(event){
		event.preventDefault();
		active_modal( modal_filter_resort );
	});
	// functions for tabs
	$('.head-tab ul li a').click(function(event){
		event.preventDefault();
		$('.tabs a').removeClass('head-active');
		$(this).addClass('head-active');
		var id = $(this).data('id');
		$('.content-tabs .item-tab').removeClass('tab-active');
		$('#'+id).addClass('tab-active');
	});
	//validate progressbar
	setTimeout(function() { 
		function calculate_progressbar_value(){
			var progressbar_select = $('.w-progress-line span.select');
			var progressbar_book = $('.w-progress-line span.book');
			var progressbar_pay = $('.w-progress-line span.pay');
			var progressbar_confirm = $('.w-progress-line span.confirm');
			switch (true) {
	            case (progressbar_select.hasClass('active')):
	                console.log('select');
	                $('.w-progress-line .line .progress').css('width', ' 21%');
	                break;
	            case (progressbar_book.hasClass('active')):
	               console.log('book');
	               $('.w-progress-line .line .progress').css('width', ' 41%');
	                break;
	            case (progressbar_pay.hasClass('active')):
	            	$('.w-progress-line .line .progress').css('width', ' 61%');
	            	console.log('pay');
	                break;
	            case (progressbar_confirm.hasClass('active')):
	            	$('.w-progress-line .line .progress').css('width', ' 100%');
	            	console.log('confirm');
	                break;
	            default:
	                console.log('none');
	        }
		}
		calculate_progressbar_value();
	}, 750);
	$('#next-1').click(function(event){
		event.preventDefault();
		if($('#chk_terms').is(':checked')){
			console.log('is checked');
			$('html, body').animate({scrollTop: 0}, 900);
			var id = $(this).data('id');
			$('.booking').removeClass('booking-active');
			$('#'+id).addClass('booking-active');
		}else{
			alert('Are you acept terms & conditions?');
		} 
	});	
	$('.phone').click(function(event){
		event.preventDefault();
		active_modal( modal_alert );
	});
    $('.w-credit .head-credit input[type="checkbox"]').on('change', function() {
	    $('.w-credit .head-credit input[type="checkbox"]').not(this).prop('checked', false);
	});
	function active_exchange_credit(){
		$('.exchange-result').addClass('active-message');
		$('.exchange-credit hgroup').addClass('desactive-message');
		$('.exchange-credit .exchange-list').addClass('desactive-message');
	}

	var text = $('.item-tab-cnt'),
		btn = $('.content-tabs .seemore span'),
		icon = $('.content-tabs .seemore .icon-arrow-down'),
		h = text.prop('scrollHeight');
	if(h > 110) {
		btn.addClass('less');
		btn.css('display', 'block');
	}
	btn.click(function(event) {
		event.preventDefault();
	  	event.stopPropagation();

		if ( $(this).hasClass('less')) {
			btn.removeClass('less');
			$(this).addClass('more');
			icon.addClass('rotate');
			$(this).find('span').text('See less');

			text.animate({'height': h});
		} else {
			btn.addClass('less');
			btn.removeClass('more');
			icon.removeClass('rotate');
			btn.find('span').text('See more');
			text.animate({'height': '110px'});
		}  
	});	

	var text_b1 = $('#expand_item_1 .cnt-expand'),
		btn_b1 = $('#expand_item_1 .cnt-seemore'),
		icon_b1 = $('#expand_item_1 .seemore .icon-arrow-down'),
		h_b1 = text_b1.prop('scrollHeight');
	if(h_b1 > 110) {
		btn_b1.addClass('less');
		btn_b1.css('display', 'block');
	}
	btn_b1.click(function(event) {
		event.preventDefault();
	  	event.stopPropagation();

		if ( $(this).hasClass('less')) {
			btn_b1.removeClass('less');
			$(this).addClass('more');
			icon_b1.addClass('rotate');
			$(this).find('span').text('Read less');

			text_b1.animate({'height': h_b1});
		} else {
			btn_b1.addClass('less');
			btn_b1.removeClass('more');
			icon_b1.removeClass('rotate');
			btn_b1.find('span').text('Read more');
			text_b1.animate({'height': '110px'});
		}  
	});	

	var text_b2 = $('#expand_item_2 .cnt-expand'),
		btn_b2 = $('#expand_item_2 .cnt-seemore'),
		icon_b2 = $('#expand_item_2 .seemore .icon-arrow-down'),
		h_b2 = text_b2.prop('scrollHeight');
	if(h_b2 > 110) {
		btn_b2.addClass('less');
		btn_b2.css('display', 'block');
	}
	btn_b2.click(function(event) {
		event.preventDefault();
	  	event.stopPropagation();

		if ( $(this).hasClass('less')) {
			btn_b2.removeClass('less');
			$(this).addClass('more');
			icon_b2.addClass('rotate');
			$(this).find('span').text('Read less');

			text_b2.animate({'height': h_b2});
		} else {
			btn_b2.addClass('less');
			btn_b2.removeClass('more');
			icon_b2.removeClass('rotate');
			btn_b2.find('span').text('Read more');
			text_b2.animate({'height': '110px'});
		}  
	});	


	$('.w-status .close').click(function(event){
		event.preventDefault();
		var $this = $(this);
		var $modal = $this.closest('.w-item-view');
		$modal.addClass('remove-modal');
	});

	// click on label to focus input
	$('.material-input label').click(function(event){
		event.preventDefault();
		var $this = $(this);
		var $wrapper = $this.closest('.material-input');
		var $child = $wrapper.find('input');
		$child.focus();
	});


	 if ($('#gallery_resort').size() > 0) {
      	$('#gallery_resort').royalSlider({
        	fullscreen: {
          	enabled: true,
          	nativeFS: true
        	},
	        controlNavigation: 'thumbnails',
	        autoScaleSlider: true,
	        autoScaleSliderWidth: 400,
	        autoScaleSliderHeight: 350,
	        loop: false,
	        imageScaleMode: 'fill',
	        navigateByClick: true,
	        numImagesToPreload: 2,
	        arrowsNav: true,
	        arrowsNavAutoHide: true,
	        arrowsNavHideOnTouch: true,
	        keyboardNavEnabled: true,
	        fadeinLoadedSlide: true,
	        globalCaption: true,
	        globalCaptionInside: false,
	        addActiveClass:true,
	        touch: true,
	        thumbs: {
	        	autoCenter:	true,
				appendSpan: true,
				firstMargin: true,
				paddingBottom: 4,
				paddingTop: 4
	        }	
    	});
	}

	function acordeon( $obj ){
		var $this = $obj;
		var $modal = $this.closest('.w-expand');
	}
		
	if( $('body').hasClass('home') ){
		$('.modal-alert').addClass('active-modal');
	}

	//acordeon resort-profile
	var btn_b3 = $('#expand_1 .title');

	var con1 = false;
	btn_b3.click(function(event) {
		event.preventDefault();
		$(this).addClass('activar');
		if(con1 != true){
			$(this).addClass('activar');
			con1 = true;
			console.log("sea abrió");
		}
		else{
			btn_b3.removeClass('activar');
			con1 = false;
			console.log("sea cerró");
		}
		$(this).parent().find('.cnt-list').stop(false).slideToggle();
		console.log(con1);
	});	



	var btn_b4 = $('#expand_2 .title');

	var con2 = false;
	btn_b4.click(function(event) {
		event.preventDefault();
		$(this).addClass('activar');
		if(con2 != true){
			$(this).addClass('activar');
			con2 = true;
			console.log("sea abrió");
		}
		else{
			btn_b4.removeClass('activar');
			con2 = false;
			console.log("sea cerró");
		}
		$(this).parent().find('.cnt-list').stop(false).slideToggle();
		console.log(con2);
	});

	var btn_b5 = $('#expand_3 .title');

	var con3 = false;
	btn_b5.click(function(event) {
		event.preventDefault();
		$(this).addClass('activar');
		if(con3 != true){
			$(this).addClass('activar');
			con3 = true;
			console.log("sea abrió");
		}
		else{
			btn_b5.removeClass('activar');
			con3 = false;
			console.log("sea cerró");
		}
		$(this).parent().find('.cnt-list').stop(false).slideToggle();
		console.log(con3);
		
	});
	//preview show more item from home
	$('.seemoreitems , .seemoreitems span').click(function(event){
		event.preventDefault();
		$('.w-featured .w-list').clone().insertAfter('.w-list-items').addClass('new-list');
    });
	
	
	if( $('body').hasClass('active-session') ){
		$('.header .top-nav .access .call-modal-login').text('Sign out');
	}


});


$(window).load(function() {
  $(window).scroll(function(){

    var offset = $("#11").offset();
    window_y = $(window).scrollTop();
    scroll_critical = parseInt(offset.top) - 1000; 
    if (window_y > scroll_critical) { 
       $(".scrolltop").removeClass('s-active');
    } else {
       // ACA HACES TODO LO CONTRARIO
       $(".scrolltop").addClass('s-active');
    }
  });
});