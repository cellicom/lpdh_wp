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
            <form method="get" action="<?php echo esc_url(get_post_type_archive_link('banned_card')); ?>"
                class="mb-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="input-group">
                            <input type="text" name="s" class="form-control"
                                placeholder="<?php esc_attr_e('Search banned cards...', 'bootscore'); ?>"
                                value="<?php echo get_search_query(); ?>">
                            <input type="hidden" name="post_type" value="banned_card">
                            <button class="btn btn-danger" type="submit">
                                <i class="fas fa-search"></i> <?php esc_html_e('Search', 'bootscore'); ?>
                            </button>
                            <?php if (get_search_query()): ?>
                                <a href="<?php echo esc_url(get_post_type_archive_link('banned_card')); ?>"
                                    class="btn btn-outline-secondary"
                                    title="<?php esc_attr_e('Clear', 'bootscore'); ?>"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>

            <?php if (have_posts()): ?>
                <div class="banned-cards-list mx-auto" style="max-width: 900px;">
                    <?php while (have_posts()):
                        the_post(); ?>
                        <?php get_template_part('template-parts/card', 'banned-card'); ?>
                    <?php endwhile; ?>
                </div>

            <?php else: ?>
                <div class="alert alert-info text-center">
                    <?php esc_html_e('No banned cards found.', 'bootscore'); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>



<?php get_footer(); ?>