<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
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


function natura_2000_add_geojson_meta_box() {
    add_meta_box(
        'natura_2000_geojson',
        'GeoJSON/TXT File',
        'natura_2000_geojson_meta_box_callback',
        'natura_2000_site',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'natura_2000_add_geojson_meta_box');

function natura_2000_geojson_meta_box_callback($post) {
    wp_nonce_field('natura_2000_geojson_save', 'natura_2000_geojson_nonce');
    $file_id = get_post_meta($post->ID, '_geojson_file_id', true);
    $file_url = wp_get_attachment_url($file_id);
    ?>
    <p>
        <label for="geojson_file_id"><?php _e('Upload GeoJSON or TXT file:', 'beforeafter'); ?></label><br>
        <input type="hidden" name="geojson_file_id" id="geojson_file_id" value="<?php echo esc_attr($file_id); ?>" />
        <span id="geojson_file_name"><?php echo $file_url ? basename($file_url) : 'No file selected'; ?></span><br>
        <button type="button" class="button" id="upload_geojson_button"><?php _e('Select File', 'beforeafter'); ?></button>
        <button type="button" class="button" id="remove_geojson_button" style="<?php echo $file_id ? '' : 'display:none;'; ?>"><?php _e('Remove File', 'beforeafter'); ?></button>
    </p>
    <script>
    jQuery(document).ready(function($){
        $('#upload_geojson_button').click(function(e) {
            e.preventDefault();
            var uploader = wp.media({
                title: 'Select GeoJSON or TXT file',
                button: { text: 'Use this file' },
                multiple: false
            }).on('select', function() {
                var attachment = uploader.state().get('selection').first().toJSON();
                $('#geojson_file_id').val(attachment.id);
                $('#geojson_file_name').text(attachment.filename);
                $('#remove_geojson_button').show();
            }).open();
        });
        $('#remove_geojson_button').click(function(e) {
            e.preventDefault();
            $('#geojson_file_id').val('');
            $('#geojson_file_name').text('No file selected');
            $(this).hide();
        });
    });
    </script>
    <?php
}

// Meta Box Callback
function natura_2000_meta_box_callback($post)
{
    wp_nonce_field('natura_2000_save_meta_box_data', 'natura_2000_meta_box_nonce');

    $fields = array(
        'sitecode',
        'sitename',
        'raster_ymin', // Add this
        'raster_ymax', // Add this
        'raster_xmin', // Add this
        'raster_xmax', // Add this
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
        'most_disturbed_year',
        'most_disturbed_year_ha'
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
        'raster_ymin', // Add this
        'raster_ymax', // Add this
        'raster_xmin', // Add this
        'raster_xmax', // Add this
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
        'most_disturbed_year',
        'most_disturbed_year_ha'
    );

    foreach ($fields as $field) {
        if (isset($_POST[$field . '_field'])) {
            $data = sanitize_text_field($_POST[$field . '_field']);
            update_post_meta($post_id, '_' . $field, $data);
        }
    }

    if (isset($_POST['natura_2000_geojson_nonce']) && wp_verify_nonce($_POST['natura_2000_geojson_nonce'], 'natura_2000_geojson_save')) {
        if (isset($_POST['geojson_file_id'])) {
            update_post_meta($post_id, '_geojson_file_id', sanitize_text_field($_POST['geojson_file_id']));
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
                Ensure your CSV file is formatted correctly with the columns in the following order: <br />
                    "sitecode","countrycode","perclogged","site_ha","area_ha_2001","area_ha_2002","area_ha_2003",<br />
                    "area_ha_2004","area_ha_2005","area_ha_2006","area_ha_2007","area_ha_2008","area_ha_2009",<br />
                    "area_ha_2010","area_ha_2011","area_ha_2012","area_ha_2013","area_ha_2014","area_ha_2015",<br />
                    "area_ha_2016","area_ha_2017","area_ha_2018","area_ha_2019","area_ha_2020","area_ha_2021",<br />
                    "area_ha_2022","area_ha_2023","most_disturbed_year","most_disturbed_year_ha","sitename". <br />
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

        // --- ADDED: Include WordPress file handling functions ---
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload_dir = wp_upload_dir();
        $import_dir = $upload_dir['basedir'] . '/before-after-import/';

        // --- ADDED: For storing messages ---
        $import_successes = [];
        $import_warnings = [];


        // Process the CSV file
        $csv_file = $_FILES['csv_file']['tmp_name'];
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            $column_map = array_flip($header);
            $row_number = 1;


            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row_number++;
                $sitecode = isset($column_map['sitecode']) ? $data[$column_map['sitecode']] : '';

                if (empty($sitecode)) {
                    $import_warnings[] = "Row " . $row_number . ": Skipping row due to empty sitecode.";
                    continue; // Skip rows without a sitecode
                }

                // Create new post
                $new_post = array(
                    'post_title' => $sitecode,
                    'post_type' => 'natura_2000_site',
                    'post_status' => 'publish',
                );

                $post_id = wp_insert_post($new_post);


                if ($post_id) {
                    $import_successes[] = "Successfully imported post for sitecode: " . esc_html($sitecode);

                    // ... (rest of your meta data import logic)
                    $fields_to_import = array(
                        'sitecode',
                        'sitename',
                        'countrycode',
                        'perclogged',
                        'site_ha',
                        'most_disturbed_year',
                        'most_disturbed_year_ha'
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

                    // --- REVISED: GeoJSON/TXT file handling ---
                    $geojson_filename = $sitecode . '.json'; // Or .txt
                    $txt_filename = $sitecode . '.txt';

                    $geojson_filepath = $import_dir . $geojson_filename;
                    $txt_filepath = $import_dir . $txt_filename;
                    
                    $file_to_upload = null;

                    if (file_exists($geojson_filepath)) {
                        $file_to_upload = $geojson_filepath;
                    } elseif (file_exists($txt_filepath)) {
                        $file_to_upload = $txt_filepath;
                    }


                    if ($file_to_upload) {
                        // Create a temporary copy to avoid issues with file permissions
                        $temp_file = wp_tempnam(basename($file_to_upload));
                        if(copy($file_to_upload, $temp_file)) {
                            $file_array = [
                                'name'     => basename($file_to_upload),
                                'tmp_name' => $temp_file,
                                'type' => $mime_type,
                            ];

                            $attachment_id = media_handle_sideload($file_array, $post_id);

                            if (!is_wp_error($attachment_id)) {
                                update_post_meta($post_id, '_geojson_file_id', $attachment_id);
                                $import_successes[] = "Successfully attached " . basename($file_to_upload) . " to " . $sitecode;
                            } else {
                                @unlink($temp_file); // Clean up temp file
                                $import_warnings[] = "Row " . $row_number . ": Error attaching file for sitecode " . esc_html($sitecode) . ": " . $attachment_id->get_error_message();
                            }
                        } else {
                             $import_warnings[] = "Row " . $row_number . ": Could not create temporary file for " . esc_html(basename($file_to_upload));
                        }
                    } else {
                        $import_warnings[] = "Row " . $row_number . ": No matching .json or .txt file found for sitecode " . esc_html($sitecode);
                    }
                } else {
                     $import_warnings[] = "Row " . $row_number . ": Failed to create post for sitecode: " . esc_html($sitecode);
                }
            }
            fclose($handle);

            // --- ADDED: Display notices ---
            if (!empty($import_successes)) {
                add_action('admin_notices', function () use ($import_successes) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . implode('<br>', array_slice($import_successes, 0, 15)) . '...</p></div>';
                });
            }
            if (!empty($import_warnings)) {
                add_action('admin_notices', function () use ($import_warnings) {
                    echo '<div class="notice notice-warning is-dismissible"><p>' . implode('<br>', array_slice($import_warnings, 0, 15)) . '...</p></div>';
                });
            }


        } else {
             add_action('admin_notices', function () {
                echo '<div class="notice notice-error is-dismissible"><p>Could not open the CSV file.</p></div>';
            });
        }
    }
}
add_action('admin_init', 'natura_2000_handle_import');

