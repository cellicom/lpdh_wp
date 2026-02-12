<?php
/**
 * Template for displaying place archive
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4 text-center">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <div class="container pb-5">
            <form method="get" action="<?php echo esc_url(get_post_type_archive_link('place')); ?>" class="mb-5">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="input-group">
                            <input type="text" name="place_q" class="form-control"
                                placeholder="<?php esc_attr_e('Search places by name or address...', 'bootscore'); ?>"
                                value="<?php echo esc_attr(get_query_var('place_q')); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> <?php esc_html_e('Search', 'bootscore'); ?>
                            </button>
                            <?php if (get_query_var('place_q')): ?>
                                <a href="<?php echo esc_url(get_post_type_archive_link('place')); ?>"
                                    class="btn btn-outline-secondary"
                                    title="<?php esc_attr_e('Clear', 'bootscore'); ?>"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>

            <?php if (have_posts()): ?>
                <div class="places-list">
                    <?php while (have_posts()):
                        the_post(); ?>
                        <?php get_template_part('template-parts/card', 'place'); ?>
                    <?php endwhile; ?>
                </div>

                <div class="mt-5">
                    <?php
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => __('Previous', 'bootscore'),
                        'next_text' => __('Next', 'bootscore'),
                    ));
                    ?>
                </div>

            <?php else: ?>
                <div class="alert alert-info">
                    <?php esc_html_e('No places found.', 'bootscore'); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>



<?php get_footer(); ?>