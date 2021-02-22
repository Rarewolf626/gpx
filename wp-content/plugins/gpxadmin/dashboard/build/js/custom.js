/**
 * Resize function without multiple trigger
 * 
 * Usage: jQuery(window).smartresize(function(){ // code here });
 */
(function($, sr) {
    // debouncing function from John Hann
    // http://unscriptable.com/index.php/2009/03/20/debouncing-javascript-methods/
    var debounce = function(func, threshold, execAsap) {
	var timeout;

	return function debounced() {
	    var obj = this, args = arguments;
	    function delayed() {
		if (!execAsap)
		    func.apply(obj, args);
		timeout = null;
	    }

	    if (timeout)
		clearTimeout(timeout);
	    else if (execAsap)
		func.apply(obj, args);

	    timeout = setTimeout(delayed, threshold || 100);
	};
    };

    // smartresize
    jQuery.fn[sr] = function(fn) {
	return fn ? this.bind('resize', debounce(fn)) : this.trigger(sr);
    };

})(jQuery, 'smartresize');
/**
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates and open the template
 * in the editor.
 */

var CURRENT_URL = window.location.href.split('?')[0], $BODY = jQuery('body'), $MENU_TOGGLE = jQuery('#menu_toggle'), $SIDEBAR_MENU = jQuery('#sidebar-menu'), $SIDEBAR_FOOTER = jQuery('.sidebar-footer'), $LEFT_COL = jQuery('.left_col'), $RIGHT_COL = jQuery('.right_col'), $NAV_MENU = jQuery('.nav_menu'), $FOOTER = jQuery('footer');



jQuery( document ).ready( function( $ ) {

	jQuery('.date-filter-control').datepicker().on('change', function(e){
		jQuery(e.currentTarget).keyup();
	});
	jQuery('html body').on('change', '.bootstrap-table-filter-control-ExpiryDate', function(){
		var $this = jQuery(this);
		var e = jQuery.Event("keydown");
	    e.which = 13; // Enter
	    jQuery($this).trigger(e);
	});
	jQuery('#transactionReport').click(function(e){
		e.preventDefault();
		var link = jQuery(this).attr('href');
		
		jQuery('#gpxModal').modal('show');
		jQuery('#gpxModal .modal-body').html('<form action="'+link+'" method="GET"><input type="hidden" name="action" value="gpx_csv_download" /><input type="hidden" name="table" value="wp_gpxTransactions" /><input type="hidden" name="column" value="transactionData" /><div class="form-row"><label>Date From</label><input type="date" class="form-control" name="datefrom" placeholder="Date From" /></div><div class="form-row" style="margin: 20px 0;"><label>Date To</label><input type="date" class="form-control" name="dateto" placeholder="Date To" /><br /><br /><input type="submit" name="submit" value="Generate Report" class="btn btn-primary"></div></form>');
	});
	jQuery('.delete-unit').click(function(e){
		e.preventDefault();
		var unit_id = jQuery(this).data('id');
		
		jQuery.ajax({
		    url : 'admin-ajax.php?&action=deleteUnittype',
		    type : 'POST',
		    data: {
		    	unit_id: unit_id,
		    },
		    success : function(data) {

			if (data) {
			    jQuery('#sucessmessage').html('Unit Type deleted successfully');
			    jQuery('#sucessmessage').css('display','block');
			    jQuery('#name').val('');
				window.location.reload();	    
			} else{
			    alert('Web Services Failed');
			}

		    }
		}); 
	});
	jQuery('#unitTypeaddsubmit').click(function(e){
		e.preventDefault();
		var name = jQuery('#name').val();
		var resort_id = jQuery('#resort_id').val();
		var number_of_bedrooms = jQuery('#number_of_bedrooms').val();
		var sleeps_total  = jQuery('#sleeps_total').val();
		var unit_id  = jQuery('#unit_id').val();
		

		jQuery.ajax({
		    url : 'admin-ajax.php?&action=unitType_Form',
		    type : 'POST',
		    data: {
		    	name: name, 
		    	resort_id: resort_id,
		    	number_of_bedrooms: number_of_bedrooms,
		    	sleeps_total: sleeps_total,
		    	unit_id: unit_id,
		    },
		    success : function(data) {

			if (data) {
			    jQuery('#sucessmessage').html('Unit Type created successfully');
			    jQuery('#sucessmessage').css('display','block');
			    jQuery('#name').val('');
				window.location.reload();	    
			} else{
			    alert('Web Services Failed');
			}

		    }
		}); 
	});
//2020-06-27 00:00:00
	jQuery('html body .modal-body').on('click', '.dropdown-item', function(){
		var type = $(this).data('type');
		var text = $(this).text();
		console.log(text);
		jQuery(this).closest('.input-group-btn').find('.dropdown-toggle').text(text);
		jQuery(this).closest('.input-group').find('.refundType').val(type);
		jQuery(this).closest('.input-group').find('input[type="submit"]').show();
	});
	jQuery('html body').on('click', '.update-transaction-fee', function(){
		var spinner = jQuery(this).append('<i class("fa  fa-spinner fa-pulse"></i>');
		var gp = jQuery(this).closest('.modal-body').find('.input-group');
		var amt = jQuery(gp).find('input.feeamt').val();
		var type = jQuery(gp).find('input.feeamt').data('type');
		var refundtype = jQuery(gp).find('.refundType').val();
		console.log(type);
		var id = jQuery('#transactionID').val();
		jQuery.ajax({
			type: 'POST',
			url: 'admin-ajax.php?action=gpx_transaction_fees_adjust',
			data: {
				id: id,
				amount: amt,
				refundType: refundtype,
				type: type
			},
			success: function(resp){
				if(resp.success){
					location.reload();
					console.log('reload');
				} else {
					jQuery('#feeupdate .modal-body').append(resp.html);
				}
				jQuery(spinner).remove();
			},
		});
	});
	jQuery('html body').on('click', '.refresh-fee', function(){
		location.reload();
	});
	jQuery('html body').on('click', '.feeupdate', function(){
		var content = jQuery(this).closest('.modal-btn-group').find('.updateoptions').html();
		var type = jQuery(this).data('type');
		jQuery('#updateType').text(type);
		jQuery('#feeupdate .modal-body').html(content);
	});
	jQuery('html body').on('click', '.credit-extend', function(){
		var id = jQuery(this).data('id');
		jQuery('#creID').val(id);
	});
	jQuery('html body').on('submit', '.creditExtForm', function(e){
  e.preventDefault();	
  
  var formdata = jQuery(this).serialize();
  		    jQuery.ajax({
			        type: 'POST',
			        url: "admin-ajax.php?&action=creditExtention&"+formdata+"",
			        contentType: false,
			        processData: false,
			        success: function(response){
			            jQuery('#creModal').modal('hide');
			            jQuery('#depositTable').find('button[name="refresh"]').trigger('click');
			        }
			    });	

 });


			jQuery('#csv_file_button').on('click', function( event ){

				event.preventDefault();

				
        	tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	// return false;

	// 			var fd = new FormData();
	// 		    var file = jQuery(document).find('input[type="file"]');
	// 		    var individual_file = file[0].files[0];
	// 		    fd.append("file", individual_file);			     
	// 		    fd.append('action', 'fiu_upload_file');  

	// 		    jQuery.ajax({
	// 		        type: 'POST',
	// 		        url: "admin-ajax.php?&action=csv_",
	// 		        data: fd,
	// 		        contentType: false,
	// 		        processData: false,
	// 		        success: function(response){
	// 		            console.log(response);
	// 		        }
	// 		    });

			});

			jQuery('#submit-csv').on('click', function( event ){

				event.preventDefault();

			    var file = jQuery('#csv_file').val();
			    jQuery.ajax({
			    url : 'admin-ajax.php?&action=csv_upload',
			    type : 'POST',
			    data: {
			    	file_url: file, 
			    },
			    success : function(data) {
				    	console.log(data);
					if (data) {

						if(data[0] != "error")
						{
							jQuery('#sucessmessage').html(data[1]);
					    jQuery('#sucessmessage').css('display','block');	
						}
						else{
							jQuery('#myModal').modal();
							//importerror
							jQuery('#importerror').html(data[1]);
							// window.location.href = "https://beta.my-gpx.com/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_error";
						}
					    
					} else{
					    alert('Web Services Failed');
					}

				    }
				});

			});

			window.send_to_editor = function (html) {  // Send WP media uploader response
	url = $(html).attr('href');
	$('#csv_file').val(url);
	tb_remove();
	  // Function to blur file upload field (gets column count from .csv file)
    }
		});





// Sidebar
jQuery(document)
	.ready(
		function() {
			
		    // TODO: This is some kind of easy fix, maybe we can improve
		    // this

		    var setContentHeight = function() {
			// reset height
			jQuery('.right_col').css('min-height',
				jQuery(window).height());

			var bodyHeight = jQuery('.dashboard_body')
				.outerHeight(), footerHeight = jQuery(
				'.dashboard_body').hasClass('footer_fixed') ? -10
				: jQuery('footer').height(), leftColHeight = jQuery(
				'.left_col').eq(1).height()
				+ jQuery('.sidebar-footer').height(), contentHeight = bodyHeight < leftColHeight ? leftColHeight
				: bodyHeight;

			// normalize content
			contentHeight -= jQuery('.nav_menu').height()
				+ footerHeight;

			jQuery('.right_col').css('min-height', contentHeight);
		    };
		    jQuery('#sidebar-menu')
			    .find('a')
			    .on(
				    'click',
				    function(ev) {
					console.log('side menu');
					var $li = jQuery(this).parent();

					if ($li.is('.active')) {
					    $li.removeClass('active active-sm');
					    jQuery('ul:first', $li).slideUp(
						    function() {
							setContentHeight();
						    });
					} else {
					    // prevent closing menu if we are on
					    // child menu
					    if (!$li.parent().is('.child_menu')) {
						jQuery('#sidebar-menu').find(
							'li').removeClass(
							'active active-sm');
						jQuery('#sidebar-menu').find(
							'li ul').slideUp();
					    }

					    $li.addClass('active');

					    jQuery('ul:first', $li).slideDown(
						    function() {
							setContentHeight();
						    });
					}
				    });

		    // toggle small or large menu
		    jQuery('#menu_toggle').on(
			    'click',
			    function() {
				if (jQuery('.dashboard_body')
					.hasClass('nav-md')) {
				    jQuery('#sidebar-menu')
					    .find('li.active ul').hide();
				    jQuery('#sidebar-menu').find('li.active')
					    .addClass('active-sm').removeClass(
						    'active');
				} else {
				    jQuery('#sidebar-menu').find(
					    'li.active-sm ul').show();
				    jQuery('#sidebar-menu')
					    .find('li.active-sm').addClass(
						    'active').removeClass(
						    'active-sm');
				}

				jQuery('.dashboard_body').toggleClass(
					'nav-md nav-sm');

				setContentHeight();
			    });

		    // check active menu
		    jQuery('#sidebar-menu').find(
			    'a[href="' + CURRENT_URL + '"]').parent('li')
			    .addClass('current-page');

		    jQuery('#sidebar-menu').find('a').filter(function() {
			return this.href == CURRENT_URL;
		    }).parent('li').addClass('current-page').parents('ul')
			    .slideDown(function() {
				setContentHeight();
			    }).parent().addClass('active');

		    // recompute content when resizing
		    jQuery(window).smartresize(function() {
			setContentHeight();
		    });

		    setContentHeight();

		    // fixed sidebar
		    if (jQuery.fn.mCustomScrollbar) {
			jQuery('.menu_fixed').mCustomScrollbar({
			    autoHideScrollbar : true,
			    theme : 'minimal',
			    mouseWheel : {
				preventDefault : true
			    }
			});
		    }
		    jQuery('.ms-filtered').multiselect({
			enableFiltering : true
		    });
		    jQuery('.multiselect').multiselect({

		    });
		});
// /Sidebar

// Panel toolbox
jQuery(document)
	.ready(
		function() {
		    jQuery('.collapse-link')
			    .on(
				    'click',
				    function() {
					var $BOX_PANEL = jQuery(this).closest(
						'.x_panel'), $ICON = jQuery(
						this).find('i'), $BOX_CONTENT = $BOX_PANEL
						.find('.x_content');

					// fix for some div with hardcoded fix
					// class
					if ($BOX_PANEL.attr('style')) {
					    $BOX_CONTENT
						    .slideToggle(
							    200,
							    function() {
								$BOX_PANEL
									.removeAttr('style');
							    });
					} else {
					    $BOX_CONTENT.slideToggle(200);
					    $BOX_PANEL.css('height', 'auto');
					}

					$ICON
						.toggleClass('fa-chevron-up fa-chevron-down');
				    });

		    jQuery('.close-link').click(function() {
			var $BOX_PANEL = jQuery(this).closest('.x_panel');

			$BOX_PANEL.remove();
		    });
		});
// /Panel toolbox

// Tooltip
jQuery(document).ready(function() {
    jQuery('[data-toggle="tooltip"]').tooltip({
	container : 'body'
    });
});
// /Tooltip

// Progressbar
if (jQuery(".progress .progress-bar")[0]) {
    jQuery('.progress .progress-bar').progressbar();
}
// /Progressbar

// Switchery
jQuery(document).ready(
	function() {
	    if (jQuery(".js-switch")[0]) {
		var elems = Array.prototype.slice.call(document
			.querySelectorAll('.js-switch'));
		elems.forEach(function(html) {
		    var switchery = new Switchery(html, {
			color : '#26B99A'
		    });
		});
	    }
	});
// /Switchery

