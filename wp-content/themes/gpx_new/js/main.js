// 01100010 01101111 01101111 01100010 01110011 00001010
$(function(){
    var tb;
    var home = $('.home');
    var slider_home  = $('#slider-home');
    var modal_login  = $('#modal-login');
    var modal_pwreset  = $('#modal-pwreset');
    var modal_filter = $('#modal-filter');
    var modal_filter_resort = $('#modal-filter-resort');
    var modal_alert  = $('#modal-alert');
    var title_acordeon = $('.w-list-availables .title');
//    if($('#acRequest').length){
//	var coupon = $('#acRequest').data('coupon');
//	Cookies.set('auto-coupon', coupon, {path: '/' });
//    }
    $('html body').on('click', '.copyText', function(){
	var copy = $(this).find('.copy');
	var copyval = copy.text();
	copyToClipboard(copy);
    
	$(copy).hide();
	setTimeout(function(){
	    $(copy).show();
	}, 300);
	//Cookies.set('auto-coupon', copyval, {path: '/' });
    });
//    $('#couponAdd').click(function(e){
//	e.preventDefault();
//	var el = $(this).closest('.gwrapper').find('#couponCode');
//	var coupon = $(el).val();
//	var book = $(el).data('book');
//	var cid = $(el).data('cid');
//	var cartID = $(el).data('cartid');
//	var currentPrice = $(el).data('currentprice');
//	Cookies.set('auto-coupon', null, { expires: -1, path: '/' });
//	Cookies.remove('auto-coupon',  {path: '/' });
//	$.post('/wp-admin/admin-ajax.php?action=gpx_enter_coupon', {coupon: coupon, book: book, cid: cid, cartID: cartID, currentPrice: currentPrice}, function(data){
//	   if(data.success) {
//	       window.location.href='/booking-path-payment';
//	   } else {
//	       $(el).addClass('iserror');
//	       $('#couponError').html(data.error);
//	       $("#apply-coupon").hide();
//	   }
//	});
//    });
    if($('.load-results').length) {
    	$('.load-results').each(function(){
    		var thisel = $(this);
    		var resort = thisel.data('resortid');
    		 
    		var loadedresort = '#loaded-result-'+resort;
    		var loadedcount = '#loaded-count-'+resort; // display count for current resort
    		var loadedtotcount = '#loaded-totcount'; // total at top of page
    		var loadedtopofresort = $(loadedcount).closest('.w-item-view'); // top of current resort
    		var loadedresultcontent = '#results-content'; // container for resorts
    		var loadedreschilds = $(loadedresultcontent).children('.w-item-view'); // all result rows for sort
    		
    		var monthstart = $(loadedcount).attr('data-monthstart');
    		var monthend = $(loadedcount).attr('data-monthend');
    		 
    		var thiscnt = 0;
    		var totcnt = 0;
    		
    		$.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability',{resortid: resort, limitstart: 0, limitcount: 8, monthstart: monthstart,monthend: monthend}, function(data){
    		    if(data.html) {
    		    	$(loadedresort).html(data.html);
    		    	
    		    	// grab count hidden in div at bottom of results
    		    	thiscnt = parseInt($("#res_count_"+resort).attr('data-res-count'));
    		    	$(loadedcount).html(thiscnt+' Result');
    		    	// add an s to the end of Result, except for 1 result
    		    	if(thiscnt!=1) $(loadedcount).append('s');
    		    	
    		    	// add prop cnt to top li for sorting
    		    	$(loadedtopofresort).attr({"data-propcount" : thiscnt});    		    	
					
					// update total props top of page
					$(loadedreschilds).each(function () 
					{
						var propcount = parseInt($(this).attr('data-propcount'));
						if(propcount>=1)
						{						
							totcnt=parseInt(totcnt)+propcount;
							$(loadedtotcount).html(totcnt+' Search Results');
						}
						else
						{
							$(this).detach().appendTo('#results-content');
						}						
					});
    		    	
    		    }
    		    else {
    		    	thisel.hide();
    		    	thisel.closest('li').find('.hide-on-load').show();
    		    }
    		});	 
    	});
    }
    if($('#apply-coupon').length) {
	$('#couponAdd').trigger('click');
    }
    $('.vc_carousel-control').attr('aria-label', "controls");
    
	$('html body').on('click', '.extend-week', function(e){
	    e.preventDefault();
	    $(this).closest('.extend-box').find('.extend-input').show();
	});
	    $('html body').on('click', '.extend-btn', function(e){
	    e.preventDefault();
	    var id = $(this).data('id');
	    var date = $(this).closest('.extend-input').find('.extend-date').val();
	    $(this).closest('.extend-box').hide();
	    $.ajax({
		url : '/wp-admin/admin-ajax.php?&action=gpx_extend_week',
        	type : 'POST',
        	data : {id: id, newdate: date},
		success: function(data){
			if(data.error) {
				alert(data.error);
			}

			if(data.cid) {
				   var id = data.cid;
				   var loading = 'load_transactions';
				   $.ajax({
				       method: 'GET',
				       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
				       data: {load: loading, cid: id},
				       success: function(data){
					   $('#ownership').html(data.ownership);
					   $('#deposit').html(data.deposit);
					   $('#depositused').html(data.depositused);
					   $('#exchange').html(data.exchange);
					   $('#bnr').html(data.rental);
					   $('#misc').html(data.misc);
					   $('#creditBal').text(data.credit);
					   $('#holdweeks').html(data.hold);
					   $('.loading').hide();
					   		tb.destroy();
					        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
					            responsive: true,
					            paging: true,
					            "order": [ ],
					            pageLength: 5,
					            "language": {
					                "lengthMenu": "Display _MENU_ records per page",
					                "zeroRecords": "Nothing found - sorry",
					                "info": "of _PAGES_",
					                "infoEmpty": "No records available",
					                "infoFiltered": "(filtered from _MAX_ total records)"
					            },
					            columnDefs: [
					                {
					                    //targets: [-1, -3],
					                   // className: 'dt-body-right'
					                }
					            ]
					        });
				       },
				   });
			}
		}
	    });
	});
//	    $('html body').on('change', '.ice-select', function(e){
//	    	e.preventDefault();
//	    	var value = $(this).find('option:selected').text();
//	    	if(value == 'Extend') {
//	   		 $(this).closest('.extend-box').find('.extend-input').show();
//			 var dpick = $(this).closest('.extend-box').find('.credit-extension-date');
//			 var dfrom = new Date($(dpick).data('datefrom'));
//			 var dto = new Date($(dpick).data('dateto'));
//			 var amt = $(dpick).data('amt');
//			 $(dpick).datepicker({
//			    minDate: dfrom,
//			    maxDate: dto,
//			 });
//			 $(dpick).datepicker('setDate', dto);
//			 $(dpick).focus();
//	    	}else{
//	    		$(this).closest('.extend-box').find('.donate-input').show();
//	    	}
//	    });
//	    $('html body').on('click', '.credit-extension', function(e){
//		e.preventDefault();
//		 $(this).closest('.extend-box').find('.extend-input').show();
//		 var dpick = $(this).closest('.extend-box').find('.credit-extension-date');
//		 var dfrom = new Date($(dpick).data('datefrom'));
//		 var dto = new Date($(dpick).data('dateto'));
//		 var amt = $(dpick).data('amt');
//		 $(dpick).datepicker({
//		    minDate: dfrom,
//		    maxDate: dto,
//		 });
//		 $(dpick).datepicker('setDate', dto);
//		 $(dpick).focus();
//	    });
	    $('html body').on('click', '.pay-extension', function(e){
	    	e.preventDefault();
	    	$(this).closest('.w-credit').addClass('make').find('.head-credit').addClass('not').removeClass('disabeled');
	    	$(this).remove();
	    });
	    $('html body').on('click', '.credit-extension-btn', function(e){
		    e.preventDefault();
		    $('.payment-msg').text('');
		    var id = $(this).data('id');
		    var interval = $(this).data('interval');
		    var wrapper = $(this).closest('.extend-input');
		    var date = $(wrapper).find('.credit-extension-date').val();
		    var amt = $(wrapper).find('.credit-extension-date').data('amt');
		    $(this).closest('.extend-box').hide();
		    $.ajax({
			url : '/wp-admin/admin-ajax.php?&action=gpx_extend_credit',
	        	type : 'POST',
	        	data : {id: id, newdate: date, interval: interval},
			success: function(data){
				if(data.error) {
					alert(data.error);
				}
				if(data.paymentrequired) {
  				    $('.payment-msg').text('');
				    $('#checkout-amount').val(data.amount);
              			    $('#checkout-item').val(data.type);
              			    $('#alertMsg').html(data.html);
              			    active_modal('#modal-hold-alert');
                		  }else{
                		      $('#alertMsg').html(data.message);
                			  active_modal('#modal-hold-alert'); 
                		  }
				if(data.cid) {
					   var id = data.cid;
					   var loading = 'load_transactions';
					   $.ajax({
					       method: 'GET',
					       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
					       data: {load: loading, cid: id},
					       success: function(data){
						   $('#ownership').html(data.ownership);
						   $('#deposit').html(data.deposit);
						   $('#depositused').html(data.depositused);
						   $('#exchange').html(data.exchange);
						   $('#bnr').html(data.rental);
						   $('#misc').html(data.misc);
						   $('#creditBal').text(data.credit);
						   $('#holdweeks').html(data.hold);
						   $('.loading').hide();
						   		tb.destroy();
						        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
						            responsive: true,
						            paging: true,
						            "order": [ ],
						            pageLength: 5,
						            "language": {
						                "lengthMenu": "Display _MENU_ records per page",
						                "zeroRecords": "Nothing found - sorry",
						                "info": "of _PAGES_",
						                "infoEmpty": "No records available",
						                "infoFiltered": "(filtered from _MAX_ total records)"
						            },
						            columnDefs: [
						                {
						                    //targets: [-1, -3],
						                   // className: 'dt-body-right'
						                }
						            ]
						        });
					       },
					   });
				}
			}
		    });
		});
	    $('html body').on('click', '.close-box', function(e){
		e.preventDefault();
		$(this).closest('.extend-input').hide();
		$(this).closest('.donate-input').hide();
	    });
//    $('.deposit-cookie').click(function(){
//    	Cookies.set('deposit-login', '1');
//    });
//    if($('.deposit-login').length) {
//    	var owner = $('.deposit-login').data('owner');
//    	if(owner != 1) {
//    		//this is not an owner do we need to switch owners?
//        	var switchuser = Cookies.get('switchuser');
//        	if(switchuser > 0) {
//        		//switchuser has been set so we can display the form
//            	$('#main-deposit-link').trigger('click');
//            	Cookies.remove('deposit-login');  
//        	}  else {
//        		//go to the switch owner page
//        		location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_switch'
//        	}
// 		
//    	} else {
//    		//this is an owner just display the form
//        	$('#main-deposit-link').trigger('click');
//        	Cookies.remove('deposit-login');  
//    	}
//
//    }
	    
function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).text()).select();
    document.execCommand("copy");
    $temp.remove();
}

    $('html body').on('click', '.agent-cancel-booking', function(e){
    	e.preventDefault();
    	var link = $(this).attr('href')+'&fe=1 #admin-modal-content';
    	$('#modal-transaction .modal-body').load(link);
    	active_modal('#modal-transaction');
    });
    
    $('html body').on('click', 'tbody .guestNameTD, li.guestNameTD', function(e){
	 e.preventDefault();
	  var link = $(this).find('.updateGuestName').data('href')+' #guest-details-info';
	  $('#modal-transaction .modal-body').load(link);
//	  var fname = $(this).find('.updateGuestName').data('fname');
//	  var lname = $(this).find('.updateGuestName').data('lname');
//	  var email = $(this).find('.updateGuestName').data('email');
//	  var adults = $(this).find('.updateGuestName').data('adults');
//	  var children = $(this).find('.updateGuestName').data('children');
//	  var owner = $(this).find('.updateGuestName').data('owner');
//	  var transaction = $(this).find('.updateGuestName').data('transaction');
//	  $('#transactionID').val(transaction);
//	  $('#FirstName1').val(fname);
//	  $('#LastName1').val(lname);
//	  $('#Email').val(email);
//	  $('#Adults').val(adults);
//	  $('#Children').val(children);
//	  $('#Owner').val(owner);
	  
	});
    $('html body').on('click', '.remove-from-cart', function(){
//      $('.remove-from-cart').click(function(){
  	var pid = $(this).data('pid');
  	var cid = $(this).data('cid');
  	console.log(cid);
  	$.get('/wp-admin/admin-ajax.php?action=gpx_remove_from_cart&pid='+pid+'&cid='+cid, function(data){
  	    setTimeout(function(){
  		if(data.rr == 'refresh')		
  		    location.reload();
  		else
  		    window.location.href = '/';
  	    }, 500);  
  	});
      });
