<?php
/*
Plugin Name: Before & After Showcase
Plugin URI:  https://samdavisphd.com/before-after-showcase
Description: Registers a custom post type for Before & After images with location data.
Version:     1.0
Author:     Sam Davis
Author URI: https://samdavisphd.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: beforeafter
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the custom post type 'beforeafter'
 */
function beforeafter_register_post_type()
{
    $labels = array(
        'name' => _x('Before & Afters', 'Post Type General Name', 'beforeafter'),
        'singular_name' => _x('Before & After', 'Post Type Singular Name', 'beforeafter'),
        'menu_name' => __('Before & Afters', 'beforeafter'),
        'name_admin_bar' => __('Before & After', 'beforeafter'),
        'archives' => __('Before & After Archives', 'beforeafter'),
        'attributes' => __('Before & After Attributes', 'beforeafter'),
        'parent_item_colon' => __('Parent Before & After:', 'beforeafter'),
        'all_items' => __('All Before & Afters', 'beforeafter'),
        'add_new_item' => __('Add New Before & After', 'beforeafter'),
        'add_new' => __('Add New', 'beforeafter'),
        'new_item' => __('New Before & After', 'beforeafter'),
        'edit_item' => __('Edit Before & After', 'beforeafter'),
        'update_item' => __('Update Before & After', 'beforeafter'),
        'view_item' => __('View Before & After', 'beforeafter'),
        'view_items' => __('View Before & Afters', 'beforeafter'),
        'search_items' => __('Search Before & After', 'beforeafter'),
        'not_found' => __('Not found', 'beforeafter'),
        'not_found_in_trash' => __('Not found in Trash', 'beforeafter'),
        'featured_image' => __('Featured Image', 'beforeafter'),
        'set_featured_image' => __('Set featured image', 'beforeafter'),
        'remove_featured_image' => __('Remove featured image', 'beforeafter'),
        'use_featured_image' => __('Use as featured image', 'beforeafter'),
        'insert_into_item' => __('Insert into Before & After', 'beforeafter'),
        'uploaded_to_this_item' => __('Uploaded to this Before & After', 'beforeafter'),
        'items_list' => __('Before & Afters list', 'beforeafter'),
        'items_list_navigation' => __('Before & Afters list navigation', 'beforeafter'),
        'filter_items_list' => __('Filter Before & Afters list', 'beforeafter'),
    );
    $args = array(
        'label' => __('Before & After', 'beforeafter'),
        'description' => __('Custom post type for Before & After images', 'beforeafter'),
        'labels' => $labels,
        'supports' => array('title', 'thumbnail'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-images-alt2', // You can choose a different icon
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true, // Enable for Gutenberg editor and REST API
    );
    register_post_type('beforeafter', $args);
}
add_action('init', 'beforeafter_register_post_type', 0);

/**
 * Link 'beforeafter' custom post type to 'location' taxonomy.
 */
function beforeafter_add_taxonomy_support()
{
    register_taxonomy_for_object_type('location', 'beforeafter');
    register_taxonomy_for_object_type('type', 'beforeafter');
}
add_action('init', 'beforeafter_add_taxonomy_support');

/**
 * Include custom archive template from plugin directory.
 */
function beforeafter_archive_template($template)
{
    if (is_post_type_archive('beforeafter')) {
        $new_template = plugin_dir_path(__FILE__) . 'archive-beforeafter.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'beforeafter_archive_template');

/**
 * Enqueue custom before/after slider CSS and JS for single 'beforeafter' posts.
 */
function beforeafter_enqueue_custom_slider_scripts()
{
    if (is_singular('beforeafter')) {
        // Enqueue custom slider CSS
        wp_enqueue_style(
            'beforeafter-slider-css',
            plugin_dir_url(__FILE__) . 'before-after-slider.css',
            array(),
            '1.0.0'
        );

        // Enqueue Leaflet CSS
        wp_enqueue_style(
            'leaflet-css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );

        // Enqueue custom slider JS in the footer
        wp_enqueue_script(
            'beforeafter-slider-js',
            plugin_dir_url(__FILE__) . 'before-after-slider.js',
            array('jquery'), // Depends on jQuery for simplicity, can be made vanilla JS if preferred
            '1.0.0',
            true // Load in footer
        );

        // Enqueue Leaflet JS
        wp_enqueue_script(
            'leaflet-js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );

        // Enqueue custom map JS
        wp_enqueue_script(
            'beforeafter-map-js',
            plugin_dir_url(__FILE__) . 'before-after-map.js',
            array('leaflet-js'),
            '1.0.0',
            true
        );

        // Pass data to script
        $post_id = get_the_ID();
        $latitude = get_post_meta($post_id, '_beforeafter_latitude', true);
        $longitude = get_post_meta($post_id, '_beforeafter_longitude', true);
        $zoom_level = get_post_meta($post_id, '_beforeafter_zoom_level', true);
        $geojson_file_id = get_post_meta($post_id, '_beforeafter_geojson_file_id', true);
        $geojson_file_url = $geojson_file_id ? wp_get_attachment_url($geojson_file_id) : '';


        wp_localize_script('beforeafter-map-js', 'beforeafter_map_data', array(
            'lat' => $latitude,
            'lng' => $longitude,
            'zoom' => $zoom_level,
            'geojson_url' => $geojson_file_url
        ));

        // Enqueue Chart.js from a CDN
        wp_enqueue_script(
            'chart-js',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '4.4.0', // Use a specific version for stability
            true
        );

        // Enqueue our new custom graph script
        wp_enqueue_script(
            'beforeafter-graph-js',
            plugin_dir_url(__FILE__) . 'before-after-graph.js',
            array('chart-js'), // Make it dependent on Chart.js
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'beforeafter_enqueue_custom_slider_scripts');


/**
 * Filter image attributes to prevent lazy loading on custom slider images.
 * This is crucial to ensure images load immediately for the slider.
 */
function beforeafter_filter_custom_slider_image_attributes($attr, $attachment, $size)
{
    // Only apply this filter if we are on a single 'beforeafter' post
    if (is_singular('beforeafter')) {
        // Check if the image is one of our before/after images
        $post_id = get_the_ID();
        $before_image_id = get_post_meta($post_id, '_beforeafter_before_image_id', true);
        $after_image_id = get_post_meta($post_id, '_beforeafter_after_image_id', true);

        if ($attachment->ID == $before_image_id || $attachment->ID == $after_image_id) {
            // Add 'skip-lazy' class to prevent lazy loading plugins from processing it
            if (isset($attr['class'])) {
                $attr['class'] .= ' skip-lazy';
            } else {
                $attr['class'] = 'skip-lazy';
            }
            // Remove native lazy loading attribute
            $attr['loading'] = 'eager';
            // Remove any data- attributes that lazy loading plugins might use
            unset($attr['data-src']);
            unset($attr['data-srcset']);
            unset($attr['data-sizes']);
            // Remove lazyload classes if they were added
            $attr['class'] = str_replace('lazyloaded', '', $attr['class']);
            $attr['class'] = str_replace('lazyloading', '', $attr['class']);
        }
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'beforeafter_filter_custom_slider_image_attributes', 10, 3);


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
        <input type="text" name="beforeafter_sitecode" id="beforeafter_sitecode"
            value="<?php echo esc_attr($sitecode); ?>" pattern="[a-zA-Z0-9]{9}"
            title="Please enter a 9-character alphanumeric code" />
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
            remove_action('save_post', 'beforeafter_save_meta_data', 10, 3);

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

/**
 * Frontend template for 'beforeafter' custom post type
 * This function will be called if a single 'beforeafter' post is viewed.
 * You can customize the output here.
 */
function beforeafter_single_template($template)
{
    if (is_singular('beforeafter')) {
        $new_template = plugin_dir_path(__FILE__) . 'single-beforeafter.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('single_template', 'beforeafter_single_template');

/**
 * Add custom columns to the 'beforeafter' post list
 */
function beforeafter_set_custom_columns($columns)
{
    $columns['before_image'] = __('Before Image', 'beforeafter');
    $columns['after_image'] = __('After Image', 'beforeafter');
    $columns['sitecode'] = __('Sitecode', 'beforeafter');
    return $columns;
}
add_filter('manage_beforeafter_posts_columns', 'beforeafter_set_custom_columns');

/**
 * Display content for custom columns
 */
function beforeafter_custom_column_content($column, $post_id)
{
    switch ($column) {
        case 'before_image':
            $image_id = get_post_meta($post_id, '_beforeafter_before_image_id', true);
            if ($image_id) {
                echo wp_get_attachment_image($image_id, array(50, 50));
            } else {
                echo '—';
            }
            break;
        case 'after_image':
            $image_id = get_post_meta($post_id, '_beforeafter_after_image_id', true);
            if ($image_id) {
                echo wp_get_attachment_image($image_id, array(50, 50));
            } else {
                echo '—';
            }
            break;
        case 'sitecode':
            echo esc_html(get_post_meta($post_id, '_beforeafter_sitecode', true));
            break;
    }
}
add_action('manage_beforeafter_posts_custom_column', 'beforeafter_custom_column_content', 10, 2);

/**
 * Make custom columns sortable (optional)
 */
function beforeafter_sortable_columns($columns)
{
    $columns['sitecode'] = 'sitecode'; // 'sitecode' is the meta key
    return $columns;
}
add_filter('manage_edit-beforeafter_sortable_columns', 'beforeafter_sortable_columns');

/**
 * Handle custom column sorting query (optional)
 */
function beforeafter_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ('sitecode' === $query->get('orderby')) {
        $query->set('orderby', 'meta_value');
        $query->set('meta_key', '_beforeafter_sitecode');
        $query->set('meta_type', 'CHAR'); // Or 'NUMERIC' if it were numbers
    }
}
add_action('pre_get_posts', 'beforeafter_orderby');

/**
 * Allow geojson and json file uploads.
 */
function beforeafter_add_custom_mime_types($mimes)
{
    $mimes['geojson'] = 'application/geo+json';
    $mimes['json'] = 'application/json';
    return $mimes;
}
add_filter('upload_mimes', 'beforeafter_add_custom_mime_types');




/**
 * Sets a default featured image for 'beforeafter' posts using an SVG from the plugin's assets folder.
 *
 * This function hooks into the 'post_thumbnail_html' filter. If the incoming HTML is empty
 * (meaning no featured image is set) and the post type is 'beforeafter', it generates
 * an <img> tag pointing to the default SVG file.
 *
 * @param string $html              The post thumbnail HTML.
 * @param int    $post_id           The post ID.
 * @return string The post thumbnail HTML, modified if necessary.
 */
function beforeafter_default_svg_featured_image($html, $post_id)
{
    // Only run this check for our 'beforeafter' custom post type.
    if ('beforeafter' === get_post_type($post_id)) {
        // If the post thumbnail HTML is empty, it means no featured image is set.
        if (empty($html)) {
            // Construct the full URL to the default SVG image in the plugin's assets folder.
            $default_svg_url = plugin_dir_url(__FILE__) . 'assets/pattern.svg';

            // Create the HTML for the default image.
            $html = '<img src="' . esc_url($default_svg_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" class="wp-post-image" />';
        }
    }
    return $html;
}
add_filter('post_thumbnail_html', 'beforeafter_default_svg_featured_image', 10, 2);


/**
 * Remove the 'Type' taxonomy meta box from the 'beforeafter' edit screen.
 *
 * Since the 'type' is set automatically on save, there is no need for the
 * user to see or interact with this meta box.
 */
function beforeafter_remove_type_meta_box()
{
    remove_meta_box(
        'typediv',       // The ID of the taxonomy meta box. For 'type', it's 'typediv'.
        'beforeafter',   // The post type screen where it should be removed.
        'side'           // The context (where it appears on the screen).
    );
}
add_action('admin_menu', 'beforeafter_remove_type_meta_box');


/**
 * Displays related 'beforeafter' posts based on a shared sitecode.
 *
 * This function queries for other posts of the same type that have the same
 * '_beforeafter_sitecode' meta value and displays them using the theme's
 * 'content-excerpt' template part for consistent styling.
 */
function beforeafter_display_related_by_sitecode()
{
    // Get the current post's ID and sitecode.
    $current_post_id = get_the_ID();
    $sitecode = get_post_meta($current_post_id, '_beforeafter_sitecode', true);

    // Only proceed if a sitecode exists.
    if (!empty($sitecode)) {

        // Set up the query arguments.
        $args = array(
            'post_type' => 'beforeafter',
            'posts_per_page' => 4, // You can change this number
            'post__not_in' => array($current_post_id), // Exclude the current post
            'meta_query' => array(
                array(
                    'key' => '_beforeafter_sitecode',
                    'value' => $sitecode,
                    'compare' => '=',
                ),
            ),
        );

        $related_query = new WP_Query($args);

        // If we found related posts, display them.
        if ($related_query->have_posts()) {
            // Use the theme's existing HTML structure for the section.
            echo '<div class="related-posts">';
            echo '<div class="container my-10 lg:my-12">';
            echo '<h3 class="block text-moss mb-4">Related Before & After Posts</h3>';
            echo '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 ">';

            while ($related_query->have_posts()) {
                $related_query->the_post();
                // Reuse the theme's template part for individual post styling.
                get_template_part('template-parts/content/content-excerpt');
            }

            echo '</div>'; // close .grid
            echo '</div>'; // close .container
            echo '</div>'; // close .related-posts
        }

        // Restore the original post data.
        wp_reset_postdata();
    }
}

/**
 * Restructures the $_FILES array for easier processing.
 *
 * @param array $files_array The original $_FILES array for a specific field.
 * @return array The restructured array.
 */
function beforeafter_restructure_files_array($files_array)
{
    $reordered_array = [];
    foreach ($files_array['name'] as $key => $name) {
        if (!empty($name)) {
            $reordered_array[] = [
                'name' => $name,
                'type' => $files_array['type'][$key],
                'tmp_name' => $files_array['tmp_name'][$key],
                'error' => $files_array['error'][$key],
                'size' => $files_array['size'][$key],
            ];
        }
    }
    return $reordered_array;
}


/**
 * Handle the bulk import form submission.
 */
function beforeafter_handle_bulk_import()
{
    if (!isset($_POST['submit']) || !isset($_POST['beforeafter_bulk_import_nonce_field'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['beforeafter_bulk_import_nonce_field'], 'beforeafter_bulk_import_nonce')) {
        wp_die('Nonce verification failed!');
    }
    if (!current_user_can('manage_options')) {
        wp_die('You do not have sufficient permissions.');
    }

    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    $upload_dir = wp_upload_dir();
    $import_dir = $upload_dir['basedir'] . '/before-after-import/';

    if (isset($_FILES['import_csv']) && $_FILES['import_csv']['error'] == UPLOAD_ERR_OK) {
        $csv_path = $_FILES['import_csv']['tmp_name'];
        $import_successes = [];
        $import_warnings = [];

        if (($handle = fopen($csv_path, "r")) !== FALSE) {
            fgetcsv($handle, 1000, ","); // Skip header
            $row_number = 1;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row_number++;

                $site_id = $data[0];
                $before_image_name = $data[1];
                $after_image_name = $data[2];
                $before_date = $data[3];
                $after_date = $data[4];
                $sitecode = $data[5];
                $sitename = $data[6];
                $latitude = $data[7];
                $longitude = $data[8];
                $zoom_level = $data[9];
                $disturbed_date = $data[10];
                $conclusion = $data[11];
                $geojson_file_name = $data[12];
                $location_name = $data[13];

                $post_title = $sitecode . ' - ' . $sitename;

                // --- START: MODIFIED BLOCK ---
// Default post date to current time
                $post_date = current_time('mysql');

                // If after_date exists and is in YYYY-MM format, use it.
                if (!empty($after_date) && preg_match('/^\d{4}-\d{2}$/', $after_date)) {
                    // Set the date to the first day of that month and year
                    $post_date = $after_date . '-01 00:00:00';
                }

                $post_id = wp_insert_post([
                    'post_title' => sanitize_text_field($post_title),
                    'post_status' => 'publish',
                    'post_type' => 'beforeafter',
                    'post_date' => $post_date, // Set the publish date here
                    'post_date_gmt' => get_gmt_from_date($post_date), // Also set the GMT date
                ]);
                // --- END: MODIFIED BLOCK ---

                if ($post_id) {
                    update_post_meta($post_id, '_beforeafter_sitecode', sanitize_text_field($sitecode));
                    update_post_meta($post_id, '_beforeafter_sitename', sanitize_text_field($sitename));
                    update_post_meta($post_id, '_beforeafter_before_date', sanitize_text_field($before_date));
                    update_post_meta($post_id, '_beforeafter_after_date', sanitize_text_field($after_date));
                    update_post_meta($post_id, '_beforeafter_latitude', sanitize_text_field($latitude));
                    update_post_meta($post_id, '_beforeafter_longitude', sanitize_text_field($longitude));
                    update_post_meta($post_id, '_beforeafter_zoom_level', sanitize_text_field($zoom_level));
                    update_post_meta($post_id, '_beforeafter_disturbed_date', sanitize_text_field($disturbed_date));
                    update_post_meta($post_id, '_beforeafter_conclusion', sanitize_text_field($conclusion));

                    if (!empty($location_name)) {
                        $term = term_exists($location_name, 'location');
                        if (0 === $term || null === $term) {
                            $term = wp_insert_term($location_name, 'location');
                        }
                        if (!is_wp_error($term)) {
                            wp_set_object_terms($post_id, (int) $term['term_id'], 'location');
                        }
                    }

                    $type_term_name = 'Logging Photos';
                    $type_taxonomy = 'type';
                    $term = term_exists($type_term_name, $type_taxonomy);
                    if (0 === $term || null === $term) {
                        $term = wp_insert_term($type_term_name, $type_taxonomy);
                    }
                    if (!is_wp_error($term)) {
                        wp_set_object_terms($post_id, (int) $term['term_id'], $type_taxonomy);
                    }

                    // --- UPDATED: Helper function to copy files instead of moving ---
                    $attach_file = function ($filename, $parent_post_id) use ($import_dir, &$import_warnings, $row_number, $post_title) {
                        $filepath = $import_dir . $filename;
                        if (!empty($filename) && file_exists($filepath)) {
                            // Create a temporary copy of the file
                            $temp_file = wp_tempnam($filename);
                            if (copy($filepath, $temp_file)) {
                                $file_array = [
                                    'name' => basename($filename),
                                    'tmp_name' => $temp_file
                                ];

                                // Sideload the temporary file. WordPress will move/delete this temp file.
                                $attachment_id = media_handle_sideload($file_array, $parent_post_id);

                                // If the sideload fails, the temp file might not be deleted, so we clean it up.
                                if (is_wp_error($attachment_id)) {
                                    @unlink($temp_file);
                                    $import_warnings[] = "Row " . $row_number . ": Error processing file '" . esc_html($filename) . "': " . $attachment_id->get_error_message();
                                    return null;
                                }
                                return $attachment_id;
                            } else {
                                $import_warnings[] = "Row " . $row_number . ": Could not create temporary copy of file '" . esc_html($filename) . "'.";
                                return null;
                            }
                        } elseif (!empty($filename)) {
                            $import_warnings[] = "Row " . $row_number . ": File '" . esc_html($filename) . "' not found in import directory for post '" . esc_html($post_title) . "'.";
                            return null;
                        }
                        return null;
                    };

                    $before_image_id = $attach_file($before_image_name, $post_id);
                    if ($before_image_id)
                        update_post_meta($post_id, '_beforeafter_before_image_id', $before_image_id);

                    $after_image_id = $attach_file($after_image_name, $post_id);
                    if ($after_image_id) {
                        update_post_meta($post_id, '_beforeafter_after_image_id', $after_image_id);
                        set_post_thumbnail($post_id, $after_image_id);
                    }

                    $geojson_file_id = $attach_file($geojson_file_name, $post_id);
                    if ($geojson_file_id)
                        update_post_meta($post_id, '_beforeafter_geojson_file_id', $geojson_file_id);

                    $import_successes[] = "Successfully imported post: " . esc_html($post_title);
                } else {
                    $import_warnings[] = "Failed to import post for sitecode: " . esc_html($sitecode) . " on row " . $row_number;
                }
            }
            fclose($handle);

            if (!empty($import_successes)) {
                add_action('admin_notices', function () use ($import_successes) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . implode('<br>', $import_successes) . '</p></div>'; });
            }
            if (!empty($import_warnings)) {
                add_action('admin_notices', function () use ($import_warnings) {
                    echo '<div class="notice notice-warning is-dismissible"><p>' . implode('<br>', $import_warnings) . '</p></div>'; });
            }

        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Could not open the CSV file.', 'beforeafter') . '</p></div>'; });
        }
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Please upload a CSV file.', 'beforeafter') . '</p></div>'; });
    }
}
add_action('admin_init', 'beforeafter_handle_bulk_import');


/**
 * Add Bulk Import page to the Before & After menu.
 */
function beforeafter_add_bulk_import_page()
{
    add_submenu_page(
        'edit.php?post_type=beforeafter', // Parent slug
        __('Bulk Import', 'beforeafter'),    // Page title
        __('Bulk Import', 'beforeafter'),    // Menu title
        'manage_options',                  // Capability
        'beforeafter-bulk-import',         // Menu slug
        'beforeafter_render_bulk_import_page' // Callback function
    );
}
add_action('admin_menu', 'beforeafter_add_bulk_import_page');

/**
 * Render the Bulk Import page content.
 */
function beforeafter_render_bulk_import_page()
{
    // Define the import directory path
    $upload_dir = wp_upload_dir();
    $import_dir_path = $upload_dir['basedir'] . '/before-after-import/';
    $import_dir_url = $upload_dir['baseurl'] . '/before-after-import/';

    // Ensure the directory exists
    if (!file_exists($import_dir_path)) {
        wp_mkdir_p($import_dir_path);
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="notice notice-info">
            <p><strong><?php _e('Instructions:', 'beforeafter'); ?></strong></p>
            <ol style="list-style: decimal; padding-left: 20px;">
                <li><?php printf(__('Upload your JPG and GeoJSON files to the following directory on your server using FTP or your hosting file manager: %s', 'beforeafter'), '<code>' . esc_html($import_dir_path) . '</code>'); ?>
                </li>
                <li><?php _e('Ensure your CSV file is formatted correctly with the columns in the following order: `site_id`, `before_image_name`, `after_image_name`, `before_date`, `after_date`, `sitecode`, `sitename`, `latitude`, `longitude`, `zoom_level`, `disturbed_date`, `conclusion`, `geojson_file_name`, and `location_name`. The file must also reference the filenames you just uploaded.', 'beforeafter'); ?>
                </li>
                <li><?php _e('Upload your CSV file below and click "Start Import".', 'beforeafter'); ?></li>
            </ol>
        </div>

        <form method="post" enctype="multipart/form-data"
            action="edit.php?post_type=beforeafter&page=beforeafter-bulk-import">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('CSV File', 'beforeafter'); ?></th>
                    <td>
                        <input type="file" name="import_csv" id="import_csv" accept=".csv">
                        <p class="description"><?php _e('Upload the main CSV file with post data.', 'beforeafter'); ?></p>
                    </td>
                </tr>
            </table>

            <?php
            wp_nonce_field('beforeafter_bulk_import_nonce', 'beforeafter_bulk_import_nonce_field');
            submit_button(__('Start Import', 'beforeafter'));
            ?>
        </form>
    </div>
    <?php
}

// Register Custom Post Type for Natura 2000 Sites
function natura_2000_custom_post_type()
{

    $labels = array(
        'name' => _x('Natura 2000 Sites', 'Post Type General Name', 'text_domain'),
        'singular_name' => _x('Natura 2000 Site', 'Post Type Singular Name', 'text_domain'),
        'menu_name' => __('Natura 2000 Sites', 'text_domain'),
        'name_admin_bar' => __('Natura 2000 Site', 'text_domain'),
        'archives' => __('Site Archives', 'text_domain'),
        'attributes' => __('Site Attributes', 'text_domain'),
        'parent_item_colon' => __('Parent Site:', 'text_domain'),
        'all_items' => __('All Sites', 'text_domain'),
        'add_new_item' => __('Add New Site', 'text_domain'),
        'add_new' => __('Add New', 'text_domain'),
        'new_item' => __('New Site', 'text_domain'),
        'edit_item' => __('Edit Site', 'text_domain'),
        'update_item' => __('Update Site', 'text_domain'),
        'view_item' => __('View Site', 'text_domain'),
        'view_items' => __('View Sites', 'text_domain'),
        'search_items' => __('Search Site', 'text_domain'),
        'not_found' => __('Not found', 'text_domain'),
        'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
        'featured_image' => __('Featured Image', 'text_domain'),
        'set_featured_image' => __('Set featured image', 'text_domain'),
        'remove_featured_image' => __('Remove featured image', 'text_domain'),
        'use_featured_image' => __('Use as featured image', 'text_domain'),
        'insert_into_item' => __('Insert into site', 'text_domain'),
        'uploaded_to_this_item' => __('Uploaded to this site', 'text_domain'),
        'items_list' => __('Sites list', 'text_domain'),
        'items_list_navigation' => __('Sites list navigation', 'text_domain'),
        'filter_items_list' => __('Filter sites list', 'text_domain'),
    );
    $args = array(
        'label' => __('Natura 2000 Site', 'text_domain'),
        'description' => __('Custom post type for Natura 2000 sites', 'text_domain'),
        'labels' => $labels,
        'supports' => array('title', ),
        'taxonomies' => array('category', 'post_tag'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'page',
    );
    register_post_type('natura_2000_site', $args);

}
add_action('init', 'natura_2000_custom_post_type', 0);

// Add Meta Box to Natura 2000 Site CPT
function natura_2000_add_meta_box()
{
    add_meta_box(
        'natura_2000_site_details',
        'Site Details',
        'natura_2000_meta_box_callback',
        'natura_2000_site'
    );
}
add_action('add_meta_boxes', 'natura_2000_add_meta_box');

// Meta Box Callback
function natura_2000_meta_box_callback($post)
{
    wp_nonce_field('natura_2000_save_meta_box_data', 'natura_2000_meta_box_nonce');

    $fields = array(
        'sitecode',
        'sitename',
        'area_ha_2023',
        'area_ha_2022',
        'area_ha_2021',
        'area_ha_2020',
        'area_ha_2019',
        'area_ha_2018',
        'area_ha_2017',
        'area_ha_2016',
        'area_ha_2015',
        'area_ha_2014',
        'area_ha_2013',
        'area_ha_2012',
        'area_ha_2011',
        'area_ha_2010',
        'area_ha_2009',
        'area_ha_2008',
        'area_ha_2007',
        'area_ha_2006',
        'area_ha_2005',
        'area_ha_2004',
        'area_ha_2003',
        'area_ha_2002',
        'area_ha_2001',
        'site_ha',
        'most_disturbed_year'
    );

    foreach ($fields as $field) {
        $value = get_post_meta($post->ID, '_' . $field, true);
        echo '<label for="' . $field . '_field">' . ucfirst(str_replace('_', ' ', $field)) . ':</label>';
        echo '<input type="text" id="' . $field . '_field" name="' . $field . '_field" value="' . esc_attr($value) . '" size="25" />';
        echo '<br/><br/>';
    }
}

// Save Meta Box Data
function natura_2000_save_meta_box_data($post_id)
{
    if (!isset($_POST['natura_2000_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['natura_2000_meta_box_nonce'], 'natura_2000_save_meta_box_data')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST['post_type']) && 'natura_2000_site' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    $fields = array(
        'sitecode',
        'sitename',
        'area_ha_2023',
        'area_ha_2022',
        'area_ha_2021',
        'area_ha_2020',
        'area_ha_2019',
        'area_ha_2018',
        'area_ha_2017',
        'area_ha_2016',
        'area_ha_2015',
        'area_ha_2014',
        'area_ha_2013',
        'area_ha_2012',
        'area_ha_2011',
        'area_ha_2010',
        'area_ha_2009',
        'area_ha_2008',
        'area_ha_2007',
        'area_ha_2006',
        'area_ha_2005',
        'area_ha_2004',
        'area_ha_2003',
        'area_ha_2002',
        'area_ha_2001',
        'site_ha',
        'most_disturbed_year'
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field . '_field'])) {
            $data = sanitize_text_field($_POST[$field . '_field']);
            update_post_meta($post_id, '_' . $field, $data);
        }
    }
}
add_action('save_post', 'natura_2000_save_meta_box_data');

// Add submenu page for importing Natura 2000 sites
function natura_2000_import_submenu_page()
{
    add_submenu_page(
        'edit.php?post_type=natura_2000_site',
        'Import Natura 2000 Sites',
        'Import Sites',
        'manage_options',
        'natura-2000-import',
        'natura_2000_import_page_callback'
    );
}
add_action('admin_menu', 'natura_2000_import_submenu_page');

// Callback function for the import page
function natura_2000_import_page_callback()
{
    ?>
    <div class="wrap">
        <h1>Import Natura 2000 Sites</h1>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('natura_2000_import_nonce', 'natura_2000_import_nonce_field'); ?>
            <p>
                <label for="csv_file">Upload CSV file:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv">
            </p>
            <p>
                <input type="submit" name="submit_import" class="button button-primary" value="Import">
            </p>
        </form>
    </div>
    <?php
}

// Handle the CSV import
function natura_2000_handle_import()
{
    if (isset($_POST['submit_import']) && isset($_FILES['csv_file'])) {
        // Verify nonce
        if (!isset($_POST['natura_2000_import_nonce_field']) || !wp_verify_nonce($_POST['natura_2000_import_nonce_field'], 'natura_2000_import_nonce')) {
            wp_die('Security check failed.');
        }

        // Check for file upload errors
        if ($_FILES['csv_file']['error'] > 0) {
            wp_die('File upload error: ' . $_FILES['csv_file']['error']);
        }

        // Check if file is a CSV
        $file_info = wp_check_filetype(basename($_FILES['csv_file']['name']));
        if ($file_info['ext'] !== 'csv') {
            wp_die('Please upload a valid CSV file.');
        }

        // Process the CSV file
        $csv_file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            // --- NEW: Read header row to map column names to indexes ---
            $header = fgetcsv($handle, 1000, ",");
            $column_map = array_flip($header); // Creates an array like ['sitecode' => 1, 'site_ha' => 4, ...]

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Use the column map to get data by name, not by a fixed index
                $sitecode = isset($column_map['sitecode']) ? $data[$column_map['sitecode']] : '';

                if (empty($sitecode)) {
                    continue; // Skip rows without a sitecode
                }

                // Create new post
                $new_post = array(
                    'post_title' => $sitecode,
                    'post_type' => 'natura_2000_site',
                    'post_status' => 'publish',
                );

                $post_id = wp_insert_post($new_post);

                // Add meta data using the column map
                if ($post_id) {
                    // Define all the fields we want to import
                    $fields_to_import = array(
                        'sitecode',
                        'sitename',
                        'countrycode',
                        'perclogged',
                        'site_ha',
                        'most_disturbed_year_ha',
                        'most_disturbed_year'
                    );

                    // Add all yearly disturbance fields
                    for ($year = 2001; $year <= 2023; $year++) {
                        $fields_to_import[] = 'area_ha_' . $year;
                    }

                    // Loop through the fields and update post meta if the column exists in the CSV
                    foreach ($fields_to_import as $field) {
                        if (isset($column_map[$field]) && isset($data[$column_map[$field]])) {
                            update_post_meta($post_id, '_' . $field, sanitize_text_field($data[$column_map[$field]]));
                        }
                    }
                }
            }
            fclose($handle);

            echo '<div class="updated"><p>Import complete!</p></div>';
        }
    }
}
add_action('admin_init', 'natura_2000_handle_import');


/**
 * Adds a 'Move All to Trash' button on the Natura 2000 Sites admin list page for administrators.
 */
function natura_2000_add_bulk_trash_button()
{
    $screen = get_current_screen();
    if ('edit-natura_2000_site' !== $screen->id) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $nonce = wp_create_nonce('beforeafter_bulk_trash_natura_sites_nonce');
    $trash_url = add_query_arg(array(
        'action' => 'beforeafter_bulk_trash_natura_sites',
        '_wpnonce' => $nonce,
    ), admin_url('admin.php'));
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.bulkactions').append(
                '<a href="<?php echo esc_url($trash_url); ?>" id="doaction_trash_all" class="button action" style="margin-left: 5px;"><?php _e('Move All to Trash', 'beforeafter'); ?></a>'
            );

            $('#doaction_trash_all').on('click', function (e) {
                if (!confirm('<?php _e('Are you sure you want to move all Natura 2000 Sites to the trash? This will be done in the background and may take some time.', 'beforeafter'); ?>')) {
                    e.preventDefault();
                } else {
                    $(this).text('<?php _e('Trashing...', 'beforeafter'); ?>').prop('disabled', true);
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer-edit.php', 'natura_2000_add_bulk_trash_button');

/**
 * Handles the logic for initiating the bulk trashing of 'natura_2000_site' posts.
 */
function natura_2000_handle_bulk_trash()
{
    check_admin_referer('beforeafter_bulk_trash_natura_sites_nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'beforeafter'));
    }

    $all_posts = get_posts(array(
        'post_type' => 'natura_2000_site',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'any', // Get all statuses except trash
    ));

    if (!empty($all_posts)) {
        // Store the list of post IDs in a transient to be processed in the background
        set_transient('natura_2000_bulk_trash_ids', $all_posts, HOUR_IN_SECONDS);

        // Schedule an immediate, one-off event to start the processing
        wp_schedule_single_event(time(), 'natura_2000_process_trash_batch_hook');

        // Set a transient to show the "started" notice
        set_transient('natura_2000_trash_notice', 'started', 60);
    }

    // Redirect back to the admin page
    wp_redirect(admin_url('edit.php?post_type=natura_2000_site'));
    exit;
}
add_action('admin_action_beforeafter_bulk_trash_natura_sites', 'natura_2000_handle_bulk_trash');

/**
 * Processes a single batch of 'natura_2000_site' posts to be trashed.
 * This is triggered by a WP-Cron event.
 */
function natura_2000_process_trash_batch()
{
    $post_ids = get_transient('natura_2000_bulk_trash_ids');

    if (empty($post_ids)) {
        // Job is done, set a notice and finish
        set_transient('natura_2000_trash_notice', 'completed', 60);
        return;
    }

    // Process a batch of 50 posts at a time to prevent timeouts
    $batch_size = 50;
    $ids_to_trash = array_splice($post_ids, 0, $batch_size);

    foreach ($ids_to_trash as $post_id) {
        wp_trash_post($post_id);
    }

    if (!empty($post_ids)) {
        // If there are more posts, update the transient and reschedule the next batch
        set_transient('natura_2000_bulk_trash_ids', $post_ids, HOUR_IN_SECONDS);
        wp_schedule_single_event(time() + 2, 'natura_2000_process_trash_batch_hook');
    } else {
        // If this was the last batch, delete the transient and set the completed notice
        delete_transient('natura_2000_bulk_trash_ids');
        set_transient('natura_2000_trash_notice', 'completed', 60);
    }
}
add_action('natura_2000_process_trash_batch_hook', 'natura_2000_process_trash_batch');

/**
 * Displays admin notices for the bulk trash process.
 */
function natura_2000_trash_admin_notices()
{
    $notice = get_transient('natura_2000_trash_notice');

    if (!$notice) {
        return;
    }

    if ('started' === $notice) {
        echo '<div class="notice notice-info is-dismissible"><p>' . __('Started moving all Natura 2000 Sites to the trash. This will happen in the background.', 'beforeafter') . '</p></div>';
    } elseif ('completed' === $notice) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Successfully moved all Natura 2000 Sites to the trash.', 'beforeafter') . '</p></div>';
    }

    // Delete the transient so the notice doesn't show again
    delete_transient('natura_2000_trash_notice');
}
add_action('admin_notices', 'natura_2000_trash_admin_notices');

/**
 * Adds a 'Move All to Trash' button on the Before & After admin list page for administrators.
 */
function beforeafter_add_bulk_trash_button() {
    $screen = get_current_screen();
    // Check if we are on the correct admin page
    if ( 'edit-beforeafter' !== $screen->id ) {
        return;
    }

    // Only show for users who can manage options
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Create a secure URL for the action
    $nonce = wp_create_nonce( 'beforeafter_bulk_trash_all_nonce' );
    $trash_url = add_query_arg( array(
        'action'   => 'beforeafter_bulk_trash_all',
        '_wpnonce' => $nonce,
    ), admin_url( 'admin.php' ) );
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Add the button next to the other bulk action controls
            $('.bulkactions').append(
                '<a href="<?php echo esc_url( $trash_url ); ?>" id="doaction_trash_all_beforeafter" class="button action" style="margin-left: 5px;"><?php _e( 'Move All to Trash', 'beforeafter' ); ?></a>'
            );

            // Add a confirmation dialog
            $('#doaction_trash_all_beforeafter').on('click', function(e) {
                if (!confirm('<?php _e( 'Are you sure you want to move all Before & After posts to the trash? This will be done in the background and may take some time.', 'beforeafter' ); ?>')) {
                    e.preventDefault();
                } else {
                    $(this).text('<?php _e( 'Trashing...', 'beforeafter' ); ?>').prop('disabled', true);
                }
            });
        });
    </script>
    <?php
}
add_action( 'admin_footer-edit.php', 'beforeafter_add_bulk_trash_button' );

/**
 * Handles the logic for initiating the bulk trashing of 'beforeafter' posts.
 */
function beforeafter_handle_bulk_trash() {
    check_admin_referer( 'beforeafter_bulk_trash_all_nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to perform this action.', 'beforeafter' ) );
    }

    $all_posts = get_posts( array(
        'post_type'      => 'beforeafter',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_status'    => 'any',
    ) );

    if ( ! empty( $all_posts ) ) {
        // Store post IDs in a transient for background processing
        set_transient( 'beforeafter_bulk_trash_ids', $all_posts, HOUR_IN_SECONDS );
        
        // Schedule a one-off event to start the process immediately
        wp_schedule_single_event( time(), 'beforeafter_process_trash_batch_hook' );
        
        // Set a transient to show a "started" notice
        set_transient( 'beforeafter_trash_notice', 'started', 60 );
    }

    // Redirect back to the admin page
    wp_redirect( admin_url( 'edit.php?post_type=beforeafter' ) );
    exit;
}
add_action( 'admin_action_beforeafter_bulk_trash_all', 'beforeafter_handle_bulk_trash' );

/**
 * Processes a single batch of 'beforeafter' posts to be trashed via WP-Cron.
 */
function beforeafter_process_trash_batch() {
    $post_ids = get_transient( 'beforeafter_bulk_trash_ids' );

    if ( empty( $post_ids ) ) {
        set_transient( 'beforeafter_trash_notice', 'completed', 60 );
        return;
    }

    // Process in batches of 50 to avoid timeouts
    $batch_size = 50;
    $ids_to_trash = array_splice( $post_ids, 0, $batch_size );

    foreach ( $ids_to_trash as $post_id ) {
        wp_trash_post( $post_id );
    }

    if ( ! empty( $post_ids ) ) {
        // Reschedule for the next batch
        set_transient( 'beforeafter_bulk_trash_ids', $post_ids, HOUR_IN_SECONDS );
        wp_schedule_single_event( time() + 2, 'beforeafter_process_trash_batch_hook' );
    } else {
        // Clean up when done
        delete_transient( 'beforeafter_bulk_trash_ids' );
        set_transient( 'beforeafter_trash_notice', 'completed', 60 );
    }
}
add_action( 'beforeafter_process_trash_batch_hook', 'beforeafter_process_trash_batch' );

/**
 * Displays admin notices for the bulk trash process.
 */
function beforeafter_trash_admin_notices() {
    // Only show notices on the relevant admin page
    $screen = get_current_screen();
    if ( 'edit-beforeafter' !== $screen->id ) {
        return;
    }

    $notice = get_transient( 'beforeafter_trash_notice' );

    if ( ! $notice ) {
        return;
    }
    
    if ( 'started' === $notice ) {
        echo '<div class="notice notice-info is-dismissible"><p>' . __( 'Started moving all Before & After posts to the trash. This is happening in the background.', 'beforeafter' ) . '</p></div>';
    } elseif ( 'completed' === $notice ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __( 'Successfully moved all Before & After posts to the trash.', 'beforeafter' ) . '</p></div>';
    }

    // Delete the transient so the notice is only shown once
    delete_transient( 'beforeafter_trash_notice' );
}
add_action( 'admin_notices', 'beforeafter_trash_admin_notices' );