<?php
/**
 * Template for displaying deck archive
 * 
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <div class="container pb-5">

        <?php if ( have_posts() ) : ?>

            <div class="row g-4">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part('template-parts/card', 'deck'); ?>
                <?php endwhile; ?>
            </div>

            <?php
            // Pagination
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __( 'Previous', 'bootscore' ),
                'next_text' => __( 'Next', 'bootscore' ),
            ) );
            ?>

        <?php else : ?>

            <section class="no-results not-found">
                <header class="page-header">
                    <h1 class="page-title"><?php esc_html_e( 'No decks found', 'bootscore' ); ?></h1>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'It seems there are no decks to display.', 'bootscore' ); ?></p>
                </div>
            </section>

        <?php endif; ?>

        </div>

    </main>
</div>

<style>
.transition-transform {
    transition: transform 0.3s ease;
}
.deck-card:hover .transition-transform {
    transform: scale(1.05);
}

.deck-thumbnail img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.deck-content {
    padding: 20px;
}

.entry-title {
    margin-top: 0;
    margin-bottom: 10px;
}

.entry-title a {
    color: #495057;
    text-decoration: none;
}

.entry-title a:hover {
    color: #007bff;
}

.entry-meta {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 15px;
}

.entry-meta span {
    margin-right: 15px;
}

.entry-meta i {
    margin-right: 5px;
}

.entry-summary {
    margin-bottom: 15px;
    line-height: 1.6;
}

.decklist-preview {
    margin-bottom: 15px;
}

.entry-footer {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: 15px;
}

.entry-footer .btn {
    text-decoration: none;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    margin-bottom: 10px;
    color: #495057;
}

.archive-description {
    color: #6c757d;
    font-size: 1.1rem;
}
</style>

<?php get_footer(); ?>
