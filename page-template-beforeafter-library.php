<?php
/**
 * Template Name: Before & After Library
 *
 * This is the template for the standalone "Before & After" photo library page.
 * This version is integrated with FacetWP for filtering.
 */

// --- ADD THIS BLOCK ---
$post_id = get_the_ID();
$before_image_id = get_post_meta($post_id, '_lib_before_image_id', true);
$after_image_id = get_post_meta($post_id, '_lib_after_image_id', true);

$before_image_url = $before_image_id ? wp_get_attachment_url($before_image_id) : '';
$after_image_url = $after_image_id ? wp_get_attachment_url($after_image_id) : '';

$before_date = get_post_meta($post_id, '_lib_before_date', true);
$after_date = get_post_meta($post_id, '_lib_after_date', true);
$splash_text = get_post_meta($post_id, '_lib_splash_text', true);
$slider_caption = get_post_meta($post_id, '_lib_slider_caption', true);
// --- END OF BLOCK ---

get_header(); ?>

<div class="page-header">
    <div class="bg-beige pt-36 pb-8 lg:pt-44 lg:pb-10">
        <div class="container">
            <div class="max-w-4xl">
                <header class="page-title">
                    <?php the_title('<h1 class="page-title h1 text-moss font-display capitalize">', '</h1>'); ?>
                </header><!-- .entry-header -->
                <?php if (get_field('intro', 12)) {
                    echo '<p class="mt-3 lg:text-2xl">' . esc_html(get_field('intro', get_the_id())) . '</p>';
                } ?>
            </div>
        </div>
    </div>
    <div class="flexible-content">
        <div class="pt-8 lg:pt-14">

            <div class="text-image">
                <div class="container">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-5 lg:mb-10">
                        <div class="relative"> <!-- Set parent to relative -->
                            <?php if ($before_image_url && $after_image_url): ?>
                                <figure class="cd-image-container my-8">
                                    <img src="<?php echo esc_url($after_image_url); ?>"
                                        alt="<?php _e('After image of forest disturbance', 'beforeafter'); ?>">
                                    <?php if ($after_date): ?>
                                        <span class="cd-image-label"
                                            data-type="original"><?php echo esc_html($after_date); ?></span>
                                    <?php endif; ?>

                                    <div class="cd-resize-img">
                                        <img src="<?php echo esc_url($before_image_url); ?>"
                                            alt="<?php _e('Before image of forest', 'beforeafter'); ?>">
                                        <?php if ($before_date): ?>
                                            <span class="cd-image-label"
                                                data-type="modified"><?php echo esc_html($before_date); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <span class="cd-handle"></span>
                                </figure>
                            <?php else: ?>
                                <p><?php _e('Please select a "Before" and "After" image in the page editor.', 'beforeafter'); ?>
                                </p>
                            <?php endif; ?>
                            <?php // --- ADD THIS SNIPPET FOR THE CAPTION --- ?>
                            <?php if ($slider_caption): ?>
                                <p class="slider-caption text-sm text-gray-600 text-center -mt-4 mb-4">
                                    <?php echo esc_html($slider_caption); ?>
                                </p>
                            <?php endif; ?>
                            <?php // --- END OF CAPTION SNIPPET --- ?>
                        </div>
                        <div class="text">
                            <div
                                class="entry-content prose prose-studiolake-starter prose-headings:mt-1 prose-headings:mb-1 text-sm lg:text-base prose-lead:1">
                                <?php
                                // Display our new WYSIWYG splash text field
                                if (!empty($splash_text)) {
                                    echo apply_filters('the_content', $splash_text);
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php // --- NEW: All Sites Map Button --- ?>
            <div class="container text-center my-2">
                <button id="open-all-sites-map-modal"
                    class="button-primary"><?php _e('View All Sites on Map', 'beforeafter'); ?></button>
            </div>


            <?php // --- ADD THIS NEW SECTION FOR MAIN CONTENT --- ?>
            <?php
            // We loop here to get the main content from the WP editor
            while (have_posts()):
                the_post();
                ?>
                <?php if (get_the_content()):  // Only show this section if there is content ?>
                    <div class="main-content-area py-10 lg:py-16 bg-white"> <?php // You can change bg-white if needed ?>
                        <div class="container">
                            <div class="entry-content prose prose-studiolake-starter max-w-4xl mx-auto">
                                <?php the_content(); // This displays the main editor content ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php
            endwhile;
            // We don't need wp_reset_postdata() here because the FacetWP query below will handle it.
            ?>
            <?php // --- END OF NEW SECTION --- ?>

            <div id="primary" class="content-area pt-8 lg:pt-36">
                <main id="main" class="site-main" role="main">


                    <div class="container">

                        <div class="grid grid-cols-12 gap-2">

                            <div class="col-span-12 lg:col-span-3">
                                <h3 class="widget-title h4 text-moss mb-4"><?php _e('Filter By', 'pfpi'); ?></h3>
                                <?php
                                // This is where your filters will appear
                                echo facetwp_display('facet', 'location_filter_for_beforeafters');
                                ?>
                                <button class="button-primary mt-4"
                                    onclick="FWP.reset()"><?php _e('Reset', 'pfpi'); ?></button>
                            </div>



                            <div class="col-span-12 lg:col-span-8">
                                <h3>Results</h3>
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 my-10 lg:my-12 facetwp-template">
                                    <?php
                                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
                                    $args = array(
                                        'post_type' => 'beforeafter',
                                        'posts_per_page' => 9,
                                        'paged' => $paged,
                                        'facetwp' => true, // Tells FacetWP to use this query
                                    );
                                    $library_query = new WP_Query($args);

                                    if ($library_query->have_posts()):
                                        while ($library_query->have_posts()):
                                            $library_query->the_post();
                                            $disturbed_date = get_post_meta(get_the_ID(), '_beforeafter_disturbed_date', true);
                                            $locations = get_the_terms(get_the_ID(), 'location');
                                            $sitecode = get_post_meta(get_the_ID(), '_beforeafter_sitecode', true);
                                            $sitename = '';

                                            if (!empty($sitecode)) {
                                                $args_natura = array(
                                                    'post_type' => 'natura_2000_site',
                                                    'title' => $sitecode,
                                                    'posts_per_page' => 1,
                                                    'fields' => 'ids',
                                                );
                                                $natura_query = new WP_Query($args_natura);
                                                $most_disturbed_year = '';
                                                if ($natura_query->have_posts()) {
                                                    $natura_post_id = $natura_query->posts[0];
                                                    $sitename = get_post_meta($natura_post_id, '_sitename', true);
                                                    $most_disturbed_year = get_post_meta($natura_post_id, '_most_disturbed_year', true);
                                                }
                                            }
                                            ?>
                                            <article id="post-<?php the_ID(); ?>" <?php post_class('bg-beige rounded-lg shadow-xl overflow-hidden'); ?>>
                                                <?php if (has_post_thumbnail()): ?>
                                                    <div class="thumbnail relative">
                                                        <a href="<?php the_permalink(); ?>" class="block">
                                                            <div class="bg-lime w-full h-44 relative">
                                                                <?php echo get_the_post_thumbnail(get_the_ID(), 'horizontal', array('class' => 'absolute top-0 left-0 w-full h-full object-cover')); ?>
                                                            </div>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="p-4">
                                                    <h2 class="h3 text-moss font-display capitalize mb-2">
                                                        <a href="<?php the_permalink(); ?>"
                                                            class="hover:underline"><?php the_title(); ?></a>
                                                    </h2>
                                                    <?php if (!empty($sitename) || ($locations && !is_wp_error($locations))): ?>
                                                        <p class="text-sm text-gray-700 mb-2">
                                                            <strong><?php _e('Location:', 'beforeafter'); ?></strong>
                                                            <?php
                                                            $display_parts = [];
                                                            if (!empty($sitename))
                                                                $display_parts[] = esc_html($sitename);
                                                            if ($locations && !is_wp_error($locations)) {
                                                                foreach ($locations as $location)
                                                                    $display_parts[] = esc_html($location->name);
                                                            }
                                                            echo implode(', ', $display_parts);
                                                            ?>
                                                        </p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($disturbed_date)): ?>
                                                        <p class="text-sm text-gray-700 mb-4">
                                                            <strong><?php _e('Disturbed Year:', 'beforeafter'); ?></strong>
                                                            <?php echo esc_html($disturbed_date); ?>
                                                        </p>
                                                    <?php endif; ?>

                                                    <a href="<?php the_permalink(); ?>" class="button-primary">
                                                        <?php _e('View Project', 'beforeafter'); ?>
                                                    </a>
                                                </div>
                                            </article>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <p><?php _e('No Before & After posts found matching your criteria.', 'beforeafter'); ?>
                                        </p>
                                    <?php endif; ?>
                                </div><?php
                                // 4. FacetWP Pagination
                                echo facetwp_display('pager');
                                wp_reset_postdata(); // Restore original Post Data
                                ?>

                            </div>
                        </div>
                    </div>
                </main>
            </div>
            <?php // --- NEW: All Sites Map Modal --- ?>
            <div id="all-sites-map-modal" class="graph-modal">
                <div class="graph-modal-content" style="max-width: 90vw; width: 1200px;">
                    <span id="all-sites-map-modal-close" class="graph-modal-close">&times;</span>
                    <div id="all-sites-leaflet-map" style="height: 70vh; width: 100%;"></div>
                </div>
            </div>
            <?php
            get_footer();