<?php


/**
 * Adds a 'Move All to Trash' button on the Natura 2000 Sites admin list page for administrators.
 */
function natura_2000_add_bulk_trash_button()
{
    $screen = get_current_screen();
    if ('edit-natura_2000_site' !== $screen->id) {
        return;
    }

    if (!current_user_can('manage_options')) {
        return;
    }

    $nonce = wp_create_nonce('beforeafter_bulk_trash_natura_sites_nonce');
    $trash_url = add_query_arg(array(
        'action' => 'beforeafter_bulk_trash_natura_sites',
        '_wpnonce' => $nonce,
    ), admin_url('admin.php'));
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            $('.bulkactions').append(
                '<a href="<?php echo esc_url($trash_url); ?>" id="doaction_trash_all" class="button action" style="margin-left: 5px;"><?php _e('Move All to Trash', 'beforeafter'); ?></a>'
            );

            $('#doaction_trash_all').on('click', function (e) {
                if (!confirm('<?php _e('Are you sure you want to move all Natura 2000 Sites to the trash? This will be done in the background and may take some time.', 'beforeafter'); ?>')) {
                    e.preventDefault();
                } else {
                    $(this).text('<?php _e('Trashing...', 'beforeafter'); ?>').prop('disabled', true);
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer-edit.php', 'natura_2000_add_bulk_trash_button');

/**
 * Handles the logic for initiating the bulk trashing of 'natura_2000_site' posts.
 */
function natura_2000_handle_bulk_trash()
{
    check_admin_referer('beforeafter_bulk_trash_natura_sites_nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'beforeafter'));
    }

    $all_posts = get_posts(array(
        'post_type' => 'natura_2000_site',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'any', // Get all statuses except trash
    ));

    if (!empty($all_posts)) {
        // Store the list of post IDs in a transient to be processed in the background
        set_transient('natura_2000_bulk_trash_ids', $all_posts, HOUR_IN_SECONDS);

        // Schedule an immediate, one-off event to start the processing
        wp_schedule_single_event(time(), 'natura_2000_process_trash_batch_hook');

        // Set a transient to show the "started" notice
        set_transient('natura_2000_trash_notice', 'started', 60);
    }

    // Redirect back to the admin page
    wp_redirect(admin_url('edit.php?post_type=natura_2000_site'));
    exit;
}
add_action('admin_action_beforeafter_bulk_trash_natura_sites', 'natura_2000_handle_bulk_trash');

/**
 * Processes a single batch of 'natura_2000_site' posts to be trashed.
 * This is triggered by a WP-Cron event.
 */
function natura_2000_process_trash_batch()
{
    $post_ids = get_transient('natura_2000_bulk_trash_ids');

    if (empty($post_ids)) {
        // Job is done, set a notice and finish
        set_transient('natura_2000_trash_notice', 'completed', 60);
        return;
    }

    // Process a batch of 50 posts at a time to prevent timeouts
    $batch_size = 50;
    $ids_to_trash = array_splice($post_ids, 0, $batch_size);

    foreach ($ids_to_trash as $post_id) {
        wp_trash_post($post_id);
    }

    if (!empty($post_ids)) {
        // If there are more posts, update the transient and reschedule the next batch
        set_transient('natura_2000_bulk_trash_ids', $post_ids, HOUR_IN_SECONDS);
        wp_schedule_single_event(time() + 2, 'natura_2000_process_trash_batch_hook');
    } else {
        // If this was the last batch, delete the transient and set the completed notice
        delete_transient('natura_2000_bulk_trash_ids');
        set_transient('natura_2000_trash_notice', 'completed', 60);
    }
}
add_action('natura_2000_process_trash_batch_hook', 'natura_2000_process_trash_batch');

/**
 * Displays admin notices for the bulk trash process.
 */
function natura_2000_trash_admin_notices()
{
    $notice = get_transient('natura_2000_trash_notice');

    if (!$notice) {
        return;
    }

    if ('started' === $notice) {
        echo '<div class="notice notice-info is-dismissible"><p>' . __('Started moving all Natura 2000 Sites to the trash. This will happen in the background.', 'beforeafter') . '</p></div>';
    } elseif ('completed' === $notice) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Successfully moved all Natura 2000 Sites to the trash.', 'beforeafter') . '</p></div>';
    }

    // Delete the transient so the notice doesn't show again
    delete_transient('natura_2000_trash_notice');
}
add_action('admin_notices', 'natura_2000_trash_admin_notices');


/**
 * Adds a 'Move All to Trash' button on the Before & After admin list page for administrators.
 */
function beforeafter_add_bulk_trash_button()
{
    $screen = get_current_screen();
    // Check if we are on the correct admin page
    if ('edit-beforeafter' !== $screen->id) {
        return;
    }

    // Only show for users who can manage options
    if (!current_user_can('manage_options')) {
        return;
    }

    // Create a secure URL for the action
    $nonce = wp_create_nonce('beforeafter_bulk_trash_all_nonce');
    $trash_url = add_query_arg(array(
        'action' => 'beforeafter_bulk_trash_all',
        '_wpnonce' => $nonce,
    ), admin_url('admin.php'));
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            // Add the button next to the other bulk action controls
            $('.bulkactions').append(
                '<a href="<?php echo esc_url($trash_url); ?>" id="doaction_trash_all_beforeafter" class="button action" style="margin-left: 5px;"><?php _e('Move All to Trash', 'beforeafter'); ?></a>'
            );

            // Add a confirmation dialog
            $('#doaction_trash_all_beforeafter').on('click', function (e) {
                if (!confirm('<?php _e('Are you sure you want to move all Before & After posts to the trash? This will be done in the background and may take some time.', 'beforeafter'); ?>')) {
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
 * Handles the logic for initiating the bulk trashing of 'beforeafter' posts.
 */
function beforeafter_handle_bulk_trash()
{
    check_admin_referer('beforeafter_bulk_trash_all_nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to perform this action.', 'beforeafter'));
    }

    $all_posts = get_posts(array(
        'post_type' => 'beforeafter',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'any',
    ));

    if (!empty($all_posts)) {
        // Store post IDs in a transient for background processing
        set_transient('beforeafter_bulk_trash_ids', $all_posts, HOUR_IN_SECONDS);

        // Schedule a one-off event to start the process immediately
        wp_schedule_single_event(time(), 'beforeafter_process_trash_batch_hook');

        // Set a transient to show a "started" notice
        set_transient('beforeafter_trash_notice', 'started', 60);
    }

    // Redirect back to the admin page
    wp_redirect(admin_url('edit.php?post_type=beforeafter'));
    exit;
}
add_action('admin_action_beforeafter_bulk_trash_all', 'beforeafter_handle_bulk_trash');

/**
 * Processes a single batch of 'beforeafter' posts to be trashed via WP-Cron.
 */
function beforeafter_process_trash_batch()
{
    $post_ids = get_transient('beforeafter_bulk_trash_ids');

    if (empty($post_ids)) {
        set_transient('beforeafter_trash_notice', 'completed', 60);
        return;
    }

    // Process in batches of 50 to avoid timeouts
    $batch_size = 50;
    $ids_to_trash = array_splice($post_ids, 0, $batch_size);

    foreach ($ids_to_trash as $post_id) {
        wp_trash_post($post_id);
    }

    if (!empty($post_ids)) {
        // Reschedule for the next batch
        set_transient('beforeafter_bulk_trash_ids', $post_ids, HOUR_IN_SECONDS);
        wp_schedule_single_event(time() + 2, 'beforeafter_process_trash_batch_hook');
    } else {
        // Clean up when done
        delete_transient('beforeafter_bulk_trash_ids');
        set_transient('beforeafter_trash_notice', 'completed', 60);
    }
}
add_action('beforeafter_process_trash_batch_hook', 'beforeafter_process_trash_batch');

/**
 * Displays admin notices for the bulk trash process.
 */
function beforeafter_trash_admin_notices()
{
    // Only show notices on the relevant admin page
    $screen = get_current_screen();
    if ('edit-beforeafter' !== $screen->id) {
        return;
    }

    $notice = get_transient('beforeafter_trash_notice');

    if (!$notice) {
        return;
    }

    if ('started' === $notice) {
        echo '<div class="notice notice-info is-dismissible"><p>' . __('Started moving all Before & After posts to the trash. This is happening in the background.', 'beforeafter') . '</p></div>';
    } elseif ('completed' === $notice) {
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Successfully moved all Before & After posts to the trash.', 'beforeafter') . '</p></div>';
    }

    // Delete the transient so the notice is only shown once
    delete_transient('beforeafter_trash_notice');
}
add_action('admin_notices', 'beforeafter_trash_admin_notices');