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

if (is_array($rankings)) {
    foreach ($rankings as $rank) {
        if (isset($rank['pos']) && intval($rank['pos']) === 1) {
            $winner_name = isset($rank['name']) ? $rank['name'] : '';

            // Check if player_id is linked and use display_name if available
            $player_user = isset($rank['player_id']) ? $rank['player_id'] : null;
            $player_id = 0;

            if ($player_user) {
                if (is_numeric($player_user)) {
                    $player_id = intval($player_user);
                } elseif (is_array($player_user) && isset($player_user['ID'])) {
                    $player_id = intval($player_user['ID']);
                } elseif (is_object($player_user) && isset($player_user->ID)) {
                    $player_id = intval($player_user->ID);
                }
            }
            
            if ($player_id) {
                $user_info = get_userdata($player_id);
                if ($user_info) {
                    $winner_name = $user_info->display_name;
                }
            }

            $winner_deck = isset($rank['deck']) ? $rank['deck'] : '';
            break;
        }
    }
}

$is_past = false;
if ($event_date) {
    if (strtotime($event_date) < current_time('timestamp')) {
        $is_past = true;
    }
}
$card_classes = 'event-card' . ($is_past ? ' event-past' : '');
?>

<article id="post-<?php the_ID(); ?>" <?php post_class($card_classes); ?>>
    <div class="event-card-inner hover-lift d-flex flex-column h-100 position-relative">
        <?php
        $event_type_val = get_field('event_type') ?: 'Tournament';
        $format_val = get_field('format') ?: 'LPDH';
        $max_players_val = intval(get_field('max_players'));
        ?>
        <div class="position-absolute top-0 end-0 p-2 d-flex flex-column gap-1 align-items-end" style="z-index: 3;">
            <span class="badge bg-primary text-white shadow-sm" style="font-size: 0.75rem; border-radius: 4px;"><?php echo esc_html($event_type_val); ?></span>
            <span class="badge bg-secondary text-white shadow-sm" style="font-size: 0.75rem; border-radius: 4px;"><?php echo esc_html($format_val); ?></span>
            <?php if ($max_players_val > 0): ?>
                <span class="badge bg-dark text-white shadow-sm" style="font-size: 0.75rem; border-radius: 4px;">
                    <i class="fas fa-users me-1" style="font-size: 0.7rem;"></i>Max <?php echo esc_html($max_players_val); ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="event-thumbnail">
            <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('medium', array('class' => 'img-fluid w-100')); ?>
            <?php else: ?>
                <div class="placeholder-image">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/logo/logo-lpdh-ext-transparent.png"
                        alt="<?php the_title_attribute(); ?>">
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
                <?php if ($show_place && $place_name): ?>
                    <div class="event-place mb-1 text-truncate" title="<?php echo esc_attr($place_name); ?>">
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                        <?php echo esc_html($place_name); ?>
                    </div>
                <?php endif; ?>

                <?php if ($formatted_date): ?>
                    <div class="event-time mb-2">
                        <i class="fas fa-calendar-alt me-2" style="color: #003366;"></i>
                        <?php echo esc_html($formatted_date); ?>
                    </div>
                <?php endif; ?>

                <?php if ($winner_name): ?>
                    <div class="event-winner mt-auto pt-2 border-top">
                        <div class="fw-bold mb-1" style="font-size: 0.9em; color: #ffc107;">
                            <i class="fas fa-trophy me-1"></i> Winner
                        </div>
                        <div class="winner-name fw-bold text-truncate"><?php echo esc_html($winner_name); ?></div>
                        <?php if ($winner_deck): ?>
                            <div class="winner-deck fst-italic text-truncate" style="font-size: 0.9em;">
                                <?php echo esc_html($winner_deck); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</article>