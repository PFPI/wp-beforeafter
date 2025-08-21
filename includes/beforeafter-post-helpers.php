<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

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
