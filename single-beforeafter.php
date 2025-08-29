<?php
/**
 * The template for displaying all single 'beforeafter' posts.
 * This file is adapted to use the CodyHouse slider structure.
 * @package WordPress
 * @subpackage BeforeAfterShowcase
 * @since 1.0
 */

get_header(); ?>

<div id="primary" class="content-area pt-28 lg:pt-36">
    <main id="main" class="site-main" role="main">

        <?php
        while (have_posts()):
            the_post();

            // Get custom field values
            $before_image_id = get_post_meta(get_the_ID(), '_beforeafter_before_image_id', true);
            $after_image_id = get_post_meta(get_the_ID(), '_beforeafter_after_image_id', true);

            $before_image_url = $before_image_id ? wp_get_attachment_url($before_image_id) : '';
            $after_image_url = $after_image_id ? wp_get_attachment_url($after_image_id) : '';

            $before_image_label = get_post_meta(get_the_ID(), '_beforeafter_before_date', true);
            $after_image_label = get_post_meta(get_the_ID(), '_beforeafter_after_date', true);

            // Get all the project details
            $sitecode = get_post_meta(get_the_ID(), '_beforeafter_sitecode', true);
            $sitename = get_post_meta(get_the_ID(), '_beforeafter_sitename', true);
            $latitude = get_post_meta(get_the_ID(), '_beforeafter_latitude', true);
            $longitude = get_post_meta(get_the_ID(), '_beforeafter_longitude', true);
            $zoom_level = get_post_meta(get_the_ID(), '_beforeafter_zoom_level', true);
            $disturbed_date = get_post_meta(get_the_ID(), '_beforeafter_disturbed_date', true);
            $conclusion = get_post_meta(get_the_ID(), '_beforeafter_conclusion', true);


            // --- NEW: Query for related Natura 2000 Site data ---
            $site_ha = '';
            $most_disturbed_year = '';
            $total_disturbed_area = 0;
            $graph_data_points = []; // Initialize array for graph data
        
            if (!empty($sitecode)) {
                $args = array(
                    'post_type' => 'natura_2000_site',
                    'title' => $sitecode,
                    'posts_per_page' => 1,
                    'fields' => 'ids',
                );

                $natura_query = new WP_Query($args);

                if ($natura_query->have_posts()) {
                    $natura_post_id = $natura_query->posts[0];
                    $site_ha = get_post_meta($natura_post_id, '_site_ha', true);
                    $sitename = get_post_meta($natura_post_id, '_sitename', true);
                    $most_disturbed_year = get_post_meta($natura_post_id, '_most_disturbed_year', true);
                    // --- Get GeoJSON from Natura 2000 Site ---
                    $geojson_file_id = get_post_meta($natura_post_id, '_geojson_file_id', true);
                    $geojson_file_url = $geojson_file_id ? wp_get_attachment_url($geojson_file_id) : '';

                    // Loop through years to build data for graph and calculate total
                    for ($year = 2001; $year <= 2023; $year++) {
                        $meta_key = '_area_ha_' . $year;
                        $yearly_area = get_post_meta($natura_post_id, $meta_key, true);
                        $yearly_area_float = is_numeric($yearly_area) ? (float) $yearly_area : 0;

                        $total_disturbed_area += $yearly_area_float;

                        // Add data point for the graph
                        $graph_data_points[] = array(
                            'x' => $year,
                            'y' => $yearly_area_float
                        );
                    }

                    // Pass the prepared data to our graph script
                    wp_localize_script('beforeafter-graph-js', 'beforeafter_graph_data', array(
                        'points' => $graph_data_points,
                        'sitename' => $sitename,
                    ));
                }
                wp_reset_postdata();
                // --- Pass all data to the map script ---
            wp_localize_script('beforeafter-map-js', 'beforeafter_map_data', array(
                'lat' => $latitude,
                'lng' => $longitude,
                'zoom' => $zoom_level,
                'geojson_url' => isset($geojson_file_url) ? $geojson_file_url : ''
            ));
            }
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="container">
                    <div class="my-10 lg:my-12">
                        <header class="entry-header">
                            <?php the_title('<h1 class="entry-title h1 text-moss font-display capitalize">', '</h1>'); ?>
                        </header>

                        <div class="grid grid-cols-12 gap-8 mt-4">
                            <div class="col-span-12 lg:col-span-8">
                                <div class="entry-content prose">
                                    <?php
                                    // These variables should already be defined at the top of this file from our previous steps.
                                    // We are just re-listing them here for clarity:
                                    // $latitude = get_post_meta( get_the_ID(), '_beforeafter_latitude', true );
                                    // $longitude = get_post_meta( get_the_ID(), '_beforeafter_longitude', true );
                                    // $sitecode = get_post_meta( get_the_ID(), '_beforeafter_sitecode', true );
                                    // $disturbed_date = get_post_meta( get_the_ID(), '_beforeafter_disturbed_date', true );
                                    // $conclusion = get_post_meta( get_the_ID(), '_beforeafter_conclusion', true );
                                
                                    // Check if we have the data before trying to display it.
                                    if (!empty($latitude) && !empty($longitude) && !empty($sitecode) && !empty($disturbed_date) && !empty($conclusion)) {

                                        // Build the formatted string
                                        $point_information = sprintf(
                                            '<strong>POINT INFORMATION:</strong> This point at %s, %s in %s (%s) was potentially disturbed in %s. After evaluation of satellite imagery, it was rated as %s.',
                                            esc_html(number_format($latitude, 4)),
                                            esc_html(number_format($longitude, 4)),
                                            esc_html($sitecode),
                                            esc_html($sitename),
                                            esc_html($disturbed_date),
                                            esc_html($conclusion)
                                        );

                                        // Display the final text inside a paragraph
                                        echo '<p>' . $point_information . '</p>';
                                    }
                                    ?>
                                </div>


                                <?php if (!empty($latitude) && !empty($longitude)): ?>
                                    <div id="beforeafter-map" style="height: 400px; margin-top: 20px;"></div>
                                <?php endif; ?>


                                <?php if ($before_image_url && $after_image_url): ?>
                                    <figure id="custom-slider-<?php echo get_the_ID(); ?>" class="cd-image-container my-8">
                                        <img src="<?php echo esc_url($before_image_url); ?>"
                                            alt="<?php echo esc_attr(get_the_title() . ' Before'); ?>">
                                        <?php if (!empty($before_image_label)): ?>
                                            <span class="cd-image-label"
                                                data-type="original"><?php echo esc_html($before_image_label); ?></span>
                                        <?php endif; ?>

                                        <div class="cd-resize-img"> <img src="<?php echo esc_url($after_image_url); ?>"
                                                alt="<?php echo esc_attr(get_the_title() . ' After'); ?>">
                                            <?php if (!empty($after_image_label)): ?>
                                                <span class="cd-image-label"
                                                    data-type="modified"><?php echo esc_html($after_image_label); ?></span>
                                            <?php endif; ?>
                                        </div>

                                        <span class="cd-handle"></span>
                                    </figure>
                                <?php else: ?>
                                    <p><?php _e('Please upload both "Before" and "After" images.', 'beforeafter'); ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="col-span-12 lg:col-span-4">

                                <?php // --- Analysis Details Box --- ?>
                                <div class="beforeafter-data bg-beige rounded-lg p-5 border border-neutral-300 mb-5">
                                    <h3 class="h3 text-moss font-display capitalize pb-5">
                                        <?php _e('Analysis Details', 'beforeafter'); ?>
                                    </h3>
                                    <ul class="list-none p-0 m-0">
                                        <?php if (!empty($latitude)): ?>
                                            <li class="mb-2"><strong><?php _e('Latitude:', 'beforeafter'); ?></strong>
                                                <?php echo number_format((float) $latitude, 4); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($longitude)): ?>
                                            <li class="mb-2"><strong><?php _e('Longitude:', 'beforeafter'); ?></strong>
                                                <?php echo number_format((float) $longitude, 4); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($before_image_label)): ?>
                                            <li class="mb-2"><strong><?php _e('Before Date:', 'beforeafter'); ?></strong>
                                                <?php echo esc_html($before_image_label); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($disturbed_date)): ?>
                                            <li class="mb-2"><strong><?php _e('Disturbed Year:', 'beforeafter'); ?></strong>
                                                <?php echo esc_html($disturbed_date); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($after_image_label)): ?>
                                            <li class="mb-2"><strong><?php _e('After Date:', 'beforeafter'); ?></strong>
                                                <?php echo esc_html($after_image_label); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($conclusion)): ?>
                                            <li class="mb-2"><strong><?php _e('Conclusion:', 'beforeafter'); ?></strong>
                                                <?php echo esc_html($conclusion); ?></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php // --- Site Details Box --- ?>
                                <div class="site-details bg-beige rounded-lg p-5 border border-neutral-300 mb-5">
                                    <h3 class="h3 text-moss font-display capitalize pb-5">
                                        <?php _e('Site Details', 'beforeafter'); ?>
                                    </h3>
                                    <ul class="list-none p-0 m-0">
                                        <?php if (!empty($sitecode)): ?>
                                            <?php
                                            // Construct the search URL for the sitecode, pointing to the resources page.
                                            $search_url = site_url('/resources/?_search=' . urlencode($sitecode));
                                            ?>
                                            <li class="mb-2"><strong><?php _e('Site Code:', 'beforeafter'); ?></strong> <a
                                                    href="<?php echo esc_url($search_url); ?>"
                                                    class="underline hover:no-underline"><?php echo esc_html($sitecode); ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <?php if (!empty($sitename)): ?>
                                            <li class="mb-2"><strong><?php _e('Sitename:', 'beforeafter'); ?></strong>
                                                <?php echo esc_html($sitename); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($site_ha)): ?>
                                            <li class="mb-2"><strong><?php _e('Site Area (ha):', 'beforeafter'); ?></strong>
                                                <?php echo number_format((float) $site_ha, 0); ?></li>
                                        <?php endif; ?>
                                        <?php if (!empty($most_disturbed_year)): ?>
                                            <li class="mb-2">
                                                <strong><?php _e('Most Disturbed Year:', 'beforeafter'); ?></strong>
                                                <?php echo esc_html($most_disturbed_year); ?>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($total_disturbed_area > 0): ?>
                                            <li class="mb-2">
                                                <strong><?php _e('Total Disturbed Area (ha):', 'beforeafter'); ?></strong>
                                                <?php echo number_format($total_disturbed_area, 2); ?>
                                            </li>
                                            <li class="mb-2"><i>The disturbed area value is for the entire Natura 2000 site, 
                                                including areas not pictured by aerial photography, in the years 2001-2023. Not all
                                                    disturbance is from logging.</i></li>
                                        <?php endif; ?>
                                        <?php // --- NEW: Graph Modal Button --- ?>
                                        <?php if ($total_disturbed_area > 0): ?>
                                            <button id="open-graph-modal"
                                                class="button-primary mt-4 w-full"><?php _e('View Disturbance Graph', 'beforeafter'); ?></button>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <?php beforeafter_display_related_by_sitecode(); ?>

        <?php endwhile; // End of the loop. ?>

    </main>
</div>

<?php // --- NEW: Graph Modal and Styles --- ?>
<style>
    .graph-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.6);
        align-items: center;
        justify-content: center;
    }
    .graph-modal.is-visible {
        display: flex;
    }
    .graph-modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 90%;
        max-width: 800px;
        border-radius: 8px;
        position: relative;
    }
    .graph-modal-close {
        color: #aaa;
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .graph-modal-close:hover,
    .graph-modal-close:focus {
        color: black;
    }
</style>

<div id="disturbance-graph-modal" class="graph-modal">
    <div class="graph-modal-content">
        <span class="graph-modal-close">&times;</span>
        <canvas id="disturbance-chart"></canvas>
    </div>
</div>

</main></div>
<?php
get_footer();
