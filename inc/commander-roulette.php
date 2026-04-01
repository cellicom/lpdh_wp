<?php
/**
 * Commander Roulette Functions
 * AJAX handlers and helper functions for the Commander Roulette feature.
 *
 * @package lpdh-wordpress
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Commander Roulette AJAX Handler
 */
function lpdh_spin_roulette()
{
    // Check Nonce
    check_ajax_referer('lpdh_spin_nonce', 'nonce');

    // Check Login
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to spin the wheel.'));
    }

    $user_id = get_current_user_id();
    $today = date('Y-m-d');
    $last_spin_date = get_user_meta($user_id, 'lpdh_last_spin_date', true);
    $spins_today = intval(get_user_meta($user_id, 'lpdh_spins_today', true));
    $daily_limit = 3;
    $is_admin = current_user_can('manage_options');

    // Reset drops if new day
    if ($last_spin_date !== $today) {
        $spins_today = 0;
        update_user_meta($user_id, 'lpdh_last_spin_date', $today);
        update_user_meta($user_id, 'lpdh_spins_today', 0);
    }

    // Check Limit
    if (!$is_admin && $spins_today >= $daily_limit) {
        wp_send_json_error(array('message' => 'You have used all your spins for today. Come back tomorrow!'));
    }

    // --- Scryfall API Fetch ---
    $query = '(type:creature type:legendary) (game:paper) rarity:uncommon';
    $banned_cards = lpdh_get_banned_card_names();
    if (!empty($banned_cards)) {
        foreach ($banned_cards as $card) {
            // Decode entities and escape quotes
            $card_clean = str_replace('"', '', html_entity_decode($card));
            $query .= ' -!"' . $card_clean . '"';
        }
    }

    $api_url = 'https://api.scryfall.com/cards/random?q=' . urlencode($query);
    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        error_log('LPDH Roulette API Error: ' . $response->get_error_message());
        wp_send_json_error(array('message' => 'System Error: Unable to connect to Card Database.'));
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $card_data = json_decode($body, true);

    if ($response_code !== 200 || !$card_data || (isset($card_data['object']) && $card_data['object'] === 'error')) {
        error_log('LPDH Roulette Scryfall Error Code: ' . $response_code);
        error_log('LPDH Roulette Scryfall Response: ' . $body);
        error_log('LPDH Roulette Query URL: ' . $api_url); // Log the constructed URL for debugging

        $msg = isset($card_data['details']) ? $card_data['details'] : 'Unknown Scryfall Error';
        wp_send_json_error(array('message' => 'Scryfall Error: ' . $msg));
    }

    // --- Update Token Count ---
    if (!$is_admin) {
        $spins_today++;
        update_user_meta($user_id, 'lpdh_spins_today', $spins_today);
    }

    // Update Lifetime Spins (Everyone counts for achievements and stats)
    $lifetime_spins = intval(get_user_meta($user_id, 'lpdh_lifetime_spins', true));
    update_user_meta($user_id, 'lpdh_lifetime_spins', $lifetime_spins + 1);

    // Return Data
    wp_send_json_success(array(
        'card' => $card_data,
        'remaining_spins' => $is_admin ? 999 : ($daily_limit - $spins_today),
        'total_spins' => $spins_today
    ));
}
add_action('wp_ajax_lpdh_spin_roulette', 'lpdh_spin_roulette');

/**
 * Helper to get current spin stats for frontend
 */
function lpdh_get_spin_stats($user_id)
{
    if (!$user_id) {
        return array('remaining' => 0, 'limit' => 3, 'is_admin' => false);
    }

    $today = date('Y-m-d');
    $last_spin_date = get_user_meta($user_id, 'lpdh_last_spin_date', true);
    $spins_today = intval(get_user_meta($user_id, 'lpdh_spins_today', true));

    if ($last_spin_date !== $today) {
        $spins_today = 0;
    }

    $is_admin = user_can($user_id, 'manage_options');
    $limit = 3;
    $remaining = $is_admin ? 999 : max(0, $limit - $spins_today);

    return array(
        'remaining' => $remaining,
        'limit' => $limit,
        'is_admin' => $is_admin
    );
}
