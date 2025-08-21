<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

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

