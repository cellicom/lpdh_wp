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

// Function to convert Scryfall images to cached local files
function cache_scryfall_image($url) {
    if (empty($url)) {
        return '';
    }
    
    // Only convert if it's a Scryfall image
    if (strpos($url, 'scryfall.io') === false) {
        return $url; // Return original URL if not Scryfall
    }
    
    // Create cache directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $cache_dir = $upload_dir['basedir'] . '/ig-converted';
    
    if (!file_exists($cache_dir)) {
        wp_mkdir_p($cache_dir);
    }
    
    // Generate filename from URL hash
    $filename = md5($url) . '.jpg';
    $cache_file = $cache_dir . '/' . $filename;
    $cache_url = $upload_dir['baseurl'] . '/ig-converted/' . $filename;
    
    // Check if already cached
    if (file_exists($cache_file)) {
        return $cache_url;
    }
    
    // Download image from Scryfall
    $response = wp_remote_get($url, array(
        'timeout' => 15,
        'sslverify' => false
    ));
    
    if (is_wp_error($response)) {
        return $url; // Return original URL if fetch fails
    }
    
    $image_data = wp_remote_retrieve_body($response);
    
    if (empty($image_data)) {
        return $url;
    }
    
    // Save to cache directory
    $saved = file_put_contents($cache_file, $image_data);
    
    if ($saved === false) {
        return $url;
    }
    
    return $cache_url;
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

// Get template type from query parameter
$ig_type = isset($_GET['ig_type']) ? sanitize_text_field($_GET['ig_type']) : 'top4';
$max_players = 4;
if ($ig_type === 'top3') $max_players = 3;
if ($ig_type === 'top8') $max_players = 8;

// Extract players data
$players_data = array();
if (is_array($rankings) && count($rankings) > 0) {
    for ($i = 0; $i < $max_players && $i < count($rankings); $i++) {
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
                
                // Remove query strings from image URLs
                if ($commander_img) {
                    $commander_img_clean = strtok($commander_img, '?');
                    if ($commander_img_clean !== false) {
                        $commander_img = $commander_img_clean;
                    }
                }
                if ($partner_img) {
                    $partner_img_clean = strtok($partner_img, '?');
                    if ($partner_img_clean !== false) {
                        $partner_img = $partner_img_clean;
                    }
                }
                
                // Cache Scryfall images locally
                if ($commander_img) {
                    $commander_img = cache_scryfall_image($commander_img);
                }
                if ($partner_img) {
                    $partner_img = cache_scryfall_image($partner_img);
                }
                
                // Get commander and partner names
                $commander_name = get_field('commander', $player_deck_id);
                $partner_name = get_field('partner', $player_deck_id);
            }
        }

        $players_data[] = array(
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
            background: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            width: 100%;
        }

        .instagram-image {
            width: 1080px;
            height: 1350px;
            position: relative;
            margin: 0 auto;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8);
        }

        /* === EPIC FANTASY THEME === */
        .instagram-fantasy {
            background: #1a1a1a;
        }

        /* Epic Fantasy Background */
        .instagram-fantasy::before {
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
        .instagram-fantasy::after {
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
        .instagram-fantasy .content::before {
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
        .instagram-fantasy .content {
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
            justify-content: space-evenly;
            gap: 10px;
            z-index: 3;
        }

        /* First Place - Large and Central */
        .first-place {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
        }

        .first-place-cards-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 50px;
        }

        .first-place-cards-wrapper.dual {
            gap: 60px;
        }

        .first-place-card {
            position: relative;
            width: 280px;
            height: 350px;
        }

        .first-place-cards-wrapper.dual .first-place-card {
            width: 220px;
            height: 300px;
        }

        /* Ornate Golden Frame */
        .first-place-card::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            right: -20px;
            bottom: -20px;
            background: 
                radial-gradient(ellipse at center, rgba(255, 215, 0, 0.2) 0%, transparent 70%),
                linear-gradient(145deg, #FFD700 0%, #FFA500 25%, #FFD700 50%, #FFA500 75%, #FFD700 100%);
            border-radius: 18px;
            z-index: 1;
            box-shadow: 
                0 0 50px rgba(255, 215, 0, 0.7),
                0 0 100px rgba(255, 165, 0, 0.5),
                0 15px 40px rgba(0, 0, 0, 0.8);
            animation: goldenGlow 3s ease-in-out infinite;
        }

        @keyframes goldenGlow {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.2); }
        }

        .first-place-card::after {
            content: '';
            position: absolute;
            top: -12px;
            left: -12px;
            right: -12px;
            bottom: -12px;
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
            object-position: top;
            border-radius: 15px;
            z-index: 3;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
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
            gap: 10px;
            margin-top: -40px;
        }

        .place-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 0 0 280px;
        }

        .place-cards-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }

        .place-cards-wrapper.dual {
            gap: 0px;
        }

        .place-card {
            position: relative;
            width: 160px;
            height: 220px;
        }

        .place-cards-wrapper.dual .place-card {
            width: 160px;
            height: 220px;
        }

        /* Colored Frames for 2nd-4th Place */
        .place-card::before {
            content: '';
            position: absolute;
            top: -8px;
            left: -8px;
            right: -8px;
            bottom: -8px;
            border-radius: 12px;
            z-index: 1;
            box-shadow: 
                0 0 30px rgba(0, 0, 0, 0.5),
                0 10px 30px rgba(0, 0, 0, 0.6);
        }

        /* Silver - 2nd Place */
        .place-item.silver .place-card::before {
            background: linear-gradient(145deg, #E8E8E8 0%, #C0C0C0 25%, #E8E8E8 50%, #C0C0C0 75%, #E8E8E8 100%);
            box-shadow: 
                0 0 40px rgba(192, 192, 192, 0.6),
                0 10px 30px rgba(0, 0, 0, 0.6);
        }

        .place-item.silver .place-card::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid #C0C0C0;
            border-radius: 10px;
            z-index: 2;
            box-shadow: inset 0 0 15px rgba(192, 192, 192, 0.3);
        }

        /* Bronze - 3rd Place */
        .place-item.bronze .place-card::before {
            background: linear-gradient(145deg, #E39A5C 0%, #CD7F32 25%, #E39A5C 50%, #CD7F32 75%, #E39A5C 100%);
            box-shadow: 
                0 0 40px rgba(205, 127, 50, 0.6),
                0 10px 30px rgba(0, 0, 0, 0.6);
        }

        .place-item.bronze .place-card::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid #CD7F32;
            border-radius: 10px;
            z-index: 2;
            box-shadow: inset 0 0 15px rgba(205, 127, 50, 0.3);
        }

        /* Orange - 4th Place */
        .place-item.fourth .place-card::before {
            background: linear-gradient(145deg, #FF6347 0%, #FF4500 25%, #FF6347 50%, #FF4500 75%, #FF6347 100%);
            box-shadow: 
                0 0 40px rgba(255, 69, 0, 0.6),
                0 10px 30px rgba(0, 0, 0, 0.6);
        }

        .place-item.fourth .place-card::after {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            border: 2px solid #FF4500;
            border-radius: 10px;
            z-index: 2;
            box-shadow: inset 0 0 15px rgba(255, 69, 0, 0.3);
        }

        .place-card img {
            position: relative;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;
            border-radius: 10px;
            z-index: 3;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.6);
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

        .place-item.fourth .place-position {
            color: #FF4500;
            text-shadow: 
                0 0 15px rgba(255, 69, 0, 0.6),
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

        /* Theme Selector */
        .theme-selector {
            max-width: 1080px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .theme-selector label {
            font-size: 18px;
            color: #333;
        }

        /* Action Buttons */
        .action-buttons {
            max-width: 1080px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            min-width: 200px;
            font-size: 18px;
            font-weight: 600;
            padding: 15px 30px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .action-buttons .btn-primary {
            background: <?php echo esc_attr($primary_color); ?>;
            border: none;
            color: white;
        }

        .action-buttons .btn-primary:hover {
            background: <?php echo esc_attr($secondary_color); ?>;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .action-buttons .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .action-buttons .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
        }

        .action-buttons .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* === VAPORWAVE THEME === */
        .instagram-vaporwave {
            background: linear-gradient(180deg, #05001e 0%, #1a0033 50%, #05001e 100%);
        }

        .instagram-vaporwave::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 30% 40%, rgba(255, 113, 206, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 70% 60%, rgba(1, 205, 254, 0.15) 0%, transparent 50%),
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255, 113, 206, 0.03) 2px, rgba(255, 113, 206, 0.03) 4px);
            z-index: 1;
        }

        .instagram-vaporwave::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 30px solid transparent;
            border-image: linear-gradient(135deg, 
                rgba(255, 113, 206, 0.6) 0%, 
                rgba(1, 205, 254, 0.4) 50%,
                rgba(185, 103, 255, 0.6) 100%) 30;
            z-index: 5;
            pointer-events: none;
        }

        .instagram-vaporwave .content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 80px 60px 60px;
        }

        .instagram-vaporwave .header {
            text-align: center;
            margin-bottom: 40px;
            z-index: 3;
        }

        .instagram-vaporwave .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 48px;
            font-weight: 900;
            background: linear-gradient(135deg, #ff71ce, #01cdfe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(255, 113, 206, 0.8);
            margin-bottom: 15px;
        }

        .instagram-vaporwave .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #01cdfe;
            text-shadow: 0 0 20px rgba(1, 205, 254, 0.8);
        }

        .instagram-vaporwave .first-place-card::before {
            background: linear-gradient(145deg, #ff71ce 0%, #b967ff 50%, #ff71ce 100%);
            box-shadow: 
                0 0 50px rgba(255, 113, 206, 1),
                0 0 100px rgba(185, 103, 255, 0.7);
        }

        .instagram-vaporwave .place-item.silver .place-card::before {
            background: linear-gradient(135deg, #01cdfe 0%, #0099cc 50%, #01cdfe 100%);
        }

        .instagram-vaporwave .place-item.bronze .place-card::before {
            background: linear-gradient(135deg, #b967ff 0%, #8e44ad 50%, #b967ff 100%);
        }

        .instagram-vaporwave .place-item.fourth .place-card::before {
            background: linear-gradient(135deg, #ff71ce 0%, #ff1493 50%, #ff71ce 100%);
        }

        /* === VAPORWAVE GREEN THEME === */
        .instagram-vaporwave-green {
            background: linear-gradient(180deg, #07241B 0%, #0a2d23 50%, #07241B 100%);
        }

        .instagram-vaporwave-green::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 30% 40%, rgba(177, 198, 114, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 60%, rgba(33, 186, 69, 0.1) 0%, transparent 50%);
            z-index: 1;
        }

        .instagram-vaporwave-green::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 30px solid transparent;
            border-image: linear-gradient(135deg, 
                rgba(177, 198, 114, 0.6) 0%, 
                rgba(61, 105, 74, 0.4) 50%,
                rgba(177, 198, 114, 0.6) 100%) 30;
            z-index: 5;
            pointer-events: none;
        }

        .instagram-vaporwave-green .content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 80px 60px 60px;
        }

        .instagram-vaporwave-green .header {
            text-align: center;
            margin-bottom: 40px;
            z-index: 3;
        }

        .instagram-vaporwave-green .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 48px;
            font-weight: 900;
            color: #B1C672;
            text-shadow: 0 0 30px rgba(177, 198, 114, 0.8);
            margin-bottom: 15px;
        }

        .instagram-vaporwave-green .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #21BA45;
            text-shadow: 0 0 20px rgba(33, 186, 69, 0.8);
        }

        .instagram-vaporwave-green .first-place-card::before {
            background: linear-gradient(145deg, #B1C672 0%, #21BA45 50%, #B1C672 100%);
            box-shadow: 
                0 0 50px rgba(177, 198, 114, 1),
                0 0 100px rgba(33, 186, 69, 0.7);
        }

        /* === LOST WOOD THEME === */
        .instagram-lostwood {
            background: #07241B;
        }

        .instagram-lostwood::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 50%, rgba(61, 105, 74, 0.15) 0%, transparent 70%);
            z-index: 1;
        }

        .instagram-lostwood::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 35px solid transparent;
            border-image: linear-gradient(135deg, 
                rgba(139, 69, 19, 0.5) 0%, 
                rgba(61, 105, 74, 0.4) 50%,
                rgba(139, 69, 19, 0.5) 100%) 35;
            z-index: 5;
            pointer-events: none;
        }

        .instagram-lostwood .content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 80px 60px 60px;
        }

        .instagram-lostwood .header {
            text-align: center;
            margin-bottom: 40px;
            z-index: 3;
        }

        .instagram-lostwood .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 48px;
            font-weight: 900;
            color: #B1C672;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.7);
            margin-bottom: 15px;
        }

        .instagram-lostwood .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: #E8F5E9;
        }

        .instagram-lostwood .first-place-card::before {
            background: linear-gradient(145deg, #B1C672 0%, #8fb657 50%, #B1C672 100%);
            box-shadow: 
                0 0 30px rgba(177, 198, 114, 0.6),
                0 15px 40px rgba(0, 0, 0, 0.8);
        }

        .instagram-lostwood .place-item.silver .place-card::before {
            background: linear-gradient(135deg, #8fb657 0%, #6a8f44 50%, #8fb657 100%);
        }

        .instagram-lostwood .place-item.bronze .place-card::before {
            background: linear-gradient(135deg, #6a8f44 0%, #3D694A 50%, #6a8f44 100%);
        }

        .instagram-lostwood .place-item.fourth .place-card::before {
            background: linear-gradient(135deg, #8fb657 0%, #7a9e4a 50%, #8fb657 100%);
        }

        /* === BOOTSTRAP CLASSIC THEME === */
        .instagram-bootscore {
            background: #ffffff;
        }

        .instagram-bootscore::after {
            content: '';
            position: absolute;
            inset: 0;
            border: 20px solid #dee2e6;
            z-index: 5;
            pointer-events: none;
        }

        .instagram-bootscore .content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 80px 60px 60px;
        }

        .instagram-bootscore .header {
            text-align: center;
            margin-bottom: 40px;
            z-index: 3;
        }

        .instagram-bootscore .event-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 48px;
            font-weight: 900;
            color: var(--bs-primary, #0d6efd);
            margin-bottom: 15px;
        }

        .instagram-bootscore .subtitle {
            font-family: 'Montserrat', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--bs-secondary, #6c757d);
        }

        .instagram-bootscore .event-meta {
            color: #495057;
        }

        .instagram-bootscore .first-place-card::before {
            background: linear-gradient(145deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .instagram-bootscore .first-place-position {
            color: #ffc107 !important;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .instagram-bootscore .first-place-player,
        .instagram-bootscore .place-player {
            color: #212529 !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .instagram-bootscore .first-place-commanders,
        .instagram-bootscore .place-commanders {
            color: #6c757d !important;
            text-shadow: none;
        }

        .instagram-bootscore .place-position {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Selectors Area -->
        <div class="selectors-container mb-4">
            <div class="row align-items-end g-3">
                <div class="col-md-6">
                    <label for="ig-theme-select" class="form-label fw-bold">🎨 Select Theme</label>
                    <select id="ig-theme-select" class="form-select form-select-lg">
                        <option value="instagram-fantasy" selected>🏰 Epic Fantasy</option>
                        <option value="instagram-vaporwave">🌸 Vaporwave</option>
                        <option value="instagram-vaporwave-green">💚 Vaporwave Green</option>
                        <option value="instagram-lostwood">🌲 Lost Wood</option>
                        <option value="instagram-bootscore">📘 Bootstrap Classic</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="ig-type-select" class="form-label fw-bold">🏆 Select Type</label>
                    <select id="ig-type-select" class="form-select form-select-lg">
                        <option value="top3" <?php selected($ig_type, 'top3'); ?>>Top 3</option>
                        <option value="top4" <?php selected($ig_type, 'top4'); ?>>Top 4 (Default)</option>
                        <option value="top8" <?php selected($ig_type, 'top8'); ?>>Top 8</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Image Preview -->
        <div class="instagram-image instagram-fantasy" id="ig-image">
            <div class="content">
                <!-- Header -->
                <div class="header">
                    <div class="event-title">
                        <?php echo esc_html($event_title); ?>
                    </div>
                    <div class="subtitle"><?php echo strtoupper(str_replace('top', 'TOP ', $ig_type)); ?> DECKLISTS</div>
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

                <!-- Dynamic Template Loading -->
                <?php 
                $template_file = __DIR__ . "/ig-template/{$ig_type}.php";
                if (file_exists($template_file)) {
                    include($template_file);
                } else {
                    echo '<p class="text-white text-center">Template not found: ' . esc_html($ig_type) . '</p>';
                }
                ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <button id="download-btn" class="btn btn-primary btn-lg">
                📥 Download Image
            </button>
            <a href="<?php echo esc_url(get_permalink($event_id)); ?>" class="btn btn-secondary btn-lg">
                ← Back to Event
            </a>
        </div>
    </div>

    <!-- html2canvas Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        // Theme Switcher
        document.getElementById('ig-theme-select').addEventListener('change', function() {
            const theme = this.value;
            const container = document.getElementById('ig-image');
            
            // Remove existing theme classes
            container.className = 'instagram-image ' + theme;
        });

        // Type Switcher
        document.getElementById('ig-type-select').addEventListener('change', function() {
            const type = this.value;
            const theme = document.getElementById('ig-theme-select').value;
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('ig_type', type);
            // Optionally persist theme in URL if you want it to survive refresh
            // currentUrl.searchParams.set('ig_theme', theme); 
            window.location.href = currentUrl.toString();
        });

        // Download Image
        document.getElementById('download-btn').addEventListener('click', function() {
            const button = this;
            const originalText = button.innerHTML;
            
            // Show loading state
            button.disabled = true;
            button.innerHTML = '⏳ Generating...';
            
            const element = document.getElementById('ig-image');
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                allowTaint: false,
                backgroundColor: null,
                width: 1080,
                height: 1350,
                logging: false
            }).then(canvas => {
                // Convert canvas to blob
                canvas.toBlob(function(blob) {
                    // Create download link
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.download = 'instagram-top4-<?php echo sanitize_title($event_title); ?>.png';
                    link.href = url;
                    link.click();
                    
                    // Cleanup
                    URL.revokeObjectURL(url);
                    
                    // Reset button
                    button.disabled = false;
                    button.innerHTML = originalText;
                }, 'image/png');
            }).catch(function(error) {
                console.error('Error generating image:', error);
                console.error('Error details:', error.message, error.stack);
                alert('Error generating image: ' + (error.message || 'Unknown error') + '\n\nCheck console for details.');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });
    </script>
</body>

</html>