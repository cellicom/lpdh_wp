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

        <header class="page-header container my-4 text-center">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <div class="container pb-5">

            <!-- Filters -->
            <?php lpdh_render_event_filters(); ?>

            <?php if (have_posts()): ?>
                <div class="event-archive-grid">
                    <?php while (have_posts()):
                        the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
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
                    <?php esc_html_e('No events found.', 'bootscore'); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>


<?php get_footer(); ?>