// iCheck
jQuery(document).ready(function() {
    if (jQuery("input.flat")[0]) {
	jQuery(document).ready(function() {
	    jQuery('input.flat').iCheck({
		checkboxClass : 'icheckbox_flat-green',
		radioClass : 'iradio_flat-green'
	    });
	});
    }
    jQuery('#ping-dae').click(function(){
		jQuery.ajax({
		    url : 'admin-ajax.php?&action=get_countryList',
		    type : 'POST',
		    data: {ping: 'ping'},
		    success : function(data) {
			if (data.success) {
			    alert('Web Services Connected');
			} else{
			    alert('Web Services Failed');
			}
		    }
		}); 
	});

	jQuery('#source').on('change',function(){
		var source = jQuery(this).val();

		if(source == 1){
			jQuery('#sourcepartnerfield').removeClass('hide');
		}
		else if(source == 3){
			jQuery('#sourcepartnerfield').removeClass('hide');
		}
		else{
			jQuery('#sourcepartnerfield').addClass('hide');
		}

	});


	jQuery(document).ready(function() {
    jQuery('.select2').select2();
	});



//	jQuery('.unitTypeEdit').submit(function(e){
//		e.preventDefault();
//		var str = jQuery(this).serialize();
//    	
//		jQuery.ajax({
//		    url : 'admin-ajax.php?&action=unitType_Form',
//		    type : 'POST',
//		    data: str,
//		    success : function(data) {
//
//			if (data) {
//			    jQuery('#sucessmessage').html('Unit Type updated successfully');
//			    jQuery('#sucessmessage').css('display','block');
//				window.location.reload();	    
//			} else{
//			    alert('Web Services Failed');
//			}
//
//		    }
//		}); 
//	});


//$('#myModal').modal('show'); 


jQuery(function($) {


	jQuery.fn.editUnittype = function(id){ 

		jQuery('#unittypeopen'+id).modal('show'); 
	}


   jQuery.fn.deleteUnittype = function(id){ 

   	jQuery.ajax({
		    url : 'admin-ajax.php?&action=deleteUnittype',
		    type : 'POST',
		    data: {
		    	unitType: id
		    },
		    success : function(data) {

			if (data) {
			    window.location.reload();
			}

		    }
		}); 
   }
 });
	

		
		// jQuery.ajax({
		//     url : 'admin-ajax.php?&action=fetchunitType',
		//     type : 'POST',
		//     data: {
		//     	name: name, 
		//     	resort_id: resort_id,
		//     	number_of_bedrooms: number_of_bedrooms,
		//     	sleeps_total: sleeps_total
		//     },
		//     success : function(data) {

		// 	if (data) {
		// 	    jQuery('#sucessmessage').html('Unit Type created successfully');
		// 	    jQuery('#sucessmessage').css('display','block');
		// 	} else{
		// 	    alert('Web Services Failed');
		// 	}

		//     }
		// }); 
	
jQuery('.select2autocomplete').change(function(){
   var type = jQuery('#source').val(); 

    jQuery(this).select2({
    	  ajax: {
    		    url: '/wp-admin/admin-ajax.php?action=partner_autocomplete&type='+type,
    		    dataType: 'json',
    		    processResults: function (data) {
    	            return {
    	                results: $.map(data.items, function (item) {
    	                	console.log(item.text);
    	                    return {
    	                        text: item.text,
    	                        id: item.id
    	                    }
    	                })
    	            };
    	        }
    		  }
    });
});
jQuery( "#autocomplete" ).autocomplete({
  source: function( request, response ) {
   // Fetch data  partner_autocomplete
   jQuery.ajax({
    url: "admin-ajax.php?&action=partner_autocomplete",
    type: 'post',
    dataType: "json",
    data: {
     search: request.term,
     type: jQuery('#source').val()
    },
    success: function( data ) {
     response( data );
    }
   });
  },
  select: function (event, ui) {
   // Set selection
   jQuery('#autocomplete').val(ui.item.label); // display the selected text
   jQuery('#source_partner_id').val(ui.item.value); // save selected id to input
   return false;
  }
 });


jQuery( "#autocompleteAvailability" ).autocomplete({
    source: function( request, response ) {
	// Fetch data  partner_autocomplete
	jQuery.ajax({
	    url: "admin-ajax.php?&action=partner_autocomplete",
	    type: 'post',
	    dataType: "json",
	    data: {
		search: request.term,
		type: jQuery('#availability').val(),
		availabilty: true,
	    },
	    success: function( data ) {
		response( data );
	    }
	});
    },
    select: function (event, ui) {
	// Set selection
	jQuery('#autocompleteAvailability').val(ui.item.label); // display the selected text
	jQuery('#available_to_partner_id').val(ui.item.value); // save selected id to input
	return false;
    }
});

// resort confirmation 

var username_state = false;


jQuery( "#resort_confirmation_number" ).on('blur', function(){ 
   // Fetch data  partner_autocomplete
   var resort_confirmation_number = jQuery('#resort_confirmation_number').val();
   var resort = jQuery('#resort').val();
   var cnt = jQuery('#count').val();
   if(cnt > 1){
		jQuery('#resort_confirmation_number').val('');
   }
   if(resort == '' || resort == '0' || cnt > 1) {
	   return;
   }
   if (resort_confirmation_number == '') {
//  	username_state = false;
  	return;
  }

   jQuery.ajax({
    url: "admin-ajax.php?&action=resort_confirmation_number",
    type: 'post',
    dataType: "json",
    data: {
     resortConfirmation: resort_confirmation_number,
     resort: resort,
    },
    success: function( data ) {

		if (data != '') {
			username_state = false;
			jQuery('#resorterror').removeClass("form_error");
		    jQuery('#resorterror').addClass("form_error");
		    jQuery('#resort_confirmation_number').siblings("span").text('Resort Confirmation  number ('+resort_confirmation_number+') already taken');
		    if(resort !== '0'){
					jQuery('#resort_confirmation_number').focus().val('');
					jQuery('#resort_confirmation_number').attr('data-parsley-value', "");
		    }
		
		}
		else{
			jQuery('#resorterror').removeClass("form_error");
			//text(''); 
			jQuery('#resorterror').text('');
			jQuery('#resort_confirmation_number').attr('data-parsley-value', resort_confirmation_number);
			username_state = true;	

		}

    }
   });
  });
jQuery('html body').on('change', '#resort', function(){
    // Fetch data  partner_autocomplete
    var resort_confirmation_number = jQuery('#resort_confirmation_number').val();
    var resort = jQuery('#resort').val();
    var cnt = jQuery('#count').val();
    if(cnt > 1){
		jQuery('#resort_confirmation_number').val('');
    }
    if (resort_confirmation_number == '' || cnt > 1) {
	username_state = false;
	return;
    }
    
    jQuery.ajax({
	url: "admin-ajax.php?&action=resort_confirmation_number",
	type: 'post',
	dataType: "json",
	data: {
	     resortConfirmation: resort_confirmation_number,
	     resort: resort,
	},
	success: function( data ) {
	    
	    if (data != '') {
			username_state = false;
			jQuery('#resorterror').removeClass("form_error");
			jQuery('#resorterror').addClass("form_error");
			jQuery('#resort_confirmation_number').siblings("span").text('Resort Confirmation number ('+resort_confirmation_number+') already taken');
			jQuery('#resort_confirmation_number').focus().val('');
			jQuery('#resort_confirmation_number').attr('data-parsley-value', "");
	    }
	    else{
		jQuery('#resorterror').removeClass("form_error");
		//text(''); 
		jQuery('#resort_confirmation_number').attr('data-parsley-value', resort_confirmation_number);
		jQuery('#resorterror').text('');
			username_state = true;	
	    }
	    
	}
    });
});

jQuery('html body').on('change', '#count', function(){
	   var cnt = jQuery('#count').val();
	   if(cnt > 1){
			jQuery('#resorterror').removeClass("form_error").text('');
			jQuery('#resort_confirmation_number').val('');
			jQuery('#resort_confirmation_number').attr('data-parsley-value', '');	
	   }
	   jQuery('#resort_confirmation_number').trigger('blur');
});


    jQuery('#check_in_date').datepicker({minDate: 0, onSelect: function(dateStr) {
          var date = jQuery(this).datepicker('getDate');
          var minDate = jQuery(this).datepicker('getDate');
          if (date) {

                var activedate = new Date(date.getFullYear()-1, date.getMonth(), 1);
                minDate.setDate(minDate.getDate()+1);
                date.setDate(date.getDate() + 7);
          }
       
       console.log(date);
         jQuery('#check_out_date').datepicker('destroy').datepicker({dateFormat: 'mm/dd/yy', minDate: minDate}).datepicker('setDate', date);
         jQuery('#active_specific_date').datepicker({dateFormat: 'mm/dd/yy'}).datepicker('setDate', activedate);
    }});
    
jQuery('html body').on('change', '#resort', function(){
		var resort = jQuery("#resort option:selected").val();
		jQuery.ajax({
			url : 'admin-ajax.php?&action=get_unit_type',
			type : 'POST',
			data: {
				resort: resort
			},
			success : function(data) {
				console.log(data);
				
				var $mySelect = jQuery('#unit_type_id');
				$mySelect.empty();
				//<option value="0">Please Select</option>
				$mySelect.append("<option value='0'>Please Select</option>");
				jQuery.each(data, function(key, value) {
					var $option = jQuery("<option/>", {
						value: key,
						text: value
					});
					$mySelect.append($option);
				});
				
			}
		});
		
	});
//jQuery('#resort').change(function(){
//	var resort = jQuery("#resort option:selected").val();
//	jQuery.ajax({
//		    url : 'admin-ajax.php?&action=get_unit_type',
//		    type : 'POST',
//		    data: {
//		    	resort: resort
//		    },
//		    success : function(data) {
//		    	console.log(data);
//
//		    	var $mySelect = jQuery('#unit_type_id');
//					$mySelect.empty();
//					//<option value="0">Please Select</option>
//					$mySelect.append("<option value='0'>Please Select</option>");
//				jQuery.each(data, function(key, value) {
//				  var $option = jQuery("<option/>", {
//				    value: key,
//				    text: value
//				  });
//				  $mySelect.append($option);
//				});
//
//		    }
//		});
//
//  });




jQuery('#check_in_date').datepicker({
    dateFormat: 'mm/dd/yy',
    onSelect: function(dateStr){
	var min = jQuery(this).datepicker('getDate');
	jQuery('#check_out_date').datepicker('option', {minDate: min});
    }
	
});
jQuery('#rental_push_date').datepicker({dateFormat: 'mm/dd/yy'});
 jQuery('#check_out_date').datepicker({dateFormat: 'mm/dd/yy'});
 jQuery('#active_specific_date').datepicker({dateFormat: 'mm/dd/yy'});

jQuery('#roomaddForm').parsley();

setTimeout(function(){
    jQuery('#active_display_date').hide();
});
jQuery('html body').on('click', '.show_active_date', function(){
   if(jQuery(this).is(':checked')) {
       jQuery('#active_display_date').show();
   } 
});
jQuery('html body').on('click', '.hide_active_date', function(){
    if(jQuery(this).is(':checked')) {
	jQuery('#active_display_date').hide();
    } 
});

jQuery('html body').on('change', '#active_type', function(){
    jQuery('#active_week_month, #active_specific_date').hide();
    var type = jQuery(this).val();
    if(type == 'date') {
	jQuery('#active_specific_date').show();
    }else{
	jQuery('#active_week_month').show();
    }
});
jQuery('html body').on('click', '#roomaddsubmitclear, #roomaddsubmit', function(e){
//jQuery('#roomaddForm').submit(function(e){
		e.preventDefault();
		var clear = jQuery(this).data('clear');
		var form = jQuery(this).closest('form');

		

		var check_in_date = jQuery('#check_in_date').val();
		var check_out_date = jQuery('#check_out_date').val();
		var active_specific_date = jQuery('#active_specific_date').val();
		var active_week_month = jQuery('#active_week_month_sel').val();
		var active_type = jQuery('#active_type').val();
		var unit_type_id = jQuery('#unit_type_id').val();
		var source = jQuery('#source').val();
		var resort = jQuery('#resort').val();
		var source_partner_id = jQuery('#source_partner_id').val();
		var resort_confirmation_number = jQuery('#resort_confirmation_number').val();
		var active = jQuery("input[type='radio']:checked").val();
		var availability = jQuery('#availability').val();
		var available_to_partner_id = jQuery('#available_to_partner_id').val();
		var type = jQuery('#type').val();
		var rental_push = jQuery('#rental_push').val();
		var cnt = jQuery('#count').val();
		// var price = jQuery('#price').val();
		// var given_to_partner_id = jQuery('#given_to_partner_id').val();
		var note = jQuery('#note').val();
		var price = jQuery('#price').val();
		// if (check_in_date == '') {
			
	  // 		jQuery('#resorterror').removeClass("form_error");
		 //    jQuery('#resorterror').addClass("form_error");
		 //    jQuery('#resort_confirmation_number').siblings("span").text('Resort Confirmation number already taken');

			// }
			// else{

//		jQUery('.bootstrap-table-filter-control-check_in_date').datepicker({
//			  onSelect: function() {
//				    return jQuery(this).trigger('keyup');
//				  }
//		})
	 if(jQuery('#roomaddForm').parsley().isValid())
  		{

		jQuery.ajax({
		    url : 'admin-ajax.php?&action=room_Form',
		    type : 'POST',
		    data: {
		    	check_in_date: check_in_date, 
		    	check_out_date: check_out_date,
		    	active_specific_date: active_specific_date,
		    	active_week_month: active_week_month,
		    	active_type: active_type,
		    	unit_type_id: unit_type_id,
		    	source: source,
		    	resort: resort,
		    	source_partner_id: source_partner_id,
		    	resort_confirmation_number: resort_confirmation_number,
		    	active: active,
		    	availability: availability,
		    	available_to_partner_id: available_to_partner_id,
		    	type: type,
		    	note: note,
		    	price: price,
		    	rental_push: rental_push,
		    	count: cnt,
		    	
		    },
		    success : function(data) {
		    	console.log(data);
			if (data) {
//			    jQuery('#sucessmessage').html('Room created successfully');
//			    jQuery('#sucessmessage').css('display','block');
			    jQuery('#myModal').modal();
			    if(clear == 'clear'){
				    jQuery(form).find('input, select').val(null).trigger('change');
				    //refresh the table
				    jQuery('button[name="refresh"]').trigger('click');
				    jQuery(form).find('#Radio1').val('1').trigger('click');			    	
			    }else{
			    	jQuery('#resort_confirmation_number').val(null).trigger('change');
			    }

			} else{
			    alert('Web Services Failed');
			}

		    }
		});
	}

		// } 
	});





		var source = jQuery('#source').val();

		if (source == 1) {
			jQuery('#sourcepartnerfield').removeClass('hide');
			jQuery('#source_partner_id').val("");
		}
		else if (source == 3) {
			jQuery('#sourcepartnerfield').removeClass('hide');

		}
		else {
			jQuery('#sourcepartnerfield').addClass('hide');
			jQuery('#source_partner_id').val("");
			jQuery('#autocomplete').val("");
			//autocomplete
		}
		
		var roomtype = jQuery('#type').val();
		var minRoomPrice = jQuery('#price').data('min');
		console.log(minRoomPrice);
		if (roomtype == 1) {
		    jQuery('#pricewrapper').addClass('hide');
		    jQuery('#price').removeAttr('required');
		    jQuery('#price').removeAttr('min');
		}
		else {
		    jQuery('#pricewrapper').removeClass('hide');
		    jQuery('#pricewrapper').val("");
		    jQuery('#price').attr('required', 'required');
		    jQuery('#price').attr('min', minRoomPrice);
		}
	jQuery('html body').on('change', '#type', function(){
//	jQuery('#type').change(function(){
		var roomtype = jQuery('#type').val();
		
		if (roomtype == 1) {
		    jQuery('#pricewrapper').addClass('hide');
		    jQuery('#price').removeAttr('required');
		    jQuery('#price').removeAttr('min');
		}
		else {
		    jQuery('#pricewrapper').removeClass('hide');
		    jQuery('#price').val("");
		    jQuery('#price').attr('required', 'required');
		    jQuery('#price').prop('min', minRoomPrice);
		}
	});


jQuery('.room-history').hide();
jQuery('.show-history').click(function(e){
	e.preventDefault();
	jQuery('.room-history').hide();
	jQuery(this).next().toggle();
});
//jQuery('#tp_username').blur(function(){
//	var $this = jQuery(this);
//	var username = jQuery(this).val();
//	jQuery.get('/wp-admin/admin-ajax.php?action=gpx_check_username&username='+username, function(data){
//		if(data.exists){
//			jQuery($this).val('');
//			jQuery($this).focus();
//		}
//	});
//});
jQuery('#roomeditForm').submit(function(e){
		e.preventDefault();

		if(! jQuery('#roomeditForm')[0].checkValidity()) return false;


		var check_in_date = jQuery('#check_in_date').val();
		var check_out_date = jQuery('#check_out_date').val();
		var active_specific_date = jQuery('#active_specific_date').val();
		var active_week_month = jQuery('#active_week_month_sel').val();
		var active_type = jQuery('#active_type').val();
		var resort = jQuery('#resort').val();
		var unit_type_id = jQuery('#unit_type_id').val();
		var source = jQuery('#source').val();
		
		var source_partner_id = jQuery('#source_partner_id').val();
		var resort_confirmation_number = jQuery('#resort_confirmation_number').val();
		var active = jQuery("input[type='radio']:checked").val();
		var availability = jQuery('#availability').val();
		var available_to_partner_id = jQuery('#available_to_partner_id').val();
		var type = jQuery('#type').val();
		var room_id = jQuery('#room_id').val();
		var price = jQuery('#price').val();
		var rental_push_date = jQuery('#rental_push_date').val();
		// var given_to_partner_id = jQuery('#given_to_partner_id').val();
		var note = jQuery('#note').val();

		jQuery.ajax({
		    url : 'admin-ajax.php?&action=room_Form_edit',
		    type : 'POST',
		    data: {
		    	room_id: room_id,
		    	check_in_date: check_in_date, 
		    	check_out_date: check_out_date,
		    	active_specific_date: active_specific_date,
		    	active_week_month: active_week_month,
		    	active_type: active_type,
		    	resort: resort,
		    	unit_type_id: unit_type_id,
		    	source: source,
		    	source_partner_id: source_partner_id,
		    	resort_confirmation_number: resort_confirmation_number,
		    	active: active,
		    	availability: availability,
		    	available_to_partner_id: available_to_partner_id,
		    	type: type,
		    	note: note,
		    	price: price,
		    	rental_push_date: rental_push_date,
		    },
		    success : function(data) {
		    	console.log(data);
			if (data) {
			    jQuery('#sucessmessage').html('Room details updated successfully');
			    jQuery('#sucessmessage').css('display','block');
			    jQuery('#myModal').modal();
			} else{
			    alert('Web Services Failed');
			}

		    }
		}); 
	});



	jQuery('#country_2').change(function(){
		var id = jQuery(this).val();
		jQuery.ajax({
		    url : 'admin-ajax.php?&action=get_countryList',
		    type : 'POST',
		    data: {country: id},
		    success : function(data) {
			if (data.success) {
			    alert('Web Services Connected');
			} else{
			    alert('Web Services Failed');
			}
		    }
		}); 
	});
});
// /iCheck

