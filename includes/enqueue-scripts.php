<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue custom before/after slider CSS and JS for single 'beforeafter' posts.
 */
function beforeafter_enqueue_custom_slider_scripts()
{
    if (is_singular('beforeafter')) {
        // Enqueue custom slider CSS
        wp_enqueue_style(
            'beforeafter-slider-css',
            BEFOREAFTER_PLUGIN_URL . 'before-after-slider.css',
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
            BEFOREAFTER_PLUGIN_URL . 'before-after-slider.js',
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
            BEFOREAFTER_PLUGIN_URL . 'before-after-map.js',
            array('leaflet-js'),
            '1.0.0',
            true
        );
/* 
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
        )); */

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
            BEFOREAFTER_PLUGIN_URL . 'before-after-graph.js',
            array('chart-js'), // Make it dependent on Chart.js
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'beforeafter_enqueue_custom_slider_scripts');

