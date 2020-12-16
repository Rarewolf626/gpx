(function($) {
	$(document).ready(function(){
		$('.gpr_sb_close').click(function(){
			var cookie = $(this).data('sb');
			var ip = $(this).data('ip');
			var cookies = Cookies.get('gpr_sb');
		    try {
		    	var cookiesArr = JSON.parse(cookies);
		    } catch (e) {
		        var cookiesArr = [];
		    }
			cookiesArr.push(cookie);
			var newcookie = JSON.stringify(cookiesArr);
			
			//add the cookie
			Cookies.set('gpr_sb', newcookie, { expires: 183, path: '' });
			
			//add to the database
			$.post('/wp-admin/admin-ajax.php?action=gpr_sb_store_hidden', {name: cookie, ip: ip},function(data){
				if(data.success){
					
				}
			});
			var alert = $(this).closest('.each_gpr_sb');
			var  tab = $(alert).data('id');
			$(alert).hide();
			$('.sb-tabs[data-id="'+tab+'"]').hide();
		});	
		setTimeout(function(){
		    $('.each_gpr_sb').hide();
		    $('.sb-tabs').show();
		}, 3600000);
		$('.sb-tabs').click(function(){
		    if($(this).hasClass('active')) {
			//just hide the current bar
			   var tab = $(this).data('id');
			   $(this).removeClass('active');
			   $('.each_gpr_sb[data-id="'+tab+'"]').hide();
		    }else {
			   $('.sb-tabs').removeClass('active');
			   $(this).addClass('active');
			   $('.each_gpr_sb').hide();
			   var tab = $(this).data('id');
			   $('.each_gpr_sb[data-id="'+tab+'"]').show();			
		    }
		});
		$('html body').on('change', '.gsa-settings', function(){
			var item = $(this).data('item');
			var value = $(this).val();
			$.post(gsa_ajax_object.ajax_url, {item: item, value: value, secure: gsa_secure}, function(data){
				
			});
		});
		$('.gpx_smartbar_new_child').click(function(){
		   var site = $('#gpr_smartbar_new_website_name').val();
		   var url = $('#gpr_smartbar_new_website_url').val();
		   console.log(url);
		    $.post(gsa_ajax_object.ajax_url+'?action=gpr_new_child', {site: site, url: url, secure: gsa_secure}, function(data){
			$('#gpr_smartbar_children').append('<div class"gpr_smartbar_child_row"><button class="remove_children" data-row="'+site+'">&times;</button>'+site+' " '+url+'</div>');
		    });		
		});
		$('html body').on('click', '.remove_children', function(){
		    if(confirm('Are you sure you want to delete this child website?')) {
			    var item = $(this).data('row');
			    var thisrow = $(this).closest('.gpr_smartbar_child_row');
			    $.post(gsa_ajax_object.ajax_url+'?action=gpr_remove_child', {item: item, secure: gsa_secure}, function(data){
				$(thisrow).remove();
			    });			
		    }
		});
	});
})( jQuery );