<?php
/**
 * Template Name: Archive Leaderboard
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container py-5">
            <header class="page-header text-center mb-5">
                <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
                <?php the_archive_description( '<div class="archive-description lead text-muted">', '</div>' ); ?>
            </header>

            <?php if ( have_posts() ) : ?>
                <div class="row g-4 justify-content-center">
                    <?php while ( have_posts() ) : the_post(); 
                        $year = get_field('year');
                        // Se il campo anno è compilato usa quello, altrimenti il titolo del post
                        $display_text = $year ? $year : get_the_title();
                    ?>
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                <div class="card h-100 shadow-sm border-0 hover-lift text-center transition-base bg-light">
                                    <div class="card-body d-flex flex-column justify-content-center align-items-center py-5">
                                        <div class="display-1 fw-bold text-primary mb-0">
                                            <?php echo esc_html($display_text); ?>
                                        </div>
                                        <?php if ($year && get_the_title() != $year) : ?>
                                            <div class="text-muted mt-2"><?php the_title(); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="mt-5">
                    <?php
                    the_posts_pagination( array(
                        'mid_size'  => 2,
                        'prev_text' => __( 'Previous', 'bootscore' ),
                        'next_text' => __( 'Next', 'bootscore' ),
                    ) );
                    ?>
                </div>

            <?php else : ?>
                <div class="alert alert-info text-center">
                    <?php esc_html_e( 'No leaderboards found.', 'bootscore' ); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>

<?php get_footer(); ?>
