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

            if ( $events_query->have_posts() ) : ?>
                <div class="event-archive-grid">
                    <?php while ( $events_query->have_posts() ) : $events_query->the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            <?php else : ?>
                <div class="alert alert-info">
                    <?php esc_html_e( 'Nessun evento futuro trovato.', 'bootscore' ); ?>
                </div>
            <?php endif; ?>

            <div class="text-center mt-5">
                <a href="<?php echo get_post_type_archive_link('event'); ?>" class="btn btn-primary btn-lg">
                    <?php esc_html_e( 'See all event', 'bootscore' ); ?>
                </a>
            </div>

            <div class="places-section mt-5 pt-5 border-top">
                <h2 class="text-center mb-5"><?php esc_html_e('Where to play', 'bootscore'); ?></h2>
                
                <?php
                $places_args = array(
                    'post_type'      => 'place',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                );
                $places_query = new WP_Query($places_args);
                
                if ( $places_query->have_posts() ) : ?>
                    <div class="places-list">
                        <?php while ( $places_query->have_posts() ) : $places_query->the_post(); 
                            $address = get_field('place_address');
                            $homepage = get_field('place_homepage');
                        ?>
                            <div class="card mb-4 border-0 shadow-sm overflow-hidden">
                                <div class="row g-0">
                                    <div class="col-md-4">
                                        <?php if ( has_post_thumbnail() ) : ?>
                                            <div class="h-100">
                                                <?php the_post_thumbnail('large', array('class' => 'img-fluid h-100 w-100 object-fit-cover', 'style' => 'min-height: 250px; object-fit: cover;')); ?>
                                            </div>
                                        <?php else : ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                                                <i class="fas fa-map-marker-alt fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card-body p-4">
                                            <h3 class="card-title h4 mb-3"><?php the_title(); ?></h3>
                                            
                                            <div class="card-text mb-4">
                                                <?php the_content(); ?>
                                            </div>
                                            
                                            <div class="place-meta">
                                                <?php if ( $address ) : ?>
                                                    <div class="d-flex align-items-start mb-2">
                                                        <i class="fas fa-map-marker-alt text-primary mt-1 me-2"></i>
                                                        <span><?php echo esc_html($address); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ( $homepage ) : ?>
                                                    <div class="d-flex align-items-start">
                                                        <i class="fas fa-globe text-primary mt-1 me-2"></i>
                                                        <a href="<?php echo esc_url($homepage); ?>" target="_blank" rel="noopener" class="text-decoration-none">
                                                            <?php echo esc_html($homepage); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                <?php endif; ?>
            </div>
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