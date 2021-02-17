(function($) {

    function active_modal( $modal ){
        if( $('.dgt-modal').hasClass('active-modal') ){
            $('.dgt-modal').removeClass('active-modal');
            $('.dgt-modal').removeClass('desactive-modal');
            $($modal).addClass('active-modal');
            $($modal).removeClass('desactive-modal');
        }
        else{
            $($modal).addClass('active-modal');
            $($modal).removeClass('desactive-modal');   
        }
        
    }
	
    $('.faux-deposit').click(function(){
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
    	sessionStorage.setItem('setPerksDeposit', 'set');
    	location.href='/view-profile/#weeks-profile'
//    	$('.deposit.better-modal-link').trigger('click');
    });
    
	$('.logged-in-check').click(function(){
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
	});

	$('.ice-cta-link-benifits').click(function(){
		console.log('clicked');
		var link = $(this).find('a');
		$(link).trigger('click');
	});
	
    $('.ice-link').click(function(){
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
    	var redirect = '';
    	if($(this).hasClass('ice-cta-link-benefits')){
    		redirect = 'benefits';
    	}
    	if($(this).hasClass('ice-cta-link-shop-benifits')){
    		redirect = 'shop-benifits';
    	}
    	var cid = $(this).data('cid');
    	
    	if(cid == 'undefined' || cid == '0' || cid == ''){
    		active_modal( modal_login );
    	}
    	else {
    		$('#alertMsg').html("<strong>Gathering Information <i class='fa fa-spinner fa-pulse'></i></strong>");
			active_modal('#modal-hold-alert');
    		$.post('/wp-admin/admin-ajax.php?action=post_IceMemeber',{redirect: redirect}, function(data){
    		    if(data.redirect) {
    			window.location.href = data.redirect;
    		    }
    		});	    
    	}
    	return false;
   });
    $('.ice-submit').click(function(e){
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
    	e.preventDefault();
    	if($('#ice-checkbox').is(':checked')) {
        	var redirect = '';
        	if($(this).hasClass('ice-cta-link-benefits')){
        		redirect = 'benefits';
        	}
        	if($(this).hasClass('ice-cta-link-shop-travel')){
        		redirect = 'shop-travel';
        	}
        	var cid = $(this).data('cid');
        	
        	if(cid == 'undefined' || cid == '0' || cid == ''){
        		active_modal( modal_login );
        	}
        	else {
        		$('#alertMsg').html("<strong>Hold tight, we're redirecting you now. <i class='fa fa-spinner fa-pulse'></i></strong>");
    			active_modal('#modal-hold-alert');
    			
        		var deposit = sessionStorage.getItem('perksDeposit');
        		$.post('/wp-admin/admin-ajax.php?action=gpx_credit_action',{id: deposit, type: 'transferred', redirect: redirect}, function(data){
        		    if(data.redirect) {
        		    	sessionStorage.removeItem("perksDeposit");
        	    		$('#alertMsg').html("<strong>We're On It!</strong> Your request has been received and a confirmation eMail has been sent to you. Keep an eye on your inbox for updates. Go ahead and get to shopping! We're redirecting you now.");
        				active_modal('#modal-hold-alert');
        		    	setTimeout(function(){
        		    		window.location.href = data.redirect;
        		    	}, 2500)
        		    }
        		});	    
        	}
        	return false; 
    	}else{
    		$('#alertMsg').text("Please confirm that you agree to the terms and conditions.");
			active_modal('#modal-hold-alert');
    	}
    });
    
    if(window.location.hash) {
    	var activehash = window.location.hash;
    	$('.tab-menu-items li, .tabbed .w-information').removeClass('active');
    	$('.tab-menu-item[data-link="'+activehash+'"], '+activehash).addClass('active');
	} 
    $('html body').on('change', '.ice-select', function(e){
    	e.preventDefault();
    	var thissel = $(this).find('option:selected');
    	var id = $(thissel).data('id');
    	if($(thissel).hasClass('perks-link')) {
        	sessionStorage.setItem('perksDeposit', id);
        	window.location.href="/gpx-perks/";
    	}
    	if($(thissel).hasClass('credit-extension')) {
	   		 $(this).closest('.extend-box').find('.extend-input').show();
	   		 var dpick = $(this).closest('.extend-box').find('.credit-extension-date');
	   		 var dfrom = new Date($(dpick).data('datefrom'));
	   		 var dto = new Date($(dpick).data('dateto'));
	   		 var amt = $(dpick).data('amt');
	   		 $(dpick).datepicker({
	   		    minDate: dfrom,
	   		    maxDate: dto,
	   		 });
	   		 $(dpick).datepicker('setDate', dto);
	   		 $(dpick).focus();
    	}
    	if($(thissel).hasClass('credit-donate-btn')) {
    		$(this).closest('.extend-box').find('.donate-input').show();
    	}
    });
    $('html body').on('click', '.close-box', function(e){
		e.preventDefault();
		$(this).closest('span').hide();
	});
//    $('html body').on('click', '.perks-link', function(){
//    	
//    	var id = $(this).data('id');
//    	sessionStorage.setItem('perksDeposit', id);
//    	window.location.href="/gpx-perks/";
//    });
	
})( jQuery );
