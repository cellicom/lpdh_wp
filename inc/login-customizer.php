<?php
/**
 * LPDH Login Customizer
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Enqueue custom login styles and logo
 */
function lpdh_login_enqueue_scripts() {
    $logo_url = lpdh_get_logo();
    $active_theme = get_option('lpdh_active_theme', 'default');
    
    wp_enqueue_style('lpdh-login-style', get_stylesheet_directory_uri() . '/assets/css/login-style.css', array(), '1.0.1');

    // Dynamic Colors and background based on Active Theme
    $brand_color = '#00733e'; // Default Bootscore green
    $brand_hover = '#004d29';
    $bg_img = 'default.png'; 

    if ($active_theme === 'vaporwave') {
        $brand_color = '#ff00ff'; // Neon pink
        $brand_hover = '#bc13fe';
        $bg_img = 'vaporwave.png';
    } elseif ($active_theme === 'vaporwave-green') {
        $brand_color = '#39ff14'; // Neon green
        $brand_hover = '#32cd32';
        $bg_img = 'vaporwave-green.png';
    } elseif ($active_theme === 'lost-wood') {
        $brand_color = '#2d5a27'; // Dark green
        $brand_hover = '#1e3d1a';
        $bg_img = 'lost-wood.png'; 
    }

    $bg_url = get_stylesheet_directory_uri() . '/assets/img/login-bg/' . $bg_img;

    $inline_css = "
        :root {
            --lpdh-login-logo: url('{$logo_url}');
            --lpdh-login-bg-img: url('{$bg_url}');
            --lpdh-login-brand: {$brand_color};
            --lpdh-login-brand-hover: {$brand_hover};
        }
    ";

    wp_add_inline_style('lpdh-login-style', $inline_css);
}
add_action('login_enqueue_scripts', 'lpdh_login_enqueue_scripts');

/**
 * Add custom body class for layout toggle
 */
function lpdh_login_body_class($classes) {
    if (get_option('lpdh_enable_custom_login', 0)) {
        $classes[] = 'lpdh-custom-login';
    }
    return $classes;
}
add_filter('login_body_class', 'lpdh_login_body_class');

/**
 * Change Logo URL to Home
 */
function lpdh_login_headerurl() {
    return home_url();
}
add_filter('login_headerurl', 'lpdh_login_headerurl');

/**
 * Change Logo title
 */
function lpdh_login_headertext() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'lpdh_login_headertext');
