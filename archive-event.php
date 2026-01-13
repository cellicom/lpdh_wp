<?php
/**
 * Template for displaying event archive
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
            <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
        </header>

        <div class="container pb-5">
            <?php if ( have_posts() ) : ?>
                <div class="event-archive-grid">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
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
                <div class="alert alert-info">
                    <?php esc_html_e( 'No events found.', 'bootscore' ); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
.event-archive-grid {
    display: grid;
    grid-template-columns: 1fr; /* Mobile: 1 card per riga */
    gap: 20px;
}

.event-card-inner {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
    position: relative;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.event-card-inner:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    transform: translateY(-5px);
}

.event-thumbnail {
    height: 160px;
    overflow: hidden;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.event-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    padding: 20px;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.placeholder-image img {
    object-fit: contain;
    max-height: 100px;
    width: auto;
}

.event-title {
    font-weight: 700;
    line-height: 1.3;
    min-height: 2.6em; /* Circa 2 righe */
}

.event-divider {
    margin: 10px 0 15px;
    opacity: 0.1;
}

/* Tablet: 2 card per riga */
@media (min-width: 768px) {
    .event-archive-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop: 4 card per riga */
@media (min-width: 1200px) {
    .event-archive-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>

<?php get_footer(); ?>