// --- ADD THE FOLLOWING CODE TO THE END OF includes/natura-2000-sites.php ---

/**
 * Add submenu page for importing raster bounds.
 */
function natura_2000_bounds_import_submenu_page() {
    add_submenu_page(
        'edit.php?post_type=natura_2000_site',
        'Import Raster Bounds',
        'Import Raster Bounds',
        'manage_options',
        'natura-2000-bounds-import',
        'natura_2000_bounds_import_page_callback'
    );
}
add_action('admin_menu', 'natura_2000_bounds_import_submenu_page');

/**
 * Callback function for the bounds import page.
 */
function natura_2000_bounds_import_page_callback() {
    ?>
    <div class="wrap">
        <h1>Import Raster Bounds from CSV</h1>
        <p>This tool will update existing Natura 2000 Site posts with the corner coordinates from the raster bounds CSV.</p>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('natura_2000_bounds_import_nonce', 'natura_2000_bounds_import_nonce_field'); ?>
            <p>
                <label for="bounds_csv_file">Upload the `raster_bounds_... .csv` file:</label>
                <input type="file" id="bounds_csv_file" name="bounds_csv_file" accept=".csv">
            </p>
            <p>
                <input type="submit" name="submit_bounds_import" class="button button-primary" value="Start Bounds Import">
            </p>
        </form>
    </div>
    <?php
}

/**
 * Handle the raster bounds CSV import.
 */
function natura_2000_handle_bounds_import() {
    if (isset($_POST['submit_bounds_import']) && isset($_FILES['bounds_csv_file'])) {
        if (!isset($_POST['natura_2000_bounds_import_nonce_field']) || !wp_verify_nonce($_POST['natura_2000_bounds_import_nonce_field'], 'natura_2000_bounds_import_nonce')) {
            wp_die('Security check failed.');
        }

        if ($_FILES['bounds_csv_file']['error'] > 0) {
            wp_die('File upload error: ' . $_FILES['bounds_csv_file']['error']);
        }

        $csv_file = $_FILES['bounds_csv_file']['tmp_name'];
        if (($handle = fopen($csv_file, "r")) !== FALSE) {
            $header = fgetcsv($handle); // Read header
            $updated_count = 0;
            $not_found_count = 0;

            while (($data = fgetcsv($handle)) !== FALSE) {
                $sitecode = $data[0];
                $bounds = [
                    '_raster_ymin' => $data[1],
                    '_raster_ymax' => $data[2],
                    '_raster_xmin' => $data[3],
                    '_raster_xmax' => $data[4],
                ];

                // Find the post with this sitecode (title)
                $args = [
                    'post_type' => 'natura_2000_site',
                    'title' => $sitecode,
                    'posts_per_page' => 1,
                    'fields' => 'ids'
                ];
                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    $post_id = $query->posts[0];
                    foreach ($bounds as $key => $value) {
                        update_post_meta($post_id, $key, sanitize_text_field($value));
                    }
                    $updated_count++;
                } else {
                    $not_found_count++;
                }
            }
            fclose($handle);

            // Add an admin notice with the results
            add_action('admin_notices', function() use ($updated_count, $not_found_count) {
                $message = "Raster bounds import complete. Updated: {$updated_count} sites. Not Found: {$not_found_count} sites.";
                echo "<div class='notice notice-success is-dismissible'><p>{$message}</p></div>";
            });
        }
    }
}
add_action('admin_init', 'natura_2000_handle_bounds_import');
