<?php
/**
 * Template Name: Instagram Generator
 * Template for generating Instagram promotional images for events
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Access control: Only administrators
if (!current_user_can('administrator')) {
    wp_redirect(home_url());
    exit;
}

// Get event ID from query parameter
$event_id = isset($_GET['ig_event_id']) ? intval($_GET['ig_event_id']) : 0;

if (!$event_id) {
    wp_redirect(home_url());
    exit;
}

$event = get_post($event_id);
if (!$event || $event->post_type !== 'event') {
    wp_redirect(home_url());
    exit;
}

// Get event data
$event_title = get_the_title($event_id);
$event_date = get_field('field_event_date', $event_id);
$event_place = get_field('field_event_place', $event_id);
$rankings = get_field('field_event_ranking', $event_id);

// Get theme colors
$primary_color = get_option('lpdh_theme_primary_color', '#6a1b9a');
$secondary_color = get_option('lpdh_theme_secondary_color', '#00bcd4');

// Extract top 3
$top3 = array();
if (is_array($rankings) && count($rankings) >= 3) {
    for ($i = 0; $i < 3 && $i < count($rankings); $i++) {
        $rank = $rankings[$i];
        $player_deck_id = isset($rank['player_deck_id']) ? $rank['player_deck_id'] : '';

        $player_name = '';
        $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
        if ($player_id_field) {
            if (is_array($player_id_field) && isset($player_id_field['display_name'])) {
                $player_name = $player_id_field['display_name'];
            } elseif (is_numeric($player_id_field)) {
                $user_info = get_userdata($player_id_field);
                if ($user_info) {
                    $player_name = $user_info->display_name;
                }
            }
        }
        if (!$player_name) {
            $player_name = isset($rank['name']) ? $rank['name'] : 'Unknown Player';
        }

        $deck_name = isset($rank['deck']) ? $rank['deck'] : '';
        $commander_img = '';
        $partner_img = '';

        if ($player_deck_id) {
            $deck_post = get_post($player_deck_id);
            if ($deck_post) {
                $deck_name = $deck_post->post_title;
                $commander_img = get_commander_image($player_deck_id);
                $partner_img = get_partner_image($player_deck_id);
            }
        }

        $top3[] = array(
            'position' => $i + 1,
            'player_name' => $player_name,
            'deck_name' => $deck_name,
            'commander_img' => $commander_img,
            'partner_img' => $partner_img
        );
    }
}

// Format date
$formatted_date = $event_date ? date_i18n('d/m/Y', strtotime($event_date)) : '';
$place_name = $event_place ? $event_place->post_title : '';
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram Generator -
        <?php echo esc_html($event_title); ?>
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Roboto:wght@400;500;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: #1a1a1a;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .instagram-image {
            width: 1080px;
            height: 1350px;
            background: linear-gradient(135deg, <?php echo esc_attr($primary_color); ?> 0%, <?php echo esc_attr($secondary_color); ?> 100%);
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        /* Decorative Background Pattern */
        .instagram-image::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.3;
            transform: rotate(20deg);
        }

        /* Content Wrapper */
        .content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 60px;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 60px;
        }

        .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 56px;
            font-weight: 900;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .event-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 24px;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Podium Section */
        .podium {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 40px;
        }

        .rank-item {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px 40px;
            display: flex;
            align-items: center;
            gap: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .rank-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 8px;
        }

        .rank-item.gold::before {
            background: linear-gradient(180deg, #FFD700 0%, #FFA500 100%);
        }

        .rank-item.silver::before {
            background: linear-gradient(180deg, #C0C0C0 0%, #808080 100%);
        }

        .rank-item.bronze::before {
            background: linear-gradient(180deg, #CD7F32 0%, #8B4513 100%);
        }

        .rank-item.gold {
            transform: scale(1.05);
        }

        /* Position Badge */
        .position {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Montserrat', sans-serif;
            font-size: 42px;
            font-weight: 900;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .gold .position {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        }

        .silver .position {
            background: linear-gradient(135deg, #C0C0C0 0%, #808080 100%);
        }

        .bronze .position {
            background: linear-gradient(135deg, #CD7F32 0%, #8B4513 100%);
        }

        /* Commander Images */
        .commander-images {
            flex-shrink: 0;
            position: relative;
            width: 120px;
            height: 120px;
        }

        .commander-images img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .commander-images.dual {
            display: flex;
            gap: 5px;
        }

        .commander-images.dual img {
            width: 57px;
        }

        /* Player Info */
        .player-info {
            flex: 1;
            min-width: 0;
        }

        .player-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .deck-name {
            font-size: 26px;
            font-weight: 500;
            color: #555;
            font-style: italic;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: auto;
            padding-top: 40px;
        }

        .footer-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.95);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        /* Instructions */
        .instructions {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            max-width: 1080px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }

        .instructions h2 {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .instructions p {
            font-size: 18px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .instructions ul {
            margin: 15px 0;
            padding-left: 30px;
        }

        .instructions li {
            font-size: 16px;
            color: #555;
            margin-bottom: 8px;
        }

        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 30px;
            background: <?php echo esc_attr($primary_color); ?>;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            transition: background 0.3s ease;
        }

        .btn-back:hover {
            background: <?php echo esc_attr($secondary_color); ?>;
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="instagram-image" id="ig-image">
            <div class="content">
                <!-- Header -->
                <div class="header">
                    <div class="event-title">
                        <?php echo esc_html($event_title); ?>
                    </div>
                    <div class="event-meta">
                        <?php if ($formatted_date): ?>
                            <span>📅
                                <?php echo esc_html($formatted_date); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($place_name): ?>
                            <span>📍
                                <?php echo esc_html($place_name); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Podium -->
                <div class="podium">
                    <?php foreach ($top3 as $index => $player):
                        $rank_class = '';
                        if ($index === 0)
                            $rank_class = 'gold';
                        elseif ($index === 1)
                            $rank_class = 'silver';
                        elseif ($index === 2)
                            $rank_class = 'bronze';
                        ?>
                        <div class="rank-item <?php echo esc_attr($rank_class); ?>">
                            <div class="position">
                                <?php echo $player['position']; ?>
                            </div>

                            <?php if ($player['commander_img']): ?>
                                <div class="commander-images <?php echo $player['partner_img'] ? 'dual' : ''; ?>">
                                    <img src="<?php echo esc_url($player['commander_img']); ?>" alt="Commander">
                                    <?php if ($player['partner_img']): ?>
                                        <img src="<?php echo esc_url($player['partner_img']); ?>" alt="Partner">
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <div class="player-info">
                                <div class="player-name">
                                    <?php echo esc_html($player['player_name']); ?>
                                </div>
                                <div class="deck-name">
                                    <?php echo esc_html($player['deck_name']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Footer -->
                <div class="footer">
                    <div class="footer-text">LEGA PLAYER DI HOMM</div>
                </div>
            </div>
        </div>

        <!-- Instructions -->
        <div class="instructions">
            <h2>📸 How to Use</h2>
            <p><strong>To save the image for Instagram:</strong></p>
            <ul>
                <li><strong>Windows:</strong> Right-click on the image → "Save Image As..."</li>
                <li><strong>Mac:</strong> Right-click on the image → "Save Image As..." or take a screenshot (Cmd +
                    Shift + 4)</li>
                <li><strong>Screenshot Tools:</strong> Use a screenshot tool to capture the exact 1080x1350px area</li>
            </ul>
            <p>The image is optimized for Instagram's vertical format (4:5 ratio).</p>
            <a href="<?php echo esc_url(get_permalink($event_id)); ?>" class="btn-back">← Back to Event</a>
        </div>
    </div>
</body>

</html>