//    $('html body').on('click', '.credit-donate-btn', function(e){
//		e.preventDefault();
//		 $(this).closest('.extend-box').find('.donate-input').show();
//	    });
    $('html body').on('click', '.credit-donate-transfer', function(e){
    	e.preventDefault();
    	var thistd = $(this).closest('td');
    	var thisrow = $(this).closest('tr');
    	var id = $(this).data('id');
    	var type = $(this).data('type');
		$(thistd).text('');
    	$.post('/wp-admin/admin-ajax.php?action=gpx_credit_action', {id: id, type: type}, function(data){
    		$(thisrow).find('td:nth-child(5)').text(data.action);
    		window.location='/view-profile';
    	});
    });
    $('html body').on('click', '#cancel-booking', function(e){
	e.preventDefault();
	$(this).hide();
	var transactionID = $(this).data('transaction');
    	var link = $(this).attr('href')+' #admin-modal-content';
    	var name = $('.agent-cancel-booking').data('agent');
    	
	if(confirm('Are you sure you want to cancel this booking request?  The record will report that '+name+' cancelled the request.')) {
		$.ajax({
		   url: '/wp-admin/admin-ajax.php?action=gpx_cancel_booking',
		   type: 'POST',
		   data: {transaction: transactionID, requester: 'user', type: type},
		   success: function(data) {
		       	var transmodal = $('#modal-transaction').find('.close-modal');
		       	close_modal(transmodal);
				   var id = data.cid;
				   var loading = 'load_transactions';
				   $.ajax({
				       method: 'GET',
				       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
				       data: {load: loading, cid: id},
				       success: function(data){
					   $('#ownership').html(data.ownership);
					   $('#deposit').html(data.deposit);
					   $('#depositused').html(data.depositused);
					   $('#exchange').html(data.exchange);
					   $('#bnr').html(data.rental);
					   $('#misc').html(data.misc);
					   $('#creditBal').text(data.credit);
					   $('#holdweeks').html(data.hold);
					   $('.loading').hide();
					        tb.destroy();
					        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
					            responsive: true,
					            paging: true,
					            "order": [ ],
					            pageLength: 5,
					            "language": {
					                "lengthMenu": "Display _MENU_ records per page",
					                "zeroRecords": "Nothing found - sorry",
					                "info": "of _PAGES_",
					                "infoEmpty": "No records available",
					                "infoFiltered": "(filtered from _MAX_ total records)"
					            },
					            columnDefs: [
					                {
					                    //targets: [-1, -3],
					                   // className: 'dt-body-right'
					                }
					            ]
					        });
				       },
				   });
		   }
		});
	}
    });
    $('html body').on('click', '.remove-guest', function(e){
	e.preventDefault();
	var transaction = $(this).data('id');
	$.post('/wp-admin/admin-ajax.php?action=gpx_remove_guest', {transactionID: transaction}, function(data){
	    if(data.success) {
	       	var transmodal = $('#modal-transaction').find('.close-modal');
	       	close_modal(transmodal);
			   var id = data.cid;
			   var loading = 'load_transactions';
			   $.ajax({
			       method: 'GET',
			       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
			       data: {load: loading, cid: id},
			       success: function(data){
				   $('#ownership').html(data.ownership);
				   $('#deposit').html(data.deposit);
				   $('#depositused').html(data.depositused);
				   $('#exchange').html(data.exchange);
				   $('#bnr').html(data.rental);
				   $('#misc').html(data.misc);
				   $('#creditBal').text(data.credit);
				   $('#holdweeks').html(data.hold);
				   $('.loading').hide();
				   		tb.destroy();
				        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
				            responsive: true,
				            paging: true,
				            "order": [ ],
				            pageLength: 5,
				            "language": {
				                "lengthMenu": "Display _MENU_ records per page",
				                "zeroRecords": "Nothing found - sorry",
				                "info": "of _PAGES_",
				                "infoEmpty": "No records available",
				                "infoFiltered": "(filtered from _MAX_ total records)"
				            },
				            columnDefs: [
				                {
				                    //targets: [-1, -3],
				                   // className: 'dt-body-right'
				                }
				            ]
				        });
			       },
			   });
	    }
	});
    });
    $('html body').on('click', '.save-edit-transaction', function(e){
	e.preventDefault();
	var transaction = $(this).data('transaction');
	var firstName = $('#tFirstName1').val();
	var lastName = $('#tLastName1').val();
	var email = $('#tEmail').val();
	var adults = $('#tAdults').val();
	var children = $('#tChildren').val();
    	var link = $(this).attr('href')+' #admin-modal-content';
		$.post('/wp-admin/admin-ajax.php?action=gpx_reasign_guest_name', {transactionID: transaction, FirstName1: firstName, LastName1: lastName, Email: email, Adults: adults, Children: children}, function(data){
		    if(data.paymentrequired)	{
			    $('.payment-msg').text('');
			    $('#checkout-amount').val(data.amount);
  			    $('#checkout-item').val(data.type);
  			    $('#alertMsg').html(data.html);
  			    active_modal('#modal-hold-alert');
		    }else{
			 $('#modal-transaction .modal-body').load(link);
			   var id = data.cid;
			   var loading = 'load_transactions';
			   $.ajax({
			       method: 'GET',
			       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
			       data: {load: loading, cid: id},
			       success: function(data){
				   $('#ownership').html(data.ownership);
				   $('#deposit').html(data.deposit);
				   $('#depositused').html(data.depositused);
				   $('#exchange').html(data.exchange);
				   $('#bnr').html(data.rental);
				   $('#misc').html(data.misc);
				   $('#creditBal').text(data.credit);
				   $('#holdweeks').html(data.hold);
				   $('.loading').hide();
				   		tb.destroy();
				        tb = $('.ajax-data-table').addClass('nowrap').dataTable({
				            responsive: true,
				            paging: true,
				            "order": [ ],
				            pageLength: 5,
				            "language": {
				                "lengthMenu": "Display _MENU_ records per page",
				                "zeroRecords": "Nothing found - sorry",
				                "info": "of _PAGES_",
				                "infoEmpty": "No records available",
				                "infoFiltered": "(filtered from _MAX_ total records)"
				            },
				            columnDefs: [
				                {
				                    //targets: [-1, -3],
				                   // className: 'dt-body-right'
				                }
				            ]
				        });
			       },
			   });
		    }
		});
		
	
    });
//    
//    $('#html body').on('click', '.guestNameTD', function(){
//	   $(this).find('input').show().focus(); 
//	});
//   $('#html body').on('blur', '.updateGuestName input', function(){
//	$(this).hide();
//   });
    $('.slider-item.rsContent').each(function(){
    	var img = $(this).find('img');
    	var imgsrc = $(img).attr('src');
    	$(this).css('background-image', 'url(' + imgsrc + ')');
    	$(img).hide();
    });
    $('html body').on('click', '.menu-item-has-children', function(e){
    	$(this).find('ul.sub-menu').toggle();
    });

    if($('.collapse').length) {
	//set the height
	var height = $('.collapse:first').find('li:first').outerHeight();
	var liwidth = $('.collapse:first').find('li:first').outerWidth();
	$('.collapse').css({height: height});
	//get the width of the container
	var containerwidth = $('.dgt-container:first').outerWidth();
	var lisperrow = Math.floor(containerwidth / liwidth);
	$('.collapse').each(function(){
	    var els = $(this).find('li.item-result').length;
	    if(els > lisperrow) {
		$(this).closest('li').css({paddingBottom: '50px'});
		$(this).closest('li').find('.hidden-more-button').css({
		   border: '1px solid #000',
		   fontSize: '22px',
		});
	    } else {
		$(this).closest('li').find('.result-resort-availability').hide();
	    }
	});
    }
    $('.result-resort-availability').click(function(e){
	e.preventDefault();
	var rid = $(this).data('resortid');
	var height = $('.collapse:first').find('li:first').outerHeight();
	var chevron = $(this).closest('.w-item-view').find('.result-resort-availability i');
	if($(chevron).hasClass('fa-chevron-down')) {
		$('#gpx-listing-result-'+rid).css({height: 'auto'});
		var newheight = $('#gpx-listing-result-'+rid).css("height");
		$('#gpx-listing-result-'+rid).css({height: height});
		$('#gpx-listing-result-'+rid).animate({
		    height: newheight,
		}, 250);
	} else { 
		$('#gpx-listing-result-'+rid).css({height: 'auto'});
		$('#gpx-listing-result-'+rid).animate({
		    height: height,
		}, 250);
	}
	$(chevron).toggleClass('fa-chevron-down fa-chevron-up');
    });
    $('.tab-menu-item').click(function(e){
    	e.preventDefault();
    	var $li = $(this).closest('li');
    	var $link = $(this).find('a').attr('href');
    	$('.tab-menu-items li, .tabbed .w-information').removeClass('active');
    	$($li).addClass('active');
    	$($link).addClass('active');
    });
    $('#owner-shared-main-gallery').slick({
    	adaptiveHeight: true,
    	slidesToShow: 1,
    	slidesToScroll: 1,
    	arrows: true,
    	fade: true,
//    	asNavFor: '#owner-shared-thumbnail-gallery'
    });
    $('#owner-shared-thumbnail-gallery').slick({
    	slidesToShow: 3,
    	slidesToScroll: 1,
//    	asNavFor: '#owner-shared-main-gallery',
    	dots: false,
    	arrows: true,
//    	  centerMode: true,
    	focusOnSelect: true
    });
    $('#gallery_resort_main').slick({
    	  adaptiveHeight: true,
    	  slidesToShow: 1,
    	  slidesToScroll: 1,
    	  arrows: false,
    	  fade: true,
    	  asNavFor: '#gallery_resort_thumbs'
    	});
    	$('#gallery_resort_thumbs').slick({
    	  slidesToShow: 3,
    	  slidesToScroll: 1,
    	  asNavFor: '#gallery_resort_main',
    	  dots: false,
    	  arrows: true,
//    	  centerMode: true,
    	  focusOnSelect: true
    	});
    $('.carousel-slider').slick({
    	  slidesToShow: 1,
    	  slidesToScroll: 1,

    	  mobileFirst: true,
    	  responsive: [
    		    {
    		      breakpoint: 1024,
    		      settings: {
    		        slidesToShow: 3,
    		        slidesToScroll: 1,
    		        infinite: true,
    		      }
    		    },
    		    {
    		      breakpoint: 600,
    		      settings: {
    		        slidesToShow: 2,
    		        slidesToScroll: 2,
    		        infinite: true,
    		      }
    		    },
    		    {
    		      breakpoint: 480,
    		      settings: {
    		        slidesToShow: 1,
    		        slidesToScroll: 1,
    		        infinite: true,
    		      }
    		    }
    		  ]
    	});   
    //mask scroll
    $('.edit-custom-request').click(function(e){
	e.preventDefault();
	var rid = $(this).data('rid');
            	$.get('/wp-admin/admin-ajax.php?action=gpx_get_custom_request&rid='+rid, function(data){
            	    $('#crID').val(data.id).addClass('filled');
            	    $('.crCountry').val(data.country).addClass('filled').prop('readonly', true);
            	    $('#00N40000003S58X').val(data.region).addClass('filled').prop('readonly', true);
            	    $('#00N40000003DG5S').val(data.city).addClass('filled').prop('readonly', true);
            	    $('#miles').val(data.miles).addClass('filled').prop('readonly', true);
            	    $('.crResort').val(data.resort).addClass('filled').prop('readonly', true);
            	    if(data.date) {
            		$('.crDateFrom').val(data.date).addClass('filled').prop('readonly', true);
            	    }
            	    if(data.nearby == "1"){
            	    	$('#nearby').prop('checked', true);
            	    }else{
            		$('#nearby').prop('checked', false);
            	    }
            	    if(data.or_larger == "1"){
            		$('#or_larger').prop('checked', true);
            	    }else{
            		$('#or_larger').prop('checked', false);
            	    }
            	    $('.crEmail').val(data.email).addClass('filled').prop('readonly', true);
            	    $('.crFirstName').val(data.fname).addClass('filled').prop('readonly', true);
            	    $('.crLastName').val(data.lname).addClass('filled').prop('readonly', true);
            	    $('.crNo').val(data.daememberno).addClass('filled').prop('readonly', true);
            	    $('.crPhone').val(data.phone).addClass('filled').prop('readonly', true);
            	    $('.crMobile').val(data.mobile).addClass('filled').prop('readonly', true);
            	    $('#00N40000003DG56').val(data.adults).addClass('filled').prop('readonly', true);
            	    $('#00N40000003DG57').val(data.child).addClass('filled').prop('readonly', true);
            	    $('input[name="00N40000003DG54"][value="'+data.roomtype+'"]').trigger('click');
            	    $('#00N40000003DG54').val(data.roomtype).addClass('filled').prop('readonly', true);
            	    $('#week_preference').val(data.roompref).addClass('filled').prop('readonly', true);
            	    $('input[name="preference"][value="'+data.roompref+'"]').trigger('click');
            	    if(data.error) {
            		$('#modal-custom-request .w-modal h2').html(data.error);
            		$('#customRequestForm').remove();
            	    }
            	    var mcrPar = $('#modal-custom-request').closest('.dgt-container');
            	    $(mcrPar).appendTo('#customrequest-profile');
            	    $('#modal-custom-request').addClass('mcr-moved');
            	    var crmindate = new Date();
            	    var crmaxdate = crmindate.getDate() + 547;
            	    var startdate = new Date(data.startdate);
            	    startdate.setHours(0);
            	    if(data.enddate){
            		var end = data.enddate;
            	    }else{
            		var end = data.startdate;
            	    }
            	    var enddate = new Date(end);
            	    enddate.setHours(0);
            	    $(".crrangepicker").daterangepicker({
            		     presetRanges: [{
            		         text: 'Today',
            		         dateStart: function() { return moment() },
            		         dateEnd: function() { return moment() }
            		     },{
            		         text: 'This Month',
            		         dateStart: function() { return moment() },
            		         dateEnd: function() { return moment().add('months', 1) }
            		     }, {
            		         text: 'Next Month',
            		         dateStart: function() { return moment().add('months', 1) },
            		         dateEnd: function() { return moment().add('months', 2) }
            		     }],
            		     applyOnMenuSelect: false,
            		     datepickerOptions: {
            		         minDate: crmindate,
            		         maxDate: crmaxdate
            		     },
            		     dateFormat: 'mm/dd/yy',
            		     change: function(){ 
            			 checkRestricted($('#00N40000003DG5P')) },
            	    });
            	    $('.crrangepicker').daterangepicker('setRange', {
  			     start: startdate,
  			     end: enddate,
            	    });
            	    $('.submit-custom-request').remove();
            	    active_modal('#modal-custom-request', 'noscroll');
        	});
    });
    var crmindate = new Date();
    var crmaxdate = new Date(crmindate.getFullYear(), crmindate.getMonth()+ 14, 0);
    $(".crrangepicker").daterangepicker({
	     presetRanges: [{
	         text: 'Today',
	         dateStart: function() { return moment() },
	         dateEnd: function() { return moment() }
	     },{
	         text: 'This Month',
	         dateStart: function() { return moment() },
	         dateEnd: function() { return moment().add('months', 1) }
	     }, {
	         text: 'Next Month',
	         dateStart: function() { return moment().add('months', 1) },
	         dateEnd: function() { return moment().add('months', 2) }
	     }],
	     applyOnMenuSelect: false,
	     datepickerOptions: {
	         minDate: crmindate,
	         maxDate: crmaxdate
	     },
	     dateFormat: 'mm/dd/yy',
	     change: function(){ 
	    	 console.log('check');
		 checkRestricted($('#00N40000003DG5P')) },
});
    $('#00N40000003S58X, #00N40000003DG5S, #00N40000003DG59, #miles').blur(function(){
	checkRestricted($(this));
    });
    function checkRestricted($this) {
    	var form = $($this).closest('#customRequestForm').serialize();
    	
    	$.post('/wp-admin/admin-ajax.php?action=custom_request_validate_restrictions', form, function(data){
    	    if(data.restricted) {
    		$('#restrictedTC').addClass('hasRestricted');
    		if(data.restricted == 'All Restricted') {
    		    $('button.submit-custom-request').addClass('gpx-disabled');
    		}else{
    		    $('button.submit-custom-request').removeClass('gpx-disabled');
    		}
    	    }else{
    		$('#restrictedTC').removeClass('hasRestricted');
    		 $('button.submit-custom-request').removeClass('gpx-disabled');
    	    }
    	});
    }
