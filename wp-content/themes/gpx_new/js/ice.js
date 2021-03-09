(function($) {

	function getParameterByName(name)
	{
	    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
	    var regexS = "[\\?&]" + name + "=([^&#]*)";
	    var regex = new RegExp(regexS);
	    var results = regex.exec(window.location.search);
	    if(results == null)
	    return "";
	    else
	    return decodeURIComponent(results[1].replace(/\+/g, " "));
	}  
/*
	if(getParameterByName('perks_select').length > 0) {
	    $('.perks-choose-credit').show();
	}
*/	
	$('html body').on('click', '.perks-choose-credit .exchange-item', function(){
		var id = $(this).find('.exchange-credit-check').data('creditweekid');
    	sessionStorage.setItem('perksDeposit', id);
	});
	
	$('html body').on('click', '.if-perks-ownership', function(){
		sessionStorage.removeItem("perksDeposit");
		$('.if-perks-credit').prop("checked", false);
		$('.if-perks-credit').attr("checked", false);
	});
	
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
    $('.logged-in-hide .call-modal-login.login').click(function(e){
    	e.preventDefault();
    	active_modal('#modal-hold-alert');
    })
    $('.perks-nav-link, .perks-nav-link a').click(function(){
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
    });
	$('.faux-link-box').click(function(e){
		e.preventDefault();
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
		var link = $(this).find('a').attr('href');
		window.location.href=link;
	});
	
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
    		$('#alertMsg').html("<strong>We’re stacking up your savings credits! Give us just a few seconds.</strong>");
			active_modal('#modal-hold-alert');
    		$.post('/wp-admin/admin-ajax.php?action=post_IceMemeber',{redirect: redirect}, function(data){
    		    if(data.redirect) {
    			window.location.href = data.redirect;
    		    }
    		});	    
    	}
    	return false;
   });
    
    $('.perks-choose-credit ').on('change', '.exchange-credit-check', function(){
    	console.log('changed');
    	$('input[type="checkbox"]').is(':checked').trigger('change');
    	$(this).trigger('change');
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
        		
        		var type = 'transferred';
        		var deposit = sessionStorage.getItem('perksDeposit');
        		
        		if(getParameterByName('perks_select').length > 0) {
        			
        			var creditweekid = $('.exchange-credit-check:checked').data('creditweekid');
        		    var creditextensionfee = $('.exchange-credit-check:checked').data('creditexpiredfee');
        		    var creditvalue = $('.exchange-credit-check:checked').val();
        		    
        		    if((typeof creditweekid === 'undefined' || !creditweekid  || typeof creditvalue === 'undefined' )) {
        		    	$error = 'You must select an exchange credit.';

        			    $('#alertMsg').html($error);
        			    active_modal('#modal-hold-alert');
        			    $($this).find('.fa-refresh').remove();	
        			    
        			    return false;
        		    }
        			
        		    
        		    
        			   
        		    if($('.exchangeOK').length)
        			$error = '';
        		    var form = form + '&creditweekid='+creditweekid+'&creditvalue='+creditvalue+'&creditextensionfee='+creditextensionfee;
        		    if(creditweekid == 'deposit') {
	        			var creditdate = $('#exchangendeposit input[name="CheckINDate"]:not([disabled])').val();
	        			if(creditdate == ''){
	        			    $error = 'You must enter a check in date.';
	
	        			    $('#alertMsg').html($error);
	        			    active_modal('#modal-hold-alert');
	        			    $($this).find('.fa-refresh').remove();	
	        			    
	        			    return false;
	        			    
	        			}else{
	        					sessionStorage.removeItem("perksDeposit");
	                			$set = true;
	                			var pid = $('#guestInfoForm').find('input[name="propertyID"]').val();
	                			var depositform = $('#exchangendeposit').serialize();
	                			depositform  = depositform + '&pid='+pid;
	                			$.post('/wp-admin/admin-ajax.php?action=gpx_deposit_on_exchange',depositform, function(data){
	                				form = form + '&deposit='+data.id;
	            					deposit = data.id;
	            					type = 'deposit_transferred';
	                				if(data.paymentrequired){
	                		    		$('#alertMsg').text("Please contact us to make this deposit.");
	                					active_modal('#modal-hold-alert');
	                					return false;
	//                				    $('.payment-msg').text('');
	//                				    $('#checkout-amount').val(data.amount);
	//                      			    $('#checkout-item').val(data.type);
	//                      			    $('#modal_billing_submit').attr('href', link);
	//                      			    $('#alertMsg').html(data.html);
	//                      			    active_modal('#modal-hold-alert');
	//                      			  $("html, body").animate({ scrollTop: 0 }, "slow");
	//                					$.post('/wp-admin/admin-ajax.php?action=gpx_save_guest',form, function(data){
	//                    				    if(data.success) {
	//                    				    	$($this).removeClass('submit-guestInfo');
	//                    				    } 
	//                    				    $($this).find('.fa-refresh').remove();
	//                    				});
	                				}
	                			});	
	        			}
        		    }
        		}
        		else{
        			
        		}
        		
	    		$('#alertMsg').html("<strong>We're On It!</strong> Your request has been received and a confirmation eMail has been sent to you. Keep an eye on your inbox for updates. Go ahead and get to shopping! We're redirecting you now.");
    			active_modal('#modal-hold-alert');
    			setTimeout(function(){
            		$.post('/wp-admin/admin-ajax.php?action=gpx_credit_action',{id: deposit, type: type, redirect: redirect}, function(data){
            		    if(data.redirect) {
            		    	$.get('/wp-admin/admin-ajax.php?action=gpx_load_exchange_form&weektype=&weekid=&weekendpointid=&id=', function(data){
            		    		    $('#exchangeList').html(data.html);
            		    		    $('#perksCheckout').show();
            		    	});
            		    	sessionStorage.removeItem("perksDeposit");
            		    	setTimeout(function(){
            		    		window.location.href = data.redirect;
            		    	}, 700)
            		    }
            		});	    				
    			}, 3500);
        	}
        	return false; 
    	}else{
    		$('#alertMsg').text("Please confirm that you agree to the terms and conditions.");
			active_modal('#modal-hold-alert');
    	}
    });
    $('#perksCheckout').hide();
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
