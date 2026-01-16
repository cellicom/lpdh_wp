<?php
/**
 * Template part for displaying place card
 *
 * @package Bootscore Child
 */

$address = get_field('place_address');
$homepage = get_field('place_homepage');

// Check for future events
$place_id = get_the_ID();
$has_future_events = false;
$today = date('Y-m-d H:i:s');
$events_check_query = new WP_Query(array(
    'post_type'      => 'event',
    'posts_per_page' => 1,
    'meta_query'     => array(
        'relation' => 'AND',
        array(
            'key'     => 'event_date',
            'value'   => $today,
            'compare' => '>=',
            'type'    => 'DATETIME'
        ),
        array(
            'key'     => 'event_place',
            'value'   => $place_id,
            'compare' => '='
        )
    ),
    'fields' => 'ids',
    'no_found_rows' => true,
));
$has_future_events = $events_check_query->have_posts();
?>
<div class="card mb-4 border-0 shadow-sm overflow-hidden">
    <div class="row g-0">
        <div class="col-md-4 position-relative bg-light d-flex align-items-center justify-content-center">
            <a href="<?php the_permalink(); ?>" class="d-block w-100 text-decoration-none text-center">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="p-3">
                        <?php the_post_thumbnail('large', array('class' => 'img-fluid', 'style' => 'max-height: 300px; width: auto; object-fit: contain;')); ?>
                    </div>
                <?php else : ?>
                    <div class="bg-light d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                        <i class="fas fa-map-marker-alt fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>
            </a>
            <?php if ( $has_future_events ) : ?>
                <div class="position-absolute top-0 start-0 m-3">
                    <span class="badge bg-success shadow-sm">
                        <i class="fas fa-calendar-check me-1"></i> <?php esc_html_e('Upcoming Events', 'bootscore'); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <div class="card-body p-4">
                <h3 class="card-title h4 mb-3">
                    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark">
                        <?php the_title(); ?>
                    </a>
                </h3>
                
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