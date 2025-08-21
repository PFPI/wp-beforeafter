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