// Table
jQuery('table input').on('ifChecked', function() {
    checkState = '';
    jQuery(this).parent().parent().parent().addClass('selected');
    countChecked();
});
jQuery('table input').on('ifUnchecked', function() {
    checkState = '';
    jQuery(this).parent().parent().parent().removeClass('selected');
    countChecked();
});
// alert msg
jQuery(document).ready(function() {
    jQuery('#activeAlertMsg').click(function(){
        var t = jQuery(this);
       var active = jQuery(this).data('active'); 
       jQuery.post('admin-ajax.php?&action=gpx_switch_alert',{active:active}, function(data){
           if(active == '1') {
    	   jQuery(t).removeClass('btn-danger').addClass('btn-success').text('Active').data('active', '0');
           }else{
    	   jQuery(t).removeClass('btn-success').addClass('btn-danger').text('Inactive').data('active', '1');
           }
       });
    });
    jQuery('#editAlertMsg').click(function(){
        jQuery('#alertMsg').prop('disabled', function(i, v) { return !v; });
        jQuery('#alertSubmit').toggle();
        jQuery('#alertMsg').focus();
    });
    jQuery('#alertMsg').blur(function(){
	var msgval = jQuery(this).val();
	 jQuery.post('admin-ajax.php?&action=gpx_alert_submit',{msg:msgval}, function(data){
	        jQuery('#alertMsg').prop('disabled', function(i, v) { return !v; });
	        jQuery('#alertSubmit').toggle();    
	 });
    });
    jQuery('#activeBookingDisabledMsg').click(function(){
	var t = jQuery(this);
	var active = jQuery(this).data('active'); 
	jQuery.post('admin-ajax.php?&action=gpx_switch_booking_disabled',{active:active}, function(data){
	    if(active == '1') {
		jQuery(t).removeClass('btn-danger').addClass('btn-success').text('Active').data('active', '0');
	    }else{
		jQuery(t).removeClass('btn-success').addClass('btn-danger').text('Inactive').data('active', '1');
	    }
	});
    });
    jQuery('#editBookingDisabledMsg').click(function(){
	jQuery('#bookingDisabledMsg').prop('disabled', function(i, v) { return !v; });
	jQuery('#bookingDisabledSubmit').toggle();
	jQuery('#bookingDisabledMsg').focus();
    });
    jQuery('#bookingDisabledMsg').blur(function(){
	var msgval = jQuery(this).val();
	jQuery.post('admin-ajax.php?&action=gpx_booking_disabeled_submit',{msg:msgval}, function(data){
	    jQuery('#bookingDisabledMsg').prop('disabled', function(i, v) { return !v; });
	    jQuery('#bookingDisabledSubmit').toggle();    
	});
    });
    jQuery('#activeCREmail').click(function(){
        var t = jQuery(this);
       var active = jQuery(this).data('active'); 
       jQuery.post('admin-ajax.php?&action=gpx_switch_crEmail',{active:active}, function(data){
           if(active == '1') {
    	   jQuery(t).removeClass('btn-danger').addClass('btn-success').text('Active').data('active', '0');
           }else{
    	   jQuery(t).removeClass('btn-success').addClass('btn-danger').text('Inactive').data('active', '1');
           }
       });
    });
    jQuery('#activeGF').click(function(){
	var t = jQuery(this);
	var active = jQuery(this).data('active'); 
	jQuery.post('admin-ajax.php?&action=gpx_switch_gf',{active:active}, function(data){
	    if(active == '1') {
		jQuery(t).removeClass('btn-danger').addClass('btn-success').text('Active').data('active', '0');
	    }else{
		jQuery(t).removeClass('btn-success').addClass('btn-danger').text('Inactive').data('active', '1');
	    }
	});
    });
    jQuery('#editminRentalFee').click(function(){
    	jQuery('#minRentalFee').prop('disabled', function(i, v) { return !v; });
    	jQuery('#minRentalFeeSubmit').toggle();
    	jQuery('#minRentalFee').focus();
    });
    jQuery('#minRentalFee').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_min_rental_fee',{min_rental:msgval}, function(data){
    		jQuery('#minRentalFee').prop('disabled', function(i, v) { return !v; });
    		jQuery('#minRentalFeeSubmit').toggle();    
    	});
    });
    jQuery('#edifbFee').click(function(){
    	jQuery('#fbFee').prop('disabled', function(i, v) { return !v; });
    	jQuery('#fbFeeSubmit').toggle();
    	jQuery('#fbFee').focus();
    });
    jQuery('#fbFee').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_fbfee_submit',{amt:msgval}, function(data){
    		jQuery('#fbFee').prop('disabled', function(i, v) { return !v; });
    		jQuery('#fbFeeSubmit').toggle();    
    	});
    });
    jQuery('#editExtensionFee').click(function(){
    	jQuery('#ExtensionFee').prop('disabled', function(i, v) { return !v; });
    	jQuery('#ExtensionFeeSubmit').toggle();
    	jQuery('#ExtensionFee').focus();
    });
    jQuery('#ExtensionFee').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_ExtensionFee_submit',{amt:msgval}, function(data){
    		jQuery('#ExtensionFee').prop('disabled', function(i, v) { return !v; });
    		jQuery('#ExtensionFeeSubmit').toggle();    
    	});
    });
    jQuery('#remove-report').click(function(){
    	var id = jQuery(this).data('id');
    	if(confirm('Are you sure you want to delete this report?  Your action cannot be undone!')){
    		jQuery.post('/wp-admin/admin-ajax.php?action=gpx_remove_report', {id: id}, function(){
    			window.location = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer';
    		});
    	}
    });
    jQuery('#editLateDepositFeeWithin').click(function(){
    	jQuery('#lateDepositFeeWithin').prop('disabled', function(i, v) { return !v; });
    	jQuery('#lateDepositFeeSubmitWithin').toggle();
    	jQuery('#lateDepositFeeWithin').focus();
    });
    jQuery('#lateDepositFeeWithin').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_lateDepositFee_submit_within',{amt:msgval}, function(data){
    		jQuery('#lateDepositFeeWithin').prop('disabled', function(i, v) { return !v; });
    		jQuery('#lateDepositFeeSubmitWithin').toggle();    
    	});
    });
    jQuery('#editLateDepositFee').click(function(){
	jQuery('#lateDepositFee').prop('disabled', function(i, v) { return !v; });
	jQuery('#lateDepositFeeSubmit').toggle();
	jQuery('#lateDepositFee').focus();
    });
    jQuery('#lateDepositFee').blur(function(){
	var msgval = jQuery(this).val();
	jQuery.post('admin-ajax.php?&action=gpx_lateDepositFee_submit',{amt:msgval}, function(data){
	    jQuery('#lateDepositFee').prop('disabled', function(i, v) { return !v; });
	    jQuery('#lateDepositFeeSubmit').toggle();    
	});
    });
    jQuery('#editExchagneFee').click(function(){
	jQuery('#exchangeFee').prop('disabled', function(i, v) { return !v; });
	jQuery('#exchageFeeSubmit').toggle();
	jQuery('#exchangeFee').focus();
    });
    jQuery('#exchangeFee').blur(function(){
	var msgval = jQuery(this).val();
	jQuery.post('admin-ajax.php?&action=gpx_exchangefee_submit',{amt:msgval}, function(data){
	    jQuery('#exchangeFee').prop('disabled', function(i, v) { return !v; });
	    jQuery('#exchageFeeSubmit').toggle();    
	});
    });
    jQuery('#editGFAmount').click(function(){
    	jQuery('#gfAmount').prop('disabled', function(i, v) { return !v; });
    	jQuery('#gfAmountSubmit').toggle();
    	jQuery('#gfAmount').focus();
    });
    jQuery('#gfAmount').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_gfamount_submit',{amt:msgval}, function(data){
    		jQuery('#gfAmount').prop('disabled', function(i, v) { return !v; });
    		jQuery('#gfAmountSubmit').toggle();    
    	});
    });
    jQuery('#editHoldLimitMessage').click(function(){
    	jQuery('#holdLimitMessage').prop('disabled', function(i, v) { return !v; });
    	jQuery('#editHoldLimitSubmit').toggle();
    	jQuery('#holdLimitMessage').focus();
    });
    jQuery('#holdLimitMessage').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_hold_limit_submit',{amt:msgval}, function(data){
    		jQuery('#holdLimitMessage').prop('disabled', function(i, v) { return !v; });
    		jQuery('#editHoldLimitSubmit').toggle();    
    	});
    });
    jQuery('#editHoldLimitTime').click(function(){
    	jQuery('#holdLimitTime').prop('disabled', function(i, v) { return !v; });
    	jQuery('#editHoldLimitTimeSubmit').toggle();
    	jQuery('#holdLimitTime').focus();
    });
    jQuery('#holdLimitTime').blur(function(){
    	var msgval = jQuery(this).val();
    	jQuery.post('admin-ajax.php?&action=gpx_hold_limit_time_submit',{amt:msgval}, function(data){
    		jQuery('#holdLimitTime').prop('disabled', function(i, v) { return !v; });
    		jQuery('#editHoldLimitTimeSubmit').toggle();    
    	});
    });
    jQuery('#editHoldLimitTimer').click(function(){
    	jQuery('#holdLimitTimer').prop('disabled', function(i, v) { return !v; });
    	jQuery('#editHoldLimitTimerSubmit').toggle();
    	jQuery('#holdLimitTimer').focus();
        });
    jQuery('#holdLimitTimer').blur(function(){
	var msgval = jQuery(this).val();
	jQuery.post('admin-ajax.php?&action=gpx_hold_limit_timer_submit',{amt:msgval}, function(data){
	    jQuery('#holdLimitTimer').prop('disabled', function(i, v) { return !v; });
	    jQuery('#editHoldLimitTimerSubmit').toggle();    
	});
    });
    jQuery('.edit-dae-ws').click(function(){
	var thisid = '#'+jQuery(this).data('input');
	jQuery(thisid).prop('disabled', function(i, v) { return !v; });
	jQuery(this).next().toggle();
	jQuery(thisid).focus();
    });
    jQuery('.input-dae-ws').blur(function(){
	var parent = jQuery(this).closest('row');
	var wsval = jQuery(this).val();
	var wsfield = jQuery(this).attr('name');
	jQuery.post('admin-ajax.php?&action=gpx_dae_ws_submit',{field:wsfield,val:wsval}, function(data){
	    jQuery(parent).find('edit-dae-ws').prop('disabled', function(i, v) { return !v; });
	    jQuery(parent).find('submit-dae-ws').toggle();    
	});
    });
});
var checkState = '';

jQuery('.bulk_action input').on('ifChecked', function() {
    checkState = '';
    jQuery(this).parent().parent().parent().addClass('selected');
    countChecked();
});
jQuery('.bulk_action input').on('ifUnchecked', function() {
    checkState = '';
    jQuery(this).parent().parent().parent().removeClass('selected');
    countChecked();
});
jQuery('.bulk_action input#check-all').on('ifChecked', function() {
    checkState = 'all';
    countChecked();
});
jQuery('.bulk_action input#check-all').on('ifUnchecked', function() {
    checkState = 'none';
    countChecked();
});

function countChecked() {
    if (checkState === 'all') {
	jQuery(".bulk_action input[name='table_records']").iCheck('check');
    }
    if (checkState === 'none') {
	jQuery(".bulk_action input[name='table_records']").iCheck('uncheck');
    }

    var checkCount = jQuery(".bulk_action input[name='table_records']:checked").length;

    if (checkCount) {
	jQuery('.column-title').hide();
	jQuery('.bulk-actions').show();
	jQuery('.action-cnt').html(checkCount + ' Records Selected');
    } else {
	jQuery('.column-title').show();
	jQuery('.bulk-actions').hide();
    }
}

// Accordion
jQuery(document).ready(function() {
    jQuery(".expand").on("click", function() {
	jQuery(this).next().slideToggle(200);
	$expand = jQuery(this).find(">:first-child");

	if ($expand.text() == "+") {
	    $expand.text("-");
	} else {
	    $expand.text("+");
	}
    });
});

// NProgress
if (typeof NProgress != 'undefined') {
    jQuery(document).ready(function() {
	NProgress.start();
    });

    jQuery(window).load(function() {
	NProgress.done();
    });
}


jQuery(window).load(function(){
   setTimeout(function(){
       jQuery('#active_week_month, #active_specific_date').hide(); 
   },100);
});
// bootstrap-wysiwyg

jQuery(document)
	.ready(
		function() {
		    function initToolbarBootstrapBindings() {
			var fonts = [ 'Serif', 'Sans', 'Arial', 'Arial Black',
				'Courier', 'Courier New', 'Comic Sans MS',
				'Helvetica', 'Impact', 'Lucida Grande',
				'Lucida Sans', 'Tahoma', 'Times',
				'Times New Roman', 'Verdana' ], fontTarget = jQuery(
				'[title=Font]').siblings('.dropdown-menu');
			jQuery
				.each(
					fonts,
					function(idx, fontName) {
					    fontTarget
						    .append(jQuery('<li><a data-edit="fontName '
							    + fontName
							    + '" style="font-family:\''
							    + fontName
							    + '\'">'
							    + fontName
							    + '</a></li>'));
					});
			jQuery('a[title]').tooltip({
			    container : 'body'
			});
			jQuery('.dropdown-menu input').click(function() {
			    return false;
			}).change(
				function() {
				    jQuery(this).parent('.dropdown-menu')
					    .siblings('.dropdown-toggle')
					    .dropdown('toggle');
				}).keydown('esc', function() {
			    this.value = '';
			    jQuery(this).change();
			});

			jQuery('[data-role=magic-overlay]')
				.each(
					function() {
					    var overlay = jQuery(this), target = jQuery(overlay
						    .data('target'));
					    overlay
						    .css('opacity', 0)
						    .css('position', 'absolute')
						    .offset(target.offset())
						    .width(target.outerWidth())
						    .height(
							    target
								    .outerHeight());
					});

			if ("onwebkitspeechchange" in document
				.createElement("input")) {
			    var editorOffset = jQuery('#editor').offset();

			    jQuery('.voiceBtn').css('position', 'absolute')
				    .offset(
					    {
						top : editorOffset.top,
						left : editorOffset.left
							+ jQuery('#editor')
								.innerWidth()
							- 35
					    });
			} else {
			    jQuery('.voiceBtn').hide();
			}
		    }

		    function showErrorAlert(reason, detail) {
			var msg = '';
			if (reason === 'unsupported-file-type') {
			    msg = "Unsupported format " + detail;
			} else {
			    console.log("error uploading file", reason, detail);
			}
			jQuery(
				'<div class="alert"> <button type="button" class="close" data-dismiss="alert">&times;</button>'
					+ '<strong>File upload error</strong> '
					+ msg + ' </div>').prependTo('#alerts');
		    }

		    initToolbarBootstrapBindings();

		    jQuery('#editor').wysiwyg({
			fileUploadError : showErrorAlert
		    });

		    prettyPrint();
		});

// compose

jQuery('#compose, .compose-close').click(function() {
    jQuery('.compose').slideToggle();
});
jQuery(document).ready(
	function() {
	    jQuery('html body').on(
		    'change',
		    '#country_1',
		    function() {
			var topel = jQuery(this).closest('.usage_exclude');
			var type = jQuery(topel).data('type');
			var country = jQuery(this).val();
			var rid = jQuery(this).attr('id');
			var sid = rid.split('_');
			var newid = parseInt(sid[1]) + 1;
			var id = 'region_' + newid;
			jQuery(this).closest('.form-group').nextAll(
				'.parent-regions').remove();
			jQuery.get(
				'admin-ajax.php?&action=get_gpx_region_list&country='
					+ country, function(data) {
				    jQuery(topel).find('.insert-above').before(
					    show_region_option(data, id, type));
				});
		    });
	});
