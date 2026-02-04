<?php
/**
 * Admin Color Schemes Functions
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Register custom admin color schemes
 * All color schemes with their RGB values converted to HEX
 */
function register_custom_admin_color_schemes() {
    // === Mono Verde ===
    // rgb(0,115,62) = #00733e, rgb(196,211,202) = #c4d3ca
    wp_admin_css_color(
        'mono-verde',
        _x('Mono Verde', 'admin color scheme'),
        trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/admin-colors-mono-verde.css',
        array('#00733e', '#c4d3ca', '#004d29', '#ffffff'),
        array('base' => '#00733e', 'focus' => '#c4d3ca', 'current' => '#00733e')
    );

    // === Mono Blu ===
    // rgb(179,206,234) = #b3ceea, rgb(14,104,171) = #0e68ab
    wp_admin_css_color(
        'mono-blu',
        _x('Mono Blu', 'admin color scheme'),
        trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/admin-colors-mono-blu.css',
        array('#0e68ab', '#b3ceea', '#084a7a', '#ffffff'),
        array('base' => '#0e68ab', 'focus' => '#b3ceea', 'current' => '#0e68ab')
    );

    // === Mono Rosso ===
    // rgb(235,159,130) = #eb9f82, rgb(211,32,42) = #d3202a
    wp_admin_css_color(
        'mono-rosso',
        _x('Mono Rosso', 'admin color scheme'),
        trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/admin-colors-mono-rosso.css',
        array('#d3202a', '#eb9f82', '#9e181f', '#ffffff'),
        array('base' => '#d3202a', 'focus' => '#eb9f82', 'current' => '#d3202a')
    );

    // === Mono Bianco ===
    // rgb(248,231,185) = #f8e7b9, rgb(249,250,244) = #f9faf4
    wp_admin_css_color(
        'mono-bianco',
        _x('Mono Bianco', 'admin color scheme'),
        trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/admin-colors-mono-bianco.css',
        array('#f9faf4', '#f8e7b9', '#c9b885', '#212529'),
        array('base' => '#f9faf4', 'focus' => '#f8e7b9', 'current' => '#f9faf4')
    );

    // === Mono Nero ===
    // rgb(166,159,157) = #a69f9d, rgb(21,11,0) = #150b00
    wp_admin_css_color(
        'mono-nero',
        _x('Mono Nero', 'admin color scheme'),
        trailingslashit(get_stylesheet_directory_uri()) . 'assets/css/admin-colors-mono-nero.css',
        array('#150b00', '#a69f9d', '#0a0500', '#ffffff'),
        array('base' => '#150b00', 'focus' => '#a69f9d', 'current' => '#150b00')
    );
}
add_action('admin_init', 'register_custom_admin_color_schemes');

/**
 * Remove default WordPress admin color schemes
 * Only keep "Default" and our custom color schemes
 */
function remove_default_admin_color_schemes() {
    global $_wp_admin_css_colors;
    
    // Remove non-default color schemes - do this after all schemes are registered
    $schemes_to_remove = array('light', 'modern', 'blue', 'coffee', 'ectoplasm', 'midnight', 'ocean', 'sunrise');
    
    foreach ($schemes_to_remove as $scheme) {
        if (isset($_wp_admin_css_colors[$scheme])) {
            unset($_wp_admin_css_colors[$scheme]);
        }
    }
}
// Run this later to ensure all schemes are registered
add_action('admin_init', 'remove_default_admin_color_schemes', 999);

/**
 * Filter the color schemes shown in the admin color picker
 * Only show our custom schemes and the default one
 */
