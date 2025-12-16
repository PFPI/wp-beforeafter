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

// Define plugin constants for path and URL
define('BEFOREAFTER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BEFOREAFTER_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once BEFOREAFTER_PLUGIN_PATH . 'includes/custom-post-types.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/custom-taxonomies.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/beforeafter-display.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/beforeafter-helpers.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/beforeafter-trash.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/enqueue-scripts.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/admin-columns.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/template-functions.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/natura-2000-sites.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/beforeafter-post-helpers.php';
require_once BEFOREAFTER_PLUGIN_PATH . 'includes/page-template-meta.php';


/**
 * Hide ALL media uploaded by 'sam' from the Media Library (List and Grid views).
 */
function beforeafter_hide_sam_media_strict( $query ) {
    // 1. Only run in the admin dashboard
    if ( ! is_admin() ) {
        return;
    }

    // 2. Identify if we are on a Media Library screen
    global $pagenow;
    
    // Check for List View (upload.php)
    $is_list_view = ( 'upload.php' === $pagenow && $query->is_main_query() );

    // Check for Grid View (AJAX action 'query-attachments')
    $is_grid_view = ( 
        defined( 'DOING_AJAX' ) && 
        DOING_AJAX && 
        isset( $_REQUEST['action'] ) && 
        'query-attachments' === $_REQUEST['action'] 
    );

    // If we aren't in the Media Library, stop here.
    if ( ! $is_list_view && ! $is_grid_view ) {
        return;
    }

    // 3. Get the user object for 'sam'
    $user = get_user_by( 'login', 'sam' );
    
    // If 'sam' doesn't exist, do nothing.
    if ( ! $user ) {
        return;
    }

    // 4. Force the query to exclude 'sam'
    $query->set( 'author__not_in', array( $user->ID ) );
}
add_action( 'pre_get_posts', 'beforeafter_hide_sam_media_strict' );