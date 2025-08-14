<?php

/**
 * This file contains: 
 *  - beforeafter_register_post_type(): Function to register the 'beforeafter' custom post type.
 */

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