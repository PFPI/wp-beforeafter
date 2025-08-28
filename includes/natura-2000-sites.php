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
                }
            }
            fclose($handle);

            echo '<div class="updated"><p>Import complete!</p></div>';
        }
    }
}
add_action('admin_init', 'natura_2000_handle_import');

