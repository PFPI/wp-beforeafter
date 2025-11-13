<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter image attributes to prevent lazy loading on custom slider images.
 *
 * @param array  $attr       Attributes for the image markup.
 * @param object $attachment Image attachment post.
 * @return array Filtered attributes.
 */
function beforeafter_filter_image_attributes($attr, $attachment)
{
    // Check if the image is one of our custom slider images
    if (isset($attr['class']) && strpos($attr['class'], 'beforeafter-slider-image') !== false) {
        // Remove the 'loading' attribute to prevent lazy loading
        unset($attr['loading']);
        // Add a 'data-skip-lazy' attribute if using a lazy-loading plugin that supports it
        $attr['data-skip-lazy'] = 'true';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'beforeafter_filter_image_attributes', 10, 2);

/**
 * Load a custom single template for the 'beforeafter' post type.
 *
 * @param string $template The path of the template to include.
 * @return string The path of the template to include.
 */
function beforeafter_single_template($template)
{
    if (is_singular('beforeafter')) {
        $new_template = BEFOREAFTER_PLUGIN_PATH . 'single-beforeafter.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('single_template', 'beforeafter_single_template');

/**
 * Sets a default featured image for 'beforeafter' posts if one isn't set.
 *
 * @param string $html          The post thumbnail HTML.
 * @param int    $post_id       The post ID.
 * @param int    $post_thumbnail_id The post thumbnail ID.
 * @param string $size          The post thumbnail size.
 * @param array  $attr          Query string of attributes.
 * @return string The post thumbnail HTML.
 */
function beforeafter_set_default_featured_image($html, $post_id, $post_thumbnail_id, $size, $attr)
{
    if (get_post_type($post_id) === 'beforeafter') {
        // If the post thumbnail HTML is empty, it means no featured image is set.
        if (empty($html)) {
            // Construct the full URL to the default SVG image in the plugin's assets folder.
            $default_svg_url = BEFOREAFTER_PLUGIN_URL . 'assets/pattern.svg';

            // Create the HTML for the default image.
            $html = '<img src="' . esc_url($default_svg_url) . '" alt="' . esc_attr(get_the_title($post_id)) . '" class="wp-post-image" />';
        }
    }
    return $html;
}
add_filter('post_thumbnail_html', 'beforeafter_set_default_featured_image', 10, 5);


/**
 * Add plugin-based page templates to the "Template" dropdown.
 */
function beforeafter_register_page_templates($templates) {
    // Add our new template
    $templates['page-template-beforeafter-library.php'] = __('Before & After Library', 'beforeafter');
    return $templates;
}
add_filter('theme_page_templates', 'beforeafter_register_page_templates');

/**
 * Load the plugin-based page template file.
 */
function beforeafter_load_page_template($template) {
    if ( is_page() ) {
        $meta_template = get_post_meta( get_the_ID(), '_wp_page_template', true );

        if ( 'page-template-beforeafter-library.php' === $meta_template ) {
            $new_template = BEFOREAFTER_PLUGIN_PATH . 'page-template-beforeafter-library.php';
            if ( file_exists( $new_template ) ) {
                return $new_template;
            }
        }
    }
    return $template;
}
add_filter('template_include', 'beforeafter_load_page_template');