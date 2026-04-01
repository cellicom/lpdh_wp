<?php
/**
 * Instagram Image Generator Functions
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Register Instagram Generator Admin Page as submenu under LPDH
 */
function lpdh_register_instagram_generator_page()
{
    add_submenu_page(
        'lpdh-main',
        'Instagram Generator',
        'Instagram Generator',
        'manage_options',
        'lpdh-instagram-generator',
        'lpdh_render_instagram_generator_page'
    );
}
add_action('admin_menu', 'lpdh_register_instagram_generator_page');

/**
 * Render Instagram Generator Admin Page
 */
function lpdh_render_instagram_generator_page()
{
    // Security check - admin only
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.'));
    }

    // Check if event ID is provided
    $event_id = isset($_GET['ig_event_id']) ? intval($_GET['ig_event_id']) : 0;

    if (!$event_id) {
        echo '<div class="wrap">';
        echo '<h1>Instagram Generator</h1>';
        echo '<div class="notice notice-warning"><p>Please select an event to generate Instagram images.</p></div>';
        echo '<p><a href="' . admin_url('edit.php?post_type=event') . '" class="button button-primary">Go to Events</a></p>';
        echo '</div>';
        return;
    }

    // Validate event
    $event = get_post($event_id);
    if (!$event || $event->post_type !== 'event') {
        echo '<div class="wrap">';
        echo '<h1>Instagram Generator</h1>';
        echo '<div class="notice notice-error"><p>Invalid event ID.</p></div>';
        echo '<p><a href="' . admin_url('edit.php?post_type=event') . '" class="button button-primary">Go to Events</a></p>';
        echo '</div>';
        return;
    }

    // Define admin context to prevent redirects in template
    if (!defined('LPDH_IG_ADMIN_CONTEXT')) {
        define('LPDH_IG_ADMIN_CONTEXT', true);
    }

    // Include the Instagram Generator page template
    // This page contains all the HTML, CSS, and JavaScript needed
    $template_path = get_stylesheet_directory() . '/page-templates/page-instagram-generator.php';

    if (file_exists($template_path)) {
        // The template handles its own output
        include($template_path);
    } else {
        echo '<div class="wrap">';
        echo '<h1>Instagram Generator</h1>';
        echo '<div class="notice notice-error"><p>Instagram Generator template not found.</p></div>';
        echo '</div>';
    }
}

/**
 * Get Instagram Generator URL for Event
 */
function lpdh_get_instagram_generator_url($event_id)
{
    // Return admin page URL instead of frontend page
    return admin_url('admin.php?page=lpdh-instagram-generator&ig_event_id=' . intval($event_id));
}

/**
 * Add Instagram Generator Metabox to Events
 */
function lpdh_add_instagram_generator_metabox()
{
    add_meta_box(
        'lpdh_instagram_generator',
        'Instagram Image Generator',
        'lpdh_render_instagram_generator_metabox',
        'event',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'lpdh_add_instagram_generator_metabox');

/**
 * Render Instagram Generator Metabox
 */
function lpdh_render_instagram_generator_metabox($post)
{
    $ig_url = lpdh_get_instagram_generator_url($post->ID);

    if ($ig_url) {
        echo '<p>Generate a promotional Instagram image for this event\'s top players.</p>';
        echo '<a href="' . esc_url($ig_url) . '" class="button button-primary button-large" style="width: 100%; text-align: center; display: block;">';
        echo '<span class="dashicons dashicons-instagram" style="margin-top: 3px;"></span> ';
        echo 'Generate Instagram Image</a>';
    } else {
        echo '<p class="description">Instagram generator page not configured.</p>';
    }
}