jQuery(document)
	.ready(
		function() {
		    jQuery('html body').on(
			    'change',
			    '.parent-region',
			    function() {
				var topel = jQuery(this).closest('.usage_exclude');
				var type = jQuery(topel).data('type');
				var region = jQuery(this).val();
				var rid = jQuery(this).closest('.form-group')
					.attr('id');
				var sid = rid.split('_');
				var newid = parseInt(sid[1]) + 1;
				var id = 'region_' + newid;
				jQuery(this).closest('.form-group').nextAll(
					'.parent-regions').remove();
				jQuery.get(
					'admin-ajax.php?&action=get_gpx_region_list&region='
						+ region, function(data) {
					    if (data.length > 0)
						jQuery(topel).find('.insert-above').before(
							show_region_option(
								data, id, type));
					});
			    });
		    jQuery('#region-submit')
			    .click(
				    function(e) {
					e.preventDefault();
					jQuery(this).find('i').show();
					jQuery
						.post(
							'admin-ajax.php?&action=add_gpx_region',
							jQuery(this).closest(
								'form')
								.serialize(),
							function(data) {
							    if (data.success) {
								jQuery(
									'#region-submit')
									.find(
										'i')
									.hide();
								jQuery(
									'.update-nag')
									.removeClass(
										'nag-fail')
									.addClass(
										'nag-success')
									.text(
										data.msg)
									.show();
								if (data.type != 'edit')
								    jQuery(
									    '#new-region')
									    .attr(
										    'value',
										    '');
								else
								    location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_all';
							    } else {
								jQuery(
									'#region-submit')
									.find(
										'i')
									.hide();
								jQuery(
									'.update-nag')
									.removeClass(
										'nag-success')
									.addClass(
										'nag-fail')
									.text(
										data.msg)
									.show();
							    }
							    setTimeout(
								    function() {
									jQuery(
										'.update-nag')
										.hide(
											'show');
								    }, 4000);
							});
				    });
		    jQuery('#region-assign-submit')
			    .click(
				    function(e) {
					e.preventDefault();
					jQuery(this).find('i').show();
					jQuery
						.post(
							'admin-ajax.php?&action=assign_gpx_region',
							jQuery(this).closest(
								'form')
								.serialize(),
							function(data) {
							    if (data.success) {
								jQuery(
									'#region-assign-submit')
									.find(
										'i')
									.hide();
								jQuery(
									'.update-nag')
									.removeClass(
										'nag-fail')
									.addClass(
										'nag-success')
									.text(
										data.msg)
									.show();
								setTimeout(
									function() {
									    location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=regions_assignlist';
									}, 1500);

							    } else {
								jQuery(
									'#region-assign-submit')
									.find(
										'i')
									.hide();
								jQuery(
									'.update-nag')
									.removeClass(
										'nag-success')
									.addClass(
										'nag-fail')
									.text(
										data.msg)
									.show();
							    }
							    setTimeout(
								    function() {
									jQuery(
										'.update-nag')
										.hide(
											'show');
								    }, 4000);
							});
				    });
		    jQuery('html body').on('click', '.parent-delete label', function(){
			jQuery(this).closest('div').find('select').trigger('change');
		    });
		    jQuery('.parent-delete label').click(function(){
    		        jQuery(this).closest('div').find('select').trigger('change');
    		    });
		    jQuery('html body')
			    .on(
				    'click',
				    '.remove-element',
				    function() {
					var par = jQuery(this);
					   //var tt =  jQuery(par).closest('.usage_exclude').last('.parent-regions').find('.parent-region').trigger('change');
					jQuery(par).closest('.parent-delete').remove();
				    });
		    jQuery('.remove-element').click(function() {
			var par = jQuery(this);
			if(jQuery(par).hasClass('remove-region-assign'))
			   var tt =  jQuery(par).closest('.usage_exclude').last('.parent-regions').find('.parent-region').trigger('change');
			jQuery(par).closest('.parent-delete').remove();

		    });
		    jQuery('.remove-btn')
			    .click(
				    function() {
					var dataid = jQuery(this).data('id');
					var action = jQuery(this)
						.data('action');
					var loc = 'promos_all';
					var confirmmsg = '';
					if (action == 'add_gpx_region') {
					    loc = 'regions_all';
					}

					if (confirm('Are you sure you want to remove this record?'
						+ confirmmsg
						+ ' This action cannot be undone!')) {
					    jQuery(this).find('i').show();
					    jQuery
						    .post(
							    'admin-ajax.php?&action='
								    + action,
							    {
								remove : dataid
							    },
							    function(data) {
								if (data.success) {
								    jQuery(
									    '#region-submit')
									    .find(
										    'i')
									    .hide();
								    jQuery(
									    '.update-nag')
									    .removeClass(
										    'nag-fail')
									    .addClass(
										    'nag-success')
									    .text(
										    data.msg)
									    .show();
								    jQuery(
									    '#new-region')
									    .attr(
										    'value',
										    '');
								} else {
								    jQuery(
									    '.remove-btn')
									    .find(
										    'i')
									    .hide();
								    jQuery(
									    '.update-nag')
									    .removeClass(
										    'nag-success')
									    .addClass(
										    'nag-fail')
									    .text(
										    data.msg)
									    .show();
								}
								setTimeout(
									function() {
									    jQuery(
										    '.update-nag')
										    .hide(
											    'show');
									    location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg='
										    + loc;
									}, 2000);
							    });
					}
				    });
		    jQuery('html body').on('click', '.add-new .fa-plus', function(){
			var clonepar = jQuery(this).closest('.clone-group');
			var clonedpar = jQuery(clonepar).clone();
			if(!jQuery(clonedpar).hasClass('cloned'))
			    jQuery(clonedpar).addClass('cloned').find('.add-new').append('<i class="fa fa-minus" aria-hidden="true"></i>');
			jQuery(clonedpar).insertAfter(clonepar);
			jQuery(clonedpar).find('.hasDatepicker').removeClass('hasDatepicker').removeAttr('id');
			jQuery('.rbodatepicker').datepicker();
		    });
		    jQuery('html body').on('click', '.add-new .fa-minus', function(){
			jQuery(this).closest('.clone-group').remove();
		    });
		    jQuery('.ue-blackout, .ue-blackout-fg, .ue-travel, .ue-travel-fg').hide();
		    if(jQuery('.ue-blackout-fg.shown').length){
			 jQuery('.ue-blackout, .ue-blackout-fg').show();
		    }
		    if(jQuery('.ue-travel-fg.shown').length){
			jQuery('.ue-travel, .ue-travel-fg').show();
		    }
		    jQuery('html body').on('click', '.addBlackoutDates', function(e){
			e.preventDefault();
			var par = jQuery(this).closest('.clone-group');
			if(jQuery(par).find('.ue-blackout-fg').hasClass('shown')) {
			    var clonebo = jQuery(this).closest('.clone-group').find('.ue-blackout-fg');
			    jQuery(clonebo).find('.hasDatepicker').removeClass('hasDatepicker').removeAttr('id');
			    var clonedbo = jQuery(clonebo).remove('.cloned').clone().addClass('cloned');
			    jQuery(par).find('.boClone').append(clonedbo);
			    jQuery('.rbodatepicker').datepicker();
			}else{
			    jQuery(par).find('.ue-blackout-fg').addClass('shown').show();
			}
		    });
		    jQuery('html body').on('click', '.remove-blackout', function(e){
			e.preventDefault();
			var par = jQuery(this).closest('.ue-blackout-fg');
			if(jQuery(par).hasClass('cloned')){
			    jQuery(par).remove();
			}else{
			    jQuery(par).removeClass('shown').hide();
			    jQuery(par).find('input').val('');
			}
		    });
		    jQuery('html body').on('click', '.addTravelDates', function(e){
			e.preventDefault();
			var par = jQuery(this).closest('.clone-group');
			if(jQuery(par).find('.ue-travel-fg').hasClass('shown')) {
			    var clonebo = jQuery(this).closest('.clone-group').find('.ue-travel-fg');
			    jQuery(clonebo).find('.hasDatepicker').removeClass('hasDatepicker').removeAttr('id');
			    var clonedbo = jQuery(clonebo).remove('.cloned').clone().addClass('cloned');
			    jQuery(par).append(clonedbo);
			    jQuery('.rbodatepicker').datepicker();
			}else{
			    jQuery(par).find('.ue-travel-fg').addClass('shown').show();
			}
		    });
		    jQuery('html body').on('click', '.blackout-clone-btn .fa-plus', function(e){
			e.preventDefault();
			var par = jQuery(this).closest('.blackout-clone-gp');
			var clonebo = jQuery(par).find('.blackout-clone');
			jQuery(clonebo).find('.hasDatepicker').removeClass('hasDatepicker').removeAttr('id');
			var clonedbo = jQuery(clonebo).remove('.cloned').clone().addClass('cloned');
			jQuery(par).append(clonedbo);
			jQuery('.datepicker').datepicker();
		    });
		    jQuery('html body').on('click', '.blackout-clone-btn .fa-minus', function(e){
			e.preventDefault();
			jQuery(this).closest('.blackout-clone').remove();
		    });
		    jQuery('html body').on('change', 'select[name="usage_resort[]"]', function(){
			var urArr = [];
			       jQuery('select[name="usage_resort[]"]').each(function(){
				   urArr.push(jQuery(this).val());
			       });
			       jQuery('.ue-blackout-fg').each(function(){
				   if(jQuery(this).find('.metaResortBlackoutResorts').length) {
				       jQuery(this).find('.metaResortBlackoutResorts').val(urArr.join(","));
				   }else{
					  jQuery(this).append('<input type="hidden" class="metaResortBlackoutResorts" name="metaResortBlackoutResorts[]" value="['+urArr.join(",")+']">'); 
				   }
			       });
			       jQuery('.ue-travel-fg').each(function(){
				   if(jQuery(this).find('.metaResortTravelResorts').length) {
				       jQuery(this).find('.metaResortTravelResorts').val(urArr.join(","));
				   }else{
				       jQuery(this).append('<input type="hidden" class="metaResortTravelResorts" name="metaResortTravelResorts[]" value="['+urArr.join(",")+']">'); 
				   }
			       });
		    });
		    if(jQuery('.rbodatepicker').length){
			jQuery('.rbodatepicker').removeClass('hasDatepicker').removeAttr('id');
		    }
		    jQuery('.rbodatepicker').datepicker({
			   onSelect: function(setdate, inst) {
			       var urArr = [];
			       jQuery('select[name="usage_resort[]"]').each(function(){
				   urArr.push(jQuery(this).val());
			       });
			       jQuery('.ue-blackout-fg').each(function(){
				   if(jQuery(this).find('.metaResortBlackoutResorts').length) {
				       jQuery(this).find('.metaResortBlackoutResorts').val(urArr.join(","));
				   }else{
					  jQuery(this).append('<input type="hidden" class="metaResortBlackoutResorts" name="metaResortBlackoutResorts[]" value="['+urArr.join(",")+']">'); 
				   }
			       });
			       jQuery('.ue-travel-fg').each(function(){
				   if(jQuery(this).find('.metaResortTravelResorts').length) {
				       jQuery(this).find('.metaResortTravelResorts').val(urArr.join(","));
				   }else{
				       jQuery(this).append('<input type="hidden" class="metaResortTravelResorts" name="metaResortTravelResorts[]" value="['+urArr.join(",")+']">'); 
				   }
			       });
			       jQuery('#'+inst.id).attr('value',setdate);
			   }
			});
		    jQuery('html body').on('change', '.switchmetausage',
			    function() {
				var usage = jQuery(this).val();
				var $parent = jQuery(this).closest('.clone-group');
				jQuery($parent).find('.usage-add').html('');
				jQuery.get(
					'admin-ajax.php?action=get_gpx_switchuage&usage='
						+ usage+'&type=usage', function(data) {
					    jQuery($parent).find('.usage-add')
						    .html(data.html);
					});
				if(usage == 'resort') {
				    jQuery($parent).find('.ue-blackout, .ue-travel').show();
				}else{
				    jQuery($parent).find('.ue-blackout, .ue-travel').hide();
				}
			    });
		    jQuery('html body').on('change', '.switchmetaexclusions',
			    function() {
				var usage = jQuery(this).val();
				var $parent = jQuery(this).closest('.clone-group');
				jQuery.get(
					'admin-ajax.php?action=get_gpx_switchuage&usage='
					+ usage+'&type=exclude', function(data) {
					    jQuery($parent).find('.exclusion-add')
					    .html(data.html);
					});
			    });
		    jQuery('html body')
			    .on(
				    'change',
				    '.metaCustomerResortSpecific',
				    function() {
					var answer = jQuery(this).val();
					var $parent = jQuery(this).closest('.clone-group');
					if (answer == "Yes")
					    jQuery
						    .get(
							    'admin-ajax.php?action=get_gpx_switchuage&usage=resort',
							    function(data) {
								jQuery($parent).find(
									'.rs-add')
									.html(
										data.html);
							    });
					else {
					    jQuery.get('admin-ajax.php?action=get_gpx_customers', function(data){
						jQuery('.rs-add').html(data.html);
						jQuery('.owner-list').multiselect({
						    enableFiltering: true,
						});
					    });
					}
					    
				    });
		    jQuery('#exclusiveWeeks').change(function(){
			if(jQuery(this).val() != '') {
			    jQuery('#availability').val('Site-wide');
			}
		    });
		    jQuery('#availability').change(function(){
			if(jQuery(this).val() == 'Landing Page') {
			    jQuery('.exclusiveWeeksBox').hide();
			} else {
			    jQuery('.exclusiveWeeksBox').show();
			}
		    });
		    jQuery('.owner-list').multiselect({
			    enableFiltering: true,
		    });
		    jQuery('.daterange').daterangepicker();
		    jQuery('.advanced_date_filter').click(function(e){
			e.preventDefault();
			var fType = jQuery(this).data('filtertype');
			if(fType == 'clear'){
			    jQuery('#filteredDates, #filterType').val('');
			}else {
			    jQuery('#filterType').val(fType);
			}
			jQuery('#cr-date-filter').submit(); 
		    });
		    jQuery('html body').on('blur', '#userSearch', function(){
			var searchTerm = jQuery(this).val();
			jQuery.get('admin-ajax.php?action=get_gpx_findowner&search='+searchTerm,function(data){
			   jQuery('#selectFromList').html(data.html); 
			});
		    });
		    jQuery('html body').on('click', '#userSearchBtn', function(e){
			e.preventDefault();
		    });
			jQuery('html body').on('click', '.ownerSelectFrom', function(e){
			e.preventDefault();
			var id=jQuery(this).data('id');
			var name=jQuery(this).data('name');
			var login=jQuery(this).data('login');
			jQuery('#selectToList').append('<option value="'+id+'" selected="selected">'+login+' '+name+'</option>');
		    });
			    if(jQuery('#selectToList').length) {
				var dup = {};
				jQuery('#selectToList > option').each(function(){
				      var optval = jQuery(this).attr('value');
				      if(!jQuery(this).is(':selected')){
					  jQuery(this).remove();
				      }
				      if(dup[optval]) {
					  console.log('remove');
//					  jQuery(this).remove();
				      }else{
					  dup[optval] = true;
				      }
				});
			    }
		    jQuery('html body')
			    .on(
				    'click',
				    '.resort-list',
				    function(e) {
					e.preventDefault();

					var topel = jQuery(this).closest('.usage_exclude');
					var type = jQuery(topel).data('type');
					
					var parentEl = jQuery(this).closest(
						'.row').prev().find('select');
					var parentType = jQuery(parentEl).attr(
						'name');
					if (parentType == 'country') {
					    alert('You must select a region!');
					} else {
					    var parentVal = jQuery(parentEl)
						    .val();
					    if (parentVal.length == '') {
						alert('You must select a sub-region!');
					    } else {
						jQuery
							.post(
								'admin-ajax.php?action=get_gpx_list_resorts',
								{
								    value : parentVal,
								    type: type
								},
								function(data) {
								    jQuery(topel).find(
									    '.insert-resorts')
									    .prepend(
										    data);
								});
					    }
					}
				    });
		    if(jQuery('#bookingFunnel').length)
		    {
			var bfval = jQuery('#bookingFunnel').val();
			if(bfval == 'No')
			    {
			    	jQuery('.promo').hide();
			    	jQuery('.coupon').show();
			    }
			else
			    {
			    	jQuery('.coupon').hide();
			    	jQuery('.promo').show();
			    }
		    }
		    if(jQuery('#metaType').length)
		    {
			var $val = jQuery('#metaType').val();
			if($val == '2 for 1 Deposit') {
			    jQuery('.two4one-hide').hide();
			    jQuery('.two4one-show').show();
			    jQuery('.dateTextSwitch').text('(Travel Period)');
			}else{
			    jQuery('.two4one-hide').not('.upsell').show();
			    jQuery('.two4one-show').hide();
			    jQuery('.dateTextSwitch').text('(Available for Viewing)');
        		}
			if($val == 'Auto Create Coupon Template -- Pct Off' || $val == 'Auto Create Coupon Template -- Dollar Off' || $val == 'Auto Create Coupon Template -- Set Amt') {
			    jQuery('#actcFG').show();
			    jQuery('#acCoupon').hide();
			    jQuery('#bookingFunnel').val('No').change();
			}
			else
			{
			    jQuery('#acCoupon').show();
			    jQuery('#actcFG, #ctSelectRow').hide();
			}
		    }
		    if(jQuery('#acCoupon').length)
		    {
			if(jQuery('#acCouponField').is(':checked')){
			    var selected = jQuery('#couponTemplate').val();
			    jQuery('#ctSelectRow').show();
			    jQuery.post('admin-ajax.php?&action=gpx_get_coupon_template', {selected: selected}, function(data){
				jQuery('#couponTemplate').html(data.html);
			    });
			}
			else
			{
			    jQuery('#ctSelectRow').hide();
			}
			
		    }
		    jQuery('#metaType').change(function(){
			var $val = jQuery(this).val();
			if($val == 'BOGO' || $val == 'BOGOH') {
			    jQuery('#Amount').attr('disabled', true);
			}else{
			    jQuery('#Amount').attr('disabled', false);
			}
			if($val == 'Auto Create Coupon Template -- Pct Off' || $val == 'Auto Create Coupon Template -- Dollar Off' || $val == 'Auto Create Coupon Template -- Set Amt') {
			    jQuery('#actcFG').show();
			    jQuery('#acCoupon').hide();
			    jQuery('#bookingFunnel').val('No').change();
			}else{
			    jQuery('#acCoupon').show();
			    jQuery('#actcFG').hide();
			    if(!jQuery('#acCouponField').is(':checked')){
				jQuery('#ctSelectRow').hide
			    }
			}
			if($val == '2 for 1 Deposit') {
			    jQuery('#Amount').prop('value', '0');
			    jQuery('.two4one-hide').hide();
			    jQuery('.two4one-show').show();
			    jQuery('.dateTextSwitch').text('(Travel Period)');
			}else{
			    jQuery('.two4one-hide').show();
			    jQuery('.two4one-show').show();
			    jQuery('.dateTextSwitch').text('(Available for Viewing)');
        		}
			if(jQuery('#acCouponField').is(':checked')) {
			    jQuery('#ctSelectRow').show();
			} else {
			    jQuery('#ctSelectRow').hide();
			}
		    });
		    jQuery('#acCouponField').change(function(){
			if(jQuery(this).is(':checked')) {
			    var selected = jQuery('#couponTemplate').val();
			    jQuery('#ctSelectRow').show();
			    jQuery.post('admin-ajax.php?&action=gpx_get_coupon_template', {selected: selected}, function(data){
				jQuery('#couponTemplate').html(data.html);
			    });
			}else{
			    jQuery('#ctSelectRow').hide();
			}
			    
		    });
		    jQuery('#metaTransactionType').blur(function(){
			var $val = jQuery(this).val();
			if(jQuery.inArray('upsell', $val) !== -1 || $val == 'upsell') {
			    jQuery('.upsell').show();
			}else{
			    jQuery('.upsell').hide();
			}
		    });
		    
		    jQuery('#bookingFunnel').change(function() {
			var bfval = jQuery(this).val();
			if (bfval == 'No') {
			    jQuery('#promoorcoupon').text('Coupon Code');
			    jQuery('.coupon').show();
			    jQuery('.promo').hide();
			    jQuery('.pcSwitchType').text('Coupon');
			} else {
			    jQuery('#promoorcoupon').text('Slug');
			    jQuery('.coupon').hide();
			    jQuery('.promo').show();
			    jQuery('.pcSwitchType').text('Promo');
			}
		    });
		    jQuery('#Name').blur(function() {
			var nameVal = jQuery(this).val();
			if (!jQuery('#Slug').val()) {
			    var slug = nameVal.replace(/\s/g,"-");
			    slug = slug.replace("$",'');
			    slug = slug.replace("!",'');
			    slug = slug.replace("@",'');
			    slug = slug.replace("#",'');
			    slug = slug.replace("&",'-and-');
			    jQuery('#Slug').val(slug);
			}
		    });
		    jQuery('.timepicker').timepicker();
		    jQuery('.datepicker').datepicker();
		    jQuery('.fapicker').iconpicker();
		    jQuery('#StartDate').change(function(){
			var date = jQuery(this).val();
			if(jQuery('#metaBookStartDate').val().length == 0) {
			    jQuery('#metaBookStartDate').val(date);
			}
			if(jQuery('#metaTravelStartDate').val().length == 0) {
			    jQuery('#metaTravelStartDate').val(date);
			}
		    });
		    jQuery('#EndDate').change(function(){
			var date = jQuery(this).val();
			if(jQuery('#metaBookEndDate').val().length == 0) {
			    jQuery('#metaBookEndDate').val(date);
			}
			if(jQuery('#metaTravelEndDate').val().length == 0) {
			    jQuery('#metaTravelEndDate').val(date);
			}
		    });
		    jQuery('html body').on('change', 'select', function(){
			jQuery(this).find('option').each(function(){
			   if(jQuery(this).is(':selected')){
			       jQuery(this).attr('selected', 'selected');
			   } 
			   else {
			       jQuery(this).removeAttr('selected');
			   }
			});
		    });
		    jQuery('#promo-add')
			    .submit(
				    function(e) {
					e.preventDefault();
					var $this = jQuery(this);
					
					
					jQuery('.switchmetausage').each(function(){
					    console.log(jQuery(this).val());
					   if(jQuery(this).val() == 'region') {
					      var lastpar =  jQuery(this).closest('.clone-group').find('.parent-region:last').val();
					      if(lastpar == 'undefined' || lastpar == '') {
						  alert('You must select or remove the region');
						  jQuery(this).closest('.clone-group').find('.parent-region:last').focus();
						  return false;
					      }
					      jQuery($this).append('<input type="hidden" name="metaSetRegion[]" value="'+lastpar+'">');
					   } 
					});
					
					jQuery('.switchmetaexclusions').each(function(){
					    console.log(jQuery(this).val());
					    if(jQuery(this).val() == 'region') {
						var lastpar =  jQuery(this).closest('.clone-group').find('.parent-region:last').val();
						if(lastpar == 'undefined' || lastpar == '') {
						    alert('You must select or remove the region');
						    jQuery(this).closest('.clone-group').find('.parent-region:last').focus();
						    return false;
						}
						jQuery($this).append('<input type="hidden" name="metaSetRegionExclude[]" value="'+lastpar+'">');
					    } 
					});
					
					var $usageexclude = jQuery($this).find('.usage-exclusion-group').html();
					jQuery('#metaUseExc').val($usageexclude);
					var $data = jQuery($this).serialize();
					jQuery('#submit-btn').find('i').show();
					jQuery
						.ajax({
						    url : 'admin-ajax.php?&action=add_gpx_promo',
						    type : 'POST',
						    data : $data,
						    success : function(data) {
							if (data.success) {
							    jQuery(
								    '#submit-btn')
								    .find('i')
								    .hide();
							    jQuery(
								    '.update-nag')
								    .removeClass(
									    'nag-fail')
								    .addClass(
									    'nag-success')
								    .text(
									    data.msg)
								    .show();
							    setTimeout(
								    function() {
									location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=promos_all';
								    }, 1500);

							} else {
							    jQuery(
								    '#rsubmit-btn')
								    .find('i')
								    .hide();
							    jQuery(
								    '.update-nag')
								    .removeClass(
									    'nag-success')
								    .addClass(
									    'nag-fail')
								    .text(
									    data.msg)
								    .show();
							}
							setTimeout(
								function() {
								    jQuery(
									    '.update-nag')
									    .hide(
										    'show');
								}, 4000);
						    }
						});
				    });
		    jQuery('.newResort')
			    .click(
				    function(e) {
					e.preventDefault();
					if (jQuery('#metaCustomerResortSpecific').length != 0)
					    jQuery(
						    '#metaCustomerResortSpecific')
						    .trigger('change');
					else {
					    var type = jQuery(this).data('type');
					    jQuery(type).trigger(
					    'change');
					}
					    
					jQuery('#newResort').hide();
					return false;
				    });
		    jQuery('.title_right').on('click', '#featured-resort',  function(e) {

				var box = jQuery(this).find('i');
				var featured = jQuery(this).data('featured');
				var resort = jQuery(this).data('resort');
				jQuery
					.ajax({
					    url : 'admin-ajax.php?&action=featured_gpx_resort',
					    type : 'POST',
					    data : {featured: featured, resort: resort},
					    success : function(data) {
						if (data.success) {
						    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.msg).show();
						    box.toggleClass('fa-square fa-check-square');
						    jQuery('#featured-resort').data('featured', data.status);

						} else {
						    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.msg).show();
						}
						setTimeout(
							function() {
							    jQuery('.update-nag').hide( 'show');
							}, 4000);
					    }
					});
				return false;
			    });
	    jQuery('.title_right').on('click', '#ai-resort',  function(e) {
		e.preventDefault();
		var box = jQuery(this).find('i');
		var ai = jQuery(this).data('ai');
		var resort = jQuery(this).data('resort');
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=ai_gpx_resort',
		    type : 'POST',
		    data : {ai: ai, resort: resort},
		    success : function(data) {
			if (data.success) {
			    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.msg).show();
			    box.toggleClass('fa-square fa-check-square');
			    jQuery('#ai-resort').data('ai', data.status);
			    console.log(data);
			} else {
			    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.msg).show();
			}
			setTimeout(
				function() {
				    jQuery('.update-nag').hide( 'show');
				}, 4000);
		    }
		});
		return false;
	    });
	    jQuery('.title_right').on('click', '#guest-fees',  function(e) {

		var box = jQuery(this).find('i');
		var enabled = jQuery(this).data('enabled');
		var resort = jQuery(this).data('resort');
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=guest_fees_gpx_resort',
		    type : 'POST',
		    data : {enabled: enabled, resort: resort},
		    success : function(data) {
			if (data.success) {
			    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.msg).show();
			    box.toggleClass('fa-square fa-check-square');
			    jQuery('#guest-fees').data('enabled', data.status);
			    
			} else {
			    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.msg).show();
			}
			setTimeout(
				function() {
				    jQuery('.update-nag').hide( 'show');
				}, 4000);
		    }
		});
		return false;
	    });
	    jQuery('.title_right').on('click', '#active-resort',  function(e) {
		var box = jQuery(this).find('i');
		var active = jQuery(this).data('active');
		var resort = jQuery(this).data('resort');
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=active_gpx_resort',
		    type : 'POST',
		    data : {active: active, resort: resort},
		    success : function(data) {
			if (data.success) {
			    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.msg).show();
			    box.toggleClass('fa-square fa-check-square');
			    jQuery('#active-resort').data('active', data.status);
			    
			} else {
			    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.msg).show();
			}
			setTimeout(
				function() {
				    jQuery('.update-nag').hide( 'show');
				}, 4000);
		    }
		});
		return false;
	    });
	    jQuery('.title_right').on('click', '#reload-resort',  function(e) {
		var resort = jQuery(this).data('resort');
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=get_manualResortUpdate',
		    type : 'POST',
		    data : {resort: resort},
		    success : function(data) {
			if (data.success) {
			    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.success).show();
			    
			} else {
			    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text('Please refresh the screen and try again.').show();
			}
			setTimeout(
				function() {
				    jQuery('.update-nag').hide( 'show');
				}, 4000);
		    }
		});
		return false;
	    });
	    jQuery('.title_right').on('click', '#featured-region',  function(e) {
		var featured = jQuery(this).data('featured');
		var region = jQuery(this).data('region');
		jQuery('.featured-status').removeClass('fa-square fa-check-square');
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=featured_gpx_region',
		    type : 'POST',
		    data : {featured: featured, region: region},
		    success : function(data) {
			if (data.success) {
			    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.msg).show();
			    jQuery('.featured-status').addClass(data.fastatus);
			    jQuery('#featured-region').data('featured', data.status);
			    
			} else {
			    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.msg).show();
			}
			setTimeout(
				function() {
				    jQuery('.update-nag').hide( 'show');
				}, 4000);
		    }
		});
		return false;
	    });
	    jQuery('.title_right').on('click', '#hidden-region',  function(e) {
		e.preventDefault();
		var hidden = jQuery(this).data('hidden');
		var region = jQuery(this).data('region');
		jQuery('.hidden-status').removeClass('fa-square fa-check-square');
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=hidden_gpx_region',
		    type : 'POST',
		    data : {hidden: hidden, region: region},
		    success : function(data) {
			if (data.success) {
			    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text( data.msg).show();
			    jQuery('.hidden-status').addClass(data.fastatus);
			    jQuery('#hidden-region').data('hidden', data.status);
			    
			} else {
			    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.msg).show();
			}
			setTimeout(
				function() {
				    jQuery('.update-nag').hide( 'show');
				}, 4000);
		    }
		});
		return false;
	    });
	    jQuery('.resort-tabs').on('click', '.resort-lock', function(){
			jQuery(this).toggleClass('fa-lock, fa-unlock');
			var el = jQuery(this).closest('.form-group')
				.find('.form-element');
			jQuery(el).prop('disabled', function(i, v) {
			    return !v;
			});
		    });
		jQuery('.resort-tabs').on('change', '.resort-general-edit', function(){
		 insertattribute(jQuery(this), 'update') ;
		 jQuery(this).attr('disabled', 'disabled');
		 jQuery(this).closest('.attribute-group').find('.resort-lock').removeClass(', fa-unlock');
	    });
	    jQuery('.resort-tabs').on('change', '.resort-descriptions', function(){
		insertattribute(jQuery(this), 'descriptions', '.edit-resort-group') ;
		jQuery(this).attr('disabled', 'disabled');
		jQuery(this).closest('.edit-resort-group').find('.resort-lock').removeClass(', fa-unlock');
//		location.reload(true);
		var id = new RegExp('[\?&]id=([^&#]*)').exec(window.location.search);
//		location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id='+id[0];
	    });
	    jQuery('.resort-tabs').on('click', '.path-btn', function(e){
		e.preventDefault();
		var btn = jQuery(this);
		var status = btn.data('active');
		btn.toggleClass('btn-default btn-primary');
		btn.find('i').toggleClass('fa-check-square fa-square');
		if(status == '0') {
		    //status needs to change to active
		    btn.data('active', '1');
		} else {
		    //status needs to be inactive
		    btn.data('active', '0');
		}
		
		
		insertattribute(btn, 'descriptions', '.edit-resort-group') ;
		btn.blur();
	    });
	    jQuery('.resort-tabs').on('click', '.date-filter-desc, .ran-btn', function(e){
			e.preventDefault();
			var els = jQuery(this).closest('.repeatable').find('.edit-resort-group');
			els.each(function(){
			    var btn = jQuery(this).find('.btn-group');
			    var btn = jQuery(this);
			    insertattribute(btn, 'descriptions', '.edit-resort-group') ;
			});
	    });
	    jQuery('.resort-tabs').on('click', '.insert-attribute', function(e){
		e.preventDefault();
		insertattribute(jQuery(this));
		setTimeout(function(){
		    location.reload(true);
		},500);
	    });
	    jQuery('.image_alt, .image_title, .image_video').change(function(){
			var id = jQuery(this).data('id');
			var title = jQuery(this).closest('li').find('.image_title').val();
			var alt = jQuery(this).closest('li').find('.image_alt').val();
			var video = jQuery(this).closest('li').find('.image_video').val();
			jQuery
			.ajax({
			    url : 'admin-ajax.php?&action=gpx_resort_image_update_attr',
			    type : 'POST',
			    data : {id: id, title: title, alt: alt},
			    success : function(data) {
				if (data.success) {
				} 
			    }
			});		
	    });
	    jQuery('.attribute-list-item').on('click', '.attribute-list-item-remove', function(){
		var el = jQuery(this).closest('li');
		var item = el.data('id');
		var resort = el.closest('form').find('.resortID').val();
		var type = el.closest('form').find('.new-attribute').data('type');
		var dateFrom = el.closest('.repeatable').find('.dateFilterFrom').val();
		var dateTo = el.closest('.repeatable').find('.dateFilterTo').val();
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=gpx_resort_attribute_remove',
		    type : 'POST',
		    data : {item: item, type: type, resort: resort, from: dateFrom, to: dateTo},
		    success : function(data) {
			if (data.success) {
			    el.remove();
			    location.reload(true);
			} else {
			    
			}
		    }
		});	
	    });
	    jQuery('#images').on('click', '.sortable-image i.fa', function(){
	    	jQuery('#gpx-ajax-loading').show();
		var par = jQuery(this).closest('ul');
		var thisli = jQuery(this).closest('li');
		var resort = jQuery(this).closest('form').find('.resortID').val();
		var id = thisli.data('id');
		jQuery.ajax({
		   data: {
			image: id,
			resortID: resort
		   } ,
		   type: 'POST',
		   url: 'admin-ajax.php?&action=gpx_image_remove',
	            success : function(data) {
	            	
	        	thisli.remove();
	        	var i = 0;
	        	par.find('li').each(function(i) { 
	        	      jQuery(this).data('id', i); // updates the data object
	                      jQuery(this).attr('data-id', i); // updates the attribute
	                      jQuery(this).attr('id', 'image-'+i);
	                      i++;
	                 });
	        		jQuery('#gpx-ajax-loading').hide();
				var id = new RegExp('[\?&]id=([^&#]*)').exec(window.location.search);
				location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id='+id[0];
	            }
		});
	    });
		jQuery('.resort-edit').submit(function(e){
      		   e.preventDefault(); 
      		   return false;
      		});
	    jQuery('.resort-tabs').on('mouseenter', '.attribute-list', function(){
		    jQuery(this).sortable({
		        update: function (event, ui) {
			    	jQuery('#gpx-ajax-loading').show();
		        	var el = jQuery(this);
		        	var par = el.parent();
		            var data = el.sortable('serialize');
		            var resort = el.closest('form').find('.resortID').val();
		            data += '&resortID=' + resort + '&type=images';
		            console.log(data);
		            // POST to server using $.post or $.ajax
		            jQuery.ajax({
		                data: data,
		                type: 'POST',
		                url: 'admin-ajax.php?&action=gpx_resort_attribute_reorder',
		                success : function(data) {
		                	par.find('li').each(function(i) { 
		                		jQuery(this).data('id', i); // updates the data object
		                        jQuery(this).attr('data-id', i); // updates the attribute
		                        jQuery(this).attr('id', 'image-'+i);
		                     });
		    		    	jQuery('#gpx-ajax-loading').hide();
		                }
		            });
		        }
		    });
	    });
	    jQuery('.resort-tabs').on('mouseenter', '.images-sortable', function(){
		jQuery(this).sortable({
		    update: function (event, ui) {
			jQuery('#gpx-ajax-loading').show();
			var el = jQuery(this);
			var par = el.parent();
			var data = el.sortable('serialize');
			var resort = el.closest('form').find('.resortID').val();
			data += '&resortID=' + resort + '&type=images';
			console.log(data);
			// POST to server using $.post or $.ajax
			jQuery.ajax({
			    data: data,
			    type: 'POST',
			    url: 'admin-ajax.php?&action=gpx_resort_image_reorder',
			    success : function(data) {
				par.find('li').each(function(i) { 
				    jQuery(this).data('id', i); // updates the data object
				    jQuery(this).attr('data-id', i); // updates the attribute
				    jQuery(this).attr('id', 'image-'+i);
				});
				jQuery('#gpx-ajax-loading').hide();
				var id = new RegExp('[\?&]id=([^&#]*)').exec(window.location.search);
				location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_edit&id='+id[0];
			    }
			});
		    }
		});
	    });
	    jQuery('.resort-tabs').on('mouseenter', '.attribute-list', function(){
	    	
		    
		    jQuery(this).sortable({
		        axis: 'y',
		        update: function (event, ui) {
			    	jQuery('#gpx-ajax-loading').show();
		        	var el = jQuery(this);
		        	var par = el.parent();
		        	console.log(par);
		            var data = el.sortable('serialize');
		            var resort = el.closest('form').find('.resortID').val();
					var type = el.closest('form').find('.new-attribute').data('type');
					var dateFrom = el.closest('.repeatable').find('.dateFilterFrom').val();
					var dateTo = el.closest('.repeatable').find('.dateFilterTo').val();
		            data += '&resortID=' + resort + '&type=' + type+'&from='+dateFrom+'&to='+dateTo;
		            // POST to server using $.post or $.ajax
		            jQuery.ajax({
		                data: data,
		                type: 'POST',
		                url: 'admin-ajax.php?&action=gpx_resort_attribute_reorder',
		                success : function(data) {
		                	par.find('li').each(function(i) { 
		                		jQuery(this).data('id', i); // updates the data object
		                        jQuery(this).attr('data-id', i); // updates the attribute
		                        jQuery(this).attr('id', type+'-'+i);
		                     });
		    		    	jQuery('#gpx-ajax-loading').hide();
		                }
		            });
		        }
		    });
	    });
	    jQuery('.resort-tabs').on('click', '.clone-group .fa-copy', function(){
	    	var par = jQuery(this).closest('.repeatable');
	    	var clone = par.clone();
	    	var cloned = clone.insertAfter(par);
	    	var seq = parseInt(par.data('seq')) + 10 ;
//	    	cloned.find('.ui-sortable').sortable();
	    	cloned.attr('data-seq', seq);
	    	cloned.find('.new-attribute').attr('disabled', 'disabled');
	    	cloned.remove('.fa-lock').removeClass('fa-unlock');
	    	cloned.find('.from-date').attr('data-oldfrom', '');
	    	cloned.find('.from-date').attr('data-oldorder', seq);
	    	cloned.find('.to-date').attr('data-oldto', '');
	    	cloned.find('.resort-edit').hide();
	    	jQuery('html, body').animate({
                        scrollTop: cloned.offset().top
                    }, 1000);
	    	
//	    	location.reload(true);
	    });
	    jQuery('html body').on('change', '.from-date, .to-date', function(){
	    	jQuery(this).closest('.repeatable').find('.resort-edit').show();
	    });
	    jQuery('.resort-tabs').on('click', '.clone-group .fa-times-circle-o', function(){
		var el = jQuery(this);
		if(confirm("Are you sure you want to remove this entry?")) {
		    
			var dateFrom = el.closest('.repeatable').find('.dateFilterFrom').val();
			var oldorder = el.closest('.repeatable').find('.dateFilterFrom').data('oldorder');
			var dateTo = el.closest('.repeatable').find('.dateFilterTo').val();
			var datatype = el.data('type');
			var resortID = el.data('resortid');
			jQuery.ajax({
			   data: {
			       from: dateFrom,
			       to: dateTo,
			       type: datatype,
			       resortID: resortID,
			       oldorder: oldorder,
			   } ,
			   type: "POST",
			   url: 'admin-ajax.php?&action=gpx_resort_repeatable_remove',
			   success: function(data) {
			       el.closest('.repeatable').remove();
			   }
			});
		}
//		location.reload(true);
	    });
	    jQuery('.resort-tabs').on('click', '.date-filter', function(e){
		e.preventDefault();
		var rep = jQuery(this).closest('.repeatable');
		var from = rep.find('.from-date').val();
		var oldfrom = rep.find('.from-date').data('oldfrom');
		var oldorder = rep.find('.from-date').data('oldorder');
		var to = rep.find('.to-date').val();
		var oldto = rep.find('.to-date').data('oldto');
		var form = rep.find('form');
		form.each(function(){
		   var li = jQuery(this).find('.attribute-list-item');
		   var resort = jQuery(this).find('.resortID').val();
		   var attributeType = jQuery(this).find('.attributeType').val();
		   var list = [];
		   li.each(function(){
		       var text = jQuery(this).text();
		       list.push(text);
		   });
		   if(list.length != 0) {
			   jQuery.ajax({
				    url : 'admin-ajax.php?&action=gpx_resort_attribute_new',
				    type : 'POST',
				    async: false,
				    data: {
					resort: resort,
					type: attributeType,
					list: list,
					from: from,
					oldfrom: oldfrom,
					oldorder: oldorder,
					to: to,
					oldto: oldto,
				    }
			   });			       
		   }

		});
		location.reload(true);
	    });
	    jQuery('.resort-tabs').on('click', '.rf-date-filter', function(e){
		e.preventDefault();
		var rep = jQuery(this).closest('.repeatable');
		var from = rep.find('.from-date').val();
		var oldfrom = rep.find('.from-date').data('oldfrom');
		var oldorder = rep.find('.from-date').data('oldorder');
		var to = rep.find('.to-date').val();
		var oldto = rep.find('.to-date').data('oldto');
		var form = rep.find('.fees-group');
		form.each(function(){
		    var li = jQuery(this).find('.new-attribute');
		    li.each(function(){
			var val = jQuery(this).val();
			var resort =  jQuery(this).data('resort');
			var attributeType =  jQuery(this).data('type');
			console.log(val);
			console.log(resort);
			console.log(attributeType);
			    if(val.length != 0) {
				jQuery.ajax({
				    url : 'admin-ajax.php?&action=gpx_resort_attribute_new',
				    type : 'POST',
				    async: false,
				    data: {
					resort: resort,
					type: attributeType,
					val: val,
					from: from,
					oldfrom: oldfrom,
					oldorder: oldorder,
					to: to,
					oldto: oldto,
				    }
				});			       
			    }
		    });
		    var resortFeeParent = rep.find('.attribute-list');
		    console.log(resortFeeParent);
		    var resort = resortFeeParent.closest('.resort-edit').find('.resortID').val();
		    var attr = resortFeeParent.find('li');
		    attr.each(function(){
			var val = jQuery(this).data('fee');
			console.log(val);
			var data = {
				resort: resort,
				type: 'resortFees',
				val: val,
				from: from,
				oldfrom: oldfrom,
				oldorder: oldorder,
				to: to,
				oldto: oldto,
			    };
			console.log(data);
			jQuery.ajax({
			    url : 'admin-ajax.php?&action=gpx_resort_attribute_new',
			    type : 'POST',
			    async: false,
			    data: data,
			});	
		    });
		    
		});
		location.reload(true);
	    });
	    function insertattribute(el, update='', parent='.attribute-group') {
		
		var el = jQuery(el).closest(parent).find('.new-attribute');
		var val = el.val();
		var type = el.data('type');
		var resort = el.data('resort');
		var attributelist = el.closest('.edit-resort-group').find('.attribute-list');
		var dateFrom = el.closest('.repeatable').find('.dateFilterFrom').val();
		var oldDateFrom = el.closest('.repeatable').find('.dateFilterFrom').data('oldfrom');
		var oldorder = el.closest('.repeatable').find('.dateFilterFrom').data('oldorder');
		var dateTo = el.closest('.repeatable').find('.dateFilterTo').val();
		var oldDateTo = el.closest('.repeatable').find('.dateFilterTo').data('oldto');
		var data = {
			type: type, 
			val: val, 
			resort: resort, 
			from: dateFrom, 
			oldfrom: oldDateFrom, 
			oldorder: oldorder, 
			to: dateTo,
			oldto: oldDateTo,
		};
		console.log(data);
		if(update == 'descriptions') {
		    //we need more info for descriptions
		    var bookingpathdesc = jQuery(el).closest(parent).find('.bookingpathdesc').data('active');
		    console.log(bookingpathdesc);
		    var resortprofiledesc =  jQuery(el).closest(parent).find('.resortprofiledesc').data('active');
			var data = {
				type: type, 
				val: val, 
				resort: resort, 
				from: dateFrom,
				oldfrom: oldDateFrom, 
				oldorder: oldorder, 
				to: dateTo,
				oldto: oldDateTo,
    				bookingpathdesc: bookingpathdesc,
    				resortprofiledesc: resortprofiledesc,
    				descs: true
			};
		}
		
		console.log(dateFrom);
		jQuery
		.ajax({
		    url : 'admin-ajax.php?&action=gpx_resort_attribute_new',
		    type : 'POST',
		    data : data,
		    success : function(data) {
			if (data.success) {
			    if(!update) {
			    attributelist.append('<li class="attribute-list-item" id="'+type+'-'+data.count+'" data-item="'+data.count+'">'+val+'<span class="attribute-list-item-remove"><i class="fa fa-times-circle-o"></i></span></li>');	
			    }
			  } else {
			    
			}
		    }
		});
	    }
	    jQuery('html body').on('click', '.tab-click a', function(){
		var clicked = jQuery(this).attr('href');
		Cookies.set('resort-tab', clicked);
	    });
	    jQuery('html body').on('blur', '#tp_email', function(){
		var $this = jQuery(this);
		var val = jQuery(this).val();
		jQuery.get('/wp-admin/admin-ajax.php?action=gpx_validate_email&tp=email&val='+val, function(data){
		    if(data.used) {
			jQuery($this).val('');
			jQuery('#gpxModal .modal-body').html(data.html);
			jQuery('#gpxModal').modal('show');
		    }
		});
	    });
	    jQuery('html body').on('blur', '#tp_username', function(){
		var $this = jQuery(this);
		var val = jQuery(this).val();
		jQuery.get('/wp-admin/admin-ajax.php?action=gpx_validate_email&tp=username&val='+val, function(data){
		    if(data.used) {
			jQuery($this).val('');
			jQuery('#gpxModal .modal-body').html(data.html);
			jQuery('#gpxModal').modal('show');
		    }
		});
	    });
	    jQuery('html body').on('click', '#tp-no', function(){
		jQuery('#gpxModal').modal('hide');
	    });
	    jQuery('html body').on('click', '#tp-use', function(){
		var id = jQuery(this).data('id');
		var username = jQuery(this).data('username');
		var email = jQuery(this).data('email');
		jQuery('#tradepartner-add').prepend('<input type="hidden" name="id" value="'+id+'" />');
		jQuery('#tp_username').val(username);
		jQuery('#tp_email').val(email);
		jQuery('#gpxModal').modal('hide');
	    });
		      jQuery('html body').on('focus', '.emailvalidate', function(){
				 if(!jQuery('#oldvalue').length) {
				     var oldval = jQuery(this).val();
				     jQuery(this).parent().append('<span id="oldvalue" data-val="'+oldval+'"></span>');
				 } 
			      });
			      jQuery('html body').on('keyup', '.emailvalidate', function(){
				 jQuery('.save-return, .save-continue').prop('disabled', true);
				 var parent = jQuery(this).parent();
				 if(!jQuery('#emailValidateBtn').length)
				     jQuery('<a href="#" id="emailValidateBtn">Validate Email</a>').insertAfter(parent);
			      });
			      jQuery('.emailvalidate').blur(function(){
				 var email = jQuery(this).val();
				 var oldval = jQuery('#oldvalue').data('val');
				 jQuery('#emailValidateBtn').remove();
				 if(email != oldval) {
			        	 jQuery.post('/wp-admin/admin-ajax.php?action=gpx_validate_email',{email: email}, function(data){
			        	    if(data.error) {
			        		jQuery('.emailvalidate').val(oldval);
			        		alert(data.error);
			        	    }
			        	    jQuery('.save-return, .save-continue').prop('disabled', false);
			        	 });	     
				 }
				 else {
				     jQuery('.edit-profile-btn').prop('disabled', false).removeClass('gpx-disabled');
				 }
			      });
			      jQuery('html body').on('click', '#emailValidateBtn', function(e){
				 e.preventDefault(); 
			      });
		    jQuery('.cancel-return').click(function(e){
			e.preventDefault();
			window.history.back();
		    });
		    jQuery('.save-continue').click(function(e){
			e.preventDefault();
			jQuery('.returnurl').val('/');
			var cid = jQuery('.user').data('cid');
			Cookies.set('switchuser', cid);
			Cookies.remove('gpx-cart');
			jQuery.post('admin-ajax.php?&action=gpx_switchusers',{cid: cid},function(){});
			jQuery('.save-return').trigger('click');
		    });
		    jQuery('#ownerAdd').submit(function(e){
			e.preventDefault();
			jQuery('.fa-spin').show();
			var form = jQuery(this).serialize();
			
			jQuery.ajax({
			    url : 'admin-ajax.php?&action=gpx_add_owner',
			    type : 'POST',
			    data : form,
			    success : function(data) {
				jQuery('.fa-spin').hide();
				if (data.success) {
				    jQuery( '.update-nag').removeClass( 'nag-fail').addClass('nag-success').text("Owner Added!").show();
				    setTimeout(
						function() {
						    jQuery('.update-nag').hide( 'show');
						    location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=users_all';
						}, 2000);

				} else {
				    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text(data.error).show();
				    setTimeout(
						function() {
						    jQuery('.update-nag').hide( 'show');
						}, 4000);

				}
			    },
			    statusCode: {
				500: function () {
				    jQuery('.fa-spin').hide();
				    jQuery('.update-nag').removeClass( 'nag-success').addClass( 'nag-fail').text("EMS Error!").show();
				}
			    }
			});
		    });
		    jQuery('.password-reset-link').click(function(e){
			e.preventDefault();
			var user_login = jQuery(this).data('userlogin');
			jQuery.post('admin-ajax.php?action=request_password_reset',{user_login:user_login}, function(data){
			   alert("Passord reset email sent!"); 
			});
		    });
		    jQuery('#submitPWReset').click(function(e){
			var form = jQuery('#newpwform').serialize();
			jQuery.post('admin-ajax.php?action=gpx_change_password', form, function(data){
			   jQuery('.pwMsg').text(data.msg); 
			});
		    });
		    jQuery('html body').on('click', '.switch_user', function(){
			var cid = jQuery(this).data('user');
			Cookies.set('switchuser', cid);
			Cookies.remove('gpx-cart');
			var page = Cookies.get('switchreturn');
			jQuery.post('admin-ajax.php?&action=gpx_switchusers',{cid: cid},function(){});
			window.location.href = '/';
			return false;
		    });
		    jQuery('#remove_switch').click(function(){
			Cookies.remove('switchuser');
			Cookies.remove('gpx-cart');
			var page = Cookies.get('switchreturn');
			window.location.href = page;
			return false;
		    });
		    jQuery('#wp-admin-bar-gpx_switch').click(function(){
			 var page = window.location.href;
			 Cookies.set('switchreturn', page);
			 Cookie.remove('gpx-cart');
		    });
		    jQuery('html body').on('change', '#adjCredit', function(){
			  var amt = jQuery(this).val();
			  var el = jQuery(this).closest('td').find('.creditAmt');
			  var user = jQuery(el).data('user');
			  jQuery.post('admin-ajax.php?&action=add_gpx_credit', {amount: amt}, function(data){
			      jQuery(el).text(data) ;
			  });
		    });
		    jQuery('#resort-edit')
			    .submit(
				    function(e) {
					e.preventDefault();
					var $data = jQuery(this).serialize();
					if ($data != 'undefined') {
					    jQuery
						    .ajax({
							url : 'admin-ajax.php?&action=edit_gpx_resort',
							type : 'POST',
							data : $data,
							success : function(data) {
							    if (data.success) {
								jQuery(
									'#submit-btn')
									.find(
										'i')
									.hide();
								jQuery(
									'.update-nag')
									.removeClass(
										'nag-fail')
									.addClass(
										'nag-success')
									.text(
										data.msg)
									.show();
								setTimeout(
									function() {
									    location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_all';
									}, 1500);

							    } else {
								jQuery(
									'#rsubmit-btn')
									.find(
										'i')
									.hide();
								jQuery(
									'.update-nag')
									.removeClass(
										'nag-success')
									.addClass(
										'nag-fail')
									.text(
										data.msg)
									.show();
							    }
							    setTimeout(
								    function() {
									jQuery(
										'.update-nag')
										.hide(
											'show');
								    }, 4000);
							}
						    });
					}
				    });

		    jQuery('#resorttax-edit')
	                .submit(
	                	function(e) {
	                	    e.preventDefault();
	                	    var $data = jQuery(this).serialize();
	                	    if ($data != 'undefined') {
	                		jQuery
	                		.ajax({
	                		    url : 'admin-ajax.php?&action=edit_gpx_resorttax',
	                		    type : 'POST',
	                		    data : $data,
	                		    success : function(data) {
	                			if (data.success) {
	                			    jQuery(
	                				    '#submit-btn')
	                				    .find(
	                				    'i')
	                				    .hide();
	                			    jQuery(
	                			    '.update-nag')
	                			    .removeClass(
	                			    'nag-fail')
	                			    .addClass(
	                			    'nag-success')
	                			    .text(
	                				    data.msg)
	                				    .show();
	                			    setTimeout(
	                				    function() {
	                					location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_taxes';
	                				    }, 1500);
	                			    
	                			} else {
	                			    jQuery(
	                				    '#rsubmit-btn')
	                				    .find(
	                				    'i')
	                				    .hide();
	                			    jQuery(
	                			    '.update-nag')
	                			    .removeClass(
	                			    'nag-success')
	                			    .addClass(
	                			    'nag-fail')
	                			    .text(
	                				    data.msg)
	                				    .show();
	                			}
	                			setTimeout(
	                				function() {
	                				    jQuery(
	                					    '.update-nag')
	                					    .hide(
	                					    'slow');
	                				}, 4000);
	                		    }
	                		});
	                	    }
	                	});
		       jQuery('#taRefresh').click(function(){
			   var rid = jQuery(this).data('rid');
			   var coords = jQuery('#coords').val();
			   
			   jQuery.get('admin-ajax.php?&action=get_gpx_tripadvisor_location&coords='+coords+'&rid='+rid, function(data){
			       jQuery('#refresh-return').html(data.html);
			  });
		       });
		       jQuery('#modal-ta').on('click', '.newTA', function(){
			  var rid = jQuery(this).data('rid');
			  var taid = jQuery(this).data('taid');
			  jQuery.post('admin-ajax.php?&action=post_gpx_tripadvisor_locationid', {rid: rid, taid: taid}, function(data){
			     jQuery('.taID').text(taid); 
			     jQuery('#modal-ta').modal('toggle');
			     jQuery('.update-nag').removeClass('nag-fail').addClass('nag-success').text('TripAdvisor ID Updated!').show();
			     setTimeout(function(){
				jQuery('.update-nag').hide('slow') 
			     }, 4000);
			  });
		       });
	                jQuery('.cg-btn-group .btn').click(function(){
	                   jQuery(this).addClass('btn-primary').removeClass('btn-default').siblings().removeClass('btn-primary').addClass('btn-default'); 
	                });
	                jQuery('.cg-btn-group-checkbox .btn').click(function(){
	                    if(jQuery(this).toggleClass('btn-success btn-default'));
	                });
	                jQuery('.selectpicker').selectpicker({
	                    style: 'btn-primary',
	                    liveSearch: true,
	                });
	                jQuery('.tax-transaction-type').change(function(){
	                    var $val = [];
	                    alert('This function has been disabled.  No changes will be made.  Please update directly in the database!');
//		                    jQuery('.tax-transaction-type').each(function(){
//		                	if(jQuery(this).is(':checked'))
//		                		$val.push(jQuery(this).val());
//		                    });
//		                    jQuery.ajax({
//		                	    url : 'admin-ajax.php?&action=update_gpx_tax_transaction_type',
//		                	    type: 'POST',
//		                	    data: {ttType: $val},
//		                    });
	                });
	                
	                jQuery('#taxID').on('changed.bs.select', function(e){
	                   var $val = jQuery(this).val();
	                   if($val == 'new') {
	                       jQuery('#addTax').modal('show');
	                   }else{
	                       resortID = jQuery(this).data('resort');
                       		jQuery.ajax({
                		    url : 'admin-ajax.php?&action=update_gpx_resorttax_id',
                		    type : 'POST',
                		    data : {resortID: resortID, taxID: $val},
                		    success : function(data) {
                			if (data.success) {
                			    jQuery( '.update-nag') .removeClass('nag-fail').addClass('nag-success').text(data.msg).show();
                			} else{
                			    jQuery('.update-nag').removeClass('nag-success').addClass('nag-fail').text(data.msg).show();
                			}
                			setTimeout(function(){
                			    jQuery('.update-nag').hide('slow');
                			},4000);
                		    }
                		});
	                   }
	                });
	                jQuery('#resorttax-add').submit(function(e) {
                	    e.preventDefault();
                	    var $data = jQuery(this).serialize();
                	    if ($data != 'undefined') {
                		jQuery.ajax({
                		    url : 'admin-ajax.php?&action=add_gpx_resorttax',
                		    type : 'POST',
                		    data : $data,
                		    success : function(data) {
                			if (data.success) {
                			    jQuery('#addTax').modal('hide');
                			    jQuery( '.update-nag') .removeClass('nag-fail').addClass('nag-success').text(data.msg).show();
                			} else{
                			    jQuery('.update-nag').removeClass('nag-success').addClass('nag-fail').text(data.msg).show();
                			}
                		    }
                		});
                	    }
                	});
	                jQuery('#resorttax-edit').submit(function(e) {
	                    e.preventDefault();
	                    var $data = jQuery(this).serialize();
	                    if ($data != 'undefined') {
	                	jQuery.ajax({
	                	    url : 'admin-ajax.php?&action=edit_gpx_resorttax',
	                	    type : 'POST',
	                	    data : $data,
	                	    success : function(data) {
	                		if (data.success) {
	                		    jQuery('#addTax').modal('hide');
	                		    jQuery( '.update-nag') .removeClass('nag-fail').addClass('nag-success').text(data.msg).show();
                			    setTimeout(
                				    function() {
                					location.href = '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=resorts_taxes';
                				    }, 3500);
	                		} else{
	                		    jQuery('.update-nag').removeClass('nag-success').addClass('nag-fail').text(data.msg).show();
	                		}
	                		setTimeout(function(){
	                		    jQuery('.update-nag').hide('slow');
	                		},4000);
	                	    }
	                	});
	                    }
	                });
	                jQuery('input[name="taxMethod"]').change(function(e) {
	                    var resortID = jQuery(this).data('resort');
	                    var method = jQuery(this).val();
	                	jQuery.ajax({
	                	    url : 'admin-ajax.php?&action=edit_tax_method',
	                	    type : 'POST',
	                	    data : {ResortID: resortID, taxMethod: method},
	                	    success : function(data) {
	                		if (data.success) {
	                		    jQuery('#addTax').modal('hide');
	                		    jQuery( '.update-nag') .removeClass('nag-fail').addClass('nag-success').text(data.msg).show();
	                		} else{
	                		    jQuery('.update-nag').removeClass('nag-success').addClass('nag-fail').text(data.msg).show();
	                		}
	                		setTimeout(function(){
	                		    jQuery('.update-nag').hide('slow');
	                		},4000);
	                	    }
	                	});
	                });
		});		    
		jQuery(document).ajaxComplete(function(){
		   if(jQuery('.flash-msg').length) {
		       setTimeout(function(){
			   jQuery('.flash-msg').remove();
		       }, 3000);
		   } 
		});
		jQuery(document).ready(function(){
		    jQuery('#transactionsTable').on('click', '.guestNameTD', function(){
			   jQuery(this).find('input').show().focus(); 
			});
		    jQuery('#transactionsTable').on('blur', '.updateGuestName input', function(){
			jQuery(this).hide();
		    });
		});
		jQuery(document).ready(function(){
		    jQuery('html body').on('click', 'tbody .cancelledTransactionTD', function(){
			jQuery('.modal').modal('hide');
			  var name = jQuery(this).find('.viewCancelledTransaction').data('name');
			  var date = jQuery(this).find('.viewCancelledTransaction').data('date');
			  var refunded = jQuery(this).find('.viewCancelledTransaction').data('refunded');
			  jQuery('#tname').val(name);
			  jQuery('#tdate').val(date);
			  jQuery('#trefunded').val(refunded);
			jQuery('#cancelled-transactions').modal('show');
		    });
		    jQuery('html body').on('click', 'tbody .guestNameTD, li.guestNameTD', function(){
			  jQuery('.modal').modal('hide');
//			   jQuery(this).find('input').show().focus(); 
			  var fname = jQuery(this).find('.updateGuestName').data('fname');
			  var lname = jQuery(this).find('.updateGuestName').data('lname');
			  var email = jQuery(this).find('.updateGuestName').data('email');
			  var adults = jQuery(this).find('.updateGuestName').data('adults');
			  var children = jQuery(this).find('.updateGuestName').data('children');
			  var owner = jQuery(this).find('.updateGuestName').data('owner');
			  var transaction = jQuery(this).find('.updateGuestName').data('transaction');
			  console.log(transaction);
			  jQuery('#transactionID').val(transaction);
			  jQuery('#FirstName1').val(fname);
			  jQuery('#LastName1').val(lname);
			  jQuery('#Email').val(email);
			  jQuery('#Adults').val(adults);
			  jQuery('#Children').val(children);
			  jQuery('#Owner').val(owner);
			  jQuery('#guest-details').modal('show');
			  
			});
		    jQuery('html body').on('click', '.view-mapping', function(e){
			e.preventDefault();
			var link = jQuery(this).attr('href')+' #mapped';
			jQuery('#modal-mapped-content').load(link);
			jQuery('#mapped-user').modal('show');
		    });
//		    jQuery('html body').on('blur', '.updateGuestName input', function(){
//		    	var $this = jQuery(this);
//		    	var transaction = jQuery(this).data('transaction');
//		    	var updateGuest = jQuery(this).val();
//		    	var $thisguest = '.guestName'+transaction;
//		    	var $thisguestinput = '.guestNameInput'+transaction;
//		    	jQuery.ajax({
//		    		url: 'admin-ajax.php?action=gpx_reasign_guest_name',
//		    		type: 'POST',
//		    		data: {transaction: transaction, updateGuest: updateGuest},
//		    		success: function() {
//		    			jQuery($this).hide();
//		    			jQuery($thisguest).text(updateGuest);
//		    			jQuery($thisguestinput).val(updateGuest);
//     			       jQuery('#transactionsTable').bootstrapTable('refresh');
//		    			
//		    		},
//		    	});
//		    });
		    jQuery('html body').on('submit', '#update-guest-details', function(e){
			e.preventDefault();
			var form = jQuery(this).serialize();
			jQuery.post('admin-ajax.php?action=gpx_reasign_guest_name', form, function(data){
			    jQuery('.modal').modal('hide');
			    var btn = localStorage.getItem('gpx-modal-back');
//			   jQuery('#transactionsTable').bootstrapTable('refresh');
			    jQuery(btn).trigger('click');
			});
			
		    });
		    jQuery('html body').on('click', '.deleteWeek', function(e){
			e.preventDefault();
			if(confirm("Are you sure you want to remove this room.  This action cannot be undone!")){
        			var id = jQuery(this).data('id');
        			jQuery.ajax({
        			   url: 'admin-ajax.php?action=gpx_remove_room',
        			   type: 'POST',
        			   data: {id: id},
        			   success: function(data) {
        			       if(data.success){
        				   window.location =  '/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=room_all';
        			       }
        			       
        			   }
        			});			    
			}

		    });
		    jQuery('html body').on('change','.inventory-weeks-selected', function(e){
		    	e.preventDefault();
	    		jQuery('#holdTillRow').hide();
		    	$this = jQuery('.inventory-weeks-selected').find('option:selected');
		    	var type = jQuery($this).data('type');
		    	if(type == 'hold'){
		    		jQuery('#holdTillRow').show();
		    	}
		    });
		    jQuery('html body').on('click', '#inventory-bulk-hold', function(e){
//			    jQuery('html body').on('click', '.tp-weeks-selected', function(e){
			    	e.preventDefault();
			    	
			    	$this = jQuery('.inventory-weeks-selected').find('option:selected');
			    	
			    	//find the checked boxes.
			    	var ids = [];
			    	var thisid = '';
			    	var tp = jQuery($this).data('id');
			    	var type = jQuery($this).data('type');
			    	var date = jQuery('#holdTill').val();
			    	jQuery($this).closest('div.wswrap').prev('.row').find('tr').each(function(){
			    		if(jQuery(this).hasClass('selected')){
			    			thisid = jQuery(this).find('td:nth(2)').text();
			    			ids.push(thisid);
			    		}
			    	});
			    	jQuery.post('/wp-admin/admin-ajax.php?action=tp_claim_week&tp='+tp, {ids: ids, type: type, date: date}, function(data){
			    	    jQuery('#gpxModal').modal('hide');
			    		jQuery('#tp_inventory_table').bootstrapTable('refresh');
			    		jQuery('button[name="refresh"]').trigger('click');
			    	});
			    });
		    jQuery('html body').on('submit', '#tradepartner-edit', function(e){
		    	e.preventDefault();
		    	var form = jQuery(this).serialize();
		    	var link = jQuery(this).attr('action');
		    	var msg = jQuery(this).closest('div').find('#msg-box');
		    	jQuery.post(link, form, function(data){
		    		jQuery(msg).text('Success');
		    		setTimeout(function(){
		    			jQuery(msg).text('');
		    		}, 3000);
		    	});
		    });
		    jQuery('html body').on('change', '#tp_claim', function(){
		    	jQuery('#tp-claim').prop('disabled', false);
		    });
		    jQuery('html body').on('click', '#tp-claim', function(e){
//		    jQuery('html body').on('click', '.tp-weeks-selected', function(e){
		    	e.preventDefault();
		    	jQuery(this).prop('disabled', true);
		    	$this = jQuery('.tp-weeks-selected').find('option:selected');
		    	
		    	//find the checked boxes.
		    	var ids = [];
		    	var thisid = '';
		    	var tp = jQuery($this).data('id');
		    	var type = jQuery($this).data('type');
		    	console.log(type);
		    	jQuery($this).closest('div.row').prev('.row').find('tr').each(function(){
		    		if(jQuery(this).hasClass('selected')){
		    			thisid = jQuery(this).find('td:nth(1)').text();
		    			ids.push(thisid);
		    		}
		    	});
		    	jQuery.post('/wp-admin/admin-ajax.php?action=tp_claim_week&tp='+tp, {ids: ids, type: type}, function(data){
		    	    jQuery('#gpxModal').modal('hide');
		    		jQuery('#tp_inventory_table').bootstrapTable('refresh');
		    		jQuery('button[name="refresh"]').trigger('click');
		    	});
		    });
		    jQuery('html body').on('click', '.debitModal', function(e){
		    	var id = jQuery(this).data('id');
		    	var balance = jQuery(this).data('balance');
		    	var name = jQuery(this).data('name');
		    	jQuery('#debitID').val(id);
		    	jQuery('#debitBalance').text(balance);
		    	jQuery('#debitName').text(name);
		    });
		    jQuery('html body').on('click', '#debit-submit', function(e){
		    	var id = jQuery('#debitID').val();
		    	var amt = jQuery('#debit-adjust').val();
		    	jQuery.post('/wp-admin/admin-ajax.php?action=tp_debit', {id: id, amt: amt}, function(data){
		    		jQuery('#debitBalance').text(data.balance);
		    		setTimeout(function(){
		    			jQuery('#gpxModalBalance').modal('hide');
		    		}, 1500);
		    		
		    	});
		    });
		    jQuery('html body').on('click', '.tp-ajdust-trade-balance', function(e){
//		    	e.prevendDefault();
		    	var type = jQuery(this).data('tb');
		    	localStorage.setItem('data-type', type);
		    	var tb = jQuery('#editTBNoteWrapper').clone();
		    	jQuery('#gpxModal .modal-body').html(tb);
		    });
		    jQuery('html body').on('click', '#editTBSubmit', function(e){
		    	var type = localStorage.getItem('data-type');
		    	var user = localStorage.getItem('data-user');
		    	var note = jQuery('#editTBNote').val();
		    	var num = jQuery('#editTBNum').val();
		    	jQuery.post('/wp-admin/admin-ajax.php?action=tp_adjust_balance', {note: note, num: num, user: user, type: type}, function(data){
		    		if(data.success){
		    			jQuery('#editTPBalanaceMsg').html(data.html);
		    			jQuery('button[name="refresh"]').trigger('click');
	    				setTimeout(function(){
	    					jQuery('#editTPBalanaceMsg').html('');
	    					jQuery('#gpxModal').modal('hide');
	    				}, 2000);
		    		}
		    	});
		    });
		    jQuery('html body').on('click', '.tp-in-modal', function(e){
		    	e.preventDefault();
		    	jQuery('.modal').hide();
		    	var back = '#'+jQuery(this).attr('id');
		    	console.log(back);
		    	localStorage.setItem('gpx-modal-back', back);
		    	var select = jQuery(this).data('select'); 
		    	var link = jQuery(this).attr('href')+' #'+select;
		    	var title = jQuery(this).data('title');
		    	var type = jQuery(this).data('type');
		    	var user = jQuery(this).closest('tr').find('.data-user').data('user');
		    	localStorage.setItem('data-user', user);
		    	jQuery('#gpxModal .modal-title').text(title);
		    	jQuery('#editTPBalance button').attr('data-user', user);
		    	jQuery('#editTPBalance').show();
		    	jQuery('#gpxModal .modal-body').load(link, function(){
		    		if(type == 'inventory'){
		    			jQuery('#tp_inventory_table').bootstrapTable();
		    		}
		    		if(type == 'activity'){
		    			jQuery('#tp_activity_table').bootstrapTable();
		    		}
		    		if(type == 'add'){
		    			   setTimeout(function(){
		    			       jQuery('#active_week_month, #active_specific_date').hide(); 
		    			       jQuery('#active_display_date').hide();
		    			       jQuery('.select2').select2();
		    			   },700);
				        jQuery('#check_in_date').datepicker({minDate: 0, onSelect: function(dateStr) {
				            var date = jQuery(this).datepicker('getDate');
				            var minDate = jQuery(this).datepicker('getDate');
				            if (date) {

				                  var activedate = new Date(date.getFullYear()-1, date.getMonth(), 1);
				                  minDate.setDate(minDate.getDate()+1);
				                  date.setDate(date.getDate() + 7);
				            }
				         
				         
				           jQuery('#check_out_date').datepicker({dateFormat: 'mm/dd/yy'}).datepicker({minDate: minDate}).datepicker('setDate', date);
				           jQuery('#active_specific_date').datepicker({dateFormat: 'mm/dd/yy'}).datepicker('setDate', activedate);
				      }});	
				        jQuery( "#autocompleteAvailability" ).autocomplete({
				            source: function( request, response ) {
				        	// Fetch data  partner_autocomplete
				        	jQuery.ajax({
				        	    url: "admin-ajax.php?&action=partner_autocomplete",
				        	    type: 'post',
				        	    dataType: "json",
				        	    data: {
				        		search: request.term,
				        		type: jQuery('#availability').val(),
				        		availabilty: true,
				        	    },
				        	    success: function( data ) {
				        		response( data );
				        	    }
				        	});
				            },
				            select: function (event, ui) {
				        	// Set selection
				        	jQuery('#autocompleteAvailability').val(ui.item.label); // display the selected text
				        	jQuery('#available_to_partner_id').val(ui.item.value); // save selected id to input
				        	return false;
				            }
				        });
		    		}
		    		jQuery('#gpxModal').modal('show');
		    	});
		    });
		    jQuery('html body').on('click', '.in-modal', function(e){
				e.preventDefault();
				var link = jQuery(this).attr('href')+' #admin-modal-content';
				jQuery('#gpxModal .modal-title').text('View Transaction');
				jQuery('#gpxModal .modal-body').load(link);
				jQuery('#gpxModal').modal('show');
		    });
//		    jQuery('html body').on('click', '#cancel-booking', function(e){
//		    	e.preventDefault();
//		    	var transactionID = jQuery(this).data('transaction');
//		    	var adminamt = prompt('Please confirm the amount to refund', amt);
//		    	jQuery.ajax({
//		    		url: 'admin-ajax.php?action=gpx_cancel_booking&admin_amt'+adminamt,
//		    		type: 'POST',
//		    		data: {transaction: transactionID},
//		    		success: function() {
//		    			jQuery('#gpxModal').modal('toggle');
//		    			jQuery('#transactionsTable').bootstrapTable('refresh');
//		    		}
//		    	});
////				if(confirm('Are you sure you want to cancel this booking request?')) {
////				}
//		    });
		    jQuery('html body').on('click', '#cancel-booking', function(e){
				e.preventDefault();
				var transactionID = jQuery(this).data('transaction');
				jQuery.ajax({
				   url: 'admin-ajax.php?action=gpx_cancel_booking',
				   type: 'POST',
				   data: {transaction: transactionID},
				   success: function() {
				       jQuery('#gpxModal').modal('toggle');
				       jQuery('#transactionsTable').bootstrapTable('refresh');
				   }
				});
//				if(confirm('Are you sure you want to cancel this booking request?')) {
//				}
		    });
		    jQuery('html body').on('click', '.cancel-booking-choose', function(e){
			e.preventDefault();
			var type = jQuery(this).data('type');
			var amount = jQuery(this).data('amt');
			var input = jQuery(this).closest('.input-group').find('.feeamt');
			var transactionID = jQuery(input).data('transaction');
			var amount = jQuery(input).val();
//			var adminamt = prompt('Please confirm the amount to refund', amount);
		    	var $_GET = {};

		    	document.location.search.replace(/\??(?:([^=]+)=([^&]*)&?)/g, function () {
		    	    function decode(s) {
		    	        return decodeURIComponent(s.split("+").join(" "));
		    	    }

		    	    $_GET[decode(arguments[1])] = decode(arguments[2]);
		    	});
			
			if(confirm('Are you sure you want to cancel this booking request?')) {
			    jQuery.ajax({
				url: 'admin-ajax.php?action=gpx_cancel_booking',
				type: 'POST',
				data: {transaction: transactionID, type: type, amt: amount, admin_amt: amount},
				success: function(ret) {
					   if($_GET['gpx-pg'] == 'transactions_view'){
					       location.reload();
					   }else{
                				    
                				    jQuery('#gpxModal').modal('hide');
                				    jQuery('#transactionsTable').bootstrapTable('refresh');
                				    var btn = localStorage.getItem('gpx-modal-back');
                				    jQuery(btn).trigger('click');
                				    jQuery('.right-column .update-nag').html(ret.html);
                				    setTimeout(function(){
                					jQuery('.right-column .update-nag').hide();
                				    }, 5000);
					   }
				},
			    });
			}
		    });
		    jQuery('#transactionAdd_OwnerID').blur(function(){
			var ownerID = $(this).val();
			jQuery.ajax({
 			   url: 'admin-ajax.php?action=gpx_get_owner_for_add_transaction',
 			   type: 'GET',
 			   data: {memberNo: ownerID},
 			   success: function(data) {
 			       jquery.each(data.data, function(index, value){
 				  var id = $(this).index;
 				  var val = $(this).value;
 				  $(id).val(val);
 			       });
 			   }
 			});
		    });
		});

		jQuery(document).ready(function(){
			jQuery('#send_welcome_email').click(function(e){
				e.preventDefault();
//			jQuery('html body').on('click', '#send_welcome_email', function(){
				var cid = jQuery(this).data('cid');
				var $this = jQuery(this);
				console.log(cid);
				jQuery.post('/wp-admin/admin-ajax.php?action=send_welcome_email', {cid: cid}, function(data){
					if(data.success){
						alert('Email Sent '+data.msg);
//						jQuery($this).remove();
					}else{
						alert(data.message);
					}
						
				});
			})
			jQuery('html body').on('change', '#table', function(){
				var el = '#'+jQuery(this).val();
				jQuery('.reportwriter-drag').hide();
				jQuery('.reportwriter-drag ul').hide();
				jQuery(el).closest('.reportwriter-drag').show();
				jQuery(el).show();
			});
			jQuery('.reportwriter-drag ul, .reportwriter-drop ul').sortable({
		        connectWith: '.sortconnect',
		    });
			
			//report writer submit
			jQuery('#reportWriterSubmit').click(function(e){
				
				e.preventDefault();
				var data = [];
				var name = jQuery('#name').val();
				var reportType = jQuery('#reportType').val();
				var role = jQuery('#role').val();
				var emailrepeat = jQuery('#emailrepeat').val();
				var emailrecipients = jQuery('#emailrecipients').val();
				var gp = 1;
				var condition = {};
				var operator = {};
				var operand = {};
				var conditionValue = {};
				var formdata = jQuery('.reportwriter-drop').html();
				var editid = jQuery('#editid').val();
				jQuery('.condition').each(function(){
					gp = jQuery(this).closest('.conditionGroup').data('gp');
					condition[gp] = jQuery(this).val();
				});
				jQuery('.operator').each(function(){
					gp = jQuery(this).closest('.conditionGroup').data('gp');
					operator[gp] = jQuery(this).val();
				});
				jQuery('.conditionValue').each(function(){
					gp = jQuery(this).closest('.conditionGroup').data('gp');
					conditionValue[gp] = jQuery(this).val();
				});
				jQuery('.operand').each(function(){
				    gp = jQuery(this).closest('.conditionGroup').data('gp');
				    operand[gp] = jQuery(this).val();
				});
				jQuery('.reportwriter-drop ul li').each(function(){
					data.push(jQuery(this).data('field'));
				});
				var condition_json = JSON.stringify(condition);
				var operator_json = JSON.stringify(operator);
				var conditionValue_json = JSON.stringify(conditionValue);
				var operand_json = JSON.stringify(operand);
			    jQuery.ajax({
			    	url: '/wp-admin/admin-ajax.php?action=gpx_report_write',
			    	type: 'POST',
			    	data: {
			    	       editid: editid,
			    	       name: name, 
			    		data: data,
			    		reportType: reportType,
			    		role: role,
			    		emailrepeat: emailrepeat,
			    		emailrecipients: emailrecipients,
			    		gps: gp,
			    		condition: condition_json,
			    		operator: operator_json,
			    		operand: operand_json,
			    		conditionValue: conditionValue_json,
			    		form: formdata,
		    		},
			    	success: function(ret){
			    		if(ret.success){
			    		    if(ret.refresh){
//			    		    	window.location.href = ret.refresh
			    		    	window.open(ret.refresh);
			    		    }
			    		    if(ret.link){
			    		    	jQuery('#reportLinks').append(ret.link);
			    		    }
			    		}
			    	}
			    });
			});
			jQuery('html body').on('change', '#reportType', function(){
				if(jQuery(this).val() == 'Group') {
					jQuery('#groupType').removeClass('hidden').show();
					jQuery('#reportName, #reportEmail').show();
				}else{
					if(jQuery(this).val() == 'Single') {
						jQuery('#reportName, #reportEmail').hide();
					}else{
						jQuery('#reportName, #reportEmail').show();
					}
					jQuery('#groupType').addClass('hidden').hide();
				}
			});
			if(jQuery('#reportType').length){
			    setTimeout(function(){
				    var type = jQuery('#reportType').val();
				    if(type == 'Group') {
					jQuery('#groupType').removeClass('hidden').show();
					}else{
						jQuery('#groupType').addClass('hidden').hide();
					}				
			    },100);
			}
			jQuery('#newCondition').click(function(e){
				e.preventDefault();
				jQuery.get('/wp-admin/admin.php?page=gpx-admin-page&gpx-pg=reports_writer', function (data) {
					newCondition = jQuery(data).find('#cgp1');
					var nextNum = jQuery('.conditionGroup:last').data('gp')+1;
					if(nextNum == 'NaN'){
					    nextNum = 1;
					}
					jQuery(newCondition).attr('id', 'cgp'+nextNum).attr('data-gp', nextNum).prepend('<input type="hidden" name="operand[1]" class="operand" value="and" /><div class="btn-group"><button class="btn btn-primary selectoperand" type="button" data-value="and">AND</button><button class="btn btn-secondary selectoperand" type="button" data-value="or">OR</button></div><br>').append('<a href="#" class="removeWell"><i class="fa fa-close"></i></a>');
					jQuery('#addConditions').append(newCondition);
					jQuery(newCondition).find('.select2').select2();
				});
			});
			jQuery('html body').on('click', '.selectoperand', function(e){
				e.preventDefault();
				var op = jQuery(this).data('value');
				jQuery(this).closest('.conditionGroup').find('.operand').val(op);
				jQuery(this).closest('.conditionGroup').find('.btn-primary').removeClass('btn-primary').addClass('btn-secondary');
				jQuery(this).removeClass('btn-secondary').addClass('btn-primary');
			});
			jQuery('html body').on('click', '.removeWell', function(e){
				e.preventDefault();
				jQuery(this).closest('.conditionGroup').remove();
			});
			jQuery('html body').on('click', '.credit-donation', function(e){
				   e.preventDefault();
				   var id = jQuery(this).data('id');
				    jQuery.ajax({
					url : '/wp-admin/admin-ajax.php?&action=gpx_credit_donation',
		                	type : 'GET',
		                	data : {id: id},
					success: function(ret){
					   jQuery('#gpxModal .body').html(ret);
					}
				    });
				}); 
				    jQuery('html body').on('click', '.btn-will-bank', function(e){
					  e.preventDefault();
					  var el = $(this);
					  var form = $(el).closest('form').serialize();
				        	  jQuery.post('/wp-admin/admin-ajax.php?action=gpx_credit_donation', form, function(data){
//				        	      todo: refresh table to show credit was posted
				        	      
				        	  });	      

				    });
		    
		jQuery('html body').on('click', '.extend-week', function(e){
		    e.preventDefault();
		    jQuery(this).closest('.extend-box').find('.extend-input').show();
		});
		jQuery('html body').on('click', '.close_box', function(e){
			e.preventDefault();
		    jQuery(this).closest('.extend-box').find('.extend-input').hide();
		});
		    jQuery('html body').on('click', '.extend-btn', function(e){
		    e.preventDefault();
		    var id = jQuery(this).data('id');
		    var dateel = jQuery(this).closest('.extend-input').find('.extend-date');
		    var date = jQuery(dateel).val();
		    jQuery(dateel).closest('.extend-input').hide();
		    jQuery.ajax({
			url : '/wp-admin/admin-ajax.php?&action=gpx_extend_week',
                	type : 'POST',
                	data : {id: id, newdate: date},
			success: function(data){
				if(data.error){
					alert(data.error);
				}else{
					jQuery('#transactionsTable').bootstrapTable('refresh');
				}
					
			   
			}
		    });
		});
		jQuery('html body').on('click', '.release-week', function(e){
		    e.preventDefault();
		    var id = jQuery(this).data('id');
		    jQuery(this).hide();
		    jQuery.ajax({
			url : '/wp-admin/admin-ajax.php?&action=gpx_release_week',
			type : 'POST',
			data : {id: id},
			success: function(data){
				jQuery('#transactionsTable').bootstrapTable('refresh');
				   jQuery('#tp_activity_table').bootstrapTable('refresh');
			}
		    });
		});
		    
		jQuery('.merge-again').click(function(e){
		    e.preventDefault();
		    var file = jQuery(this).data('file');
		    var id = jQuery(this).data('id');
		    jQuery(this).hide();
		    jQuery.ajax({
			url : 'admin-ajax.php?&action=merge_again',
                	type : 'POST',
                	data : {file: file, id: id},
		    });

		});
		
            		jQuery('#Slug').blur(function(){
            		    var $this = jQuery(this);
            		    checkslug($this);
            		});
            		jQuery('#Slug').change(function(){
            		    var $this = jQuery(this);
            		    checkslug($this);
            		});
            		function checkslug(el)
            		{
            		    var $this = el;
            		    var slug = jQuery($this).val();
            			jQuery.ajax({
          			   url: 'admin-ajax.php?action=gpx_promo_dup_check',
          			   type: 'POST',
          			   data: {slug: slug},
          			   success: function(data) {
          			      if(data.error) {
          				  $this.select();
          				  jQuery('.major-error').remove();
          				  $this.after('<span class="major-error">'+data.error+'</span>');
          			      }else{
          				  jQuery('.major-error').remove();
          			      }
          			   }
          			});
            		}
            		if(jQuery('.nag-fail').length) {
            			var height = jQuery('.nag-fail').outerHeight()+41;
            			jQuery('.nag-success').css('top', height);
            		}
		});
function show_region_option(data, id, type) {
    if(type.length)
	type = type+'_';
    var op = '<div id="'
	    + id
	    + '"class="form-group parent-regions parent-delete"><label class="control-label col-md-3 col-sm-3 col-xs-12" for="coupon-name">Parent Region <span class="required">*</span></label><div class="col-md-6 col-sm-6 col-xs-11"><select name="'+type+'parent[]" class="form-control col-md-7 col-xs-12 parent-region"><option></option>';
    jQuery.each(data, function(key, val) {
	op += '<option value="' + val.id + '">' + val.region + '</option>';
    });
    op += '</select></div><div class="col-xs-1 remove-element"><i class="fa fa-trash" aria-hidden="true"></i></div></div>';
    return op;
}
