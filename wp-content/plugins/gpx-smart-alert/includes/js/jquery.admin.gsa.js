(function($) {
	$(document).ready(function(){
	        $('.child_sites_row').hide();
	    	$('#is_parent_site').click(function(){
	    	   var isparent = $(this).val();
	    	   if(isparent == 'yes') {
	    	       $('.parent_url_row').hide();
	    	       $('.child_sites_row').show();
	    	   } else {
	    	       $('.parent_url_row').show();
	    	       $('.child_sites_row').hide();	    	       
	    	   }
	    	});
		$('html body').on('change', '.gsa-settings', function(){
			var item = $(this).data('item');
			var value = $(this).val();
			
			$.post(gsa_ajax_object.ajax_url+'?action=gpr_update_parent', {item: item, value: value, secure: gsa_ajax_object.gsa_secure}, function(data){
				alert('Updated.');
			});
		});
		$('.gpx_smartbar_new_child').click(function(){
		   var site = $('#gpr_smartbar_new_website_name').val();
		   var url = $('#gpr_smartbar_new_website_url').val();
		    $.post(gsa_ajax_object.ajax_url+'?action=gpr_new_child', {name: site, url: url, secure: gsa_ajax_object.gsa_secure}, function(data){
			$('#gpr_smartbar_children').append('<div class="gpr_smartbar_child_row"><button class="remove_children" data-row="'+site+'">&times;</button>'+site+' '+url+'</div>');
		    });		
		});
		$('html body').on('click', '.remove_children', function(){
		    if(confirm('Are you sure you want to delete this child website?')) {
			    var item = $(this).data('row');
			    var thisrow = $(this).closest('.gpr_smartbar_child_row');
			    $.post(gsa_ajax_object.ajax_url+'?action=gpr_remove_child', {item: item, secure: gsa_ajax_object.gsa_secure}, function(data){
				$(thisrow).remove();
			    });			
		    }
		});
	});
})( jQuery );