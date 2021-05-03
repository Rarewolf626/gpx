(function($) {
	$('html body').on('click', '#autopopulate', function(){
		//$('#autopopulate').click(function(){
		    $(this).find('.fauxCheckbox').toggleClass('checked'); 
		    if($(this).find('.fauxCheckbox').hasClass('checked')) {
		        $.get('/wp-admin/admin-ajax.php?action=gpx_fast_populate', function(data){
		 	   $('#billing_address').val(data.billing_address).focus();
		 	   $('#billing_city').val(data.billing_city).focus();
		 	   $('#billing_state').val(data.billing_state).focus();
		 	   $('#billing_zip').val(data.billing_zip).focus();
		 	   $('#biling_country').val(data.biling_country).focus();
		 	   $('#billing_email').val(data.billing_email).focus();
		 	   $('#billing_cardholder').val(data.billing_cardholder).focus();
		 	   $('#billing_number').focus();
		        });
		    } else {
		 	   $('#billing_city').val('').focus();
		 	   $('#billing_state').val('').focus();
		 	   $('#billing_zip').val('').focus();
		 	   $('#biling_country').val('').focus();
		 	   $('#billing_email').val('').focus();
		 	   $('#billing_cardholder').val('').focus();   
		 	   $('#billing_address').val('').focus();
		    }
		 });	
	// $ Works! You can test it with next line if you like
	// console.log($);
	$('html body').on('click', '.submit-payment', function(e){
//	$('.submit-payment').click(function(e){
		e.preventDefault();
		var button = $(this);
		if($(button).hasClass('submitted')){
			return false;
		}
    	$(button).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
    	$(button).addClass('submitted');
		var thisform = $(this).closest('.paymentForm');
		
		if($(this).hasClass('modal-submit-payment')) {
		    $(button).hide();
		}
		var paid = $('input[name="paid"]').val();
		if(paid == '0'){
			paymentSubmit(button);
		}else{
			
			//send the CHD to S4 and then add the token
			var cart_id = $('input[name="cartID"]').val();
			var cc_name = $('input[name="billing_cardholder"]').val();
			var cc_address = $('input[name="billing_address"]').val();
			var cc_number = $('input[name="billing_number"]').val();
			var cc_month = $('input[name="billing_month"]:checked').val();
			var cc_year = $('input[name="billing_year"]:checked').val();
			
			if(cc_month == '' ||  typeof cc_month == 'undefined'){
				cc_month = $('select[name="billing_month"]').val();
				cc_year = $('select[name="billing_year"]').val();
			}
			var cc_code = $('input[name="billing_ccv"]').val();
			var cc_postcode = $('input[name="billing_zip"]').val();
			var cc_ip = $('input[name="client_ip"]').val();
			var cc_token = '19A593B3-5206-408D-BB7C-C07EA63C9042';
			console.log(cc_month);

			//get the access block
			$.post('/wp-admin/admin-ajax.php?action=gpx_i4goauth', {
			    	cartID: cart_id,
				fuseaction: 'account.authorizeClient', 
				i4go_clientip: cc_ip, 
				i4go_accesstoken: cc_token}, function(data){
				        //store the details
					$.post(data.data.i4go_server, {
						fuseaction: 'api.jsonPostCardEntry', 
						i4go_accessblock: data.data.i4go_accessblock, 
		//				i4go_responsetext: , 
						i4go_cardholdername: cc_name, 
						i4go_streetaddress: cc_address, 
						i4go_cardnumber: cc_number, 
						i4go_expirationmonth: cc_month, 
						i4go_expirationyear: cc_year, 
						i4go_cvv2code: cc_code,
						i4go_cvv2indicator: 0,
						i4go_cardholdername: cc_name, 
						i4go_postalcode: cc_postcode}, function(ret){
							$.post('/wp-admin/admin-ajax.php?action=gpx_14gostatus', {data: ret, paymentID: data.paymentID}, function(returndata){
							    if(returndata.i4go_responsecode === '1') {
								$('.payment-error').text('');
								$('#paymentID').val(returndata.paymentID);
								$('.paymentID').val(returndata.paymentID);
								paymentSubmit(button);
							    }else{
								$('.payment-error').text(returndata.i4go_responsetext);
								$(button).removeClass('submitted');
							    }
							});
					});
			});			
		}

	});
	/*
	 * e = submit button
	 */
	function paymentSubmit(button){
//    $('.submit-payment').click(function(e){
        	if(!$(this).hasClass('submitted')) {
                	var $this = $(button);
                	var link = $(button).attr('href');
                	var thisform = $(button).closest('.dgt-container').find('.paymentForm').serialize();
//                	$(button).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
//                	$($this).addClass('submitted');
                	$.post('/wp-admin/admin-ajax.php?action=gpx_payment_submit', thisform, function(data){
                	    
                		if(data.success) {
                			if(data.type == 'credit_extension') {
                    	    	close_modal('#modal-checkout');
                    	    	$('.payment-msg').text(data.msg);
                    	    }else{
                    	    	window.location.href=link;
                    	    }
                	   } 
                	   else {
                		   if(data.error == 'Transaction processed.'){
                			   window.location.href=link;
                		   }else{
                    	       $('.payment-error').text(data.error);
                    	       $($this).removeClass('submitted');
                		   }
                	   }
                	   $($this).find('.fa-refresh').remove();
                	});
        	}
	}
	
})( jQuery );