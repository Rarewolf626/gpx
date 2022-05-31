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

	$('html body').on('click', '.perks-choose-donation .exchange-item', function(){
		var id = $(this).find('.exchange-credit-check').data('creditweekid');
    	sessionStorage.setItem('perksDepositDonation', id);
	});

	$('html body').on('click', '.if-perks-ownership', function(){
		sessionStorage.removeItem("perksDeposit");
		$('.if-perks-credit').prop("checked", false);
		$('.if-perks-credit').attr("checked", false);
	});


    $('.logged-in-hide .call-modal-login.login').click(function(e){
    	e.preventDefault();
        alertModal.alert($('#alertMsg'));
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
    		active_modal( 'modal-login' );
    	}
    	else {

            alertModal.alert("<strong>We’re stacking up your savings credits! Give us just a few seconds.</strong>");

			$.post('/wp-admin/admin-ajax.php?action=post_IceMemeberJWT',{redirect: redirect}, function(data){
    		    if(data.redirect) {
    				window.location.href = data.redirect;
    		    } else {
					// console.log(data);
				}
    		});
    	}
    	return false;
   });

    $('.perks-choose-credit ').on('change', '.exchange-credit-check', function(){
    	$('#ice-checkbox').attr('disabled', false);
    	$('input[type="checkbox"]').is(':checked').trigger('change');
    	$(this).trigger('change');
    });

	$('.perks-choose-donation ').on('change', '.exchange-credit-check', function(){
    	$('#ice-checkbox').attr('disabled', false);
    	$('input[type="checkbox"]').is(':checked').trigger('change');
    	$(this).trigger('change');
    });

    $('#ice-checkbox').attr('disabled', true);

    $('.ice-submit').click(function(e){
    	if(!$('body').hasClass('logged-in')) {
    		$('.call-modal-login').trigger('click');
    		return false;
    	}
    	e.preventDefault();

    	if($('#ice-checkbox').is(':checked')) {
        	var redirect = '';
        	if($(this).hasClass('ice-cta-link-benefits')){
        		redirect = 'view-profile';
        	}
        	if($(this).hasClass('ice-cta-link-shop-travel')){
        		redirect = 'shop-travel';
        	}
			if($(this).hasClass('ice-cta-link-donation')){
				redirect = 'view-profile';
			}

        	var cid = $(this).data('cid');

        	if(cid == 'undefined' || cid == '0' || cid == ''){
        		active_modal( 'modal-login' );
        	}
        	else {

        		if(sessionStorage.getItem('perksDepositDonation')){
        			var type = 'donated';
        			var deposit = sessionStorage.getItem('perksDepositDonation');
        		}
        		else{
        			var type = 'transferred';
        			var deposit = sessionStorage.getItem('perksDeposit');
        		}

        		if(getParameterByName('perks_select').length > 0) {

        			var creditweekid = $('.exchange-credit-check:checked').data('creditweekid');
        		    var creditextensionfee = $('.exchange-credit-check:checked').data('creditexpiredfee');
        		    var creditvalue = $('.exchange-credit-check:checked').val();

        		    if((typeof creditweekid === 'undefined' || !creditweekid  || typeof creditvalue === 'undefined' )) {
        		    	$error = 'You must select an exchange credit.';

                        alertModal.alert($error);
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

                            alertModal.alert($error);
	        			    $($this).find('.fa-refresh').remove();

	        			    return false;

	        			}else{
	        					sessionStorage.removeItem("perksDeposit");
	        					sessionStorage.removeItem("perksDepositDonation");
	                			$set = true;
	                			var pid = $('#guestInfoForm').find('input[name="propertyID"]').val();
	                			var depositform = $('#exchangendeposit').serialize();
	                			depositform  = depositform + '&pid='+pid;
	                			$.post('/wp-admin/admin-ajax.php?action=gpx_deposit_on_exchange',depositform, function(data){
	                				form = form + '&deposit='+data.id;
	            					deposit = data.id;
	            					type = 'deposit_transferred';
	                				if(data.paymentrequired){
                                        alertModal.alert("Please contact us to make this deposit.", false);
	                					return false;
	                				}
	                			});
	        			}
        		    }
        		}
        		else{

        		}
				$('.ice-submit button').append('<i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i>');


    			setTimeout(function(){
            		$.post('/wp-admin/admin-ajax.php?action=gpx_credit_action',{id: deposit, type: type, redirect: redirect}, function(data){
            		    if(data.redirect) {
            		    	$.get('/wp-admin/admin-ajax.php?action=gpx_load_exchange_form&type='+ type + '&weektype=&weekid=&weekendpointid=&id=', function(data){
            		    		    $('#exchangeList').html(data.html);
//            		    		    $('.perksCheckout').show();
            		    	});
            		    	sessionStorage.removeItem("perksDeposit");
            		    	sessionStorage.removeItem("perksDepositDonation");
            		    	setTimeout(function(){

								//Do the JWT SSO auth to Arrivia
            		    		$.post('/wp-admin/admin-ajax.php?action=post_IceMemeberJWT',{redirect: redirect}, function(data){

									if(type == 'donated'){
										data.redirect = false;
										window.location.href = 'view-profile';
									}

									if(data.redirect) {
										window.location.href = data.redirect;
									} else {
										// console.log(data);
									}
								});

            		    	}, 700)
            		    }

            		});

					if(type ==='donated'){
                        alertModal.alert("Thank you for submitting your donation request. We're redirecting you to your profile now.", false);
					}
					else{
                        alertModal.alert("<strong>We're On It!</strong> Your request has been received and a confirmation eMail has been sent to you. Keep an eye on your inbox for updates. Go ahead and get to shopping! We're redirecting you now.");
					}
					$('.ice-submit button i').remove();
    			}, 10000);

        	}
        	return false;
    	}else{
            alertModal.alert("Please confirm that you agree to the terms and conditions.", false);
    	}
    });
//    $('.perksCheckout').hide();
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
    		sessionStorage.setItem('perksDepositDonation', id);
        	window.location.href="/donate/";
    		//$(this).closest('.extend-box').find('.donate-input').show();
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
