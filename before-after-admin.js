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

    // Media uploader for GeoJSON file
    /* $(document).on('click', '.beforeafter_upload_file_button', function(e) {
        e.preventDefault();

        var button = $(this);
        var field = button.data('field');
        var file_id_field = $('#beforeafter_' + field + '_id');
        var file_name_span = $('#beforeafter_' + field + '_name');
        var remove_button = $('.beforeafter_remove_file_button[data-field="' + field + '"]');

        var custom_uploader = wp.media({
            title: 'Select File',
            button: { text: 'Select File' },
            multiple: false
        }).on('select', function() {
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            file_id_field.val(attachment.id);
            file_name_span.text(attachment.filename);
            remove_button.show();
        }).open();
    });
 */
    // Remove file button
    $(document).on('click', '.beforeafter_remove_file_button', function(e) {
        e.preventDefault();

        var button = $(this);
        var field = button.data('field');
        var file_id_field = $('#beforeafter_' + field + '_id');
        var file_name_span = $('#beforeafter_' + field + '_name');

        file_id_field.val('');
        file_name_span.text('');
        button.hide();
    });
});
