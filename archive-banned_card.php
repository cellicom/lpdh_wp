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
        </header>

        <div class="container pb-5">
            <form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'banned_card' ) ); ?>" class="mb-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="input-group">
                            <input type="text" name="s" class="form-control" placeholder="<?php esc_attr_e( 'Search banned cards...', 'bootscore' ); ?>" value="<?php echo get_search_query(); ?>">
                            <input type="hidden" name="post_type" value="banned_card">
                            <button class="btn btn-danger" type="submit">
                                <i class="fas fa-search"></i> <?php esc_html_e( 'Search', 'bootscore' ); ?>
                            </button>
                            <?php if ( get_search_query() ) : ?>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'banned_card' ) ); ?>" class="btn btn-outline-secondary" title="<?php esc_attr_e( 'Clear', 'bootscore' ); ?>"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>

            <?php if ( have_posts() ) : ?>
                <div class="banned-cards-list mx-auto" style="max-width: 900px;">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part('template-parts/card', 'banned-card'); ?>
                    <?php endwhile; ?>
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

.card-text-ellipsis {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

<?php get_footer(); ?>
