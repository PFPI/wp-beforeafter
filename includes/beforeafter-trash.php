<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Adds a 'Move All to Trash' button on supported post type list screens.
 */
function beforeafter_add_bulk_trash_button()
{
    $screen = get_current_screen();
    $supported_post_types = array(
        'natura_2000_site' => __('Natura 2000 Sites', 'beforeafter'),
        'beforeafter'      => __('Before & After posts', 'beforeafter'),
    );

    if ( ! isset( $supported_post_types[ $screen->post_type ] ) ) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }
    
    $post_type_label = $supported_post_types[ $screen->post_type ];
    $nonce = wp_create_nonce('beforeafter_bulk_trash_' . $screen->post_type . '_nonce');
    $trash_url = add_query_arg(array(
        'action' => 'beforeafter_bulk_trash',
        'post_type' => $screen->post_type,
        '_wpnonce' => $nonce,
    ), admin_url('admin.php'));
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.bulkactions').append(
                '<a href="<?php echo esc_url($trash_url); ?>" id="doaction_trash_all_<?php echo esc_js($screen->post_type); ?>" class="button action" style="margin-left: 5px;"><?php _e('Move All to Trash', 'beforeafter'); ?></a>'
            );

            $('#doaction_trash_all_<?php echo esc_js($screen->post_type); ?>').on('click', function (e) {
                if (!confirm('<?php printf(esc_js(__('Are you sure you want to move all %s to the trash? This will be done in the background and may take some time.', 'beforeafter')), esc_js($post_type_label)); ?>')) {
                    e.preventDefault();
                } else {
                    $(this).text('<?php _e('Trashing...', 'beforeafter'); ?>').prop('disabled', true);
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer-edit.php', 'beforeafter_add_bulk_trash_button');

/**
 * Handles the logic for initiating the bulk trashing of posts.
 */
function beforeafter_handle_bulk_trash()
{
    if ( ! isset( $_REQUEST['post_type'] ) ) {
        wp_die( __( 'Missing post type.', 'beforeafter' ) );
    }
    $post_type = sanitize_key( $_REQUEST['post_type'] );

    check_admin_referer('beforeafter_bulk_trash_' . $post_type . '_nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'beforeafter'));
    }

    $all_posts = get_posts(array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'any', // Get all statuses except trash
    ));

    if (!empty($all_posts)) {
        set_transient($post_type . '_bulk_trash_ids', $all_posts, HOUR_IN_SECONDS);
        wp_schedule_single_event(time(), 'beforeafter_process_trash_batch_hook', array($post_type));
        set_transient($post_type . '_trash_notice', 'started', 60);
    }

    wp_redirect(admin_url('edit.php?post_type=' . $post_type));
    exit;
}
add_action('admin_action_beforeafter_bulk_trash', 'beforeafter_handle_bulk_trash');

/**
 * Processes a single batch of posts to be trashed.
 * This is triggered by a WP-Cron event.
 */
function beforeafter_process_trash_batch($post_type)
{
    $post_ids = get_transient($post_type . '_bulk_trash_ids');

    if (empty($post_ids)) {
        set_transient($post_type . '_trash_notice', 'completed', 60);
        return;
    }

    $batch_size = 50;
    $ids_to_trash = array_splice($post_ids, 0, $batch_size);

    foreach ($ids_to_trash as $post_id) {
        wp_trash_post($post_id);
    }

    if (!empty($post_ids)) {
        set_transient($post_type . '_bulk_trash_ids', $post_ids, HOUR_IN_SECONDS);
        wp_schedule_single_event(time() + 2, 'beforeafter_process_trash_batch_hook', array($post_type));
    } else {
        delete_transient($post_type . '_bulk_trash_ids');
        set_transient($post_type . '_trash_notice', 'completed', 60);
    }
}
add_action('beforeafter_process_trash_batch_hook', 'beforeafter_process_trash_batch', 10, 1);

/**
 * Displays admin notices for the bulk trash process.
 */
function beforeafter_trash_admin_notices()
{
    $screen = get_current_screen();
    $supported_post_types = array(
        'natura_2000_site' => __('Natura 2000 Sites', 'beforeafter'),
        'beforeafter'      => __('Before & After posts', 'beforeafter'),
    );

    if ( ! isset( $supported_post_types[ $screen->post_type ] ) ) {
        return;
    }

    $post_type = $screen->post_type;
    $post_type_label = $supported_post_types[$post_type];
    $notice = get_transient($post_type . '_trash_notice');

    if (!$notice) {
        return;
    }

    if ('started' === $notice) {
        $message = sprintf(__('Started moving all %s to the trash. This will happen in the background.', 'beforeafter'), $post_type_label);
        echo '<div class="notice notice-info is-dismissible"><p>' . esc_html($message) . '</p></div>';
    } elseif ('completed' === $notice) {
        $message = sprintf(__('Successfully moved all %s to the trash.', 'beforeafter'), $post_type_label);
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    delete_transient($post_type . '_trash_notice');
}
add_action('admin_notices', 'beforeafter_trash_admin_notices');

/**
 * Customize the messages when a post is moved to the Trash for our CPTs.
 *
 * @param array $messages Post updated messages.
 * @return array Filtered post updated messages.
 */
function beforeafter_customize_trash_messages( $messages )
{
    global $post;

    // A post object might not be available.
    if ( ! $post ) {
        return $messages;
    }

    $post_type = get_post_type( $post );

    if ( 'beforeafter' === $post_type ) {
        if ( ! isset( $messages['beforeafter'] ) ) {
            $messages['beforeafter'] = $messages['post'];
        }
        $messages['beforeafter'][10] = __( 'Before/After item moved to the Trash.', 'beforeafter' );
    } elseif ( 'n2k_trash' === $post_type ) {
        if ( ! isset( $messages['n2k_trash'] ) ) {
            $messages['n2k_trash'] = $messages['post'];
        }
        $messages['n2k_trash'][10] = __( 'N2K trash item moved to the Trash.', 'beforeafter' );
    }

    return $messages;
}
add_filter( 'post_updated_messages', 'beforeafter_customize_trash_messages' );