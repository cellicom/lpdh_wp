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

// Extract top 4
$top4 = array();
if (is_array($rankings) && count($rankings) > 0) {
    for ($i = 0; $i < 4 && $i < count($rankings); $i++) {
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
        $commander_name = '';
        $partner_name = '';

        if ($player_deck_id) {
            $deck_post = get_post($player_deck_id);
            if ($deck_post) {
                $deck_name = $deck_post->post_title;
                $commander_img = get_commander_image($player_deck_id);
                $partner_img = get_partner_image($player_deck_id);
                
                // Get commander and partner names
                $commander_name = get_field('commander', $player_deck_id);
                $partner_name = get_field('partner', $player_deck_id);
            }
        }

        $top4[] = array(
            'position' => $i + 1,
            'player_name' => $player_name,
            'deck_name' => $deck_name,
            'commander_img' => $commander_img,
            'partner_img' => $partner_img,
            'commander_name' => $commander_name,
            'partner_name' => $partner_name
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
            background: #1a1a1a;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        }

        /* Epic Fantasy Background */
        .instagram-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 50%, rgba(139, 0, 0, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 50%, rgba(139, 0, 0, 0.2) 0%, transparent 50%),
                linear-gradient(180deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            z-index: 1;
        }

        /* Parchment Border Effect */
        .instagram-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 40px solid transparent;
            border-image: linear-gradient(135deg, 
                rgba(139, 69, 19, 0.6) 0%, 
                rgba(210, 180, 140, 0.4) 25%,
                rgba(139, 69, 19, 0.6) 50%,
                rgba(210, 180, 140, 0.4) 75%,
                rgba(139, 69, 19, 0.6) 100%) 40;
            z-index: 5;
            pointer-events: none;
        }

        /* Red Crack Effects */
        .content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(45deg, transparent 48%, rgba(139, 0, 0, 0.3) 49%, rgba(139, 0, 0, 0.3) 51%, transparent 52%),
                linear-gradient(-45deg, transparent 48%, rgba(139, 0, 0, 0.3) 49%, rgba(139, 0, 0, 0.3) 51%, transparent 52%);
            background-size: 800px 800px;
            background-position: -200px -200px, 600px -200px;
            opacity: 0.4;
            z-index: 1;
            pointer-events: none;
        }

        /* Content Wrapper */
        .content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 80px 60px 60px;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 40px;
            z-index: 3;
        }

        .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 48px;
            font-weight: 900;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 3px;
            text-shadow: 
                0 0 10px rgba(255, 215, 0, 0.5),
                0 4px 20px rgba(0, 0, 0, 0.8),
                0 0 30px rgba(255, 215, 0, 0.3);
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 15px;
            text-shadow: 0 2px 15px rgba(0, 0, 0, 0.6);
        }

        .event-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 20px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Podium Section */
        .podium {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 30px;
            z-index: 3;
        }

        /* First Place - Large and Central */
        .first-place {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }

        .first-place-card {
            position: relative;
            width: 320px;
            height: 450px;
            margin-bottom: 25px;
        }

        /* Ornate Golden Frame */
        .first-place-card::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 50%, #FFD700 100%);
            border-radius: 20px;
            z-index: 1;
            box-shadow: 
                0 0 40px rgba(255, 215, 0, 0.6),
                0 0 80px rgba(255, 215, 0, 0.4),
                inset 0 0 30px rgba(255, 215, 0, 0.3);
        }

        /* Ornate Frame Pattern */
        .first-place-card::after {
            content: '';
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            background: linear-gradient(135deg, transparent 0%, rgba(139, 69, 19, 0.4) 50%, transparent 100%);
            border: 5px solid rgba(139, 69, 19, 0.6);
            border-radius: 18px;
            z-index: 2;
            box-shadow: 
                inset 0 0 20px rgba(255, 215, 0, 0.4),
                0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .first-place-card img {
            position: relative;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
            z-index: 3;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
        }

        /* Dual Commander Layout for First Place */
        .first-place-card.dual {
            display: flex;
            gap: 10px;
        }

        .first-place-card.dual img {
            width: calc(50% - 5px);
        }

        .first-place-info {
            text-align: center;
            max-width: 400px;
        }

        .first-place-position {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            font-weight: 900;
            color: #FFD700;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            text-shadow: 
                0 0 20px rgba(255, 215, 0, 0.8),
                0 4px 10px rgba(0, 0, 0, 0.8);
        }

        .first-place-player {
            font-family: 'Montserrat', sans-serif;
            font-size: 36px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            text-shadow: 0 3px 15px rgba(0, 0, 0, 0.7);
        }

        .first-place-commanders {
            font-size: 22px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            font-style: italic;
            line-height: 1.4;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
        }

        /* Bottom Three Places */
        .bottom-three {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: auto;
        }

        .place-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 0 0 280px;
        }

        .place-card {
            position: relative;
            width: 200px;
            height: 200px;
            margin-bottom: 15px;
            border-radius: 50%;
            overflow: hidden;
        }

        /* Circular Metallic Frames */
        .place-card::before {
            content: '';
            position: absolute;
            top: -15px;
            left: -15px;
            right: -15px;
            bottom: -15px;
            border-radius: 50%;
            z-index: 1;
            box-shadow: 
                0 0 30px rgba(0, 0, 0, 0.5),
                inset 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .place-item.silver .place-card::before {
            background: linear-gradient(135deg, #E8E8E8 0%, #C0C0C0 50%, #A8A8A8 100%);
        }

        .place-item.bronze .place-card::before {
            background: linear-gradient(135deg, #E39A5C 0%, #CD7F32 50%, #B8743C 100%);
        }

        .place-card img {
            position: relative;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            z-index: 2;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.6);
        }

        /* Dual Commander for Bottom Three */
        .place-card.dual {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .place-card.dual img {
            width: 48%;
            height: 95%;
            border-radius: 10px;
        }

        .place-info {
            text-align: center;
            max-width: 280px;
        }

        .place-position {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .place-item.silver .place-position {
            color: #C0C0C0;
            text-shadow: 
                0 0 15px rgba(192, 192, 192, 0.6),
                0 3px 8px rgba(0, 0, 0, 0.8);
        }

        .place-item.bronze .place-position {
            color: #CD7F32;
            text-shadow: 
                0 0 15px rgba(205, 127, 50, 0.6),
                0 3px 8px rgba(0, 0, 0, 0.8);
        }

        .place-player {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .place-commanders {
            font-size: 16px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            font-style: italic;
            line-height: 1.3;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 20px;
            z-index: 3;
        }

        .footer-text {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
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
                    <div class="subtitle">TOP 4 DECKLISTS</div>
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
                    <?php if (isset($top4[0])): 
                        $first = $top4[0];
                        $has_partner = !empty($first['partner_img']);
                    ?>
                        <!-- First Place -->
                        <div class="first-place">
                            <div class="first-place-card <?php echo $has_partner ? 'dual' : ''; ?>">
                                <?php if ($first['commander_img']): ?>
                                    <img src="<?php echo esc_url($first['commander_img']); ?>" alt="Commander">
                                    <?php if ($has_partner): ?>
                                        <img src="<?php echo esc_url($first['partner_img']); ?>" alt="Partner">
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            <div class="first-place-info">
                                <div class="first-place-position">1ST PLACE</div>
                                <div class="first-place-player"><?php echo esc_html($first['player_name']); ?></div>
                                <div class="first-place-commanders">
                                    <?php 
                                    echo esc_html($first['commander_name']);
                                    if ($first['partner_name']) {
                                        echo ' / ' . esc_html($first['partner_name']);
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Bottom Three Places -->
                    <div class="bottom-three">
                        <?php 
                        $positions = array(
                            1 => array('class' => 'silver', 'label' => '2ND PLACE'),
                            2 => array('class' => 'bronze', 'label' => '3RD PLACE'),
                            3 => array('class' => 'bronze', 'label' => '4TH PLACE')
                        );
                        
                        for ($i = 1; $i <= 3; $i++):
                            if (isset($top4[$i])):
                                $player = $top4[$i];
                                $has_partner = !empty($player['partner_img']);
                        ?>
                            <div class="place-item <?php echo $positions[$i]['class']; ?>">
                                <div class="place-card <?php echo $has_partner ? 'dual' : ''; ?>">
                                    <?php if ($player['commander_img']): ?>
                                        <img src="<?php echo esc_url($player['commander_img']); ?>" alt="Commander">
                                        <?php if ($has_partner): ?>
                                            <img src="<?php echo esc_url($player['partner_img']); ?>" alt="Partner">
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="place-info">
                                    <div class="place-position"><?php echo $positions[$i]['label']; ?></div>
                                    <div class="place-player"><?php echo esc_html($player['player_name']); ?></div>
                                    <div class="place-commanders">
                                        <?php 
                                        echo esc_html($player['commander_name']);
                                        if ($player['partner_name']) {
                                            echo ' / ' . esc_html($player['partner_name']);
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endif;
                        endfor; 
                        ?>
                    </div>
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