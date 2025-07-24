<?php
/*
Plugin Name: Before & After Showcase
Plugin URI:  https://example.com/before-after-plugin
Description: Registers a custom post type for Before & After images with location data.
Version:     1.0
Author:      Your Name
Author URI:  https://example.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: beforeafter
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the custom post type 'beforeafter'
 */
function beforeafter_register_post_type() {
    $labels = array(
        'name'                  => _x( 'Before & Afters', 'Post Type General Name', 'beforeafter' ),
        'singular_name'         => _x( 'Before & After', 'Post Type Singular Name', 'beforeafter' ),
        'menu_name'             => __( 'Before & Afters', 'beforeafter' ),
        'name_admin_bar'        => __( 'Before & After', 'beforeafter' ),
        'archives'              => __( 'Before & After Archives', 'beforeafter' ),
        'attributes'            => __( 'Before & After Attributes', 'beforeafter' ),
        'parent_item_colon'     => __( 'Parent Before & After:', 'beforeafter' ),
        'all_items'             => __( 'All Before & Afters', 'beforeafter' ),
        'add_new_item'          => __( 'Add New Before & After', 'beforeafter' ),
        'add_new'               => __( 'Add New', 'beforeafter' ),
        'new_item'              => __( 'New Before & After', 'beforeafter' ),
        'edit_item'             => __( 'Edit Before & After', 'beforeafter' ),
        'update_item'           => __( 'Update Before & After', 'beforeafter' ),
        'view_item'             => __( 'View Before & After', 'beforeafter' ),
        'view_items'            => __( 'View Before & Afters', 'beforeafter' ),
        'search_items'          => __( 'Search Before & After', 'beforeafter' ),
        'not_found'             => __( 'Not found', 'beforeafter' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'beforeafter' ),
        'featured_image'        => __( 'Featured Image', 'beforeafter' ),
        'set_featured_image'    => __( 'Set featured image', 'beforeafter' ),
        'remove_featured_image' => __( 'Remove featured image', 'beforeafter' ),
        'use_featured_image'    => __( 'Use as featured image', 'beforeafter' ),
        'insert_into_item'      => __( 'Insert into Before & After', 'beforeafter' ),
        'uploaded_to_this_item' => __( 'Uploaded to this Before & After', 'beforeafter' ),
        'items_list'            => __( 'Before & Afters list', 'beforeafter' ),
        'items_list_navigation' => __( 'Before & Afters list navigation', 'beforeafter' ),
        'filter_items_list'     => __( 'Filter Before & Afters list', 'beforeafter' ),
    );
    $args = array(
        'label'                 => __( 'Before & After', 'beforeafter' ),
        'description'           => __( 'Custom post type for Before & After images', 'beforeafter' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-images-alt2', // You can choose a different icon
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true, // Enable for Gutenberg editor and REST API
    );
    register_post_type( 'beforeafter', $args );
}
add_action( 'init', 'beforeafter_register_post_type', 0 );

/**
 * Link 'beforeafter' custom post type to 'location' taxonomy.
 */
function beforeafter_add_location_taxonomy_support() {
    register_taxonomy_for_object_type( 'location', 'beforeafter' );
}
add_action( 'init', 'beforeafter_add_location_taxonomy_support' );

/**
 * Include custom archive template from plugin directory.
 */
function beforeafter_archive_template( $template ) {
    if ( is_post_type_archive( 'beforeafter' ) ) {
        $new_template = plugin_dir_path( __FILE__ ) . 'archive-beforeafter.php';
        if ( file_exists( $new_template ) ) {
            return $new_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'beforeafter_archive_template' );

/**
 * Enqueue custom before/after slider CSS and JS for single 'beforeafter' posts.
 */
function beforeafter_enqueue_custom_slider_scripts() {
    if ( is_singular( 'beforeafter' ) ) {
        // Enqueue custom slider CSS
        wp_enqueue_style(
            'beforeafter-slider-css',
            plugin_dir_url( __FILE__ ) . 'before-after-slider.css',
            array(),
            '1.0.0'
        );

        // Enqueue custom slider JS in the footer
        wp_enqueue_script(
            'beforeafter-slider-js',
            plugin_dir_url( __FILE__ ) . 'before-after-slider.js',
            array( 'jquery' ), // Depends on jQuery for simplicity, can be made vanilla JS if preferred
            '1.0.0',
            true // Load in footer
        );
    }
}
add_action( 'wp_enqueue_scripts', 'beforeafter_enqueue_custom_slider_scripts' );

/**
 * Filter image attributes to prevent lazy loading on custom slider images.
 * This is crucial to ensure images load immediately for the slider.
 */
function beforeafter_filter_custom_slider_image_attributes( $attr, $attachment, $size ) {
    // Only apply this filter if we are on a single 'beforeafter' post
    if ( is_singular( 'beforeafter' ) ) {
        // Check if the image is one of our before/after images
        $post_id = get_the_ID();
        $before_image_id = get_post_meta( $post_id, '_beforeafter_before_image_id', true );
        $after_image_id = get_post_meta( $post_id, '_beforeafter_after_image_id', true );

        if ( $attachment->ID == $before_image_id || $attachment->ID == $after_image_id ) {
            // Add 'skip-lazy' class to prevent lazy loading plugins from processing it
            if ( isset( $attr['class'] ) ) {
                $attr['class'] .= ' skip-lazy';
            } else {
                $attr['class'] = 'skip-lazy';
            }
            // Remove native lazy loading attribute
            $attr['loading'] = 'eager';
            // Remove any data- attributes that lazy loading plugins might use
            unset( $attr['data-src'] );
            unset( $attr['data-srcset'] );
            unset( $attr['data-sizes'] );
            // Remove lazyload classes if they were added
            $attr['class'] = str_replace( 'lazyloaded', '', $attr['class'] );
            $attr['class'] = str_replace( 'lazyloading', '', $attr['class'] );
        }
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'beforeafter_filter_custom_slider_image_attributes', 10, 3 );


/**
 * Add custom meta boxes for Before & After images and data
 */
function beforeafter_add_meta_boxes() {
    add_meta_box(
        'beforeafter_images',
        __( 'Before & After Images', 'beforeafter' ),
        'beforeafter_images_callback',
        'beforeafter',
        'normal',
        'high'
    );

    add_meta_box(
        'beforeafter_dates', // New meta box for dates
        __( 'Before & After Dates', 'beforeafter' ),
        'beforeafter_dates_callback',
        'beforeafter',
        'normal',
        'high'
    );

    add_meta_box(
        'beforeafter_location_data',
        __( 'Location Data', 'beforeafter' ),
        'beforeafter_location_data_callback',
        'beforeafter',
        'normal',
        'high'
    );

    add_meta_box(
        'beforeafter_sitecode',
        __( 'Sitecode', 'beforeafter' ),
        'beforeafter_sitecode_callback',
        'beforeafter',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'beforeafter_add_meta_boxes' );

/**
 * Callback for the Images meta box
 */
function beforeafter_images_callback( $post ) {
    wp_nonce_field( basename( __FILE__ ), 'beforeafter_nonce' );

    $before_image_id = get_post_meta( $post->ID, '_beforeafter_before_image_id', true );
    $after_image_id = get_post_meta( $post->ID, '_beforeafter_after_image_id', true );

    $before_image_url = $before_image_id ? wp_get_attachment_url( $before_image_id ) : '';
    $after_image_url = $after_image_id ? wp_get_attachment_url( $after_image_id ) : '';
    ?>
    <div class="beforeafter-meta-row">
        <p>
            <label for="beforeafter_before_image"><?php _e( 'Before Image:', 'beforeafter' ); ?></label><br>
            <input type="hidden" name="beforeafter_before_image_id" id="beforeafter_before_image_id" value="<?php echo esc_attr( $before_image_id ); ?>" />
            <img id="beforeafter_before_image_preview" src="<?php echo esc_url( $before_image_url ); ?>" style="max-width:200px; height:auto; <?php echo empty($before_image_url) ? 'display:none;' : ''; ?>" /><br>
            <button type="button" class="button beforeafter_upload_image_button" data-field="before_image"><?php _e( 'Select Before Image', 'beforeafter' ); ?></button>
            <button type="button" class="button beforeafter_remove_image_button" data-field="before_image" style="<?php echo empty($before_image_url) ? 'display:none;' : ''; ?>"><?php _e( 'Remove Before Image', 'beforeafter' ); ?></button>
        </p>
    </div>

    <div class="beforeafter-meta-row">
        <p>
            <label for="beforeafter_after_image"><?php _e( 'After Image:', 'beforeafter' ); ?></label><br>
            <input type="hidden" name="beforeafter_after_image_id" id="beforeafter_after_image_id" value="<?php echo esc_attr( $after_image_id ); ?>" />
            <img id="beforeafter_after_image_preview" src="<?php echo esc_url( $after_image_url ); ?>" style="max-width:200px; height:auto; <?php echo empty($after_image_url) ? 'display:none;' : ''; ?>" /><br>
            <button type="button" class="button beforeafter_upload_image_button" data-field="after_image"><?php _e( 'Select After Image', 'beforeafter' ); ?></button>
            <button type="button" class="button beforeafter_remove_image_button" data-field="after_image" style="<?php echo empty($after_image_url) ? 'display:none;' : ''; ?>"><?php _e( 'Remove After Image', 'beforeafter' ); ?></button>
        </p>
    </div>
    <?php
}

/**
 * Callback for the Before & After Dates meta box
 */
function beforeafter_dates_callback( $post ) {
    $before_date = get_post_meta( $post->ID, '_beforeafter_before_date', true );
    $after_date = get_post_meta( $post->ID, '_beforeafter_after_date', true );
    ?>
    <p>
        <label for="beforeafter_before_date"><?php _e( 'Before Date (Text):', 'beforeafter' ); ?></label>
        <input type="text" name="beforeafter_before_date" id="beforeafter_before_date" value="<?php echo esc_attr( $before_date ); ?>" class="large-text" />
        <small><?php _e( 'e.g., 2010, Spring 2015, January 2020', 'beforeafter' ); ?></small>
    </p>
    <p>
        <label for="beforeafter_after_date"><?php _e( 'After Date (Text):', 'beforeafter' ); ?></label>
        <input type="text" name="beforeafter_after_date" id="beforeafter_after_date" value="<?php echo esc_attr( $after_date ); ?>" class="large-text" />
        <small><?php _e( 'e.g., 2020, Fall 2022, December 2023', 'beforeafter' ); ?></small>
    </p>
    <?php
}


/**
 * Callback for the Location Data meta box
 */
function beforeafter_location_data_callback( $post ) {
    $latitude = get_post_meta( $post->ID, '_beforeafter_latitude', true );
    $longitude = get_post_meta( $post->ID, '_beforeafter_longitude', true );
    $zoom_level = get_post_meta( $post->ID, '_beforeafter_zoom_level', true );
    ?>
    <p>
        <label for="beforeafter_latitude"><?php _e( 'Latitude:', 'beforeafter' ); ?></label>
        <input type="number" step="any" name="beforeafter_latitude" id="beforeafter_latitude" value="<?php echo esc_attr( $latitude ); ?>" />
    </p>
    <p>
        <label for="beforeafter_longitude"><?php _e( 'Longitude:', 'beforeafter' ); ?></label>
        <input type="number" step="any" name="beforeafter_longitude" id="beforeafter_longitude" value="<?php echo esc_attr( $longitude ); ?>" />
    </p>
    <p>
        <label for="beforeafter_zoom_level"><?php _e( 'Zoom Level:', 'beforeafter' ); ?></label>
        <input type="number" name="beforeafter_zoom_level" id="beforeafter_zoom_level" value="<?php echo esc_attr( $zoom_level ); ?>" min="0" max="21" />
    </p>
    <?php
}

/**
 * Callback for the Sitecode meta box
 */
function beforeafter_sitecode_callback( $post ) {
    $sitecode = get_post_meta( $post->ID, '_beforeafter_sitecode', true );
    ?>
    <p>
        <label for="beforeafter_sitecode"><?php _e( 'Sitecode:', 'beforeafter' ); ?></label>
        <input type="text" name="beforeafter_sitecode" id="beforeafter_sitecode" value="<?php echo esc_attr( $sitecode ); ?>" pattern="[a-zA-Z0-9]+" title="Alphanumeric characters only" />
        <small><?php _e( 'Alphanumeric characters only.', 'beforeafter' ); ?></small>
    </p>
    <?php
}

/**
 * Save custom meta data
 */
function beforeafter_save_meta_data( $post_id ) {
    // Check if our nonce is set.
    if ( ! isset( $_POST['beforeafter_nonce'] ) ) {
        return $post_id;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['beforeafter_nonce'], basename( __FILE__ ) ) ) {
        return $post_id;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return $post_id;
    }

    // Check the user's permissions.
    if ( 'beforeafter' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
    } else {
        return $post_id;
    }

    // Save Before Image ID
    if ( isset( $_POST['beforeafter_before_image_id'] ) ) {
        update_post_meta( $post_id, '_beforeafter_before_image_id', sanitize_text_field( $_POST['beforeafter_before_image_id'] ) );
    } else {
        delete_post_meta( $post_id, '_beforeafter_before_image_id' );
    }

    // Save After Image ID
    if ( isset( $_POST['beforeafter_after_image_id'] ) ) {
        update_post_meta( $post_id, '_beforeafter_after_image_id', sanitize_text_field( $_POST['beforeafter_after_image_id'] ) );
    } else {
        delete_post_meta( $post_id, '_beforeafter_after_image_id' );
    }

    // Save Before Date
    if ( isset( $_POST['beforeafter_before_date'] ) ) {
        update_post_meta( $post_id, '_beforeafter_before_date', sanitize_text_field( $_POST['beforeafter_before_date'] ) );
    } else {
        delete_post_meta( $post_id, '_beforeafter_before_date' );
    }

    // Save After Date
    if ( isset( $_POST['beforeafter_after_date'] ) ) {
        update_post_meta( $post_id, '_beforeafter_after_date', sanitize_text_field( $_POST['beforeafter_after_date'] ) );
    } else {
        delete_post_meta( $post_id, '_beforeafter_after_date' );
    }

    // Save Latitude
    if ( isset( $_POST['beforeafter_latitude'] ) ) {
        update_post_meta( $post_id, '_beforeafter_latitude', sanitize_text_field( $_POST['beforeafter_latitude'] ) );
    }

    // Save Longitude
    if ( isset( $_POST['beforeafter_longitude'] ) ) {
        update_post_meta( $post_id, '_beforeafter_longitude', sanitize_text_field( $_POST['beforeafter_longitude'] ) );
    }

    // Save Zoom Level
    if ( isset( $_POST['beforeafter_zoom_level'] ) ) {
        update_post_meta( $post_id, '_beforeafter_zoom_level', sanitize_text_field( $_POST['beforeafter_zoom_level'] ) );
    }

    // Save Sitecode
    if ( isset( $_POST['beforeafter_sitecode'] ) ) {
        update_post_meta( $post_id, '_beforeafter_sitecode', sanitize_text_field( $_POST['beforeafter_sitecode'] ) );
    }
}
add_action( 'save_post', 'beforeafter_save_meta_data' );

/**
 * Enqueue scripts for media uploader and custom meta box logic
 */
function beforeafter_admin_scripts( $hook ) {
    $screen = get_current_screen(); // Get the current screen object

    // Check if we are on the post edit screen or new post screen for 'beforeafter' CPT
    // This is a more reliable way to ensure scripts load only for our custom post type edit pages.
    if ( ( 'post.php' == $hook || 'post-new.php' == $hook ) && 'beforeafter' === $screen->post_type ) {
        // Enqueue WordPress media uploader scripts
        wp_enqueue_media();

        // Enqueue custom script for image selection
        wp_enqueue_script(
            'beforeafter-admin-script',
            plugin_dir_url( __FILE__ ) . 'before-after-admin.js',
            array( 'jquery' ),
            '1.0',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'beforeafter_admin_scripts' );

/**
 * Frontend template for 'beforeafter' custom post type
 * This function will be called if a single 'beforeafter' post is viewed.
 * You can customize the output here.
 */
function beforeafter_single_template( $template ) {
    if ( is_singular( 'beforeafter' ) ) {
        $new_template = plugin_dir_path( __FILE__ ) . 'single-beforeafter.php';
        if ( file_exists( $new_template ) ) {
            return $new_template;
        }
    }
    return $template;
}
add_filter( 'single_template', 'beforeafter_single_template' );

/**
 * Add custom columns to the 'beforeafter' post list
 */
function beforeafter_set_custom_columns( $columns ) {
    $columns['before_image'] = __( 'Before Image', 'beforeafter' );
    $columns['after_image'] = __( 'After Image', 'beforeafter' );
    $columns['sitecode'] = __( 'Sitecode', 'beforeafter' );
    return $columns;
}
add_filter( 'manage_beforeafter_posts_columns', 'beforeafter_set_custom_columns' );

/**
 * Display content for custom columns
 */
function beforeafter_custom_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'before_image' :
            $image_id = get_post_meta( $post_id, '_beforeafter_before_image_id', true );
            if ( $image_id ) {
                echo wp_get_attachment_image( $image_id, array( 50, 50 ) );
            } else {
                echo '—';
            }
            break;
        case 'after_image' :
            $image_id = get_post_meta( $post_id, '_beforeafter_after_image_id', true );
            if ( $image_id ) {
                echo wp_get_attachment_image( $image_id, array( 50, 50 ) );
            } else {
                echo '—';
            }
            break;
        case 'sitecode' :
            echo esc_html( get_post_meta( $post_id, '_beforeafter_sitecode', true ) );
            break;
    }
}
add_action( 'manage_beforeafter_posts_custom_column', 'beforeafter_custom_column_content', 10, 2 );

/**
 * Make custom columns sortable (optional)
 */
function beforeafter_sortable_columns( $columns ) {
    $columns['sitecode'] = 'sitecode'; // 'sitecode' is the meta key
    return $columns;
}
add_filter( 'manage_edit-beforeafter_sortable_columns', 'beforeafter_sortable_columns' );

/**
 * Handle custom column sorting query (optional)
 */
function beforeafter_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    if ( 'sitecode' === $query->get( 'orderby' ) ) {
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'meta_key', '_beforeafter_sitecode' );
        $query->set( 'meta_type', 'CHAR' ); // Or 'NUMERIC' if it were numbers
    }
}
add_action( 'pre_get_posts', 'beforeafter_orderby' );

