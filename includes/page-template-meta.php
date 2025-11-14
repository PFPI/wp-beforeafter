<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. REGISTER THE META BOX
 *
 * This function is hooked to 'add_meta_boxes' and checks if the
 * current page being edited is using our library template.
 */
function ba_lib_add_meta_box($post_type, $post)
{
    global $post;

    // Return if $post is not set
    if (!isset($post)) {
        return;
    }

    // Get the page template
    $page_template = get_post_meta($post->ID, '_wp_page_template', true);

    // If we are on the correct page template, add the meta box
    if ('page-template-beforeafter-library.php' == $page_template) {
        add_meta_box(
            'ba_library_page_options', // $id
            __('Library Page Options', 'beforeafter'), // $title
            'ba_lib_render_meta_box_callback', // $callback
            'page', // $screen (only on Pages)
            'normal', // $context
            'high' // $priority
        );
    }
}
add_action('add_meta_boxes', 'ba_lib_add_meta_box', 10, 2);


/**
 * 2. RENDER THE META BOX HTML
 *
 * This function is the callback that builds the HTML for our fields.
 * We use 'beforeafter_lib_...' as data-field attributes to re-use
 * the existing media uploader JS from 'before-after-admin.js'.
 */
function ba_lib_render_meta_box_callback($post)
{
    // Add a nonce field so we can check for it later.
    wp_nonce_field('ba_lib_save_meta_box_data', 'ba_lib_meta_box_nonce');

    // Get existing values
    $before_image_id = get_post_meta($post->ID, '_lib_before_image_id', true);
    $after_image_id = get_post_meta($post->ID, '_lib_after_image_id', true);
    $before_date = get_post_meta($post->ID, '_lib_before_date', true);
    $after_date = get_post_meta($post->ID, '_lib_after_date', true);
    $splash_text = get_post_meta($post->ID, '_lib_splash_text', true);

    $before_image_url = $before_image_id ? wp_get_attachment_url($before_image_id) : '';
    $after_image_url = $after_image_id ? wp_get_attachment_url($after_image_id) : '';
    ?>

    <style>
        .ba-lib-meta-field { margin-bottom: 20px; }
        .ba-lib-meta-field label { font-weight: bold; display: block; margin-bottom: 5px; }
        .ba-lib-meta-field input[type="text"] { width: 200px; }
        .ba-lib-meta-field img { max-width: 200px; height: auto; border: 1px solid #ddd; }
    </style>

    <div class="ba-lib-meta-field">
        <label for="beforeafter_lib_before_image_id"><?php _e('Before Image:', 'beforeafter'); ?></label>
        <input type="hidden" name="_lib_before_image_id" id="beforeafter_lib_before_image_id" value="<?php echo esc_attr($before_image_id); ?>" />
        <img id="beforeafter_lib_before_image_preview" src="<?php echo esc_url($before_image_url); ?>" style="<?php echo empty($before_image_url) ? 'display:none;' : ''; ?>" /><br>
        <button type="button" class="button beforeafter_upload_image_button" data-field="lib_before_image"><?php _e('Select Before Image', 'beforeafter'); ?></button>
        <button type="button" class="button beforeafter_remove_image_button" data-field="lib_before_image" style="<?php echo empty($before_image_url) ? 'display:none;' : ''; ?>"><?php _e('Remove Before Image', 'beforeafter'); ?></button>
    </div>

    <div class="ba-lib-meta-field">
        <label for="beforeafter_lib_after_image_id"><?php _e('After Image:', 'beforeafter'); ?></label>
        <input type="hidden" name="_lib_after_image_id" id="beforeafter_lib_after_image_id" value="<?php echo esc_attr($after_image_id); ?>" />
        <img id="beforeafter_lib_after_image_preview" src="<?php echo esc_url($after_image_url); ?>" style="<?php echo empty($after_image_url) ? 'display:none;' : ''; ?>" /><br>
        <button type="button" class="button beforeafter_upload_image_button" data-field="lib_after_image"><?php _e('Select After Image', 'beforeafter'); ?></button>
        <button type="button" class="button beforeafter_remove_image_button" data-field="lib_after_image" style="<?php echo empty($after_image_url) ? 'display:none;' : ''; ?>"><?php _e('Remove After Image', 'beforeafter'); ?></button>
    </div>

    <div class="ba-lib-meta-field">
        <label for="ba_lib_before_date"><?php _e('Before Date (Text):', 'beforeafter'); ?></label>
        <input type="text" name="_lib_before_date" id="ba_lib_before_date" value="<?php echo esc_attr($before_date); ?>" pattern="\d{4}-\d{2}" title="Please use the format YYYY-MM" />
        <p class="description"><?php _e('e.g., 2018-06', 'beforeafter'); ?></p>
    </div>

    <div class="ba-lib-meta-field">
        <label for="ba_lib_after_date"><?php _e('After Date (Text):', 'beforeafter'); ?></label>
        <input type="text" name="_lib_after_date" id="ba_lib_after_date" value="<?php echo esc_attr($after_date); ?>" pattern="\d{4}-\d{2}" title="Please use the format YYYY-MM" />
        <p class="description"><?php _e('e.g., 2021-08', 'beforeafter'); ?></p>
    </div>

    <div class="ba-lib-meta-field">
        <label for="ba_lib_splash_text"><?php _e('Splash Text:', 'beforeafter'); ?></label>
        <?php
        wp_editor(
            $splash_text,
            'ba_lib_splash_text', // $editor_id
            [
                'textarea_name' => '_lib_splash_text', // $name attribute
                'media_buttons' => true,
                'textarea_rows' => 10,
            ]
        );
        ?>
    </div>

<?php // --- ADD THIS BLOCK FOR THE NEW CAPTION FIELD --- ?>
    <?php $slider_caption = get_post_meta($post->ID, '_lib_slider_caption', true); ?>
    <div class="ba-lib-meta-field">
        <label for="ba_lib_slider_caption"><?php _e('Slider Caption:', 'beforeafter'); ?></label>
        <input type="text" name="_lib_slider_caption" id="ba_lib_slider_caption" value="<?php echo esc_attr($slider_caption); ?>" class="large-text" />
        <p class="description"><?php _e('A short caption to display under the slider (e.g., "Geresdi-dombvidék, Hungary").', 'beforeafter'); ?></p>
    </div>

<?php
}

/**
 * 3. ENQUEUE ADMIN SCRIPTS
 *
 * This function loads the media uploader JS, but only on the
 * edit screen for the page using our template.
 */
function ba_lib_admin_enqueue_scripts($hook)
{
    global $post;

    // Only load on post edit screens
    if ('post.php' != $hook && 'post-new.php' != $hook) {
        return;
    }

    // Return if $post is not set (e.g., on a 'new post' screen without post type)
    if (!isset($post)) {
        return;
    }

    // Check post type and page template
    if ('page' == $post->post_type) {
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        
        if ('page-template-beforeafter-library.php' == $page_template) {
            wp_enqueue_media();
            // We also enqueue the existing admin script to handle the button clicks
            wp_enqueue_script(
                'beforeafter-admin-script',
                BEFOREAFTER_PLUGIN_URL . 'before-after-admin.js',
                array('jquery'),
                '1.0',
                true
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'ba_lib_admin_enqueue_scripts');


/**
 * 4. SAVE THE META BOX DATA
 *
 * This function is hooked to 'save_post' and handles saving
 * our custom field values.
 */
function ba_lib_save_meta_box_data($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['ba_lib_meta_box_nonce'])) {
        return;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['ba_lib_meta_box_nonce'], 'ba_lib_save_meta_box_data')) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check the user's permissions.
    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    }

    // List of meta keys to save
    $meta_keys = [
        '_lib_before_image_id' => 'sanitize_text_field',
        '_lib_after_image_id'  => 'sanitize_text_field',
        '_lib_before_date'     => 'sanitize_text_field',
        '_lib_after_date'      => 'sanitize_text_field',
        '_lib_splash_text'     => 'wp_kses_post', // Use wp_kses_post for WYSIWYG
        '_lib_slider_caption'  => 'sanitize_text_field',
    ];

    foreach ($meta_keys as $key => $sanitizer) {
        if (isset($_POST[$key])) {
            $value = call_user_func($sanitizer, $_POST[$key]);
            update_post_meta($post_id, $key, $value);
        } else {
            delete_post_meta($post_id, $key);
        }
    }
}
add_action('save_post', 'ba_lib_save_meta_box_data');