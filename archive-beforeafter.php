<?php
/**
 * The template for displaying archive pages for the 'beforeafter' custom post type.
 *
 * @package WordPress
 * @subpackage BeforeAfterShowcase
 * @since 1.0
 */

get_header(); ?>

<div id="primary" class="content-area pt-28 lg:pt-36">
    <main id="main" class="site-main" role="main">

        <header class="page-header container">
            <h1 class="page-title h1 text-moss font-display capitalize">
                <?php post_type_archive_title( 'All ', 'beforeafter' ); ?>
            </h1>
        </header><!-- .page-header -->

        <div class="container">
            <?php if ( have_posts() ) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 my-10 lg:my-12">
                    <?php
                    /* Start the Loop */
                    while ( have_posts() ) :
                        the_post();

                        // Get 'location' terms
                        $locations = get_the_terms( get_the_ID(), 'location' );
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-beige rounded-lg shadow-xl overflow-hidden p-4' ); ?>>
                            <h2 class="h3 text-moss font-display capitalize mb-2">
                                Title: 
                                <?php the_title(); ?>
                            </h2>

                            <?php if ( $locations && ! is_wp_error( $locations ) ) : ?>
                                <p class="text-sm text-gray-700 mb-4">
                                    <strong><?php _e( 'Location:', 'beforeafter' ); ?></strong>
                                    <?php
                                    $location_names = array();
                                    foreach ( $locations as $location ) {
                                        $location_names[] = esc_html( $location->name );
                                    }
                                    echo implode( ', ', $location_names );
                                    ?>
                                </p>
                            <?php endif; ?>

                            <a href="<?php the_permalink(); ?>" class="button-primary">
                                <?php _e( 'View Project', 'beforeafter' ); ?>
                            </a>
                        </article><!-- #post-<?php the_ID(); ?> -->
                    <?php endwhile; ?>
                </div><!-- .grid -->

                <?php
                // Pagination
                the_posts_pagination( array(
                    'prev_text'          => __( 'Previous', 'beforeafter' ),
                    'next_text'          => __( 'Next', 'beforeafter' ),
                    'screen_reader_text' => __( 'Posts navigation', 'beforeafter' ),
                ) );
                ?>

            <?php else : ?>
                <p><?php _e( 'No Before & After posts found.', 'beforeafter' ); ?></p>
            <?php endif; ?>
        </div><!-- .container -->

    </main><!-- #main -->
</div><!-- #primary -->

<?php
get_footer();