//    $('html body').on('submit', '#customRequestForm', function(e){
//	e.preventDefault();
//	if($(this).find('button.submit-custom-request').hasClass('gpx-disabled')) {
//	    //do nothing we don't want this form to be submitted.
//	}else{
//        	var email = $('#00N40000003DG50').val();
//        	if( !isValidEmailAddress( email ) ) {
//        	    $('#crError').text('Please enter a valid email address.');
//        	}
//        	else {
//        		var form = $(this).serialize();
//        		$.post('/wp-admin/admin-ajax.php?action=gpx_post_custom_request', form, function(data){
//        		    if(data.success) {
//        			if(data.matched) {
//        			    var url = '/result?matched='+data.matched;
//        			    $('#matchedTravelButton').attr('href', url);
//        			    $('#notMatchedModal, #restrictedMatchModal').appendTo('#matchedContainer');
//        			    $('#alertMsg').html($('#matchedModal'));
//        			}
//        			else {
//        			    $('#matchedModal, #restrictedMatchModal').appendTo('#matchedContainer');
//        			    $('#alertMsg').html($('#notMatchedModal')); 
//        			}
//        			if(data.restricted) {
//        			    console.log(data.restricted);
//        			    //move the not matched message back becuase this search was only within the restricted time/area
//        			    if(data.restricted == 'All Restricted') {
//        				$('#notMatchedModal').appendTo('#matchedContainer');
//        			    }
//        			    $('#restrictedMatchModal').appendTo('#alertMsg');
//        			}
//        			$('.icon-alert').remove();
//        			active_modal('#modal-hold-alert');
//        		    }
//        		});	    
//        	}
//	}
//    });
    $('html body').on('click', '.cr-cancel', function(){
	active_modal('#modal-custom-request');
	return false;
    });
    $('html body').on('click', '.cr-finalize', function(){
	$('#alertMsg').html('We will continue to monitor for weeks matching your search criteria.  You will receive an email notification when a match is found.<br>We look forward to helping you find your dream vacation!');
	return false;
    });

    $('html body').on('click', '.custom-request', function(e){
	
	e.preventDefault();
	var pid = $(this).data('pid');
	var cid = $(this).data('cid');
	var rid = $(this).data('rid');
	if(cid == 'undefined' || cid == '0'){
		active_modal( modal_login );
	}
    	else {
            	 $('.scrolltop, .s-active').trigger('click');
            	$.get('/wp-admin/admin-ajax.php?action=gpx_get_custom_request&pid='+pid+'&cid='+cid+'&rid='+rid, function(data){
            	    $('#crID').val(data.id).addClass('filled');
            	    $('.crCountry').val(data.country).addClass('filled');
            	    $('#00N40000003S58X').val(data.region).addClass('filled');
            	    $('#00N40000003DG5S').val(data.city).addClass('filled');
            	    $('#miles').val(data.miles).addClass('filled');
            	    $('.crResort').val(data.resort).addClass('filled');
            	    if(data.dateFrom) {
            		$('.crDateFrom').val(data.date).addClass('filled');
            	    }
            	    $('.crEmail').val(data.email).addClass('filled');
            	    $('.crFirstName').val(data.fname).addClass('filled');
            	    $('.crLastName').val(data.lname).addClass('filled');
            	    $('.crNo').val(data.daememberno).addClass('filled');
            	    $('.crPhone').val(data.phone).addClass('filled');
            	    $('.crMobile').val(data.mobile).addClass('filled');
            	    $('#00N40000003DG56').val(data.adults).addClass('filled');
            	    $('#00N40000003DG57').val(data.child).addClass('filled');
            	    $('#00N40000003DG54').val(data.roomtype).addClass('filled');
            	    $('#week_preference').val(data.roompref).addClass('filled');
            	    if(data.error) {
            		$('#modal-custom-request .w-modal h2').html(data.error);
            		$('#customRequestForm').remove();
            	    }
            	    active_modal('#modal-custom-request');
        	});
	}
    });
    $('#modal-custom-request .close-modal').click(function(){
	$(this).closest('#modal-custom-request').removeClass('mcr-moved');
	close_modal($(this));
    });
    //switch custom request status
    $('html body').on('click', '.crActivate', function(e){
	e.preventDefault();
	var thisel = $(this);
	var crid = $(this).data('crid');
	var craction = $(this).data('action');
	$.post('/wp-admin/admin-ajax.php?action=custom_request_status_change',{crid:crid,craction:craction}, function(data){
		  $('#alertMsg').html("Custom Request Updated!");
	          active_modal('#modal-hold-alert'); 
	});
	var crswitch = 'No <a href="#" class="crActivate btn btn-secondary" data-crid="'+crid+'" data-action="activate">Enable</a>'; 
	if(craction == 'activate') {
	    crswitch = 'Yes <a href="#" class="crActivate btn btn-secondary" data-crid="'+crid+'" data-action="deactivate">Disable</a>';
	}
	    
	$(thisel).closest('td').html(crswitch);
//	$(thisel).closest('tr').remove();
    });
    $('.gpx_form_tooltip').click(function(){
	$(this).toggleClass('visible');
    });
    $('form.material').materialForm();
    /*-----------------------------------------------------------------------------------*/
    /* Royal Slider
     /*-----------------------------------------------------------------------------------*/
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
            enabled: true,
            delay: 8500
        }
    });
    if ($('#gallery_resort').size() > 0) {
        $('#gallery_resort').royalSlider({
            fullscreen: {
                enabled: true,
                nativeFS: true
            },
            video: {
            },
            controlNavigation: 'thumbnails',
            autoScaleSlider: true,
            autoScaleSliderWidth: 400,
            autoScaleSliderHeight: 350,
            autoHeight: true,
            loop: true,
            imageScaleMode: 'fit',
            navigateByClick: true,
            numImagesToPreload: 2,
            arrowsNav: false,
            arrowsNavAutoHide: true,
            arrowsNavHideOnTouch: true,
            keyboardNavEnabled: true,
            fadeinLoadedSlide: true,
            globalCaption: true,
            globalCaptionInside: false,
            addActiveClass:true,
            touch: true,
            thumbs: {
                autoCenter: true,
                appendSpan: true,
                firstMargin: true,
                paddingBottom: 4,
                paddingTop: 4
            }
        });
    }
    /*-----------------------------------------------------------------------------------*/
    /* Scroll Magic
     /*-----------------------------------------------------------------------------------*/
    function createScrollMagic(element,animate,aclass){
        if ($('body').hasClass('home')){
            var controller = new ScrollMagic.Controller();
            var scene1 = new ScrollMagic.Scene({triggerElement: element})
              .setClassToggle(animate, aclass)
              .addTo(controller);
        }
    }
    createScrollMagic("#trigger1","#animate1","show1");
    createScrollMagic("#trigger2","#animate2","show2");
    createScrollMagic("#trigger3","#animate3","show3");
    /*-----------------------------------------------------------------------------------*/
    /* Sumo Select
     /*-----------------------------------------------------------------------------------*/
    function createSumoSelect(element){
        if((element).length > 0) {
            var sSelect = $(element).SumoSelect();
        }
    }
    var cSelect = $('#select_country').SumoSelect();
    var lSelect = $('#select_location').SumoSelect();
    createSumoSelect('select.dgt-select');
    /*-----------------------------------------------------------------------------------*/
    /* Location Select
     /*-----------------------------------------------------------------------------------*/
    $('#select_country').change(function(){
	    $('#select_location').prop('disabled', false);
	    $.get(gpx_base.url_ajax+'?action=gpx_newcountryregion_dd&country='+$(this).val(), function(data){
		    $('#select_location').html(data);
		    $('#select_location')[0].sumo.reload();
	    });
    });   
    $('.sumo_select_region .SelectBox .placeholder, .sumo_select_region .SelectBox i').click(function(){
	return false;
    });
    $('.submit-change').change(function(){
	  var location = $(this).val();
	  if($.isNumeric(location)) {
	      var country = $('#select_country').val();
	      window.location.href = '/resorts-result/?select_country='+country+'&select_region='+location;
	  }
    });
    /*-----------------------------------------------------------------------------------*/
    /* Result page main region drop down change (load month/year)
    /*-----------------------------------------------------------------------------------*/
    $('.result-page-form #select_location').change(function(){
	    var country = $('#select_country').val();
	    var region = $('#select_location').val();
	    $.get(gpx_base.url_ajax+'?action=gpx_monthyear_dd&country='+country+'&region='+region, function(data){
		    $('#select_monthyear').html(data);
		    $('#select_monthyear')[0].sumo.reload();
	    });	  
    });    
    /*-----------------------------------------------------------------------------------*/
    /* Result page main month/year drop down change (load content)
    /*-----------------------------------------------------------------------------------*/
    $('#select_monthyear').change(function(){
	var $form = $('#results-form').serialize()
	$.post(gpx_base.url_ajax+'?action=gpx_load_results_page_fn', $form, function(data){
	    $('#results-content').html(data.html); 
	});
    });
    
    /*-----------------------------------------------------------------------------------*/
    /* Acordeon Expand
     /*-----------------------------------------------------------------------------------*/
    function acordeonExpand(element,parent){
        var btn_item = $(element);
        var condition = false;
        btn_item.click(function(event) {
            event.preventDefault();
            $(this).addClass('activar');
            if(condition != true){
                $(this).addClass('activar');
                condition = true;
            }
            else{
                btn_item.removeClass('activar');
                condition = false;
            }
            $(this).parent().find(parent).stop(false).slideToggle();
        });
    }
    acordeonExpand('#expand_1 .title','.cnt-list');
    acordeonExpand('#expand_2 .title','.cnt-list');
    acordeonExpand('#expand_3 .title','.cnt-list');
    acordeonExpand('#expand_4 .title','.cnt-list');


    $.ui.autocomplete.prototype.options.autoSelect = true;
    $(".ui-autocomplete-input").change(function (event) {
        var autocomplete = $(this).data("uiAutocomplete");

        if (!autocomplete.options.autoSelect || autocomplete.selectedItem) { return; }

        var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i");
        autocomplete.widget().children(".ui-menu-item").each(function () {
            var item = $(this).data("uiAutocompleteItem");
            if (matcher.test(item.label || item.value || item)) {
                autocomplete.selectedItem = item;
                return false;
            }
        });

        if (autocomplete.selectedItem) {
            autocomplete._trigger("select", event, { item: autocomplete.selectedItem });
        }
    });
    
    
    /*-----------------------------------------------------------------------------------*/
    /* Autocomplete filter
     /*-----------------------------------------------------------------------------------*/

    $( "#resort_autocomplete" ).autocomplete({
        source: gpx_base.url_ajax+'?action=gpx_autocomplete_resort',
        minLength: 0,
    }).focus(function () {
        $(this).autocomplete("search");
    });

     /*
      * custom render filters the results by resort and region
      */      
        var customRenderMenu = function(ul, items){
            var self = this;
            var categoryArr = [];
            
            function contain(item, array) {
                var contains = false;
                $.each(array, function (index, value) {
                    if (item == value) {
                        contains = true;
                        return false;
                    }
                });
                return contains;
            }
            
            $.each(items, function (index, item) {
                if (! contain(item.category, categoryArr)) {
                    categoryArr.push(item.category);
                }
//                console.log(categoryArr);
            });
            
            $.each(categoryArr, function (index, category) {
                ul.append("<li> -- " + category + " -- </li>");
                $.each(items, function (index, item) {
                    if (item.category == category) {
                        self._renderItemData(ul, item);
                    }
                });
            });
        };
            
    
    $( "#location_autocomplete, .location_autocomplete" ).autocomplete({
        source: gpx_base.url_ajax+'?action=gpx_autocomplete_location',
        minLength: 0,
        autoFocus: true,
        create: function () {
//            $(this).data('uiAutocomplete')._renderMenu = customRenderMenu;
        },
//        open: function (event, ui) {
//            var menu = $(this).data("uiAutocomplete").menu
//                , i = 0
//                , $items = $('li', menu.element)
//                , item
//                , text
//                , startsWith = new RegExp("^" + this.value, "i");
//
//            for (; i < $items.length && !item; i++) {
//                text = $items.eq(i).text();
//                if(text.startsWith('--')) {
//                	//skip this one
//                } else {
//                	item =  $items.eq(i);
//                	break;
//                }
////                if (startsWith.test(text)) {
////                    item = $items.eq(i);
////                }
//            }
//
//            if (item) {
//                menu.focus(null, item);
//            }
//        }
    }).focus(function () {
        $(this).autocomplete("search");
    });
    
    $( "#universal_sw_autocomplete" ).autocomplete({
	source: gpx_base.url_ajax+'?action=gpx_autocomplete_usw',
	minLength: 0,
	autoFocus: true,
	create: function () {
//            $(this).data('uiAutocomplete')._renderMenu = customRenderMenu;
	},
//        open: function (event, ui) {
//            var menu = $(this).data("uiAutocomplete").menu
//                , i = 0
//                , $items = $('li', menu.element)
//                , item
//                , text
//                , startsWith = new RegExp("^" + this.value, "i");
//
//            for (; i < $items.length && !item; i++) {
//                text = $items.eq(i).text();
//                if(text.startsWith('--')) {
//                	//skip this one
//                } else {
//                	item =  $items.eq(i);
//                	break;
//                }
////                if (startsWith.test(text)) {
////                    item = $items.eq(i);
////                }
//            }
//
//            if (item) {
//                menu.focus(null, item);
//            }
//        }
    }).focus(function () {
	$(this).autocomplete("search");
    });    
    
    $( ".location_autocomplete_cr_region" ).autocomplete({
	source: gpx_base.url_ajax+'?action=gpx_autocomplete_sr_location',
	minLength: 0,
	change: function (event, ui) {
            if(!ui.item){
                $(".location_autocomplete_cr_region").val("");
                $('.region-ac-error').show();
            }

        },
        focus: function(){
            $('.city-ac-error, .region-ac-error, .resort-ac-error').hide();
        }
    }).focus(function () {
	$(this).autocomplete("search");
    });
    $( ".location_autocomplete_sub" ).autocomplete({
	source: function( request, response ) {
	    var $region = $('.autocomplete-region').val();
	    $.ajax({
		url: gpx_base.url_ajax,
		method: 'GET',
		data: {
		    term: request.term,
		    action: 'gpx_autocomplete_location_sub',
		    region: $region,
		},
	        success: function( data ) {
	              response( data );
	         }
	    });
	},
	minLength: 0,
	change: function (event, ui) {
            if(!ui.item){
                $(".location_autocomplete_sub").val("");
                $('.city-ac-error').show();
            }
        },
        focus: function(){
            $('.city-ac-error, .region-ac-error, .resort-ac-error').hide();
        }
    }).focus(function () {
	$(this).autocomplete("search");
    });
    $( ".location_autocomplete_resort" ).autocomplete({
	source: function( request, response ) {
	    var $region = $('.location_autocomplete_sub').val();
	    if($region == '' || $region == 'undefined'){
		$region = $('.autocomplete-region').val();
	    }
	    $.ajax({
		url: gpx_base.url_ajax,
		method: 'GET',
		data: {
		    term: request.term,
		    action: 'gpx_autocomplete_location_resort',
		    region: $region,
		},
		success: function( data ) {
		    response( data );
		}
	    });
	},
	minLength: 0,
	change: function (event, ui) {
            if(!ui.item){
                $(".location_autocomplete_resort").val("");
                $('.resort-ac-error').show();
            }

        },
        focus: function(){
            $('.city-ac-error, .resort-ac-error, .region-ac-error').hide();
        }
    }).focus(function () {
	$(this).autocomplete("search");
    });
    $('#location_autocomplete, #universal_sw_autocomplete').on('keypress', function(e){
	if(e.which == 13) {
	    $('#ui-id-1 .ui-menu-item').first().trigger('click');
	}
    });

    $('.miles_container').hide();
    $('.cr-for-miles').blur(function(){
	crShowMiles();
    });
    function crShowMiles()
    {
	if($('.location_autocomplete_resort').val()) {
	    $('.miles_container').hide();
	    $('#miles').prop('value', '');
	    $('.crResort').prop('disabled', false);
	    $('.location_autocomplete_cr_region').prop('disabled', true);
	    $('.crLocality').prop('disabled', true);
	}else{
	    $('.crResort').prop('disabled', false);
	    $('.location_autocomplete_cr_region').prop('disabled', false);
	    $('.crLocality').prop('disabled', false);
	}
	if($('.location_autocomplete_sub').val()){
	    var loc = $('.location_autocomplete_sub').val();
            $.ajax({
                url: gpx_base.url_ajax,
                type: 'post',
                data: {action: 'gpx_get_location_coordinates', region: loc },
                success: function(data) {
                    if(data.success) {
                	$('.miles_container').show();
                    } else {
                	$('.miles_container').hide();
                    }
                },
                error: function(xhr, desc, err) {
                }
            });
	    $('.crResort').prop('disabled', true);
	    $('.location_autocomplete_cr_region').prop('disabled', false);
	    $('.crLocality').prop('disabled', false);
	    return true;
	}else{
	    $('.crResort').prop('disabled', false);
	    $('.location_autocomplete_cr_region').prop('disabled', false);
	    $('.crLocality').prop('disabled', false);
	    return false;
	}
    }
    /*-----------------------------------------------------------------------------------*/
    /* See more items / Home
     /*-----------------------------------------------------------------------------------*/
    function seemoreItems(element, parent, clone){
        $(element).click(function(event){
            event.preventDefault();
            $.ajax({
                url: gpx_base.url_ajax,
                type: 'post',
                data: {action: 'gpx_load_more', type: clone },
                success: function(data, status) {
                    $(parent).append(data);
                },
                error: function(xhr, desc, err) {
                }
            });
        });
    }
   // seemoreItems('#filter-home','.w-featured .w-list',1);
    seemoreItems('#filter-result','#gpx-listing-result',2);
    seemoreItems('#filter-resort','.w-list.w-list-items',3);
    /*******************************************************************/
    function seeMoreText(container,element){
	
        var text = $(container),
          btn = $(element);
        $(container).each(function(){
            if($(this).data('height')) {
        	h = $(this).data('height');
            }else {
        	h = $(this).prop('scrollHeight')+20;
            }
            console.log(h);
            $(this).attr('data-height', h);
            if(h > 110) {
                btn.addClass('less');
            }else{
                btn.addClass('hidden');
            }
        });
        btn.click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            h = text.data('height');
            if ( $(this).hasClass('less')) {
                btn.removeClass('less');
                $(this).addClass('more');
                $(this).find('span').text('See less');
                text.animate({'height': h});
            } else {
                btn.addClass('less');
                btn.removeClass('more');
                btn.find('span').text('See more');
                text.animate({'height': '110px'});
            }
        });
    }
    seeMoreText('.item-tab-cnt','.content-tabs .seemore');
    seeMoreText('#expand_item_1 .cnt-expand','#expand_item_1 .cnt-seemore');
    seeMoreText('#expand_item_2 .cnt-expand','#expand_item_2 .cnt-seemore');
    
    
    /*-----------------------------------------------------------------------------------*/
    /* Responsive Menu
     /*-----------------------------------------------------------------------------------*/
    var newmobilemenu = $('.nav-list').clone().appendTo('body').insertAfter('#footer').addClass('menu-responsive').removeClass('nav-list');
    $(newmobilemenu).attr('id', 'mobile-'+$(newmobilemenu).attr('id'));
    $(newmobilemenu).find('li').each(function(){
	var oldid = $(this).attr('id');
	var newid = 'mobile-'+oldid;
	$(this).attr('id', newid);
    });
    $('.menu-mobile').click(function(event){
        event.preventDefault();
        $(this).addClass('active-menu-mobile');
        $('.menu-mobile-close').addClass('active-menu-mobile-close');
        $('.menu-responsive').addClass('active-menu');
        $('.r-overlay').addClass('active-overlay');
        $('.cnt-wrapper').addClass('active-cnt-wrapper');
        $('.footer').addClass('active-footer');
    });
    /*-----------------------------------------------------------------------------------*/
    /* Tabs Content
     /*-----------------------------------------------------------------------------------*/
    $('.head-tab ul li a').click(function(event){
        event.preventDefault();
        $('.tabs a').removeClass('head-active');
        $(this).addClass('head-active');
        var id = $(this).data('id');
        $('.content-tabs .item-tab').removeClass('tab-active');
        $('#'+id).addClass('tab-active');
    });
    /*-----------------------------------------------------------------------------------*/
    /* ScrollTop
     /*-----------------------------------------------------------------------------------*/
    $('.scrolltop').click(function(event) {
        $('html, body').animate({scrollTop: 0}, 900);
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
    /*-----------------------------------------------------------------------------------*/
    /* Modal close
     /*-----------------------------------------------------------------------------------*/
    $('.w-status .close').click(function(event){
        event.preventDefault();
        var $this = $(this);
        var $modal = $this.closest('.w-item-view');
        $modal.addClass('remove-modal');
    });
    /*-----------------------------------------------------------------------------------*/
    /* Phone Alert / Active alert only Home
     /*-----------------------------------------------------------------------------------*/
//    $('.phone').click(function(event){
//        event.preventDefault();
//        active_modal( modal_alert );
//    });
    if( $('body').hasClass('home') ){
        $('#modal-alert').addClass('active-modal');
    }
    
    /*-----------------------------------------------------------------------------------*/
    /* Responsive menu Sub nivel
     /*-----------------------------------------------------------------------------------*/
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
    function cerrar_nav() {
        $('.menu-responsive').removeClass('active-menu');
        $('.r-overlay').removeClass('active-overlay');
        $('.menu-mobile-close').removeClass('active-menu-mobile-close');
        $('.menu-mobile').removeClass('active-menu-mobile');
        $('.cnt-wrapper').removeClass('active-cnt-wrapper');
        $('.footer').removeClass('active-footer');
    };
    $('.w-nav').on('click', '.menu-mobile-close', function(event) {
        event.preventDefault();
        cerrar_nav();
        cerrar_submenu();
    });
    $('.r-overlay').click(function() {
        cerrar_nav();
        cerrar_submenu();
    });
    /*-----------------------------------------------------------------------------------*/
    /* Show and Close modal
     /*-----------------------------------------------------------------------------------*/
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
    $('.call-modal-pwreset').click(function(event){
	event.preventDefault();
	active_modal( modal_pwreset );
    });
    if($('#signInError').length){
	active_modal( modal_login );
    }
    $('.call-modal-filter').click(function(event){
        event.preventDefault();
        active_modal( modal_filter );
    });
    $('.call-modal-filter-resort').click(function(event){
        event.preventDefault();
        active_modal( modal_filter_resort );
    });
    $('.call-modal-add-interval').click(function(event){
        event.preventDefault();
        active_modal( '#modal-interval' );
    });
    $('.call-modal-edit-profile').click(function(event){
	event.preventDefault();
	active_modal( '#modal-profile' );
    });
    $('html body').on('click', '.call-modal-edit-profile', function(event){
	event.preventDefault();
	active_modal( '#modal-profile' );
    });
    if($('#modal-autocoupon').length) {
	active_modal('#modal-autocoupon');
    }
    
    /*-----------------------------------------------------------------------------------*/
    /* Show and Close modal
     /*-----------------------------------------------------------------------------------*/
    setTimeout(function() {
        function calculate_progressbar_value(){
            var progressbar_select = $('.w-progress-line span.select');
            var progressbar_book = $('.w-progress-line span.book');
            var progressbar_pay = $('.w-progress-line span.pay');
            var progressbar_confirm = $('.w-progress-line span.confirm');
            switch (true) {
                case (progressbar_select.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 21%');
                    break;
                case (progressbar_book.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 41%');
                    break;
                case (progressbar_pay.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 61%');
                    break;
                case (progressbar_confirm.hasClass('active')):
                    $('.w-progress-line .line .progress').css('width', ' 100%');
                    break;
            }
        }
        calculate_progressbar_value();
    }, 750);
    /*-----------------------------------------------------------------------------------*/
    /* Check validation next page
     /*-----------------------------------------------------------------------------------*/
    $('#next-1').click(function(event){
        event.preventDefault();
        var $this = $(this);
        if($(this).hasClass('gpx-disabled')) {
            active_modal('#modal-hold-alert');
            return false;
        }
        if($('#chk_terms').is(':checked')){
            $($this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
            //hold the property
            var pid = $('#ajaxinfo').data('pid');
            var cid = $('#ajaxinfo').data('cid');
            var type = $('#ajaxinfo').data('type');
            var lpid = $('#ajaxinfo').data('lpid');
            var wid = $('#ajaxinfo').data('wid');
            var id = $(this).data('id');
            var bookingrequest = 'true';
            $.get('/wp-admin/admin-ajax.php?action=gpx_hold_property&pid='+pid+'&weekType='+type+'&cid='+cid+'&lpid='+lpid+'&wid='+wid+'&bookingrequest='+bookingrequest, function(data){
        	if(data.msg == 'Success') {
                    $('html, body').animate({scrollTop: 0}, 900);
                    $('.booking').removeClass('booking-active');
                    $('#'+id).addClass('booking-active');
        	}else {
        		if(data.inactive){
            	    $('#alertMsg').html(data.msg);
            	    active_modal('#modal-hold-alert');
            	    $.get('/wp-admin/admin-ajax.php?action=gpx_remove_from_cart&pid='+pid+'&cid='+cid, function(data){});
            	        setTimeout(function(){
            		window.location.href = '/';	
            	    }, 3000);
        		}else{
        			if(data.login) {
                		active_modal( modal_login );
                	    }else {
                		if(data.error ==  'memberno'){
                		   $('#alertMsg').html('<span class="hold-msg">You are not logged in as an owner.<br><a class="dgt-btn active book-btn" href="/wp-admin?page=gpx-admin-page&gpx-pg=users_switch">Switch Owner</a></span>');
                			   active_modal('#modal-hold-alert'); 
                		}else{
                		    $('.hold-error').html(data.msg);
                		}
        		}
        	    
        	    }
        	    
        	}
        	$($this).find('.fa-refresh').remove();
            });
        }else{
            $(this).closest('.check').addClass('error');
        }
    });
//    $('html body').on('click', '.hold-confirm', function(e){
//	e.preventDefault();
//	var $link = $(this).attr('href');
//	$('#alertMsg').html('Are you sure you want to continue booking? Clicking <a href="'+$link+'">"Continue"</a> will release this hold in order to place it into your cart<br /><br /><a href="'+$link+'">Continue</a>');
//	active_modal('#modal-hold-alert'); 
//    });
//    $('html body').on('click', '.book-btn', function(e){
//	if($(this).hasClass('booking-disabled')) {
//	    var $msg = $('#bookingDisabledMessage').data('msg');
//	    $('#alertMsg').html($msg);
//   	    active_modal('#modal-hold-alert'); 
//   	    e.preventDefault();
//	    return false;
//	}
////	if($(this).hasClass('week-held')) {
////	    $('#alertMsg').html('Are you sure you want to continue booking? Clicking "Book" will release this hold in order to place it into your cart<br/>');
////	    $(this).clone().removeClass('week-held').appendTo('#alertMsg');
////   	    e.preventDefault();
////	    return false;
////	}
//	var lpid = $(this).data('lpid');
//	if(lpid != '') { //set the cookie for this week
//	    Cookies.set('lppromoid'+lpid, lpid);
//	    var cid = $(this).data('cid');
//	    //also store this in the database
//	    $.post('/wp-admin/admin-ajax.php?action=gpx_lpid_cookie', {lpid: lpid, cid: cid}, function(){
//		
//	    });
//	}
//    });
//    $('html body').on('click', '.hold-btn', function(e){
//	if($(this).hasClass('booking-disabled')) {
//	    var $msg = $('#bookingDisabledMessage').data('msg');
//	    $('#alertMsg').html($msg);
//   	    active_modal('#modal-hold-alert'); 
//	    return false;
//	}
//	e.preventDefault();
//	var $this = $(this);
//	var wid = $(this).data('wid');
//	var pid = $(this).data('pid');
//	var cid = $(this).data('cid');
//	var lpid = $(this).data('lpid');
//	if(lpid != '') { //set the cookie for this week
//	    Cookies.set('lppromoid'+lpid, lpid);	    //also store this in the database
//	    $.post('/wp-admin/admin-ajax.php?action=gpx_lpid_cookie', {lpid: lpid, cid: cid}, function(){
//		
//	    });
//	}
//	$($this).find('i').show();
//	$.get('/wp-admin/admin-ajax.php?action=gpx_hold_property&pid='+pid+'&cid='+cid+'&wid='+wid+'&lpid='+lpid, function(data){
//	    $($this).find('i').hide();
//	   if(data.login) {
//	       active_modal( modal_login );
//	   } else {
//	       if(data.msg != 'Success') {
//		   $('#alertMsg').html(data.msg);
//       	    	   active_modal('#modal-hold-alert'); 
//           	}
//	       else {
//		   $('#alertMsg').html('<span class="hold-msg">This week has been placed on a hold for you for 24 hours, to retrieve your held week visit your Member Dashboard Profile under <a href="/view-profile" target="_blank" title="Held weeks can be viewed in your profile.">"My Held Weeks"</a></span>');
//		   active_modal('#modal-hold-alert'); 
//		   
//	       }
//	   }
//	});	
//    });
//    $('.html body').on('click', '.book-btn', function(e){
////    $('.book-btn').click(function(e){
//    	e.preventDefault();
//    	
//		if($(this).hasClass('booking-disabled')) {
//		    var $msg = $('#bookingDisabledMessage').data('msg');
//		    $('#alertMsg').html($msg);
//	   	    active_modal('#modal-hold-alert'); 
//	   	    e.preventDefault();
//		    return false;
//		}
//
//		
//		var link = $(this).attr('href');
//		console.log(link);
//		var wid = $(this).data('wid');
//		var pid = $(this).data('pid');
//		var cid = $(this).data('cid');
//		var type = $(this).data('type');
//		
//		/*
//		//how many credits does this user have
//		if(type == 'ExchangeWeek'){
//			$.get('/wp-admin/admin-ajax.php?action=get_booking_available_credits&cid='+cid, function(data){
//				if(data.disabled){
//				    $('#alertMsg').html(data.msg);
//			   	    active_modal('#modal-hold-alert'); 
//			   	    e.preventDefault();
//				    return false;
//				}
//			});			
//		}
//		*/
//		
//		Cookies.set('exchange_bonus', type);
//		var form = $('#home-search').serialize();
//		form = form+"&wid="+wid+"&pid="+pid+"&cid="+cid;
//		$.post('/wp-admin/admin-ajax.php?action=gpx_book_link_savesearch',form, function(data){
//		    location.href = link;
//		});
//    });
    if($('.booking-disabled-check').length) {
	    var $msg = $('#bookingDisabledMessage').data('msg');
	    $('#alertMsg').html($msg);
	    active_modal('#modal-hold-alert'); 
	    setTimeout(function(){
		window.location.href = '/';	
	    }, 3000);
    }
    $('.resort-btn').click(function(e){
	e.preventDefault();
	var link = $(this).attr('href');
	var rid = $(this).data('rid');
	var cid = $(this).data('cid');
	var form = $('#home-search').serialize();
	form = form+"&rid="+rid+"&cid="+cid;
	$.post('/wp-admin/admin-ajax.php?action=gpx_resort_link_savesearch',form, function(data){
		location.href = link;
	});
    });
    if($('.checklogin').length) {
	$.get('/wp-admin/admin-ajax.php?action=gpx_check_login', function(data){
	   if(data.login) {
	       active_modal( modal_login );
	   } 
	});
    }
    if($('.checkhold').length) {
    	
	var pid = $('.checkhold').data('pid');
	var cid = $('.checkhold').data('cid');
	var type = $('.checkhold').data('type');
    if(pid == '') {
    	return true;
    }
        $.get('/wp-admin/admin-ajax.php?action=gpx_hold_property&pid='+pid+'&weekType='+type+'&cid='+cid, function(data){
        	if(data.msg != 'Success') {
        	    $('#alertMsg').html(data.msg);
        	    active_modal('#modal-hold-alert');
        	    $.get('/wp-admin/admin-ajax.php?action=gpx_remove_from_cart&pid='+pid+'&cid='+cid, function(data){});
        	        setTimeout(function(){
        		window.location.href = '/';	
        	    }, 3000);
        	}
        });
    }
//    $('html body').on('change', '#00N5B000000ai6e, #00N5B000000ajny, #00N5B000000ajo8', function(){
//	var checkin1 = new DATE($('#00N5B000000ai6e').val());
//	var checkin2 = new DATE($('#00N5B000000ajny').val());
//	var checkin3 = new DATE($('#00N5B000000ajo8').val());
//	if((checkin2 < checkin1) || (checkin3 < checkin2) || (checkin3 < checkin1)) {
//		$('#alertMsg').html('Check in dates must be in sequential order.<br><a href="#modal-custom-request" class="better-modal-link">Try Again</a>');
//		 active_modal('#modal-hold-alert'); 
//	}
//    });
    var crmindate = new Date();
    var crmaxdate = crmindate.getDate() + 547;
    $(".crrangepicker").daterangepicker({
	     presetRanges: [{
	         text: 'Today',
	         dateStart: function() { return moment() },
	         dateEnd: function() { return moment() }
	     },{
	         text: 'This Month',
	         dateStart: function() { return moment() },
	         dateEnd: function() { return moment().add('months', 1) }
	     }, {
	         text: 'Next Month',
	         dateStart: function() { return moment().add('months', 1) },
	         dateEnd: function() { return moment().add('months', 2) }
	     }],
	     applyOnMenuSelect: false,
	     datepickerOptions: {
	         minDate: crmindate,
	         maxDate: crmaxdate
	     },
	     dateFormat: 'mm/dd/yy',
	     change: function(){ 
		 checkRestricted($('#00N40000003DG5P')) },
});
    $('#00N40000003S58X, #00N40000003DG5S, #00N40000003DG59, #miles').blur(function(){
	checkRestricted($(this));
    });
    $('#00N40000003S58X, #00N40000003DG5S, #00N40000003DG59').blur(function(){
        $('#00N40000003S58X, #00N40000003DG5S, #00N40000003DG59').removeAttr('required');
        $(this).prop('required', true);
    });
    function checkRestricted($this) {
    	var form = $($this).closest('#customRequestForm').serialize();
    	
    	$.post('/wp-admin/admin-ajax.php?action=custom_request_validate_restrictions', form, function(data){
    	    if(data.restricted) {
    	    	$('#restrictedTC').addClass('hasRestricted');
    		if(data.restricted == 'All Restricted') {
    		    $('button.submit-custom-request').addClass('gpx-disabled');
    		}else{
    		    $('button.submit-custom-request').removeClass('gpx-disabled');
    		}
    	    }else{
    	    	$('#restrictedTC').addClass('hasRestricted');
    		    $('button.submit-custom-request').removeClass('gpx-disabled');
    		}
    	});
    }
    $('html body').on('submit', '#customRequestForm', function(e){
    e.preventDefault();
    var error = '';
	if($(this).find('button.submit-custom-request').hasClass('gpx-disabled')) {
	    //do nothing we don't want this form to be submitted.
	}else{
        	var email = $('#00N40000003DG50').val();
        	if( !isValidEmailAddress( email ) ) {
                error = 'Please enter a valid email address.';              
            }

            if($('#00N40000003DG59').val().length == 0 && $('#00N40000003S58X').val().length == 0 && $('#00N40000003DG5S').val().length == 0) {
                error = 'Please select a location.';
            }

            $('#crError').text('Form Submitted');
            
        	if( error == '' ) {
        		var form = $(this).serialize();
                $.ajax({
                    url: '/wp-admin/admin-ajax.php?action=gpx_post_custom_request',
                    type: 'post',
                    data: form,
                    success: function(data) {
             		   console.log('hmm');
            		   console.log(data);
            			if(data.success) {
            			if(data.matched) {
            			    var url = '/result?matched='+data.matched;
            			    $('#matchedTravelButton').attr('href', url);
            			    $('#notMatchedModal, #restrictedMatchModal').appendTo('#matchedContainer');
            			    $('#alertMsg').html($('#matchedModal'));
            			}
            			else {
            			    $('#matchedModal, #restrictedMatchModal').appendTo('#matchedContainer');
            			    $('#alertMsg').html($('#notMatchedModal')); 
            			}
            			if(data.restricted) {
            			    //move the not matched message back becuase this search was only within the restricted time/area
            			    if(data.restricted == 'All Restricted') {
            				$('#notMatchedModal').appendTo('#matchedContainer');
            			    }
            			    $('#restrictedMatchModal').appendTo('#alertMsg');
            			}
            			if(data.holderror) {
            				$('#notMatchedModal').appendTo('#matchedContainer');
            				$('#alertMsg').text(data.holderror);
            			}
            			$('.icon-alert').remove();
            			console.log(data);
            			active_modal('#modal-hold-alert');
            		    }
                    },
                    error: function(xhr, desc, err) {
                    }
                });
        	} else {
                $('#crError').text(error);
            }
	}
    });
    $('html body').on('click', '.resend-confirmation', function(e){
	e.preventDefault();
	var $this= $(this);
	var weekid = $(this).data('weekid');
	var memberno = $(this).data('memberno');
	var resortname = $(this).data('resortname');
	$($this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
        $.get('/wp-admin/admin-ajax.php?action=gpx_resend_confirmation&weekid='+weekid+'&memberno='+memberno+'&resortname='+resortname, function(data){
        	if(data.msg) {
        	    $('#alertMsg').html(data.msg);
        	    active_modal('#modal-hold-alert');
            }
        $($this).find('.fa-refresh').remove();
        });
    });
    function base64ToArrayBuffer(base64) {
        var binaryString = window.atob(base64);
        var binaryLen = binaryString.length;
        var bytes = new Uint8Array(binaryLen);
        for (var i = 0; i < binaryLen; i++) {
           var ascii = binaryString.charCodeAt(i);
           bytes[i] = ascii;
        }
        return bytes;
     }
    function saveByteArray(reportName, byte) {
	      var blob = new Blob([byte]);
	      var link = document.createElement('a');
	      link.href = window.URL.createObjectURL(blob);
	      var timeNow = new Date();
	      var month = timeNow.getMonth() + 1;
	      var fileName = reportName + ".pdf";
	      link.download = fileName;
	      link.click();
	  };
	  $('#removeCoupon').click(function(e){
		  e.preventDefault();
		  var cid = $(this).data('cid');
		  var cartID = $(this).data('cartid');
		  $.post('/wp-admin/admin-ajax.php?action=gpx_remove_coupon',{cid:cid,cartID:cartID}, function(){
			  location.reload(); 
		  });
	  });
    $('#removeOwnerCreditCoupon').click(function(e){
	e.preventDefault();
	var cid = $(this).data('cid');
	var cartID = $(this).data('cartid');
	$.post('/wp-admin/admin-ajax.php?action=gpx_remove_owner_credit_coupon',{cid:cid,cartID:cartID}, function(){
	   location.reload(); 
	});
    });
    $('#removeCPO').click(function(e){
	e.preventDefault();
	var cid = $(this).data('cid');
	var cartID = $(this).data('cartid');
	$.post('/wp-admin/admin-ajax.php?action=gpx_cpo_adjust',{cid:cid,cartID:cartID}, function(){
	    location.reload(); 
	});
    });
    $('.removeIndCPO').click(function(e){
	e.preventDefault();
	var cid = $(this).data('cid');
	var cartID = $(this).data('cartid');
	var propertyID = $(this).data('propid');
	$.post('/wp-admin/admin-ajax.php?action=gpx_cpo_adjust',{cid:cid,cartID:cartID,propertyID:propertyID}, function(){
	    location.reload(); 
	});
    });
    $('.addIndCPO').click(function(e){
	e.preventDefault();
	var cid = $(this).data('cid');
	var cartID = $(this).data('cartid');
	var propertyID = $(this).data('propid');
	$.post('/wp-admin/admin-ajax.php?action=gpx_cpo_adjust',{cid:cid,cartID:cartID,propertyID:propertyID,add:'add cpo'}, function(){
	    location.reload(); 
	});
    });

    $('html body').on('click', '.remove-hold', function(e){
	e.preventDefault();
	var pid = $(this).data('pid');
	var cid = $(this).data('cid');
	var el = $(this);
	var bp = $(this).data('bookingpath');
	var redirect;
	var nocart = '';
	if(bp == 1) {
	    redirect = $(this).data('redirect');
	} else {
	    nocart = '&nocart=1';
	}
	$(this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
	$.get('/wp-admin/admin-ajax.php?action=gpx_remove_from_cart&pid='+pid+'&cid='+cid+nocart, function(data){
	    if(bp == 1) {
		window.location.href = redirect;
	    } else {
	        $(el).closest('tr').remove();
	    }
	});
    });
    $('.check input').change(function(){
	var $input = $(this);
	var $check = $(this).closest('.check');
	if($($check).hasClass('error')) {
	    if($($input).is(':checked')) {
		$($check).removeClass('error');
	    }
	}
    });
    $('#rdb-reservation').change(function(){
	$(this).closest('.head-form').find('label').removeClass('filled');
	var formReplace = '';
	if($(this).is(':checked')) {
	    active_modal('#modal-guest-fees');
	    $(this).closest('form').find('#GuestFeeAmount').val('1');
	    $(this).closest('form').find('.guest-reset').val('');
//	    formReplace = $('#gifReplace').html();
//	    $('#savedForm').html(formReplace);
	    //$('form.material').materialForm();
	    $(this).closest('form').find('.material-input input').each(function(){
		if($(this).val().length) {
		    $(this).addClass('filled');
		}
	    });
	}
	else {
	    $(this).closest('form').find('#GuestFeeAmount').val('');
//	    $('#gifReplace').html($('#savedForm').html());
	    $('.guest-reset').each(function(){
		$(this).val($(this).data('default'));
	    });
	    //$('form.material').materialForm();
	    $(this).closest('form').find('.material-input input').each(function(){
		if($(this).val().length) {
		    $(this).addClass('filled');
		}
	    });
	}
	
    });
    $('.guest-fee-cancel').click(function(){
	    if($('#rdb-reservation').is(':checked')) {
		$('#rdb-reservation').trigger('click');
	    }
	    close_modal($(this))
	    return false;
    });
    $('.guest-fee-confirm').click(function(){
	close_modal($(this));
	return false;
    });

    $('.list-form.guest-form-data #FirstName1, .list-form.guest-form-data #LastName1').focus(function(){
	    if($('#modal-guest-fees').length) {
        	    if(!$('#rdb-reservation').is(':checked')) {
        		$('#rdb-reservation').trigger('click');
        	    }
	    }
    });
    $('.validate-int').keyup(function(e){
	var $this = $(this);
        if (/\D/g.test(this.value))
        {
            // Filter non-digits from input value.
            this.value = this.value.replace(/\D/g, '');
        }
        var total = 0;
        var max = $(this).data('max');
        $('.validate-int').each(function(){
            total += parseInt($(this).val());
            if(total > max){
		    $('#alertMsg').html('The number of guests cannot be more than the maximum occupancy of '+max+'.');
		    active_modal('#modal-hold-alert');
		    $($this).val('');
            }
        });
    });
    $('#email.validate').blur(function(){
    	var valemail = $(this).val();
    	if(!isEmail(valemail)) {
    		$('#alertMsg').html('Please enter a valid email address.');
		    active_modal('#modal-hold-alert');
    	}
    		
    });
    function isEmail(email) {
    	  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    	  return regex.test(email);
    	}
    $('html body').on('click', '.toggleElement', function(e){
	e.preventDefault();
	var link = $(this).attr('href');
	$(link).toggle();
    });
    $('html body').on('click', '.add-fee-to-cart', function(){
    	//add the fee to the form
    	var agentskip = $(this).data('skip');
    	if(agentskip != 'Yes'){
    		$('#exchangendeposit').prepend('<input type="hidden" name="add_to_cart" value="1" />');
    	}
    	else {
    		$('#exchangendeposit').prepend('<input type="hidden" name="add_to_cart" value="2" />');
    	}
    	
    	//click the button again
    	$('.submit-guestInfo').trigger('click');
    });
    $('html body').on('click', '.add-fee-to-cart-direct', function(){
    	//add the fee to the form
    	var $this = $(this);
    	$($this).attr('disabled', true);
    	var agentskip = $(this).data('skip');
    	var amt = $(this).data('fee');
    	var tid = $(this).data('tid');
    	var type = $(this).data('type');
    	$.post('/wp-admin/admin-ajax.php?action=gpx_add_fee_to_cart', {type: type, fee: amt, skip: agentskip, tempID: tid}, function(data){
    		if(data.redirect) {
    			Cookies.set('gpx-cart', data.cartid);
    			window.location.href = '/booking-path-payment';
    		}else{
    			$('#alertMsg').html(data.message);
    			active_modal('#modal-hold-alert');
    		}
    	});
    });

    
    
    $('.submit-guestInfo').click(function(e){
	e.preventDefault();

	var valemail = $('#email').val();
	if(!isEmail(valemail)) {
		$('#alertMsg').html('Please enter a valid email address.');
	    active_modal('#modal-hold-alert');
	    return false;
	}
	if($(this).hasClass('disabled')){
		return false;
	}
	var $this = $(this);
	var $error = '';
	var $field = '';
	var adults = $('#adults').val();
	if(adults == '0') {
		$field = $('adults');
		$error = 'At least one adults is required.';
	}
	$($this).closest('form').find('input').each(function(){
	   if($(this).prop('required')) {
	       if(!$(this).val() && $(this).attr('name')) {
		   $field = $(this).attr('name');
		   $error = 'Please complete all required fields --  missing '+$field+'!'; 
	       }
	   }
	       
	});
	var acvalid = '';
//	$('#adultChildValidate .material-select').each(function(){
//	     acvalid = $(this).find('label span').text();
//	     if(acvalid == '')
//		 $error = 'Please select adults and children!'; 
//	});
	var children = $('#children').val();
	
	var $set = '';
	if($error == '') {
		$(this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
		var link = $(this).attr('href');
		var form = $('#guestInfoForm').serialize();
		if($('.exchange-credit-check').length) {
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
		    form = form + '&creditweekid='+creditweekid+'&creditvalue='+creditvalue+'&creditextensionfee='+creditextensionfee;
		    if(creditweekid == 'deposit') {
			var creditdate = $('#exchangendeposit input[name="CheckINDate"]:not([disabled])').val();
			if(creditdate == ''){
			    $error = 'You must enter a check in date.';

			    $('#alertMsg').html($error);
			    active_modal('#modal-hold-alert');
			    $($this).find('.fa-refresh').remove();	
			    
			    return false;
			    
			}else{
        			$set = true;
        			var pid = $('#guestInfoForm').find('input[name="propertyID"]').val();
        			var depositform = $('#exchangendeposit').serialize();
        			depositform  = depositform + '&pid='+pid;
        			$.post('/wp-admin/admin-ajax.php?action=gpx_deposit_on_exchange',depositform, function(data){
        				form = form + '&deposit='+data.id;
        				if(data.paymentrequired){
        				    $('.payment-msg').text('');
        				    $('#checkout-amount').val(data.amount);
              			    $('#checkout-item').val(data.type);
              			    $('#modal_billing_submit').attr('href', link);
              			    $('#alertMsg').html(data.html);
              			    active_modal('#modal-hold-alert');
              			  $("html, body").animate({ scrollTop: 0 }, "slow");
        					$.post('/wp-admin/admin-ajax.php?action=gpx_save_guest',form, function(data){
            				    if(data.success) {
            				    	$($this).removeClass('submit-guestInfo');
            				    } 
            				    $($this).find('.fa-refresh').remove();
            				});
        				}else{
        					$.post('/wp-admin/admin-ajax.php?action=gpx_save_guest',form, function(data){
            				    if(data.success) {
            				    	window.location.href='/booking-path-payment/';
            				    } 
            				    $($this).find('.fa-refresh').remove();
            				});
        				}
        			});	
			}
		    }
		   
		}
		if($error == '' && $set == ''){
			$.post('/wp-admin/admin-ajax.php?action=gpx_save_guest',form, function(data){
			    if(data.success) {
				window.location.href='booking-path-payment/';
			    } 
			    $($this).find('.fa-refresh').remove();
			});	
		}else {
		    if($error != '') {
			    $('#alertMsg').html($error);
			    active_modal('#modal-hold-alert');
			    $($this).find('.fa-refresh').remove();			
		    }
		}
	} else {
	    if($error != '') {
		    $('#alertMsg').html($error);
		    active_modal('#modal-hold-alert');
		    $($this).find('.fa-refresh').remove();		
	    }
	}

	
    });
//    $('.submit-guestInfo').click(function(e){
//	e.preventDefault();
//	var $this = $(this);
//	$(this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
//	var link = $(this).attr('href');
//	var form = $('#guestInfoForm').serialize();
//	if($('.exchange-credit-check').length) {
//	    var creditweekid = $('.exchange-credit-check:checked').data('creditweekid');
//	    var creditvalue = $('.exchange-credit-check:checked').val();
//	    form = form + '&creditweekid='+creditweekid+'&creditvalue='+creditvalue;
//	}
//	$.post('/wp-admin/admin-ajax.php?action=gpx_save_guest',form, function(data){
//	    if(data.success) {
//		window.location.href=link;
//	    } 
//	    $($this).find('.fa-refresh').remove();
//	});
//	
//    });
//    $('.submit-payment').click(function(e){
//		e.preventDefault();
//		if(!$(this).hasClass('submitted')) {
//	        	var $this = $(this);
//	        	var link = $(this).attr('href');
//	        	var form = $('#paymentForm').serialize();
//	        	$(this).append('<i class="fa fa-refresh fa-spin fa-fw"></i>');
//	        	$($this).addClass('submitted');
//	        	$.post('/wp-admin/admin-ajax.php?action=gpx_payment_submit', form, function(data){
//	        	    console.log(data);
//	        	   if(data.success) {
//	        	       console.log('silence');
//	        	       console.log(link);
//	        	       window.location.href=link;
//	        	   } 
//	        	   else {
//	        	       $('.payment-error').text(data.error);
//	        	       $($this).removeClass('submitted');
//	        	   }
//	        	   $($this).find('.fa-refresh').remove();
//	        	});
//		}
//    });
    $('html body').on('change', '.w-credit .head-credit .exchange-credit-check', function(){
        $('.w-credit .head-credit input[type="checkbox"]').not(this).prop('checked', false);
    });
    function active_exchange_credit(){
        $('.exchange-result').addClass('active-message');
        $('.exchange-credit hgroup').addClass('desactive-message');
        $('.exchange-credit .exchange-list').addClass('desactive-message');
    }
    /*-----------------------------------------------------------------------------------*/
    /* Material label focus
     /*-----------------------------------------------------------------------------------*/
    $('.material-input label').click(function(event){
        event.preventDefault();
        var $this = $(this);
        var $wrapper = $this.closest('.material-input');
        var $child = $wrapper.find('input');
        $child.focus();
    });
    /*-----------------------------------------------------------------------------------*/
    /* Resort search form -- redirect to resort page if resort name is used
     /*-----------------------------------------------------------------------------------*/    
    $('#resortsSearchForm').submit(function(){
	var resort = $('#resort_autocomplete').val();
	var country = $('#select_country').val();
	if(resort.length == 0) {
	    if(country == null) {
        	$('#alertMsg').html("Resort Name or Location are required!");
                active_modal('#modal-hold-alert');
                return false;
	    }
	    else {
		return true;
	    }
	    
	}
	else
	{
	    encoderesort = encodeURIComponent(resort);
	    window.location.href="/resort-profile/?resortName="+encoderesort;
	    return false;
	}
    });
    /*-----------------------------------------------------------------------------------*/
    /* Resort availability 
     /*-----------------------------------------------------------------------------------*/    
    $('.resort-availablity-view').hide();
    $('.resort-availability').click(function(e){
	e.preventDefault();
	var $this = $(this);
	var $thisi = $(this).find('i');
	var resortid = $(this).data('resortid');
	var rav = $(this).closest('.w-item-view').find('.resort-availablity-view');
	$(rav).toggle();
	$($thisi).toggleClass('fa-chevron-down fa-chevron-up');
	if(!$($this).hasClass('resort-availability-toggle')) {
	    	$(rav).find('.ra-loading').addClass('fa fa-refresh fa-spin');
		$.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability',{resortid: resortid}, function(data){
		    $(rav).find('.ra-content').html(data.html);
		    $(rav).find('.ra-loading').removeClass('fa fa-refresh fa-spin');
		    $($this).addClass('resort-availability-toggle');
		    $('.filter_resort_resorttype').trigger('change');
		});	    
	}
    });
    if($('#availability-cards').length) {
    	var resortid = $('#show-availability').data('resortid');
    	var month = $('#show-availability').data('month');
		var year = $('#show-availability').data('year');
		$.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability',{resortid: resortid, limitstart: 0, limitcount: 4, select_month: month, select_year: year}, function(data){
		    $('#availability-cards').html(data.html);
		});	
    }
//    $('.ice-link').click(function(){
//	var cid = $(this).data('cid');
//	
//	if(cid == 'undefined' || cid == '0' || cid == ''){
//		active_modal( modal_login );
//	}
//	else {
//		$.post('/wp-admin/admin-ajax.php?action=post_IceMemeber',{}, function(data){
//		    if(data.redirect) {
//			window.location.href = data.redirect;
//		    }
//		});	    
//	}
//	return false;
//    });
    $('html body').on('click', '.show-more-btn', function(e){
	e.preventDefault();
	var limitcount = '10000';
	var ww = $(window).width();
	if(ww < 768)
	    limitcount = $(this).data('next');
	var resortid = $('#show-availability').data('resortid');
	var month = $('#show-availability').data('month');
	var year = $('#show-availability').data('year');
	$.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability',{resortid: resortid, limitstart: 0, limitcount: limitcount, select_month: month, select_year: year}, function(data){
	    $('#availability-cards').html(data.html);
	});		
    });
//    $('#show-availability').click(function(e){
//	e.preventDefault();
//	var resortid = $('#show-availability').data('resortid');
//	$.post('/wp-admin/admin-ajax.php?action=gpx_resort_availability',{resortid: resortid, limitstart: 0, limitcount: 4}, function(data){
//	    $('#availability-cards').html(data.html);
//	});	
//    });
    /*-----------------------------------------------------------------------------------*/
    /* Resort availability calendar
     /*-----------------------------------------------------------------------------------*/    
    $('.resort-availablility').hide();
    if($('#cid').length){
	var cidset = $('#cid').data('cid');
    }
    var resort = $('#search-availability').data('resort');
	 var events = {
		 rentals: {
			    url: gpx_base.url_ajax+'?action=resort_availability_calendar&resort='+resort+'&weektype=BonusWeek' ,
			    type: 'POST',
			    color: '#EC8F09',
			    error: function() {
		        	    $('#alertMsg').html('There are not any available properties at this resort. <a href="#" class="dgt-btn active book-btn custom-request" data-pid="'+resort+'" data-cid="'+cidset+'">Submit Custom Request</a>');
		        	    active_modal('#modal-hold-alert'); 
			    } 		     
		 },
		 exchange: {
		     url: gpx_base.url_ajax+'?action=resort_availability_calendar&resort='+resort+'&weektype=ExchangeWeek' ,
		     type: 'POST',
		     color: '#8906D5',
		     error: function() {
			 $('#alertMsg').html('There are not any available properties at this resort. <a href="#" class="dgt-btn active book-btn custom-request" data-pid="'+resort+'" data-cid="'+cidset+'">Submit Custom Request</a>');
			 active_modal('#modal-hold-alert'); 
		     } 		     
		 },
	 };   
      $('.show-availabilty').click(function(e){
	  e.preventDefault();
	    $('html, body').animate({
	        scrollTop: $('#expand_4').offset().top-100
	    }, 1000);
	  $('#expand_4 .cnt-list, #availability-cards, .search-availability').show();
	  $('#expand_4 .show-availability-btn, .resort-availablility').hide();
	  
      });
      $('.search-availability').click(function(){
	 $('#expand_4 .cnt-list, .resort-availablility').show(); 
	 $('#expand_4 .search-availablity, #availability-cards').hide();
	 $('.cal-av-toggle').toggle();
	 $('#resort-calendar').fullCalendar({
		eventSources:  [events.rentals, events.exchange],
		    eventRender: function eventRender( event, element, view ) {
		        return ['All', event.bedrooms].indexOf($('#calendar-bedrooms').val()) >= 0 &&  ['All', event.weektype].indexOf($('#calendar-type').val()) >= 0
		    }
	 });
	    $('html, body').animate({
	        scrollTop: $('#resort-calendar-filter').offset().top-100
	    }, 1000);
	 return false;
      });
      $('html body').on('focus', '.emailvalidate', function(){
	 if(!$('#oldvalue').length) {
	     var oldval = $(this).val();
	     $(this).parent().append('<span id="oldvalue" data-val="'+oldval+'"></span>');
	 } 
      });
      $('html body').on('keyup', '.emailvalidate', function(){
	 $('.edit-profile-btn').prop('disabled', true).addClass('gpx-disabled');
	 var parent = $(this).parent();
	 if(!$('#emailValidateBtn').length)
	     $('<a href="#" id="emailValidateBtn">Validate Email</a>').insertAfter(parent);
      });
      $('.emailvalidate').blur(function(){
	 var email = $(this).val();
	 var oldval = $('#oldvalue').data('val');
	 $('#emailValidateBtn').remove();
	 if(email != oldval) {
        	 $.post('/wp-admin/admin-ajax.php?action=gpx_validate_email',{email: email}, function(data){
        	    if(data.error) {
        		$('.emailvalidate').val(oldval);
        		$('#alertMsg').html(data.error+'<br><a href="#" class="call-modal-edit-profile">Try Again</a>');
        		 active_modal('#modal-hold-alert'); 
        	    }
        	    $('.edit-profile-btn').prop('disabled', false).removeClass('gpx-disabled');
        	 });	     
	 }
	 else {
	     $('.edit-profile-btn').prop('disabled', false).removeClass('gpx-disabled');
	 }
      });
      $('html body').on('click', '#emailValidateBtn', function(e){
	 e.preventDefault(); 
      });
      $('html body').on('change', '#calendar-type', function(){
	  $('#resort-calendar').fullCalendar('rerenderEvents');
      });
      $('html body').on('change', '#calendar-bedrooms', function(){
	  $('#resort-calendar').fullCalendar('rerenderEvents');
      });   
      $('html body').on('change', '#calendar-month, #calendar-year', function(){
	  var date = new Date();
	  var month = $('#calendar-month').val();
	  var year = $('#calendar-year').val();
	  console.log(year);
	  if(month == null)
	  {
	      month = date.getMonth();
	  }
	  if(year == null)
	  {
	      year = date.getFullYear();
	  }	      
	  var date = year+'-'+month+'-01';
	  console.log(date);
	  $('#resort-calendar').fullCalendar('gotoDate', date);
      });   
      
    /*-----------------------------------------------------------------------------------*/
    /* Demo user login active
     /*-----------------------------------------------------------------------------------*/
    if( $('body').hasClass('active-session') ){
        $('.header .top-nav .access .call-modal-login').text('Sign out');
    }
    if($('#welcome_create').length) {
    	var wh = $('#welcome_create').data('wc');
    	
		$.get('/wp-admin/admin-ajax.php?action=get_username_modal', function(data){
			$('#form-login .gform_body').html(data.html);
			$('#form-login').append('<input type="hidden" name="wh" value="'+wh+'" />');
			$('#btn-signin').attr('value', 'Update');
			$('#btn-signin').removeClass('btn-user-login');
			$('input[name="action"]').attr('value', 'update_username');
			$('.call-modal-pwreset').hide();
			active_modal( modal_login );
		});
    }
    $('html body').on('submit', '#form-login, #form-login-footer', function(e){
		e.preventDefault();
		var thisform = $(this);
		
//    $("#form-login, #form-login-footer").submit(function() {
    	var btn = $(this).find('#btn-signin');
    	console.log(btn);
    	if($(this).find('#btn-signin').hasClass('btn-user-login')) {
	        grecaptcha.ready(function() {
	            grecaptcha.execute('6LfzhPIdAAAAALbGtjuaU7IX8xfD-dNxvGS0vjQM', {action: 'login'}).then(function(token) {
	                $(thisform).prepend('<input type="hidden" name="rec_token" value="' + token + '">');
	                $(thisform).prepend('<input type="hidden" name="rec_action" value="login">');
	            });;
	        });
	        $.ajax({
	            url: gpx_base.url_ajax,
	            type: "POST",
	            dataType: "json",
	            data: $(this).serialize(),
	            success: function(response) {
	                if(response.loggedin) {
	                	if(response.redirect_to == 'username_modal') {
	                		$.get('/wp-admin/admin-ajax.php?action=get_username_modal', function(data){
	                			$('#form-login .gform_body').html(data.html);
	                			$('#btn-signin').attr('value', 'Update');
	                			$('#btn-signin').removeClass('btn-user-login');
	                			$('input[name="action"]').attr('value', 'update_username');
	                			$('.call-modal-pwreset').hide();
	                			active_modal( modal_login );
	                		});
	                	}else{
	                		if(response.redirect_to == 'https://gpxvacations.com') {
//	                			alert(response.message);
	                			window.location.href = response.redirect_to;
	                		} else {
	                			window.location.href = response.redirect_to;
	                		}
	                		
	                	}
	                    
	                } else {
	                    $('.message-box span').html(response.message);
	                }
	            }
	        });
    	}else{
	
	        grecaptcha.ready(function() {
	            grecaptcha.execute('6LfzhPIdAAAAALbGtjuaU7IX8xfD-dNxvGS0vjQM', {action: 'password_reset'}).then(function(token) {
	                $(thisform).prepend('<input type="hidden" name="rec_token" value="' + token + '">');
	                $(thisform).prepend('<input type="hidden" name="rec_action" value="password_reset">');
	            });;
	        });
    		$.ajax({
    				url: gpx_base.url_ajax,
    				type: "POST",
    				data: $(this).serialize(),
    	            success: function(response) {
    	            	if(response.success){
    	            		$('.message-box span').html('Updated!');
    	            		setTimeout(function(){
    	            			window.location.href='/?login_again';
    	            		}, 1500)
    	            	}else{
    	            		$('.message-box span').html(response.msg);
    	            	}
    	            }
    		});
    	}
        return false;
    });
   if($('#recred').length){
		$('.message-box span').html('Please login with your new credentials.');
		$('.signin').trigger('click');
   }
    $("#form-pwreset").submit(function(e) {
	e.preventDefault();
	$.ajax({
	    url: gpx_base.url_ajax,
	    type: "POST",
	    data: $(this).serialize(),
	    success: function(response) {
		$('.message-box span').html(response.success);
	    }
	});
	return false;
    });
    $("#form-pwset").submit(function(e) {
	e.preventDefault();
	$.ajax({
	    url: gpx_base.url_ajax,
	    type: "POST",
	    data: $(this).serialize(),
	    success: function(response) {
		$('.message-box span').html(response.msg);
		if(response.action == 'login') {
		    active_modal( modal_login );
		    $('#redirectTo').val(response.redirect);
		}else {
		    if(response.action == 'pwreset') {
			$('#form-pwreset').show();
			$('#form-pwset').hide();
		    }
		}
		
	    }
	});
	return false;
    });
    $('.special-link').click(function(){
	$(this).next().addClass('active-modal');
	return false;
    });
    $(document).on('click', '.better-modal-link', function(e){
        e.preventDefault();
        var modal = $(this).attr('href');
        active_modal( modal );
        if(modal == '#modal-deposit') {
            if($('.deposit-bank-boxes li').length == 1) {
        		//$('.deposit-bank-boxes li').trigger('click');
        		//$('.disswitch').datepicker('show');
            }
        }
        $('html, body').animate({scrollTop:90}, 'slow');
    });
    $('.data-modal').click(function(){
	var htmllink = $(this).data('html');
	if(htmllink){
	    var $text = $(htmllink).html();
	}else {
	    var $text = $(this).data('text');
	}
	    $('#alertMsg').html($text);
	    active_modal('#modal-hold-alert');
	return false;
    });    
    $('html body').on('click', '.data-modal', function(e){
	e.preventDefault();
	var $text = $(this).data('text');
	    $('#alertMsg').html($text);
	    active_modal('#modal-hold-alert');
    });
    $('#checkin-btn').click(function(e){
	e.preventDefault();
	$('.comiseo-daterangepicker-triggerbutton').show();
	$('#rangepicker').daterangepicker("open");
    });

    $('.filter_city').change(function(){
	var findarr = $(this).val();
	var search = '.w-item-view';
	var filter = '';
	var type = $(this).data('filter');
	var results = [];
	var find = '';
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	if(results.length) {
		$('.w-item-view').removeClass('filtered');
		$.each(results, function(key, value){
		   $('#'+value).addClass('filtered'); 
		});	    
	}
	else {
	    $('.w-item-view').addClass('filtered');
	}
    });
    $('.filter_resort_city').change(function(){
	var findarr = $(this).val();
	var search = '.w-item-view';
	var filter = '';
	var type = $(this).data('filter');
	var results = [];
	var find = '';
	var allsearch = [];
	if($('.aiFiltered').length)
	    allsearch.push('.aiFiltered');
	if($('.typeFiltered').length)
	    allsearch.push('.typeFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	if(results.length) {
	    $('.w-item-view').removeClass('filtered').hide();
	    $.each(results, function(key, value){
		$('#'+value).addClass('filtered').show(); 
	    });	    
	}
	else {
	    $('.w-item-view').addClass('filtered').show();
	}
    });
    $('html body').on('change', '.filter_resort_resorttype', function(){
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_resort_resorttype').each(function(){
	   if(this.checked) {
	       findarr.push($(this).val());
	   }
	});
	var find = '';
	var search = '.filtered';
	if($('.aiFiltered').length)
	    allsearch.push('.aiFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	$(search).show();
	$(search).removeClass('typeFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$('#'+value).show().addClass('typeFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(search).show();
	}    
	var searchit = '.filtered .item-result';
	if($('.aiFiltered').length)
	    allsearch.push('.aiFiltered');
	if(allsearch.length)
	    searchit = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(searchit).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) == 0) {
		    results.push($(this));
		}
	    });
	});
	$('.filtered').show();
	$(searchit).removeClass('typeFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$(value).show().addClass('typeFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(searchit).show();
	}
    });  
    $('html body').on('change', '.filter_resort_ai', function(){
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_resort_ai').each(function(){
	    if(this.checked) {
		findarr.push($(this).val());
	    }
	});
	var find = '';
	var search = '.filtered';
	if($('.typeFiltered').length)
	    allsearch.push('.typeFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	$(search).show();
	$(search).removeClass('aiFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$('.filtered').removeClass('aiFiltered').hide();
		$('#'+value).show().addClass('aiFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(search).show();
	}
	var searchit = '.filtered .item-result';
	if($('.typeFiltered').length)
	    allsearch.push('.typeFiltered');
	if(allsearch.length)
	    searchit = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(searchit).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) == 0) {
		    results.push($(this));
		}
	    });
	});
	$('.filtered').show();
	$(searchit).removeClass('aiFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$(value).show().addClass('aiFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(searchit).show();
	}
    });  
    if($('.item-result').length) {
	var mindate = '';
	var maxdate = new Date();
	$('.item-result').each(function(){
	    var hasdate = $(this).data('date');
	    if(hasdate.length) {
		var mds = hasdate.split('-');
		var thisDate = new Date(mds[0], parseInt(mds[1])-1, mds[2]); 
		if(mindate == '')
		    mindate = thisDate;
		if(thisDate < mindate)
		    mindate = thisDate;
		if(thisDate > maxdate)
		    maxdate = thisDate;
	    }
	});
    }
    $('.datepicker').datepicker();
    var dpToday = new Date();
    var dpmm = dpToday.getMonth()+1;
    var dpyyyy = dpToday.getFullYear()+1;
    var dpMaxDate = new Date(dpyyyy, dpmm, 0);
    $('.maxdatepicker').datepicker({
	minDate: 0,
	maxDate: dpMaxDate,
	onSelect: function() {
	    $(this).addClass('filled');
	}
    });
    $("#rangepicker").daterangepicker({
	     presetRanges: [{
	         text: 'Today',
	         dateStart: function() { return moment() },
	         dateEnd: function() { return moment() }
	     },{
	         text: 'This Month',
	         dateStart: function() { return moment() },
	         dateEnd: function() { return moment().add('months', 1) }
	     }, {
	         text: 'Next Month',
	         dateStart: function() { return moment().add('months', 1) },
	         dateEnd: function() { return moment().add('months', 2) }
	     }],
	     applyOnMenuSelect: false,
	     datepickerOptions: {
	         minDate: mindate,
	         maxDate: maxdate
	     },
	     dateFormat: 'yy-mm-dd',
	     change: function(event, data) { 
		 var start = data.instance.getRange().start,
		       end = data.instance.getRange().end,
		       find = '',
		       filter = '',
		       search = '.item-result',
		       results = [],
		       allsearch = [];
		  if($('.typeFiltered').length)
			 allsearch.push('.typeFiltered');
		  if($('.sizeFiltered').length)
			 allsearch.push('.sizeFiltered');
		  if($('.aiFiltered').length)
		      allsearch.push('.aiFiltered');
		  if(allsearch.length)
			 search = allsearch.join('');
		  $(search).each(function(){
		      filter = $(this).data('date');
		      var fs = filter.split('-');
		      var filteredDate = new Date(fs[0], parseInt(fs[1])-1, fs[2]);
		      if(filteredDate <= end && filteredDate >= start) {
			  results.push($(this).attr('id'));
		      }
		  });
		  $('.filtered').show();
		  $(search).removeClass('dateFiltered').hide();
			if(results.length) {
			    $.each(results, function(key, value){
				$('#'+value).show().addClass('dateFiltered'); 
			    });	    
		  }
		  $('.filtered').each(function(){
		      var parel = $(this);
		      if($(parel).find('.w-list-result').children(':visible').length == 0) {
			  $(parel).hide();
		      }
		  });
	     },
});
    $('.filter_size').change(function(){
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_size').each(function(){
	   if(this.checked) {
	       findarr.push($(this).val());
	   }
	});
	var find = '';
	var search = '.filtered .item-result';
	if($('.typeFiltered').length)
	    allsearch.push('.typeFiltered');
	if($('.dateFiltered').length)
	    allsearch.push('.dateFiltered');
	if($('.aiFiltered').length)
	    allsearch.push('.aiFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	$('.filtered').show();
	$(search).removeClass('sizeFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$('#'+value).show().addClass('sizeFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(search).show();
	}
	  $('.filtered').each(function(){
	      var parel = $(this);
	      if($(parel).find('.w-list-result').children(':visible').length == 0) {
		  $(parel).hide();
	      }
	  });
	    
    });
    $('.filter_resorttype').change(function(){
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_resorttype').each(function(){
	   if(this.checked) {
	       findarr.push($(this).val());
	   }
	});
	var find = '';
	
	var searchit = '.filtered .item-result';
	if($('.sizeFiltered').length)
	    allsearch.push('.sizeFiltered');
	if($('.dateFiltered').length)
	    allsearch.push('.dateFiltered');
	if($('.aiFiltered').length)
	    allsearch.push('.aiFiltered');
	if(allsearch.length)
	    searchit = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(searchit).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) == 0) {
		    results.push($(this));
		}
	    });
	});
	$('.filtered').show();
	$(searchit).removeClass('typeFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$(value).show().addClass('typeFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(searchit).show();
	}
	  $('.filtered').each(function(){
	      var parel = $(this);
	      if($(parel).find('.w-list-result').children(':visible').length == 0) {
		  $(parel).hide();
	      }
	  });
    });  
    $('#filter_ai_dummy').change(function(){
	$('.filter_ai').trigger('click');
    });
    $('.filter_ai').change(function(){
	if(this.checked) {
	    $('#filter_ai_dummy').prop('checked', false);
	    $('#aiNot').text('Not ');
	}else{
	    $('#filter_ai_dummy').prop('checked', true);
	    $('#aiNot').text('');
	}
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_ai').each(function(){
	   if(this.checked) {
	       findarr.push($(this).val());
	   }
	});
	var find = '';
	var search = '.filtered .item-result';
	if($('.sizeFiltered').length)
	    allsearch.push('.sizeFiltered');
	if($('.dateFiltered').length)
	    allsearch.push('.dateFiltered');
	if($('.typeFiltered').length)
	    allsearch.push('.typeFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	$('.filtered').show();
	$(search).removeClass('aiFiltered').hide();
	if(results.length) {
	    console.log(results);
	    $.each(results, function(key, value){
		console.log("#"+key);
		$('#'+value).show().addClass('aiFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(search).show();
	}
	  $('.filtered').each(function(){
	      var parel = $(this);
	      if($(parel).find('.w-list-result').children(':visible').length == 0) {
		  $(parel).hide();
	      }
	  });
	    
    });  
    /*
    $('.filter_resort_city').change(function(){
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_resort_city').each(function(){
	    alert('go');
	   if(this.checked) {
	       findarr.push($(this).val());
	       alert($(this).val());
	   }
	});
	var find = '';
	var search = '.filtered .item-result';
	if($('.typeFiltered').length)
	    allsearch.push('.typeFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	search = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	$('.filtered').show();
	console.log(results)
	$(search).removeClass('cityFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$('#'+value).show().addClass('cityFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(search).show();
	}
	  $('.filtered').each(function(){
	      var parel = $(this);
	      if($(parel).find('.w-list-result').children(':visible').length == 0) {
		  $(parel).hide();
	      }
	  });
	    
    });    

    $('.filter_resort_type').change(function(){
	var findarr = [];
	var allsearch = [];
	var type = $(this).data('filter');
	$('.filter_resort_type').each(function(){
	    if(this.checked) {
		findarr.push($(this).val());
	    }
	});
	var find = '';
	var search = '.filtered .item-result';
	if($('.cityFiltered').length)
	    allsearch.push('.cityFiltered');
	if(allsearch.length)
	    search = allsearch.join('');
	search = allsearch.join('');
	var filter = '';
	var results = [];
	$.each(findarr, function(key, value){
	    find = value;
	    $(search).each(function(){
		filter = $(this).data(type);
		if(jQuery.inArray(find,filter) != -1) {
		    results.push($(this).attr('id'));
		}
	    });
	});
	$('.filtered').show();
	$(search).removeClass('typeFiltered').hide();
	if(results.length) {
	    $.each(results, function(key, value){
		$('#'+value).show().addClass('typeFiltered'); 
	    });	    
	}
	if(!findarr.length) {
	    $(search).show();
	}
	$('.filtered').each(function(){
	    var parel = $(this);
	    if($(parel).find('.w-list-result').children(':visible').length == 0) {
		$(parel).hide();
	    }
	});
	
    });  
        */
    /*
    $('.filter_resort').change(function(){
	var type = '';
	var find = null;
	var filter = '';
	var i = 0;
	var results = []; 
	$('.filter_resort').each(function(){
	    i++;
	    var search = '.filtered';
	   var type = $(this).data('filter');
	   var find = $(this).val();
	   if(find != null) {
	       if(i == 1 && !$('.filtered').length) {
		   search = '.w-item-view';
	       }
	       $(search).each(function(){
		  filter = $(this).data(type);
		  if(jQuery.inArray(find,filter) != -1) {
		      results.push($(this));
		      $(this).addClass('filtered');
		  }else{
		      $(this).removeClass('filtered');
		  }
	       });
	   }
	});
	if(results.length) {
	    $.each(results, function(key, value){
		$(this).show().addClass('aiFiltered'); 
	    });	    
	}
    });
    */
    $('#select_soonest').change(function(){
	var sortby = $(this).val(),
	      sorttype = '',
	      sortorder = '';
	switch(sortby) {
	case '1':
	      sorttype = 'timestamp';
	      sortorder = 'asc';
	break;
	
	case '2':
	    sorttype = 'timestamp';
	    sortorder = 'desc';
	break;
	
	case '3':
	    sorttype = 'price';
	    sortorder = 'asc';
	break;
	
	case '4':
	    sorttype = 'price';
	    sortorder = 'desc';
	break;
	
	}
	$('.w-item-view').each(function(){
	   var thisid = $(this).attr('id');
	   tinysort('#'+thisid+'>.w-list-result>li', {data:sorttype, order:sortorder});
	});
	tinysort('.w-item-view', {selector:'.w-list-result>.item-result', data:sorttype, order:sortorder});
    });
    if($('#select_soonest').length && $('.unset-filter-false').length){
	$('#select_soonest').trigger('change');
    }
    
    
    /*
    $('.filter_resort').on('change', function() {
	  var category_filters = [];
	  $('.search-filters select').each(function() {
	      if ($(this).val() != 0) {
	        category_filters[$(this).attr('id')] = $(this).val();
	      }
	    });

	    $(".result-set .result").hide().filter(function() { 
	      var show = true;
	      for (var category in category_filters) {
	         show = show && $(this).data(category) == category_filters[category];
	      }

	      return show;
	    }).show();
    });    
    */
//    $('#wp-admin-bar-gpx_switch').click(function(){
//	 var page = window.location.href;
//	 Cookies.set('switchreturn', page);
//   });
//    if($(".cookieset").length){
//	$('.cookieset').each(function(){
//		var el = $(this);
//		var $name = $(el).data('name');
//		var $value = $(el).data('value');
//		var $expires = $(el).data('expires');
//		var $path = $(el).data('expires');
//		var $json = "{expires: "+$expires+"}";
//		Cookies.set($name, $value, $json);	    
//	});
//    }
//    if($('.cookieremove').length){
//	var remcookie = $('.cookieremove').data('cookie');
//	Cookies.remove(remcookie);
//    }
    /*
    if ( $( ".ajax-load" ).length ) {
	$('.ajax-load').each(function(){
	    var el = $(this);
	   var loading = $(this).data('load');
	   var id = $(this).data('id');
	   $.ajax({
	       method: 'GET',
	       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
	       data: {load: loading, cid: id},
	       success: function(data){
		   $(el).html(data.html);
		   $('form.material').materialForm();
	       },
	   });
	});
    }
    */
    $('#user_email, #user_pass,#modal_user_email, #modal_user_pass, #user_email_pwreset, #user_password_pw_reset').change(function(){
	if($(this).val().length)
	    $(this).addClass('filled');
    });
    $('html body').on('change', '.ownership-deposit', function(){
    	var year = $(this).val();
        var startDate = new Date(year, 0, 1);
        $('.deposit.better-modal-link').trigger('click');
//    	$.get('/wp-admin/admin-ajax.php?action=gpx_load_deposit_form', function(data){
//    	    $('.deposit-form').html(data.html);
//    	    $('.datepicker').trigger('click');
//    	    $('.datepicker').datepicker("setDate", startDate);
//    	});
    });
    $(document).on('click', '.deposit.better-modal-link', function(){
		$.get('/wp-admin/admin-ajax.php?action=gpx_load_deposit_form', function(data){
	    	$('.deposit-form').html(data.html);
	    	$('.datepicker').trigger('click');
		});
    });

    if($('.agentLogin').length) {
	   $('#alertMsg').html('<a href="/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_switch">Please select an owner to continue!</a>');
	    active_modal('#modal-hold-alert'); 
    }
    if($('#exchangeList').length) {
	var weekendpointid = $('#exchangeList').data('weekendpointid');
	var weekid = $('#exchangeList').data('weekid');
	var weektype = $('#exchangeList').data('weektype');
	var id = $('#exchangeList').data('id');
	var type = $('#exchangeList').data('type');
	$.get('/wp-admin/admin-ajax.php?action=gpx_load_exchange_form&type='+type+'&weektype='+weektype+'&weekid='+weekid+'&weekendpointid='+weekendpointid+'&id='+id, function(data){
	    if(data.error) {
		   $('#alertMsg').html(data.error);
       	    	   active_modal('#modal-hold-alert'); 
       	    	   $('#chk_terms').prop('disabled', 'disabled');
       	    	   $('.cnt label, .cnt a').addClass('gpx-disabled');
	    }else {
	    	
	    	$('.submit-guestInfo').removeClass('disabled');
		    $('#exchangeList').html(data.html);
		    $('#CPOPrice').val(data.CPOPrice);

		    if($('.exchangeNotOK').length === 0){
	    		$('#submit_perks_form').show();
	    	}		
	    }
	});
    }
    if($('#bonusWeekDetails_disabled').length) {
	var weekendpointid = $('#bonusWeekDetails').data('weekendpointid');
	var weekid = $('#bonusWeekDetails').data('weekid');
	var weektype = $('#bonusWeekDetails').data('weektype');
	var id = $('#bonusWeekDetails').data('id');
	$.get('/wp-admin/admin-ajax.php?action=gpx_bonus_week_details&weektype='+weektype+'&weekid='+weekid+'&weekendpointid='+weekendpointid+'&id='+id, function(data){
	    if(data.PriceChange) {
		$('#alertMsg').html('The price of this property has changed.  This page will reload with the new price.');
		active_modal('#modal-hold-alert'); 
		setTimeout(function(){
		    location.reload(); 
		},2000);
	    }
	    if(data.Unavailable) {
		$('#alertMsg').html(data.Unavailable);
		active_modal('#modal-hold-alert'); 
		setTimeout(function(){
		    window.location.href='/'; 
		},2000);
	    }
	});
    }
    $('.gpx-disabled').click(function(e){
	
    });
    $('html body').on('click', '.datepicker', function(){
	$(this).datepicker();
    });
    $('html body').on('change', '.datepicker', function(){
	if($(this).val().length)
	    $(this).addClass('filled');
    });
    $('html body').on('click', '.deposit-bank-boxes li', function(e){
    	if($(e.target).hasClass('sel_unit_type')){
    		return;
    	}
    	if($(e.target).hasClass('resdisswitch')){
	    	return;
	    }
    	$(this).find('.sel_unit_type').attr('required', false);
    	$(this).find('.sel_unit_type').attr('required', true);
		$(this).find('.switch-deposit').prop('checked', true);
		$('.deposit-bank-boxes li').removeClass('selected');
		$(this).addClass('selected');
		if($(this).closest('.deposit-bank-boxes').hasClass('exchange-list')) {
		    $(this).closest('li').find('.exchange-credit-check').prop('checked', true);
		}
		$('.disswitch, .resdisswitch, .sel_unit_type').prop('disabled', true);
		$('.selected .disswitch, .selected .resdisswitch, .selected .sel_unit_type').attr('disabled', false);
		if(!$(e.target).is('.twoforone input, .twoforone a')){
			$('.selected .disswitch').focus();   
			$('.twoforone-coupon').removeClass('enable');
		}
    });
    $('html body').on('change', '.doe', function(){
    	var upgrade = $(this).find('option:selected').data('upgradefee');
    	var parel = $(this).closest('li');
    	$(parel).find('.doe_upgrade_msg').hide();
    	$(parel).find('.exchange-credit-check').val(upgrade);
    	if(upgrade > 0) {
    		$(parel).find('.doe_upgrade_msg').show();
    	}
    });
    $('html body').on('blur', '.twoforone-coupon input', function(){
	var thispar = $(this).closest('.twoforone');
	var coupon = $(this).val();
	var setdate = $(this).closest('li').find('.disswitch[name="CheckINDate"]').val();
	var resortID = $(this).closest('li').find('.disswitch[name="ResortID"]').val();
	$('.validate-error').remove();
	if(coupon != '') {
        	$.ajax({
        	    method: 'POST',
        	    url: '/wp-admin/admin-ajax.php?action=gpx_twoforone_validate',
        	    data: {coupon: coupon, setdate: setdate, resortID: resortID},
        	    success: function(data){
        		if(data.success == false) {
        		    $(thispar).append('<div class="validate-error">'+data.message+'</div>');
        		}else{
        		    $(thispar).append('<div class="validate-error">'+data.name+' is valid.</div>');
        		}
        	    }
        	});
	}
    });
    $('html body').on('click', '.btn-tfo-validate', function(e){
	e.preventDefault();
    });
    $("form").submit(function(e) {

	    var ref = $(this).find("[required]");

	    $(ref).each(function(){
	        if ( $(this).val() == '' )
	        {
	            alert("Required field should not be blank.");

	            $(this).focus();

	            e.preventDefault();
	            return false;
	        }
	    });  return true;
	});
    $('html body').on('focus', '.mindatepicker', function(){
	var par = $(this).closest('li');
	var mindate = $(this).data('mindate');
	$(this).datepicker({
	    dateFormat: 'mm/dd/yy',
	   minDate: new Date(mindate), 
	   onSelect: function(setdate) {
			    var startdate = $('.twoforone').data('start');
			    var enddate = $('.twoforone').data('end');
			    if((new Date(setdate).getTime() >= new Date(startdate).getTime()) && (new Date(setdate).getTime() <= new Date(enddate).getTime())) {
				$(par).find('.twoforone-coupon').addClass('enable');
			    }else{
				$(par).find('.twoforone-coupon').removeClass('enable');
			    }
			    
	   }
	});
    });
    /*
     * maybe we'll use this in the future.  it just adds a class when return true

    if($('.hold-hide').length){
 	   var id = $('.hold-hide').data('cid');
    	$.ajax({
    		method: 'GET',
	       url: '/wp-admin/admin-ajax.php?action=gpx_show_hold_button',
	       data: {cid: id},
	       success: function(data){
	    	   if(data.show) {
	    		   $('.hold-hide').addClass('shown');
	    	   }
	       }
    	});
    }
    */
    if ( $( ".transaction-load" ).length ) {
    	
	$('.transaction-load').each(function(){
	    console.log('ble');
	    var el = $(this);
	   var id = $(this).data('id');
	   var loading = $(this).data('load');
	   console.log(id);
	   $.ajax({
	       method: 'GET',
	       url: '/wp-admin/admin-ajax.php?action=gpx_load_data',
	       data: {load: loading, cid: id},
	       success: function(data){
		   $('#ownership').html(data.ownership);
		   $('#deposit').html(data.deposit);
		   $('#depositused').html(data.depositused);
		   $('#exchange').html(data.exchange);
		   $('#bnr').html(data.rental);
		   $('#misc').html(data.misc);
		   $('#creditBal').text(data.credit);
		   $('#holdweeks').html(data.hold);
		   $('.loading').hide();
//		   		dtable.ajax.reload();
//		       tb = $('.ajax-data-table').addClass('nowrap').dataTable({
//		            responsive: true,
//		            searching: true,
//		            paging: true,
//		            "order": [ ],
//		            pageLength: 5,
//		            "language": {
//		                "lengthMenu": "Display _MENU_ records per page",
//		                "zeroRecords": "Nothing found - sorry",
//		                "info": "of _PAGES_",
//		                "infoEmpty": "No records available",
//		                "infoFiltered": "(filtered from _MAX_ total records)"
//		            },
//		            columnDefs: [
//		                {
//		                    //targets: [-1, -3],
//		                   // className: 'dt-body-right'
//		                }
//		            ]
//		        });
	       },
	   });
	});
    }
    if( $("#ownership-profile").length){
    }

    $('html body').on('focus', '.iserror', function(){
	$(this).val('');
    });
    $('#applyDiscount').click(function(e){
	e.preventDefault();
	var cartID = $(this).data('cartid');
	$.post('/wp-admin/admin-ajax.php?action=gpx_apply_discount', {cartID: cartID}, function(data){
	    if(data.success) {
		   window.location.href='/booking-path-payment';
	    }
	});	
    });
    $('html body').on('click', '.btn-will-bank', function(e){
	  e.preventDefault();
	  var resstop = false;
	  $('.depreqtext').text('');
	  var el = $(this);
	  $(el).find('i').show();
	  var form = $(el).closest('form').serialize();
	  var checkin;
	  $(el).closest('form').find('input[name="Check_In_Date__c"]').each(function(){
		  if($(this).val()){
			  checkin = $(this).val();
		  }
		  
	  });
	  $(el).closest('form').find('li.selected').find('input[name="Reservation__c"]').each(function(e){
		  var $el = $(this);
		  if($(this).prop('required')) {
			  checkin = false;
			  if($(this).val()){
				  checkin = $(this).val();
			  }else{
				  resstop = true;
				  $(this).closest('.reswrap').append('<br ><span style="color: #ff0000;" class="depreqtext">Reservation Number Required!</span>');
//				  $el.focus(function(){
//					$('html body').animate({
//						scrollTop: $(this).offset().top+'px'
//					}, 'fast')  
//				  });
			  }		  
		  }
	  });
	  $(el).closest('form').find('li.selected').find('.sel_unit_type ').each(function(e){
		  var $el = $(this);
		  if($(this).prop('required')) {
			  checkin = false;
			  if($(this).val() == ''){
				  $(this).closest('.reswrap').append('<br ><span style="color: #ff0000;" class="depreqtext">Unit Type Required!</span></span>');
			  }		
			  else {
				  if(resstop) {
					  //do nothing
				  }else{
					  checkin = $(this).val();
				  }
			  }
		  }
	  });
	  console.log(checkin);
	  if(checkin){
    	  $.post('/wp-admin/admin-ajax.php?action=gpx_post_will_bank', form, function(data){
//    	      $('.interval-credit, #creditBal').text(data.credit);
    	      $(el).find('i').hide();
    		  $('#alertMsg').html(data.message);
    		  if(data.paymentrequired){
				    $('.payment-msg').text('');
			    $('#checkout-amount').val(data.amount);
  			    $('#checkout-item').val(data.type);
  			    $('#alertMsg').html(data.html);
  			    active_modal('#modal-hold-alert');
    		  }else{
    			  active_modal('#modal-hold-alert'); 
    		  }
      	          
    	  });		  
	  }else{
		  $(el).find('i').hide();
	  }
    });
    $('.return-back').click(function(e){
	e.preventDefault();
	window.history.back();
    });
    $('html body').on('click', '.sbt-btn', function(){
	var link = $(this).data('link');
	window.location.href=link;
    });
    $('html body').on('click', '.sbt-seemore', function(){
	var location = $(this).data('location');
	var start = $(this).data('start');
	var get = $(this).data('get');
	$.post('/wp-admin/admin-ajax.php?action=gpx_display_featured_func', {location: location, start: start, get: get}, function(data){
	    if(data.html) {
		$('.sbt-seemore-box').html(data.html);
	    }
	});
    });
    $('#newpwform').submit(function(e){
	e.preventDefault();
	var form = $(this);
	var cid = $(this).data('cid');
	var formdata = $(form).serialize() + "&cid="+cid;
	console.log(formdata);
	$('.pwMsg').hide(); 
	$.post('/wp-admin/admin-ajax.php?action=gpx_change_password_with_hash', formdata, function(data){
	   $(form)[0].reset();
	   $('.pwMsg').text(data.msg).show();
	   setTimeout(function(){
	      $('.pwMsg').hide(); 
	   }, 5000);
	});
    });
    

    /*-----------------------------------------------------------------------------------*/
    /* Data table
     /*-----------------------------------------------------------------------------------*/
     if(gpx_base.current == 'view-profile') {
	        var dtable = $('.data-table').addClass('nowrap').dataTable({
	            responsive: true,
	            paging: true,
	            pageLength: 5,
	            search: true,
	            "language": {
	                "lengthMenu": "Display _MENU_ records per page",
	                "zeroRecords": "Nothing found - sorry",
	                "info": "of _PAGES_",
	                "infoEmpty": "No records available",
	                "infoFiltered": "(filtered from _MAX_ total records)"
	            },
//	            columnDefs: [
//	                {
//	                    //targets: [-1, -3],
//	                   // className: 'dt-body-right'
//	                }
//	            ]
	        });
    }
     function getTimeRemaining(endtime) {
   	  var t = Date.parse(endtime) - Date.parse(new Date());
   	  var seconds = Math.floor((t / 1000) % 60);
   	  var minutes = Math.floor((t / 1000 / 60) % 60);
   	  var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
   	  var days = Math.floor(t / (1000 * 60 * 60 * 24));
   	  return {
   	    'total': t,
   	    'days': days,
   	    'hours': hours,
   	    'minutes': minutes,
   	    'seconds': seconds
   	  };
   	}

   	function initializeClock(id, endtime) {
   	  var clock = document.getElementById(id);
   	  var daysSpan = clock.querySelector('.days');
   	  var hoursSpan = clock.querySelector('.hours');
   	  var minutesSpan = clock.querySelector('.minutes');
   	  var secondsSpan = clock.querySelector('.seconds');

   	  function updateClock() {
   	    var t = getTimeRemaining(endtime);
   	    
   	    if(t.days > 0){
   		daysSpan.innerHTML = t.days;
   	    }else{
   		daysSpan.innerHTML = '0';
   	    }
   	    if(t.hours > 0){
   		hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
   	    }else{
   		hoursSpan.innerHTML = '0';
   	    }
   	    if(t.minutes > 0){
   		minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
   	    }else{
   		minutesSpan.innerHTML = '0';
   	    }
   	    if(t.seconds > 0){
           	secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);
           }else{
               secondsSpan.innerHTML = '0';
    	    }

   	    if (t.total <= 0) {
   	      clearInterval(timeinterval);
   	    }
   	  }

   	  updateClock();
   	  var timeinterval = setInterval(updateClock, 1000);
   	}
   	if($('.hold-limit-countdown').length){
   		$('.hold-limit-countdown').each(function(){
   	   		var holdtime = parseFloat($(this).data('limit')) * 60 * 60 * 1000;
   	   	   	var deadline = new Date(Date.parse(new Date()) +  parseInt(holdtime));
   	   	   	console.log(deadline);
   	   	   	var id = $(this).find('.show-countdown-timer').attr('id');
   	   	   	initializeClock(id, deadline); 
   		});

   	}

$('.password-reset-link').click(function(e){
	e.preventDefault();
	var user_login = jQuery(this).data('userlogin');
	$.post('/wp-admin/admin-ajax.php?action=request_password_reset',{user_login:user_login}, function(data){
		  $('#alertMsg, #vp-pw-alert-msg').html("Passord reset email sent!");
  	          active_modal('#modal-hold-alert'); 
	});
});
$(window).load(function() {
	$('.gpx-loading-disabled').removeClass('gpx-loading-disabled');
    $(window).scroll(function(){
        var offset = $("#11").offset();
        window_y = $(window).scrollTop();
        scroll_critical = parseInt(offset.top) - 1000;
        if (window_y > scroll_critical) {
            $(".scrolltop").removeClass('s-active');
        } else {
            $(".scrolltop").addClass('s-active');
        }
    });
});
/*
confirm pw match change password
*/
if($('#chPassword').length){
    var password = document.getElementById("chPassword")
    , confirm_password = document.getElementById("chPasswordConfirm");   
    password.onchange = validatePassword;
    confirm_password.onkeyup = validatePassword;
}

function validatePassword(){
if(password.value != confirm_password.value) {
  confirm_password.setCustomValidity("Passwords Don't Match");
} else {
  confirm_password.setCustomValidity('');
}
}
function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
};
$(document).on('scroll', function() {
    if($('.w-filter.dgt-container').length){
        if(($(this).scrollTop()>=$('.w-filter.dgt-container').position().top) && ($(window).height() > $('#modal-filter').height()) ){
    	if(!$('#sticky').hasClass('scrolled'))
    	    $('#sticky, #modal-filter').addClass('scrolled');
        }
        else{
    	if($('#sticky').hasClass('scrolled'))
    	    $('#sticky, #modal-filter').removeClass('scrolled');
        }
    }
});

});