function filter_admin_color_schemes($to_return) {
    global $_wp_admin_css_colors;
    
    // Get our custom scheme keys
    $our_schemes = array('mono-verde', 'mono-blu', 'mono-rosso', 'mono-bianco', 'mono-nero');
    
    // Build an array with only our schemes plus default
    $allowed_schemes = array();
    
    // Always include default
    if (isset($_wp_admin_css_colors['fresh'])) {
        $allowed_schemes['fresh'] = $_wp_admin_css_colors['fresh'];
    }
    
    // Add only our custom schemes
    foreach ($our_schemes as $scheme_key) {
        if (isset($_wp_admin_css_colors[$scheme_key])) {
            $allowed_schemes[$scheme_key] = $_wp_admin_css_colors[$scheme_key];
        }
    }
    
    // Replace the global with our filtered list
    $_wp_admin_css_colors = $allowed_schemes;
    
    return $allowed_schemes;
}
add_filter('admin_color_scheme_picker', 'filter_admin_color_schemes');

/**
 * Hide unwanted color schemes with JavaScript as additional fallback
 */
function hide_unwanted_color_schemes_js() {
    // Only apply on user profile page
    if (!current_user_can('edit_users')) {
        return;
    }
    
    global $pagenow;
    if ($pagenow !== 'profile.php' && $pagenow !== 'user-edit.php') {
        return;
    }
    
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hide unwanted color schemes by their index (0-based)
        // Default is at the beginning, then unwanted schemes
        var colorWraps = document.querySelectorAll('.user-admin-color-wrap');
        var schemeNames = ['Light', 'Modern', 'Blue', 'Coffee', 'Ectoplasm', 'Midnight', 'Ocean', 'Sunrise'];
        
        colorWraps.forEach(function(wrap) {
            var label = wrap.querySelector('label');
            if (label) {
                var text = label.textContent.trim();
                if (schemeNames.indexOf(text) !== -1) {
                    wrap.style.display = 'none';
                }
            }
        });
    });
    </script>
    <?php
}
add_action('admin_footer', 'hide_unwanted_color_schemes_js');

/**
 * Create admin color scheme CSS files for all custom color schemes
 * This ensures the CSS files are generated if they don't exist
 */
function ensure_admin_color_css_exists() {
    $css_dir = get_stylesheet_directory() . '/assets/css';
    
    // Create directory if it doesn't exist
    if (!is_dir($css_dir)) {
        mkdir($css_dir, 0755, true);
    }
    
    // Define all color schemes with their RGB values converted to HEX
    $color_schemes = array(
        'mono-verde' => array(
            'base' => '#00733e',    // rgb(0,115,62)
            'light' => '#c4d3ca',   // rgb(196,211,202)
            'darker' => '#004d29',
            'text' => '#ffffff',
        ),
        'mono-blu' => array(
            'base' => '#0e68ab',    // rgb(14,104,171)
            'light' => '#b3ceea',   // rgb(179,206,234)
            'darker' => '#084a7a',
            'text' => '#ffffff',
        ),
        'mono-rosso' => array(
            'base' => '#d3202a',    // rgb(211,32,42)
            'light' => '#eb9f82',   // rgb(235,159,130)
            'darker' => '#9e181f',
            'text' => '#ffffff',
        ),
        'mono-bianco' => array(
            'base' => '#f9faf4',    // rgb(249,250,244)
            'light' => '#f8e7b9',   // rgb(248,231,185)
            'darker' => '#c9b885',
            'text' => '#212529',
        ),
        'mono-nero' => array(
            'base' => '#150b00',    // rgb(21,11,0)
            'light' => '#a69f9d',   // rgb(166,159,157)
            'darker' => '#0a0500',
            'text' => '#ffffff',
        ),
    );
    
    // Generate CSS for each scheme
    foreach ($color_schemes as $scheme_name => $colors) {
        $css_file_path = $css_dir . '/admin-colors-' . $scheme_name . '.css';
        
        // Only create if file doesn't exist
        if (!file_exists($css_file_path)) {
            $css_content = generate_admin_color_css($scheme_name, $colors);
            file_put_contents($css_file_path, $css_content);
        }
    }
}

