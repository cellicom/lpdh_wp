<?php
/**
 * Template Name: Page Events
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4">
            <h1 class="page-title"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </header>

        <div class="container pb-5">
            <?php
            $today = date('Y-m-d H:i:s');
            $args = array(
                'post_type' => 'event',
                'posts_per_page' => 12,
                'meta_key' => 'event_date',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'event_date',
                        'value' => $today,
                        'compare' => '>=',
                        'type' => 'DATETIME'
                    )
                )
            );
            $events_query = new WP_Query($args);

            if ($events_query->have_posts()): ?>
                <div class="event-archive-grid">
                    <?php while ($events_query->have_posts()):
                        $events_query->the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <?php esc_html_e('No upcoming events found.', 'bootscore'); ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-primary btn-lg">
                    <?php esc_html_e('See all events', 'bootscore'); ?>
                </a>
            </div>

            <div class="places-section mt-5 pt-5 border-top">
                <h2 class="text-center mb-5"><?php esc_html_e('Where to play', 'bootscore'); ?></h2>

                <?php
                $places_args = array(
                    'post_type' => 'place',
                    'posts_per_page' => -1,
                    'orderby' => 'date',
                    'order' => 'ASC',
                );
                $places_query = new WP_Query($places_args);

                if ($places_query->have_posts()): ?>
                    <div class="places-list">
                        <?php while ($places_query->have_posts()):
                            $places_query->the_post(); ?>
                            <?php get_template_part('template-parts/card', 'place'); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>



<?php get_footer(); ?>