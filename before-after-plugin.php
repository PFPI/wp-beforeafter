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


// Include the CPT functions
require_once plugin_dir_path(__FILE__) . 'includes/custom-post-types.php';

// Include the Taxonomy functions
require_once plugin_dir_path(__FILE__) . 'includes/custom-taxonomies.php';

// Include the Archive template function
require_once plugin_dir_path(__FILE__) . 'includes/beforeafter-display.php';

// Include helpers for beforeafter post type
require_once plugin_dir_path(__FILE__) . 'includes/beforeafter-helpers.php';

// Add beforeafter and n2k trash functions
require_once plugin_dir_path(__FILE__) . 'includes/beforeafter-trash.php';

/**
 * Enqueue custom before/after slider CSS and JS for single 'beforeafter' posts.
 * This function must stay in this file for the sliders to work.
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
 * This function needs to be in this file to ensure it works correctly with the slider.
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
 * Frontend template for 'beforeafter' custom post type
 * This function will be called if a single 'beforeafter' post is viewed.
 * This one must stay for some reason.
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

                $post_title = $site_id;

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
                    echo '<div class="notice notice-success is-dismissible"><p>' . implode('<br>', $import_successes) . '</p></div>';
                });
            }
            if (!empty($import_warnings)) {
                add_action('admin_notices', function () use ($import_warnings) {
                    echo '<div class="notice notice-warning is-dismissible"><p>' . implode('<br>', $import_warnings) . '</p></div>';
                });
            }

        } else {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Could not open the CSV file.', 'beforeafter') . '</p></div>';
            });
        }
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Please upload a CSV file.', 'beforeafter') . '</p></div>';
        });
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
