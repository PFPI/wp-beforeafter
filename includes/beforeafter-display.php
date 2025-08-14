<?php

/**
 * Include custom archive template from plugin directory.
 */
function beforeafter_archive_template($template)
{
    if (is_post_type_archive('beforeafter')) {
        $new_template = plugin_dir_path(__FILE__) . 'archive-beforeafter.php';
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'beforeafter_archive_template');

