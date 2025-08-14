<?php

/**
 * Add custom meta boxes for Before & After images and data
 */
function beforeafter_add_meta_boxes()
{
    add_meta_box(
        'beforeafter_images',
        __('Before & After Images', 'beforeafter'),
        'beforeafter_images_callback',
        'beforeafter',
        'normal',
        'high'
    );

    add_meta_box(
        'beforeafter_dates', // New meta box for dates
        __('Before & After Dates', 'beforeafter'),
        'beforeafter_dates_callback',
        'beforeafter',
        'normal',
        'high'
    );

    add_meta_box(
        'beforeafter_location_data',
        __('Location Data', 'beforeafter'),
        'beforeafter_location_data_callback',
        'beforeafter',
        'normal',
        'high'
    );

    add_meta_box(
        'beforeafter_site_details',
        __('Site Details', 'beforeafter'),
        'beforeafter_site_details_callback',
        'beforeafter',
        'side',
        'high'
    );

    add_meta_box(
        'beforeafter_geojson',
        __('GeoJSON File', 'beforeafter'),
        'beforeafter_geojson_callback',
        'beforeafter',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'beforeafter_add_meta_boxes');


/**
 * Callback for the Images meta box
 */
function beforeafter_images_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'beforeafter_nonce');

    $before_image_id = get_post_meta($post->ID, '_beforeafter_before_image_id', true);
    $after_image_id = get_post_meta($post->ID, '_beforeafter_after_image_id', true);

    $before_image_url = $before_image_id ? wp_get_attachment_url($before_image_id) : '';
    $after_image_url = $after_image_id ? wp_get_attachment_url($after_image_id) : '';
    ?>
    <div class="beforeafter-meta-row">
        <p>
            <label for="beforeafter_before_image"><?php _e('Before Image:', 'beforeafter'); ?></label><br>
            <input type="hidden" name="beforeafter_before_image_id" id="beforeafter_before_image_id"
                value="<?php echo esc_attr($before_image_id); ?>" />
            <img id="beforeafter_before_image_preview" src="<?php echo esc_url($before_image_url); ?>"
                style="max-width:200px; height:auto; <?php echo empty($before_image_url) ? 'display:none;' : ''; ?>" /><br>
            <button type="button" class="button beforeafter_upload_image_button"
                data-field="before_image"><?php _e('Select Before Image', 'beforeafter'); ?></button>
            <button type="button" class="button beforeafter_remove_image_button" data-field="before_image"
                style="<?php echo empty($before_image_url) ? 'display:none;' : ''; ?>"><?php _e('Remove Before Image', 'beforeafter'); ?></button>
        </p>
    </div>

    <div class="beforeafter-meta-row">
        <p>
            <label for="beforeafter_after_image"><?php _e('After Image:', 'beforeafter'); ?></label><br>
            <input type="hidden" name="beforeafter_after_image_id" id="beforeafter_after_image_id"
                value="<?php echo esc_attr($after_image_id); ?>" />
            <img id="beforeafter_after_image_preview" src="<?php echo esc_url($after_image_url); ?>"
                style="max-width:200px; height:auto; <?php echo empty($after_image_url) ? 'display:none;' : ''; ?>" /><br>
            <button type="button" class="button beforeafter_upload_image_button"
                data-field="after_image"><?php _e('Select After Image', 'beforeafter'); ?></button>
            <button type="button" class="button beforeafter_remove_image_button" data-field="after_image"
                style="<?php echo empty($after_image_url) ? 'display:none;' : ''; ?>"><?php _e('Remove After Image', 'beforeafter'); ?></button>
        </p>
    </div>
    <?php
}

/**
 * Callback for the Before & After Dates meta box
 */
