<?php
/**
 * LPDH Easter Egg Functions
 *
 * Handles the enqueuing and logic for site-wide easter eggs.
 */

/**
 * Enqueue Easter Egg Scripts
 */
function lpdh_enqueue_easter_eggs() {
    // Vaporwave Easter Eggs (Conditional)
    $active_theme = get_option('lpdh_active_theme');
    if (in_array($active_theme, ['vaporwave', 'vaporwave-green'])) {
        $modified_EasterEggJS = file_exists(get_stylesheet_directory() . '/assets/js/easter_egg.js') ? date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/easter_egg.js')) : '1.0.0';
        wp_enqueue_script('lpdh-easter-egg', get_stylesheet_directory_uri() . '/assets/js/easter_egg.js', array('jquery'), $modified_EasterEggJS, true);

        // Localize Audio Paths
        wp_localize_script('lpdh-easter-egg', 'lpdh_egg_vars', array(
            'audio_on' => get_stylesheet_directory_uri() . '/assets/audio/tv-on.mp3',
            'audio_off' => get_stylesheet_directory_uri() . '/assets/audio/tv-off.mp3'
        ));
    }
}
add_action('wp_enqueue_scripts', 'lpdh_enqueue_easter_eggs');
