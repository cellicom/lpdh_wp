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

            <?php if (have_posts()): ?>

                <div class="row g-4">
                    <?php while (have_posts()):
                        the_post(); ?>
                        <?php get_template_part('template-parts/card', 'deck'); ?>
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

                <section class="no-results not-found">
                    <header class="page-header">
                        <h1 class="page-title"><?php esc_html_e('No decks found', 'bootscore'); ?></h1>
                    </header>

                    <div class="page-content">
                        <p><?php esc_html_e('It seems there are no decks to display.', 'bootscore'); ?></p>
                    </div>
                </section>

            <?php endif; ?>

        </div>

    </main>
</div>

</main>
</div>

<?php get_footer(); ?>