function beforeafter_dates_callback($post)
{
    $before_date = get_post_meta($post->ID, '_beforeafter_before_date', true);
    $after_date = get_post_meta($post->ID, '_beforeafter_after_date', true);
    ?>
    <p>
        <label for="beforeafter_before_date"><?php _e('Before Date (Text):', 'beforeafter'); ?></label>
        <input type="text" name="beforeafter_before_date" id="beforeafter_before_date"
            value="<?php echo esc_attr($before_date); ?>" class="large-text" pattern="\d{4}-\d{2}"
            title="Please use the format YYYY-MM" />
        <small><?php _e('e.g., 2010, Spring 2015, January 2020', 'beforeafter'); ?></small>
    </p>
    <p>
        <label for="beforeafter_disturbed_date"><?php _e('Disturbed Date (Text):', 'beforeafter'); ?></label>
        <input type="text" name="beforeafter_disturbed_date" id="beforeafter_disturbed_date"
            value="<?php echo esc_attr(get_post_meta($post->ID, '_beforeafter_disturbed_date', true)); ?>"
            class="large-text" pattern="\d{4}" title="Please use the format YYYY" />
        <small><?php _e('e.g., 2018, Summer 2019, March 2021', 'beforeafter'); ?></small>
    </p>
    <p>
        <label for="beforeafter_after_date"><?php _e('After Date (Text):', 'beforeafter'); ?></label>
        <input type="text" name="beforeafter_after_date" id="beforeafter_after_date"
            value="<?php echo esc_attr($after_date); ?>" class="large-text" />
        <small><?php _e('e.g., 2020, Fall 2022, December 2023', 'beforeafter'); ?></small>
    </p>
    <p>
        <label for="beforeafter_conclusion"><?php _e('Conclusion:', 'beforeafter'); ?></label>
        <select name="beforeafter_conclusion" id="beforeafter_conclusion" class="large-text">
            <?php
            $current_conclusion = get_post_meta($post->ID, '_beforeafter_conclusion', true);
            $options = array(
                'Probable Clearcut',
                'Probable Thinning',
                'False Positive',
                'Fire',
                'Undeterminable',
            );

            foreach ($options as $option) {
                echo '<option value="' . esc_attr($option) . '"' . selected($current_conclusion, $option, false) . '>' . esc_html($option) . '</option>';
            }
            ?>
        </select>
    </p>
    <?php
}


/**
 * Callback for the Location Data meta box
 */
function beforeafter_location_data_callback($post)
{
    $latitude = get_post_meta($post->ID, '_beforeafter_latitude', true);
    $longitude = get_post_meta($post->ID, '_beforeafter_longitude', true);
    $zoom_level = get_post_meta($post->ID, '_beforeafter_zoom_level', true);
    ?>
    <p>
        <label for="beforeafter_latitude"><?php _e('Latitude:', 'beforeafter'); ?></label>
        <input type="number" step="any" name="beforeafter_latitude" id="beforeafter_latitude"
            value="<?php echo esc_attr($latitude); ?>" />
    </p>
    <p>
        <label for="beforeafter_longitude"><?php _e('Longitude:', 'beforeafter'); ?></label>
        <input type="number" step="any" name="beforeafter_longitude" id="beforeafter_longitude"
            value="<?php echo esc_attr($longitude); ?>" />
    </p>
    <p>
        <label for="beforeafter_zoom_level"><?php _e('Zoom Level:', 'beforeafter'); ?></label>
        <input type="number" name="beforeafter_zoom_level" id="beforeafter_zoom_level"
            value="<?php echo esc_attr($zoom_level); ?>" min="0" max="21" />
    </p>
    <?php
}

/**
 * Callback for the Site Details meta box
 */
function beforeafter_site_details_callback($post)
{
    $sitecode = get_post_meta($post->ID, '_beforeafter_sitecode', true);
    $sitename = get_post_meta($post->ID, '_beforeafter_sitename', true);
    ?>
    <p>
        <label for="beforeafter_sitecode"><?php _e('Sitecode:', 'beforeafter'); ?></label>
        <input type="text" name="beforeafter_sitecode" id="beforeafter_sitecode" value="<?php echo esc_attr($sitecode); ?>"
            pattern="[a-zA-Z0-9]{9}" title="Please enter a 9-character alphanumeric code" />
    </p>
    <p>
        <label for="beforeafter_sitename"><?php _e('Sitename:', 'beforeafter'); ?></label>
        <input type="text" name="beforeafter_sitename" id="beforeafter_sitename"
            value="<?php echo esc_attr($sitename); ?>" />
    </p>
    <?php
}