/**
 * Also ensure CSS files are created on theme activation
 */
function custom_admin_colors_on_theme_activation() {
    ensure_admin_color_css_exists();
}
add_action('after_switch_theme', 'custom_admin_colors_on_theme_activation');

/**
 * Generate CSS content for an admin color scheme
 */
add_action('admin_init', 'ensure_admin_color_css_exists', 5);
function generate_admin_color_css($scheme_name, $colors) {
    $base = $colors['base'];
    $light = $colors['light'];
    $darker = $colors['darker'];
    $text = $colors['text'];
    
    $css_content = <<<CSS
/* {$scheme_name} Admin Color Scheme */
/* Based on {$base} and {$light} */

:root {
    --{$scheme_name}-base: {$base};
    --{$scheme_name}-light: {$light};
    --{$scheme_name}-darker: {$darker};
    --{$scheme_name}-text: {$text};
}

/* Admin Bar */
#wpadminbar {
    background-color: {$base} !important;
}

#wpadminbar .ab-top-menu > li > a,
#wpadminbar .ab-top-menu > li > .ab-item {
    color: {$text} !important;
}

#wpadminbar .ab-top-menu > li.hover > .ab-item,
#wpadminbar .ab-top-menu > li > a:focus,
#wpadminbar .ab-top-menu > li:hover > .ab-item {
    background-color: {$darker} !important;
}

/* Admin Menu */
#adminmenu,
#adminmenu .wp-submenu,
#adminmenuback,
#adminmenuwrap {
    background-color: {$base} !important;
}

#adminmenu li.menu-top {
    border-bottom: 1px solid {$light}20 !important;
}

#adminmenu .wp-menu-name {
    color: {$text} !important;
}

#adminmenu .wp-menu-image img {
    filter: brightness(0) invert(1);
}

/* Menu hover and active states */
#adminmenu li.menu-top:hover,
#adminmenu li.opensub > a.menu-top,
#adminmenu li > a.menu-top:focus {
    background-color: {$darker} !important;
}

#adminmenu li.current a.menu-top,
#adminmenu li.wp-has-current-submenu .wp-submenu .wp-submenu-head,
#adminmenu li.wp-has-current-submenu a.menu-top,
#adminmenu li.current .menu-item-top {
    background-color: {$light} !important;
}

#adminmenu li.current .menu-item-top .menu-item-icon {
    background-color: {$light} !important;
}

/* Submenu */
#adminmenu .wp-submenu {
    border-left: 3px solid {$light} !important;
}

#adminmenu .wp-submenu a {
    color: {$text}99 !important;
}

#adminmenu .wp-submenu a:hover,
#adminmenu .wp-submenu a:focus {
    color: {$text} !important;
}

/* Buttons */
.button-primary {
    background-color: {$base} !important;
    border-color: {$darker} !important;
    color: {$text} !important;
}

.button-primary:hover,
.button-primary:focus {
    background-color: {$darker} !important;
    border-color: {$base} !important;
}

/* Misc admin elements */
.wp-color-result {
    background-color: {$base} !important;
    border-color: {$darker} !important;
}

.wp-color-result-text {
    color: {$text} !important;
}

/* Login page accents */
.login #login .button-primary {
    background-color: {$base} !important;
    border-color: {$base} !important;
}

/* Focus states */
input:focus,
select:focus,
textarea:focus {
    border-color: {$base} !important;
    box-shadow: 0 0 0 1px {$base} !important;
}

/* Links */
#adminmenu a:hover,
#adminmenu a:focus {
    color: {$light} !important;
}

/* Tabs */
.wp-ui-text-icon {
    color: {$base} !important;
}

/* Notifications */
.update-plugins {
    background-color: {$light} !important;
    color: {$darker} !important;
}

/* Quicktags */
.quicktags-toolbar {
    background-color: {$base} !important;
}
CSS;
    
    return $css_content;
}