jQuery(function () {
    jQuery('#upload_logo_button').click(function () {
        wp.media.editor.send.attachment = function (info, file) {
            var photo_id = file.id;
            var thumbnail = file.sizes.thumbnail.url;

            jQuery('#sortable').append('<li class="ui-state-default"><div class="dgt-image-gallery-item"><div class="dgt-image-gallery-item-close">&times;</div><img src="'+thumbnail+'"><input type="hidden" name="gpx_image_gallery[]" value="'+photo_id+'"></div></li>');
        };

        wp.media.editor.open(this);

        return false;
    });

    // draggable, sortable
    if(jQuery("#sortable").length >0) {
        jQuery("#sortable").sortable();
        jQuery("#sortable").disableSelection();
    }
    jQuery('#dgt-images-gallery-container-droppable').on('click', '.dgt-image-gallery-item-close', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var prompt = confirm('Are you sure you want to delete this image?');
        if (prompt == true) {
            // delete node
            jQuery(this).parent().parent().remove();
        }
    });
});