/**
 * Callback for the GeoJSON meta box
 */
function beforeafter_geojson_callback($post)
{
    $geojson_file_id = get_post_meta($post->ID, '_beforeafter_geojson_file_id', true);
    $geojson_file_url = $geojson_file_id ? wp_get_attachment_url($geojson_file_id) : '';
    ?>
    <p>
        <label for="beforeafter_geojson_file"><?php _e('GeoJSON File:', 'beforeafter'); ?></label><br>
        <input type="hidden" name="beforeafter_geojson_file_id" id="beforeafter_geojson_file_id"
            value="<?php echo esc_attr($geojson_file_id); ?>" />
        <span id="beforeafter_geojson_file_name"><?php echo esc_html(basename($geojson_file_url)); ?></span><br>
        <button type="button" class="button beforeafter_upload_file_button"
            data-field="geojson_file"><?php _e('Select GeoJSON File', 'beforeafter'); ?></button>
        <button type="button" class="button beforeafter_remove_file_button" data-field="geojson_file"
            style="<?php echo empty($geojson_file_url) ? 'display:none;' : ''; ?>"><?php _e('Remove GeoJSON File', 'beforeafter'); ?></button>
    </p>
    <?php
}





/**
 * Save custom meta data
 */
function beforeafter_save_meta_data($post_id)
{
    // Check if our nonce is set.
    if (!isset($_POST['beforeafter_nonce'])) {
        return $post_id;
    }

    // Verify that the nonce is valid.
    if (!wp_verify_nonce($_POST['beforeafter_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check the user's permissions.
    if ('beforeafter' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }
    } else {
        return $post_id;
    }

    // Save Before Image ID
    if (isset($_POST['beforeafter_before_image_id'])) {
        update_post_meta($post_id, '_beforeafter_before_image_id', sanitize_text_field($_POST['beforeafter_before_image_id']));
    } else {
        delete_post_meta($post_id, '_beforeafter_before_image_id');
    }

    // Save After Image ID
    if (isset($_POST['beforeafter_after_image_id'])) {
        update_post_meta($post_id, '_beforeafter_after_image_id', sanitize_text_field($_POST['beforeafter_after_image_id']));
    } else {
        delete_post_meta($post_id, '_beforeafter_after_image_id');
    }

    // Save Before Date
    if (isset($_POST['beforeafter_before_date'])) {
        update_post_meta($post_id, '_beforeafter_before_date', sanitize_text_field($_POST['beforeafter_before_date']));
    } else {
        delete_post_meta($post_id, '_beforeafter_before_date');
    }

    // Save After Date
    if (isset($_POST['beforeafter_after_date'])) {
        update_post_meta($post_id, '_beforeafter_after_date', sanitize_text_field($_POST['beforeafter_after_date']));
    } else {
        delete_post_meta($post_id, '_beforeafter_after_date');
    }

    // Save Disturbed Date
    if (isset($_POST['beforeafter_disturbed_date'])) {
        update_post_meta($post_id, '_beforeafter_disturbed_date', sanitize_text_field($_POST['beforeafter_disturbed_date']));
    } else {
        delete_post_meta($post_id, '_beforeafter_disturbed_date');
    }

    // Save Conclusion
    if (isset($_POST['beforeafter_conclusion'])) {
        update_post_meta($post_id, '_beforeafter_conclusion', sanitize_text_field($_POST['beforeafter_conclusion']));
    } else {
        delete_post_meta($post_id, '_beforeafter_conclusion');
    }

    // Save Latitude
    if (isset($_POST['beforeafter_latitude'])) {
        update_post_meta($post_id, '_beforeafter_latitude', sanitize_text_field($_POST['beforeafter_latitude']));
    }

    // Save Longitude
    if (isset($_POST['beforeafter_longitude'])) {
        update_post_meta($post_id, '_beforeafter_longitude', sanitize_text_field($_POST['beforeafter_longitude']));
    }

    // Save Zoom Level
    if (isset($_POST['beforeafter_zoom_level'])) {
        update_post_meta($post_id, '_beforeafter_zoom_level', sanitize_text_field($_POST['beforeafter_zoom_level']));
    }

    // Save Sitecode
    if (isset($_POST['beforeafter_sitecode'])) {
        // First, trim whitespace, then sanitize the result.
        $sanitized_sitecode = sanitize_text_field(trim($_POST['beforeafter_sitecode']));
        update_post_meta($post_id, '_beforeafter_sitecode', $sanitized_sitecode);
    }

    // Save Sitename
    if (isset($_POST['beforeafter_sitename'])) {
        update_post_meta($post_id, '_beforeafter_sitename', sanitize_text_field($_POST['beforeafter_sitename']));
    }

    // Save GeoJSON File ID
    if (isset($_POST['beforeafter_geojson_file_id'])) {
        update_post_meta($post_id, '_beforeafter_geojson_file_id', sanitize_text_field($_POST['beforeafter_geojson_file_id']));
    } else {
        delete_post_meta($post_id, '_beforeafter_geojson_file_id');
    }
    // --- NEW: Set Default Taxonomy Term ---
// Always assign the 'Logging Photos' term to this post on save.
    wp_set_object_terms($post_id, 'logging-photos', 'type');

    // --- NEW: Set Post Date from After Date ---
// Check if the After Date is set and has the correct format.
    if (isset($_POST['beforeafter_after_date']) && !empty($_POST['beforeafter_after_date'])) {
        $after_date_value = sanitize_text_field($_POST['beforeafter_after_date']);

        // Validate the YYYY-MM format before proceeding.
        if (preg_match('/^\d{4}-\d{2}$/', $after_date_value)) {
            // Construct the full date string for the first day of that month.
            $new_post_date = $after_date_value . '-01 00:00:00';

            // Prepare the data for updating the post.
            $post_data = array(
                'ID' => $post_id,
                'post_date' => $new_post_date,
                'post_date_gmt' => get_gmt_from_date($new_post_date),
                'edit_date' => true, // Required to signal an edit
            );

            // To prevent an infinite loop, we must unhook our save function before updating the post.
            remove_action('save_post', 'beforeafter_save_meta_data', 10);

            // Update the post with the new date.
            wp_update_post($post_data);

            // Re-hook our function so it runs on the next save.
            add_action('save_post', 'beforeafter_save_meta_data', 10, 3);
        }
    }


    // --- NEW: Set Featured Image ---
// ... (your existing featured image code) ...
    // --- NEW: Set Featured Image ---
// Automatically set the 'After' image as the featured image.
    if (isset($_POST['beforeafter_after_image_id'])) {
        $after_image_id = sanitize_text_field($_POST['beforeafter_after_image_id']);
        if (!empty($after_image_id)) {
            // This function sets the post's featured image.
            set_post_thumbnail($post_id, $after_image_id);
        }
    }
}
add_action('save_post', 'beforeafter_save_meta_data');


/**
 * Enqueue scripts for media uploader and custom meta box logic
 */
function beforeafter_admin_scripts($hook)
{
    $screen = get_current_screen(); // Get the current screen object

    // Check if we are on the post edit screen or new post screen for 'beforeafter' CPT
    // This is a more reliable way to ensure scripts load only for our custom post type edit pages.
    if (('post.php' == $hook || 'post-new.php' == $hook) && 'beforeafter' === $screen->post_type) {
        // Enqueue WordPress media uploader scripts
        wp_enqueue_media();

        // Enqueue custom script for image selection
        wp_enqueue_script(
            'beforeafter-admin-script',
            plugin_dir_url(__FILE__) . 'before-after-admin.js',
            array('jquery'),
            '1.0',
            true
        );
    }
}
add_action('admin_enqueue_scripts', 'beforeafter_admin_scripts');
