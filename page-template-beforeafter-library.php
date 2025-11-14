<?php
/**
 * Template Name: Before & After Library
 *
 * This is the template for the standalone "Before & After" photo library page.
 * This version is integrated with FacetWP for filtering.
 */

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
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10 lg:mb-20">
                        <div class="relative"> <!-- Set parent to relative -->
                            <div class="lg:absolute inset-0 w-full h-full rounded-lg bg-lime min-h-52">
                                <!-- Make image full width and height -->
                                <img width="2560" height="1440"
                                    src="http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1.jpg"
                                    class="rounded-lg object-cover w-full h-full" alt="" decoding="async"
                                    fetchpriority="high"
                                    srcset="http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1.jpg 2560w, http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1-300x169.jpg 300w, http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1-1024x576.jpg 1024w, http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1-768x432.jpg 768w, http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1-1536x864.jpg 1536w, http://forestdefenders-org.local/wp-content/uploads/2021/11/DJI_0184-1-scaled-1-2048x1152.jpg 2048w"
                                    sizes="(max-width: 2560px) 100vw, 2560px">
                            </div>
                        </div>
                        <div class="text">
                            <div
                                class="entry-content prose prose-studiolake-starter prose-headings:mt-1 prose-headings:mb-1 text-sm lg:text-base prose-lead:1">
                                <?php
                                // This displays the content you write in the WordPress editor for this page
                                while (have_posts()):
                                    the_post();
                                    the_content();
                                endwhile;
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


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
                    <button class="button-primary mt-4" onclick="FWP.reset()"><?php _e('Reset', 'pfpi'); ?></button>
                </div>



                <div class="col-span-12 lg:col-span-8">
                    <h3>Results</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 my-10 lg:my-12 facetwp-template">
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
                            <p><?php _e('No Before & After posts found matching your criteria.', 'beforeafter'); ?></p>
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
</div><?php
get_footer();