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

                        $sitecode = get_post_meta(get_the_ID(), '_beforeafter_sitecode', true);
                        $sitename = ''; // Default to empty

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
                                // Fetch the 'sitename' from the custom meta field, NOT the title.
                                $sitename = get_post_meta($natura_post_id, '_sitename', true);
                            }
                            wp_reset_postdata();
                        }

                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class( 'bg-beige rounded-lg shadow-xl overflow-hidden' ); ?>>

    <?php // --- ADDED BLOCK --- ?>
    <?php if ( has_post_thumbnail() ) : ?>
        <div class="thumbnail relative">
            <a href="<?php the_permalink(); ?>" class="block">
                <div class="bg-lime w-full h-44 relative">
                    <?php echo get_the_post_thumbnail( get_the_ID(), 'horizontal', array( 'class' => 'absolute top-0 left-0 w-full h-full object-cover' ) ); ?>
                </div>
            </a>
        </div>
    <?php endif; ?>

    <div class="p-4"> <?php // Added a wrapping div for padding ?>
        <h2 class="h3 text-moss font-display capitalize mb-2">
           <?php the_title(); ?>
        </h2>

        <?php if ( ! empty( $sitename ) || ( $locations && ! is_wp_error( $locations ) ) ) : ?>
    <p class="text-sm text-gray-700 mb-4">
        <strong><?php _e( 'Location:', 'beforeafter' ); ?></strong>
        <?php
        $display_parts = [];
        if ( ! empty( $sitename ) ) {
            $display_parts[] = esc_html( $sitename );
        }
        if ( $locations && ! is_wp_error( $locations ) ) {
            foreach ( $locations as $location ) {
                $display_parts[] = esc_html( $location->name );
            }
        }
        echo implode( ', ', $display_parts );
        ?>
    </p>
<?php endif; ?>

        <a href="<?php the_permalink(); ?>" class="button-primary">
            <?php _e( 'View Project', 'beforeafter' ); ?>
        </a>
    </div> <?php // Closing the wrapping div ?>
</article>
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
