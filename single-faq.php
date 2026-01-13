<?php
/**
 * Template for displaying single faq
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <?php while ( have_posts() ) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-5">
                                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                            </header>

                            <div class="entry-content mb-5">
                                <?php the_content(); ?>
                            </div>

                            <footer class="entry-footer">
                                <div class="text-center mb-5">
                                    <a href="<?php echo esc_url( get_post_type_archive_link( 'faq' ) ); ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-arrow-left me-2"></i><?php esc_html_e( 'Torna alle FAQ', 'bootscore' ); ?>
                                    </a>
                                </div>

                                <?php
                                // Related FAQs
                                $args = array(
                                    'post_type'      => 'faq',
                                    'posts_per_page' => 3,
                                    'post__not_in'   => array( get_the_ID() ),
                                    'orderby'        => 'rand',
                                );
                                $related_query = new WP_Query( $args );

                                if ( $related_query->have_posts() ) :
                                    ?>
                                    <div class="related-faqs pt-5 border-top">
                                        <h3 class="text-center mb-4"><?php esc_html_e( 'FAQ Correlate', 'bootscore' ); ?></h3>
                                        <div class="row g-4">
                                            <?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
                                                <div class="col-md-4">
                                                    <div class="card h-100 shadow-sm border-0 bg-light hover-lift">
                                                        <div class="card-body d-flex align-items-center justify-content-center text-center p-4">
                                                            <h5 class="card-title h6 mb-0">
                                                                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-body stretched-link">
                                                                    <?php the_title(); ?>
                                                                </a>
                                                            </h5>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                    <?php
                                    wp_reset_postdata();
                                endif;
                                ?>
                            </footer>

                        </article>

                    <?php endwhile; ?>

                </div>
            </div>
        </div>

    </main>
</div>

<?php get_footer(); ?>