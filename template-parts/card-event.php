<?php
/**
 * Template part for displaying event card
 *
 * @package Bootscore Child
 * @version 6.0.0
 *
 * @param array $args {
 *     @type bool $show_place Whether to show the place name. Default true.
 * }
 */

$show_place = $args['show_place'] ?? true;

// Recupero campi ACF
$place_obj = get_field('field_event_place');
$place_name = $place_obj ? $place_obj->post_title : '';

$event_date = get_field('field_event_date');
$formatted_date = $event_date ? date_i18n('d/m/Y H:i', strtotime($event_date)) : '';

// Trova il vincitore (pos == 1) nel repeater rankings
$winner_name = '';
$winner_deck = '';
$rankings = get_field('field_event_ranking');

if ( is_array($rankings) ) {
    foreach ($rankings as $rank) {
        if ( isset($rank['pos']) && intval($rank['pos']) === 1 ) {
            $winner_name = isset($rank['name']) ? $rank['name'] : '';
            $winner_deck = isset($rank['deck']) ? $rank['deck'] : '';
            break;
        }
    }
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('event-card'); ?>>
    <div class="event-card-inner d-flex flex-column h-100">
        <div class="event-thumbnail">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100')); ?>
            <?php else : ?>
                <div class="placeholder-image">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo/logo-lpdh-ext-transparent.png" alt="<?php the_title_attribute(); ?>">
                </div>
            <?php endif; ?>
        </div>

        <div class="event-content p-3 d-flex flex-column flex-grow-1">
            <h2 class="event-title h6 mb-2">
                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark stretched-link">
                    <?php the_title(); ?>
                </a>
            </h2>

            <hr class="event-divider">

            <div class="event-meta small d-flex flex-column flex-grow-1">
                <?php if ( $show_place && $place_name ) : ?>
                    <div class="event-place mb-1 text-truncate" title="<?php echo esc_attr($place_name); ?>">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <?php echo esc_html( $place_name ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( $formatted_date ) : ?>
                    <div class="event-time mb-2">
                        <i class="fas fa-calendar-alt me-2" style="color: #003366;"></i>
                        <?php echo esc_html( $formatted_date ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( $winner_name ) : ?>
                    <div class="event-winner mt-auto pt-2 border-top">
                        <div class="fw-bold mb-1" style="font-size: 0.9em; color: #ffc107;">
                            <i class="fas fa-trophy me-1"></i> Winner
                        </div>
                        <div class="winner-name fw-bold text-truncate"><?php echo esc_html( $winner_name ); ?></div>
                        <?php if ( $winner_deck ) : ?>
                            <div class="winner-deck text-muted fst-italic text-truncate" style="font-size: 0.9em;"><?php echo esc_html( $winner_deck ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>