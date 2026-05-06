<?php
/**
 * Template for displaying single place posts
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <?php while (have_posts()):
                        the_post();
                        $place_id = get_the_ID();
                        $city = get_field('field_place_city');
                        $address = get_field('field_place_address');
                        $homepage = get_field('field_place_homepage');
                        ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                                <div class="place-details d-flex justify-content-center gap-4 mt-2 flex-wrap">
                                    <?php if ($city): ?>
                                        <div class="place-city">
                                            <i class="fas fa-city text-primary me-1"></i>
                                            <?php echo esc_html($city); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($address): ?>
                                        <div class="place-address">
                                            <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                            <?php echo esc_html($address); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($homepage): ?>
                                        <div class="place-homepage">
                                            <i class="fas fa-globe text-primary me-1"></i>
                                            <a href="<?php echo esc_url($homepage); ?>" target="_blank" rel="noopener">
                                                <?php echo esc_html($homepage); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <?php if (has_post_thumbnail()): ?>
                                <div class="entry-featured-image mb-5 text-center">
                                    <?php the_post_thumbnail('large', array('class' => 'img-fluid rounded shadow-sm', 'style' => 'max-height: 400px; width: auto;')); ?>
                                </div>
                            <?php endif; ?>

                            <div class="entry-content mb-5">
                                <?php the_content(); ?>
                            </div>

                        </article>

                    <?php endwhile; ?>

                    <!-- Future Events Section -->
                    <div class="place-events mt-5 pt-5 border-top">
                        <h2 class="text-center mb-4"><?php esc_html_e('Upcoming Events', 'bootscore'); ?></h2>

                        <?php
                        $today = date('Y-m-d H:i:s');
                        $args = array(
                            'post_type' => 'event',
                            'posts_per_page' => -1,
                            'meta_key' => 'event_date',
                            'orderby' => 'meta_value',
                            'order' => 'ASC',
                            'meta_query' => array(
                                'relation' => 'AND',
                                array(
                                    'key' => 'event_date',
                                    'value' => $today,
                                    'compare' => '>=',
                                    'type' => 'DATETIME'
                                ),
                                array(
                                    'key' => 'event_place',
                                    'value' => $place_id,
                                    'compare' => '='
                                )
                            )
                        );
                        $events_query = new WP_Query($args);

                        if ($events_query->have_posts()): ?>
                            <div class="event-archive-grid">
                                <?php while ($events_query->have_posts()):
                                    $events_query->the_post(); ?>
                                    <?php get_template_part('template-parts/card', 'event', ['show_place' => false]); ?>
                                <?php endwhile; ?>
                                <?php wp_reset_postdata(); ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center">
                                <?php esc_html_e('No upcoming events scheduled at this place.', 'bootscore'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    </main>
</div>



<?php get_footer(); ?>