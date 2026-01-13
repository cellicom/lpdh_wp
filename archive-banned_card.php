<?php
/**
 * Template for displaying banned cards archive
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4 text-center">
            <h1 class="page-title text-danger">
                <i class="fas fa-ban me-2"></i><?php post_type_archive_title(); ?>
            </h1>
            <?php the_archive_description( '<div class="archive-description lead text-muted">', '</div>' ); ?>
        </header>

        <div class="container pb-5">
            <?php if ( have_posts() ) : ?>
                <div class="banned-cards-list mx-auto" style="max-width: 900px;">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part('template-parts/card', 'banned-card'); ?>
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
                    <?php esc_html_e( 'No banned cards found.', 'bootscore' ); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
/* Pagination Styling */
.navigation.pagination .nav-links {
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.navigation.pagination .page-numbers {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 50%;
    background-color: #fff;
    border: 1px solid #dee2e6;
    color: #dc3545;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    line-height: 1;
}

.navigation.pagination .page-numbers:hover {
    background-color: #f8d7da;
    color: #b02a37;
    border-color: #f5c2c7;
}

.navigation.pagination .page-numbers.current {
    background-color: #dc3545;
    color: #fff;
    border-color: #dc3545;
    pointer-events: none;
}

.navigation.pagination .page-numbers.dots {
    border: none;
    background: transparent;
    color: #6c757d;
}

.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>

<?php get_footer(); ?>
