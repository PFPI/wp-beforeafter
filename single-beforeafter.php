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
    while ( have_posts() ) :
        the_post();

        // Get custom field values
        $before_image_id = get_post_meta( get_the_ID(), '_beforeafter_before_image_id', true );
        $after_image_id = get_post_meta( get_the_ID(), '_beforeafter_after_image_id', true );
        
        $before_image_url = $before_image_id ? wp_get_attachment_url( $before_image_id ) : '';
        $after_image_url = $after_image_id ? wp_get_attachment_url( $after_image_id ) : '';

        $before_image_label = get_post_meta( get_the_ID(), '_beforeafter_before_date', true );
        $after_image_label = get_post_meta( get_the_ID(), '_beforeafter_after_date', true );
        
        // Get all the project details
        $sitecode = get_post_meta( get_the_ID(), '_beforeafter_sitecode', true );
        $latitude = get_post_meta( get_the_ID(), '_beforeafter_latitude', true );
        $longitude = get_post_meta( get_the_ID(), '_beforeafter_longitude', true );
        $zoom_level = get_post_meta( get_the_ID(), '_beforeafter_zoom_level', true );
        $disturbed_date = get_post_meta( get_the_ID(), '_beforeafter_disturbed_date', true );
        $conclusion = get_post_meta( get_the_ID(), '_beforeafter_conclusion', true );
        
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="container">
                <div class="my-10 lg:my-12">
                    <header class="entry-header">
                        <?php the_title( '<h1 class="entry-title h1 text-moss font-display capitalize">', '</h1>' ); ?>
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
                                if ( ! empty( $latitude ) && ! empty( $longitude ) && ! empty( $sitecode ) && ! empty( $disturbed_date ) && ! empty( $conclusion ) ) {

                                    // Build the formatted string
                                    $point_information = sprintf(
                                        '<strong>POINT INFORMATION:</strong> This point at %s, %s in %s was potentially disturbed in %s. After evaluation of satellite imagery, it was rated as %s.',
                                        esc_html( $latitude ),
                                        esc_html( $longitude ),
                                        esc_html( $sitecode ),
                                        esc_html( $disturbed_date ),
                                        esc_html( $conclusion )
                                    );

                                    // Display the final text inside a paragraph
                                    echo '<p>' . $point_information . '</p>';
                                }
                            ?>
                            </div>

    
                            <?php if ( ! empty( $latitude ) && ! empty( $longitude ) ) : ?>
                                <div id="beforeafter-map" style="height: 400px; margin-top: 20px;"></div>
                            <?php endif; ?>
    

                                                        <?php if ( $before_image_url && $after_image_url ) : ?>
                                <figure id="custom-slider-<?php echo get_the_ID(); ?>" class="cd-image-container my-8">
                                    <img src="<?php echo esc_url( $before_image_url ); ?>" alt="<?php echo esc_attr( get_the_title() . ' Before' ); ?>">
                                    <?php if ( ! empty( $before_image_label ) ) : ?>
                                        <span class="cd-image-label" data-type="original"><?php echo esc_html( $before_image_label ); ?></span>
                                    <?php endif; ?>
    
                                    <div class="cd-resize-img"> <img src="<?php echo esc_url( $after_image_url ); ?>" alt="<?php echo esc_attr( get_the_title() . ' After' ); ?>">
                                        <?php if ( ! empty( $after_image_label ) ) : ?>
                                            <span class="cd-image-label" data-type="modified"><?php echo esc_html( $after_image_label ); ?></span>
                                        <?php endif; ?>
                                    </div>
    
                                    <span class="cd-handle"></span>
                                </figure>
                            <?php else : ?>
                                <p><?php _e( 'Please upload both "Before" and "After" images.', 'beforeafter' ); ?></p>
                            <?php endif; ?>
                        </div>
    
                        <div class="col-span-12 lg:col-span-4">
                            <?php // --- Project Details Box --- ?>
                            <?php if ( ! empty( $sitecode ) || ! empty( $latitude ) || ! empty( $longitude ) || ! empty( $zoom_level ) ) : ?>
                            <div class="beforeafter-data bg-beige rounded-lg p-5 border border-neutral-300">
                                <h3 class="h3 text-moss font-display capitalize pb-5"><?php _e( 'Analysis Details', 'beforeafter' ); ?></h3>
                                <ul class="list-none p-0 m-0">
                                    <?php if ( ! empty( $sitecode ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'Site Code:', 'beforeafter' ); ?></strong> <?php echo esc_html( $sitecode ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $latitude ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'Latitude:', 'beforeafter' ); ?></strong> <?php echo esc_html( $latitude ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $longitude ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'Longitude:', 'beforeafter' ); ?></strong> <?php echo esc_html( $longitude ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $before_image_label ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'Before Date:', 'beforeafter' ); ?></strong> <?php echo esc_html( $before_image_label ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $disturbed_date ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'Disturbed Year:', 'beforeafter' ); ?></strong> <?php echo esc_html( $disturbed_date ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $after_image_label ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'After Date:', 'beforeafter' ); ?></strong> <?php echo esc_html( $after_image_label ); ?></li>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $conclusion ) ) : ?>
                                        <li class="mb-2"><strong><?php _e( 'Conclusion:', 'beforeafter' ); ?></strong> <?php echo esc_html( $conclusion ); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </article><?php endwhile; // End of the loop. ?>

    </main></div><?php
get_footer();
