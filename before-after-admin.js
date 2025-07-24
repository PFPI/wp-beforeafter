jQuery(document).ready(function($){
    // Media uploader for Before/After images
    $(document).on('click', '.beforeafter_upload_image_button', function(e) {
        e.preventDefault();

        var button = $(this);
        var field = button.data('field'); // 'before_image' or 'after_image'
        var image_id_field = $('#beforeafter_' + field + '_id');
        var image_preview = $('#beforeafter_' + field + '_preview');
        var remove_button = $('.beforeafter_remove_image_button[data-field="' + field + '"]');

        var custom_uploader = wp.media({
            title: 'Select Image',
            library: { type: 'image' },
            button: { text: 'Select Image' },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            image_id_field.val(attachment.id);
            image_preview.attr('src', attachment.url).show();
            remove_button.show();
        }).open();
    });

    // Remove image button
    $(document).on('click', '.beforeafter_remove_image_button', function(e) {
        e.preventDefault();

        var button = $(this);
        var field = button.data('field');
        var image_id_field = $('#beforeafter_' + field + '_id');
        var image_preview = $('#beforeafter_' + field + '_preview');

        image_id_field.val('');
        image_preview.attr('src', '').hide();
        button.hide();
    });
});
