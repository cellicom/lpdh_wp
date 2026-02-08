<?php
/**
 * Easter Eggs Functionality
 * 
 * This file contains all Easter egg related features including:
 * - Client-side script enqueuing and localization
 * - AJAX handlers for Easter egg interactions
 * 
 * @package LPDH_WordPress_Theme
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Easter Egg JavaScript and localize data
 */
function lpdh_enqueue_easter_egg_scripts()
{
    // Easter Egg
    $modified_EasterEggJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/easter_egg.js'));
    wp_enqueue_script('easter-egg-js', get_stylesheet_directory_uri() . '/assets/js/easter_egg.js', array('jquery'), $modified_EasterEggJS, true);
    wp_localize_script('easter-egg-js', 'lpdh_objects', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lpdh_easter_egg_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'lpdh_enqueue_easter_egg_scripts');

/**
 * AJAX handler for checking search results existence (Easter Egg)
 */
function lpdh_ajax_check_search_results()
{
    check_ajax_referer('lpdh_easter_egg_nonce', 'nonce');

    $search_query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';

    if (empty($search_query)) {
        wp_send_json_success(array('has_results' => false));
    }

    $query = new WP_Query(array(
        's' => $search_query,
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'fields' => 'ids' // Only need to see if any exist
    ));

    wp_send_json_success(array(
        'has_results' => $query->have_posts(),
        'count' => $query->found_posts
    ));
}
add_action('wp_ajax_check_search_results', 'lpdh_ajax_check_search_results');
add_action('wp_ajax_nopriv_check_search_results', 'lpdh_ajax_check_search_results');
