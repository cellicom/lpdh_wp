<?php
/**
 * @package Bootscore Child
 *
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Dependency Check: Advanced Custom Fields PRO
 * This theme requires ACF Pro to function correctly.
 */
function lpdh_check_dependencies()
{
    if (!class_exists('acf')) {
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php _e('LPDH Theme Warning:', 'text_domain'); ?></strong>
                    <?php _e('Advanced Custom Fields PRO is NOT active. This theme requires ACF PRO for data management, modular page sections, and core settings. Please <a href="https://www.advancedcustomfields.com/pro/" target="_blank">install and activate it</a> to ensure the site works correctly.', 'text_domain'); ?>
                </p>
            </div>
            <?php
        });
    }

    if (!function_exists('bs_cookie_settings')) {
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php _e('LPDH Theme Warning:', 'text_domain'); ?></strong>
                    <?php _e('bs Cookie Settings is NOT active. This theme requires this plugin for cookie consent compliance and preferences management. Please <a href="https://bootscore.me/documentation/bs-cookie-settings/" target="_blank">install and activate it</a>.', 'text_domain'); ?>
                </p>
            </div>
            <?php
        });
    }

    // Check for ACF Font Awesome
    // We check for the version constant which is reliable for this plugin
    if (!defined('ACFFA_VERSION')) {
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error is-dismissible">
                <p><strong><?php _e('LPDH Theme Warning:', 'text_domain'); ?></strong>
                    <?php _e('ACF Font Awesome is NOT active. This theme requires this plugin for achievement icons. Please <a href="https://wordpress.org/plugins/advanced-custom-fields-font-awesome/" target="_blank">install and activate it</a>.', 'text_domain'); ?>
                </p>
            </div>
            <?php
        });
    }
}
add_action('admin_init', 'lpdh_check_dependencies');

/**
 * Theme Setup
 * Add support for WordPress features
 */
function lpdh_theme_setup()
{
    // Add custom logo support (manageable via Appearance > Customize > Site Identity)
    add_theme_support('custom-logo', array(
        'height' => 100,
        'width' => 300,
        'flex-height' => true,
        'flex-width' => true,
    ));
}
add_action('after_setup_theme', 'lpdh_theme_setup');

/**
 * Get Logo URL with Priority Logic
 * 
 * Returns the logo URL in the following priority order:
 * 1. Custom logo from Theme Settings (lpdh_custom_logo_id)
 * 2. WordPress Customizer logo (custom-logo theme mod)
 * 3. Default hardcoded logo fallback
 * 
 * @return string Logo URL
 */
function lpdh_get_logo()
{
    // Priority 1: Check Theme Settings custom logo
    $custom_logo_id = get_option('lpdh_custom_logo_id');
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo_url) {
            return $logo_url;
        }
    }

    // Priority 2: Check WordPress Customizer logo
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo_url) {
            return $logo_url;
        }
    }

    // Priority 3: Default fallback logo
    return get_stylesheet_directory_uri() . '/assets/img/logo/logo-lpdh-ext-transparent.png';
}

/**
 * Register the Co-Administrator Role
 * This role has full content management power but cannot touch system-level settings.
 */
function lpdh_register_co_administrator_role()
{
    $admin_role = get_role('administrator');
    if (!$admin_role) {
        return;
    }

    $caps = $admin_role->capabilities;

    // List of capabilities to REMOVE for Co-Admins
    $blacklisted_caps = array(
        'switch_themes',
        'edit_themes',
        'activate_plugins',
        'edit_plugins',
        'manage_options',
        'import',
        'export',
        'manage_acf_options', // ACF specific
        'update_core',
        'update_plugins',
        'update_themes',
        'install_plugins',
        'install_themes',
        'delete_themes',
        'delete_plugins',
        'edit_files',
        'edit_plugins',
        'edit_themes',
    );

    foreach ($blacklisted_caps as $cap) {
        unset($caps[$cap]);
    }

    // Ensure they HAVE the basic content management caps explicitly
    $essential_caps = array(
        'edit_posts',
        'edit_others_posts',
        'edit_published_posts',
        'edit_private_posts',
        'publish_posts',
        'read_private_posts',
        'delete_posts',
        'delete_others_posts',
        'delete_published_posts',
        'delete_private_posts',
        'edit_pages',
        'edit_others_pages',
        'edit_published_pages',
        'edit_private_pages',
        'publish_pages',
        'read_private_pages',
        'delete_pages',
        'delete_others_pages',
        'delete_published_pages',
        'delete_private_pages',
        'upload_files',
        'unfiltered_html'
    );

    foreach ($essential_caps as $ecap) {
        $caps[$ecap] = true;
    }

    // Add specific custom caps for LPDH features
    $caps['view_lpdh_help_guide'] = true;
    $caps['manage_lpdh_content'] = true;

    // Ensure Administrator also has these custom caps
    if (!$admin_role->has_cap('manage_lpdh_content')) {
        $admin_role->add_cap('manage_lpdh_content');
    }
    if (!$admin_role->has_cap('view_lpdh_help_guide')) {
        $admin_role->add_cap('view_lpdh_help_guide');
    }

    // Check if role exists to update or create
    if (get_role('co_administrator')) {
        $role = get_role('co_administrator');
        foreach ($caps as $cap => $grant) {
            if ($grant) {
                $role->add_cap($cap);
            } else {
                $role->remove_cap($cap);
            }
        }
    } else {
        add_role('co_administrator', __('Co-Administrator', 'text_domain'), $caps);
    }
}
add_action('init', 'lpdh_register_co_administrator_role');

/**
 * Restrict editable roles for Co-Administrators
 * This prevents them from promoting anyone (including themselves) to full Administrator.
 */
function lpdh_restrict_editable_roles($all_roles)
{
    if (!current_user_can('administrator') && current_user_can('co_administrator')) {
        if (isset($all_roles['administrator'])) {
            unset($all_roles['administrator']);
        }
    }
    return $all_roles;
}
add_filter('editable_roles', 'lpdh_restrict_editable_roles');

/**
 * Prevent Co-Administrators from accessing ACF menu specifically
 */
add_filter('acf/settings/show_admin', function ($show) {
    if (current_user_can('co_administrator') && !current_user_can('administrator')) {
        return false;
    }
    return $show;
});

/**
 * Centered Helper Function to check if user can manage LPDH content
 * (Administrators and Co-Administrators)
 */
function lpdh_can_manage_content()
{
    return current_user_can('administrator') || current_user_can('manage_lpdh_content');
}



/**
 * Adjust Brightness of HEX Color
 * Used for Gradient Generation
 * 
 * @param string $hex
 * @param int $steps (-255 to 255)
 * @return string
 */
function lpdh_adjust_brightness($hex, $steps)
{
    // Steps should be between -255 and 255. Negative = darker, Positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize HEX
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into R, G, B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust brightness
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return $return;
}

// Include Achievements System
require_once get_stylesheet_directory() . '/inc/achievements.php';

// Include ACF Field Groups
require_once get_stylesheet_directory() . '/inc/acfbox.php';

// Include Easter Eggs
require_once get_stylesheet_directory() . '/inc/easter-eggs.php';

// Include ELO Calculation System
require_once get_stylesheet_directory() . '/inc/elo.php';

// Include Banned Card System
require_once get_stylesheet_directory() . '/inc/banned-card.php';

// Include Deck CPT & Player Role
require_once get_stylesheet_directory() . '/inc/deck.php';

// Include Leaderboard CPT
require_once get_stylesheet_directory() . '/inc/leaderboard.php';

// Include Stats System
require_once get_stylesheet_directory() . '/inc/stats.php';

// Include Place CPT
require_once get_stylesheet_directory() . '/inc/place.php';

// Include FAQ CPT
require_once get_stylesheet_directory() . '/inc/faq.php';

// Include Email System
require_once get_stylesheet_directory() . '/inc/functions-email.php';

// Include Event CPT
require_once get_stylesheet_directory() . '/inc/events.php';

// Include Instagram Generator
require_once get_stylesheet_directory() . '/inc/instagram-generator.php';

// Include Commander Roulette
require_once get_stylesheet_directory() . '/inc/commander-roulette.php';

// Include Discord Integration
require_once get_stylesheet_directory() . '/inc/discord.php';

// Include Easter Egg Functions
require_once get_stylesheet_directory() . '/inc/easter-egg.php';


/**
 * Enqueue scripts and styles
 */
add_action('wp_enqueue_scripts', 'bootscore_child_enqueue_styles');

// Include Login Customizer
require_once get_stylesheet_directory() . '/inc/login-customizer.php';
function bootscore_child_enqueue_styles()
{

    // Fonts CSS
    $modified_fontsCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/css/fonts.css'));
    wp_enqueue_style('fonts', get_stylesheet_directory_uri() . '/assets/css/fonts.css', array(), $modified_fontsCss);

    // Compiled main.css (depends on parent-style and fonts to load after font definitions)
    $main_css_path = get_stylesheet_directory() . '/assets/css/main.css';
    $modified_bootscoreChildCss = file_exists($main_css_path) ? date('YmdHi', filemtime($main_css_path)) : '1.0.0';
    wp_enqueue_style('main', get_stylesheet_directory_uri() . '/assets/css/main.css', array('parent-style', 'fonts'), $modified_bootscoreChildCss);

    // style.css
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // custom.js
    // Get modification time. Enqueue file with modification date to prevent browser from loading cached scripts when file content changes. 
    $modificated_CustomJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/custom.js'));
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), $modificated_CustomJS, false);



    // Select2
    wp_enqueue_style('select2-css', get_stylesheet_directory_uri() . '/assets/css/select2.min.css', array(), '4.1.0-rc.0');
    wp_enqueue_script('select2-js', get_stylesheet_directory_uri() . '/assets/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);

    // Scryfall Common Utility
    $modified_ScryfallJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/scryfall-autocomplete.js'));
    wp_register_script('scryfall-autocomplete-core', get_stylesheet_directory_uri() . '/assets/js/scryfall-autocomplete.js', array('jquery', 'select2-js'), $modified_ScryfallJS, true);



    // Deck Editor JS (Conditional)
    if (is_page_template('page-templates/page-deck-editor.php')) {
        wp_enqueue_script('scryfall-autocomplete-core');
        $modified_DeckEditorJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/deck-editor.js'));
        wp_enqueue_script('deck-editor-js', get_stylesheet_directory_uri() . '/assets/js/deck-editor.js', array('jquery', 'select2-js', 'scryfall-autocomplete-core'), $modified_DeckEditorJS, true);
    }

    // LPDH Cookie Consent Init (for bs-cookie-settings plugin)
    $modified_CookieSettingsInitJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/cookie-settings-init.js'));
    wp_enqueue_script('lpdh-cookie-settings-init', get_stylesheet_directory_uri() . '/assets/js/cookie-settings-init.js', array('cookie-settings-js'), $modified_CookieSettingsInitJS, true);

    // Commander Roulette JS
    if (is_page_template('page-templates/page-roulette.php')) {
        $modified_RouletteJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/lpdh-roulette.js'));
        wp_enqueue_script('lpdh-roulette-js', get_stylesheet_directory_uri() . '/assets/js/lpdh-roulette.js', array('jquery'), $modified_RouletteJS, true);

        // Localize vars
        $user_id = get_current_user_id();
        $stats = lpdh_get_spin_stats($user_id);

        wp_localize_script('lpdh-roulette-js', 'lpdh_roulette_vars', array(
            'banned_cards' => lpdh_get_banned_card_names(),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('lpdh_spin_nonce'),
            'initial_stats' => $stats,
            'is_logged_in' => is_user_logged_in()
        ));
    }
}

/**
 * Enqueue scripts and styles for WordPress Admin
 */
function lpdh_admin_enqueue_scripts($hook)
{
    // Enqueue media uploader for Theme Settings page
    if ($hook === 'lpdh_page_lpdh-theme-settings') {
        wp_enqueue_media();
    }

    global $post_type;

    if (in_array($hook, array('post.php', 'post-new.php')) && $post_type === 'deck') {
        wp_enqueue_style('select2-css', get_stylesheet_directory_uri() . '/assets/css/select2.min.css', array(), '4.1.0-rc.0');
        wp_enqueue_script('select2-js', get_stylesheet_directory_uri() . '/assets/js/select2.min.js', array('jquery'), '4.1.0-rc.0', true);

        // Enqueue our common Scryfall utility if it exists (it should, as registered above in frontend but needs to be accessible here too)
        $modified_ScryfallJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/scryfall-autocomplete.js'));
        wp_enqueue_script('scryfall-autocomplete-core', get_stylesheet_directory_uri() . '/assets/js/scryfall-autocomplete.js', array('jquery', 'select2-js'), $modified_ScryfallJS, true);

        // Localize Banned Cards for Admin
        $banned_card_names = lpdh_get_banned_card_names();
        wp_localize_script('scryfall-autocomplete-core', 'LPDH_Banned_Cards', $banned_card_names);

        // Add inline style for Banned Badge in Select2 (mimic Bootstrap)
        $custom_admin_css = "
        .select2-results__option .badge.bg-danger {
            background-color: #dc3545 !important;
            color: #fff;
            padding: 0.25em 0.5em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
            display: inline-block;
            vertical-align: middle;
            line-height: 1;
        }
    ";
        wp_add_inline_style('select2-css', $custom_admin_css);

        // Enqueue our admin-specific autocomplete helper script
        $modified_AdminJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/admin-deck-editor.js'));
        wp_enqueue_script('admin-deck-editor-js', get_stylesheet_directory_uri() . '/assets/js/admin-deck-editor.js', array('jquery', 'select2-js', 'scryfall-autocomplete-core'), $modified_AdminJS, true);

        // Minimal styles for admin results (scoped to our classes)
        wp_add_inline_style('select2-css', "
            .scryfall-helper-container { margin-bottom: 8px; }
            .scryfall-result { display: flex; align-items: center; gap: 10px; padding: 5px; }
            .scryfall-image { width: 30px; height: auto; border-radius: 2px; }
            .scryfall-name { font-weight: 600; }
        ");
    }
}
add_action('admin_enqueue_scripts', 'lpdh_admin_enqueue_scripts');

/**
 * Restrict admin menu for players - show only Dashboard, Profile, and Decks
 * Administrators with player role see everything (admin has priority)
 */
function restrict_admin_menu_for_players()
{
    // If user is admin, show everything
    if (lpdh_can_manage_content()) {
        return;
    }

    // If user is player only (not admin)
    if (current_user_can('player')) {
        // Remove all menu items except Dashboard, Profile, and Decks

        // Posts
        remove_menu_page('edit.php');

        // Media
        remove_menu_page('upload.php');

        // Pages
        remove_menu_page('edit.php?post_type=page');

        // Comments
        remove_menu_page('edit-comments.php');

        // Appearance
        remove_menu_page('themes.php');

        // Plugins
        remove_menu_page('plugins.php');

        // Users
        remove_menu_page('users.php');

        // Tools
        remove_menu_page('tools.php');

        // Settings
        remove_menu_page('options-general.php');

        // ACF (if installed)
        remove_menu_page('edit.php?post_type=acf-field-group');

        // Contact Form 7
        remove_menu_page('wpcf7');

        // WPForms
        remove_menu_page('wpforms-overview');

        // Gravity Forms
        remove_menu_page('gf_edit_forms');

        // Ninja Forms
        remove_menu_page('ninja-forms');

        // Elementor
        remove_menu_page('elementor');

        // Divi
        remove_menu_page('et_divi_options');

        // Contact/Other custom post types
        remove_menu_page('edit.php?post_type=contact');
        remove_menu_page('edit.php?post_type=contact_form');
        remove_menu_page('edit.php?post_type=wpcf7_contact_form');
        remove_menu_page('edit.php?post_type=cf7');

        // Other common admin pages
        remove_menu_page('woocommerce');
        remove_menu_page('edit.php?post_type=product');
        remove_menu_page('edit.php?post_type=shop_order');
        remove_menu_page('loco');
        remove_menu_page('WPML');
        remove_menu_page('sitepress-multilingual-cms');

        // Remove separators
        remove_menu_page('separator-tools');
        remove_menu_page('separator-plugins');
        remove_menu_page('separator-theme');
        remove_menu_page('separator-custom');
        remove_menu_page('separator-last');

        // Custom Post Types
        remove_menu_page('edit.php?post_type=leaderboard');
        remove_menu_page('edit.php?post_type=banned_card');
        remove_menu_page('edit.php?post_type=place');
        remove_menu_page('edit.php?post_type=faq');
        remove_menu_page('edit.php?post_type=event');
        remove_menu_page('edit.php?post_type=achievement');
    }
}
add_action('admin_menu', 'restrict_admin_menu_for_players', 999);

/**
 * Hide admin bar items for players (frontend)
 */
function hide_admin_bar_items_for_players($wp_admin_bar)
{
    if (lpdh_can_manage_content()) {
        return;
    }

    if (current_user_can('player')) {
        // Keep only user-related items, remove others
        $nodes_to_keep = array('user-info', 'edit-profile', 'logout', 'top-secondary', 'my-account', 'site-name', 'user-actions', 'go-to-homepage');

        foreach ($wp_admin_bar->get_nodes() as $id => $node) {
            if (!in_array($id, $nodes_to_keep)) {
                $wp_admin_bar->remove_node($id);
            }
        }
    }
}
add_action('admin_bar_menu', 'hide_admin_bar_items_for_players', 999);

/**
 * Redirect players away from restricted admin pages
 */
function redirect_players_from_restricted_pages()
{
    if (lpdh_can_manage_content()) {
        return;
    }

    if (current_user_can('player')) {
        $current_page = $_GET['page'] ?? '';
        $restricted_pages = array(
            'edit.php',
            'upload.php',
            'edit.php?post_type=page',
            'edit-comments.php',
            'themes.php',
            'plugins.php',
            'users.php',
            'tools.php',
            'options-general.php',
        );

        if (in_array($current_page, $restricted_pages) || in_array($GLOBALS['pagenow'], array('users.php', 'profile.php'))) {
            // Allow profile page
            if ($GLOBALS['pagenow'] === 'profile.php') {
                return;
            }

            // Allow deck management
            if (isset($_GET['post_type']) && $_GET['post_type'] === 'deck') {
                return;
            }

            // Block other custom post types
            $blocked_cpts = array('leaderboard', 'banned_card', 'place', 'faq', 'event', 'achievement');
            if (isset($_GET['post_type']) && in_array($_GET['post_type'], $blocked_cpts)) {
                wp_redirect(admin_url());
                exit;
            }

            // Also check current screen for direct access
            $screen = get_current_screen();
            if ($screen && in_array($screen->post_type, $blocked_cpts)) {
                wp_redirect(admin_url());
                exit;
            }

            // Redirect to dashboard
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'redirect_players_from_restricted_pages');


/**
 * AJAX handler for real-time username availability check
 */
function ajax_check_username_availability()
{
    check_ajax_referer('bootscore_register_nonce', 'nonce');

    $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';

    if (empty($user_login)) {
        wp_send_json_error(array('message' => __('Username is required.', 'bootscore')));
    }

    if (!validate_username($user_login)) {
        wp_send_json_error(array('message' => __('Username is invalid.', 'bootscore')));
    }

    if (username_exists($user_login)) {
        wp_send_json_error(array('message' => __('This username is already taken.', 'bootscore')));
    }

    // Check if username is reserved
    $reserved_usernames = array('admin', 'administrator', 'root', 'superuser', 'guest', 'test', 'testing');
    if (in_array(strtolower($user_login), $reserved_usernames)) {
        wp_send_json_error(array('message' => __('This username is reserved.', 'bootscore')));
    }

    wp_send_json_success(array('available' => true, 'message' => __('Username is available!', 'bootscore')));
}
add_action('wp_ajax_bootscore_check_username', 'ajax_check_username_availability');
add_action('wp_ajax_nopriv_bootscore_check_username', 'ajax_check_username_availability');

/**
 * AJAX handler for real-time email availability check
 */
function ajax_check_email_availability()
{
    check_ajax_referer('bootscore_register_nonce', 'nonce');

    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';

    if (empty($user_email)) {
        wp_send_json_error(array('message' => __('Email address is required.', 'bootscore')));
    }

    if (!is_email($user_email)) {
        wp_send_json_error(array('message' => __('Email address is invalid.', 'bootscore')));
    }

    if (email_exists($user_email)) {
        wp_send_json_error(array('message' => __('This email address is already registered.', 'bootscore')));
    }

    wp_send_json_success(array('available' => true, 'message' => __('Email address is available!', 'bootscore')));
}
add_action('wp_ajax_bootscore_check_email', 'ajax_check_email_availability');
add_action('wp_ajax_nopriv_bootscore_check_email', 'ajax_check_email_availability');


function existTemplate($slug): bool
{
    $located = locate_template('template-parts/' . $slug . '-loop.php');

    return !empty($located);
}

/**
 * Aggiunge voce Login/Profilo al menu principale
 */
function lpdh_add_login_logout_menu($items, $args)
{
    // Verifica che sia il menu principale (solitamente 'menu-1' in Bootscore)
    if ($args->theme_location == 'main-menu') {

        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $profile_url = lpdh_get_user_profile_url($current_user->ID);
            $add_deck_url = lpdh_get_deck_editor_url();
            $stats_url = lpdh_get_stats_url();
            $logout_url = wp_logout_url(home_url());
            $avatar = get_avatar($current_user->ID, 24, '', '', array('class' => 'rounded-circle me-2', 'style' => 'width: 24px; height: 24px;'));

            // Voce utente con Avatar e Nome
            $items .= '<li class="menu-item menu-item-has-children dropdown user-menu-item">';
            $items .= '<a href="' . esc_url($profile_url) . '" class="nav-link d-flex align-items-center">';
            $items .= $avatar . esc_html($current_user->display_name);
            $items .= '</a>';

            // Desktop Dropdown
            $items .= '<ul class="dropdown-menu dropdown-menu-end d-none d-lg-block">';
            $items .= '<li class="menu-item"><a href="' . esc_url($add_deck_url) . '" class="dropdown-item"><i class="fas fa-plus-circle me-2"></i>Add Deck</a></li>';
            $items .= '<li class="menu-item"><a href="' . esc_url($stats_url) . '" class="dropdown-item"><i class="fas fa-chart-bar me-2"></i>View Stats</a></li>';
            $items .= '<li class="menu-item"><a href="' . esc_url($logout_url) . '" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>';
            $items .= '</ul>';

            // Mobile Flat List (visible on small screens)
            $items .= '<ul class="d-lg-none list-unstyled ms-3 mt-2">';
            $items .= '<li class="menu-item mb-2"><a href="' . esc_url($add_deck_url) . '" class="nav-link p-0"><i class="fas fa-plus-circle me-2"></i>Add Deck</a></li>';
            $items .= '<li class="menu-item mb-2"><a href="' . esc_url($stats_url) . '" class="nav-link p-0"><i class="fas fa-chart-bar me-2"></i>View Stats</a></li>';
            $items .= '<li class="menu-item"><a href="' . esc_url($logout_url) . '" class="nav-link p-0 text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>';
            $items .= '</ul>';

            $items .= '</li>';

        } else {
            // Voce Login (porta a pagina login personalizzata nella sezione login)
            $login_url = lpdh_get_login_register_url('login');
            $items .= '<li class="menu-item">';
            $items .= '<a href="' . esc_url($login_url) . '" class="nav-link"><i class="fas fa-user me-1"></i> Login</a>';
            $items .= '</li>';
        }
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'lpdh_add_login_logout_menu', 10, 2);

/**
 * Get card image from Scryfall API if no featured image exists.
 * Uses transients to cache API responses.
 *
 * @param int $post_id The post ID.
 * @return string The image URL or empty string.
 */
function lpdh_get_scryfall_image_url($post_id, $card_name = null)
{
    $search_term = $card_name ? $card_name : get_the_title($post_id);

    if (empty($search_term)) {
        return '';
    }

    $transient_key = 'scryfall_img_url_' . md5($search_term);
    $cached_url = get_transient($transient_key);
    if (false !== $cached_url) {
        return $cached_url;
    }

    $api_url = 'https://api.scryfall.com/cards/named?exact=' . urlencode($search_term);
    $response = wp_remote_get($api_url);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        set_transient($transient_key, 'error', WEEK_IN_SECONDS);
        return '';
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    $image_url = isset($data['image_uris']['normal']) ? $data['image_uris']['normal'] : '';
    set_transient($transient_key, $image_url, 4 * WEEK_IN_SECONDS); // Cache for 4 weeks
    return $image_url;
}


/**
 * Add !f-plantin to article excerpts and read-more in archives
 */
add_filter('bootscore/class/loop/card-text/excerpt', function ($class) {
    return $class . ' !f-plantin';
});
/**
 * Override bootscore_date to show only published date
 * and remove the "Last Updated" information.
 */
if (!function_exists('bootscore_date')):
    function bootscore_date()
    {
        $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time>';
        $time_string = sprintf(
            $time_string,
            esc_attr(get_the_date('c')),
            esc_html(get_the_date())
        );
        echo '<span class="posted-on"><i class="fas fa-calendar-alt me-1"></i>' . $time_string . '</span>';
    }
endif;

/**
 * Register Top Nav Search Sidebar
 */
add_action('widgets_init', 'bootscore_child_widgets_init');
function bootscore_child_widgets_init()
{
    register_sidebar(array(
        'name' => esc_html__('Top Nav Search', 'bootscore'),
        'id' => 'top-nav-search',
        'description' => esc_html__('Add widgets here. To show the search toggler, add a "Search" widget here.', 'bootscore'),
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
}

/**
 * Register Parent LPDH Admin Menu
 */
function register_lpdh_parent_menu()
{
    add_menu_page(
        'LPDH',
        'LPDH',
        'manage_lpdh_content', // Restricted to roles with this specific LPDH capability
        'lpdh-main',
        'lpdh_render_main_page',
        'dashicons-admin-generic',
        2
    );
}
add_action('admin_menu', 'register_lpdh_parent_menu', 9);

/**
 * Render LPDH Main Admin Page with README.md content
 */
function lpdh_render_main_page()
{
    $readme_path = get_stylesheet_directory() . '/README.md';

    echo '<div class="wrap lpdh-readme-wrapper">';
    echo '<h1 style="margin-bottom: 30px;"><span class="dashicons dashicons-admin-generic" style="font-size: 1.3em; margin-right: 10px;"></span>LPDH - Legendary Pauper Commander</h1>';

    if (file_exists($readme_path)) {
        $readme_content = file_get_contents($readme_path);

        // Escape for safe JS injection
        $readme_json = json_encode($readme_content);

        echo '<div id="markdown-content" class="lpdh-readme-content markdown-body" data-color-mode="dark" data-dark-theme="dark"></div>';
    } else {
        echo '<div class="notice notice-error"><p>README.md file not found.</p></div>';
    }

    echo '</div>';

    // Load Marked.js from CDN
    echo '<script src="https://cdn.jsdelivr.net/npm/marked@11.1.1/marked.min.js"></script>';

    // Load GitHub Markdown CSS (dark theme)
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/github-markdown-css@5.5.0/github-markdown-dark.min.css">';

    // Parse and render markdown
    if (file_exists($readme_path)) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                const markdownContent = ' . $readme_json . ';
                
                // Configure marked options for GFM (GitHub Flavored Markdown)
                marked.setOptions({
                    gfm: true,
                    breaks: true,
                    headerIds: true,
                    mangle: false
                });
                
                // Preprocess GitHub Alerts (> [!NOTE], > [!IMPORTANT], etc.)
                function preprocessGitHubAlerts(markdown) {
                    const alertTypes = {
                        "NOTE": { icon: "ℹ️", class: "alert-note", color: "#1f6feb" },
                        "TIP": { icon: "💡", class: "alert-tip", color: "#238636" },
                        "IMPORTANT": { icon: "❗", class: "alert-important", color: "#8957e5" },
                        "WARNING": { icon: "⚠️", class: "alert-warning", color: "#9e6a03" },
                        "CAUTION": { icon: "🚨", class: "alert-caution", color: "#da3633" }
                    };
                    
                    // Match GitHub alert pattern: > [!TYPE]
                    const alertPattern = /^> \[!(NOTE|TIP|IMPORTANT|WARNING|CAUTION)\]\s*\n((?:> .*\n?)*)/gm;
                    
                    return markdown.replace(alertPattern, (match, type, content) => {
                        const alert = alertTypes[type];
                        // Remove the "> " prefix from each line of content
                        const cleanContent = content.replace(/^> ?/gm, "").trim();
                        
                        return `<div class="markdown-alert markdown-alert-${alert.class}" style="border-left: 3px solid ${alert.color}; padding: 12px 16px; margin: 16px 0; background: rgba(${parseInt(alert.color.slice(1,3), 16)}, ${parseInt(alert.color.slice(3,5), 16)}, ${parseInt(alert.color.slice(5,7), 16)}, 0.1); border-radius: 6px;">
                            <p class="markdown-alert-title" style="display: flex; align-items: center; margin: 0 0 8px 0; font-weight: 600; color: ${alert.color};">
                                <span style="margin-right: 8px;">${alert.icon}</span>
                                ${type}
                            </p>
                            <div class="markdown-alert-content">${marked.parse(cleanContent)}</div>
                        </div>\n\n`;
                    });
                }
                
                // Preprocess alerts then parse
                const processedMarkdown = preprocessGitHubAlerts(markdownContent);
                const htmlContent = marked.parse(processedMarkdown);
                document.getElementById("markdown-content").innerHTML = htmlContent;
            });
        </script>';
    }

    // Additional styling to match GitHub dark theme
    echo '<style>
        .lpdh-readme-wrapper {
            background: #0d1117;
            padding: 20px;
            margin: -10px -20px;
        }
        
        .lpdh-readme-wrapper h1 {
            color: #c9d1d9;
        }
        
        .lpdh-readme-content.markdown-body {
            background: #0d1117;
            color: #c9d1d9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans", Helvetica, Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            padding: 40px;
            border: 1px solid #30363d;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0,0,0,.5);
            max-width: 1200px;
        }
        
        .markdown-body h1,
        .markdown-body h2,
        .markdown-body h3,
        .markdown-body h4,
        .markdown-body h5,
        .markdown-body h6 {
            color: #c9d1d9;
        }
        
        .markdown-body h1 {
            padding-bottom: 0.3em;
            border-bottom: 1px solid #21262d;
        }
        
        .markdown-body h2 {
            padding-bottom: 0.3em;
            border-bottom: 1px solid #21262d;
        }
        
        .markdown-body a {
            color: #58a6ff;
        }
        
        .markdown-body a:hover {
            text-decoration: underline;
        }
        
        .markdown-body code {
            background: #161b22;
            color: #e6edf3;
            padding: 0.2em 0.4em;
            border-radius: 6px;
        }
        
        .markdown-body pre {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 6px;
        }
        
        .markdown-body pre code {
            background: transparent;
            padding: 0;
        }
        
        .markdown-body blockquote {
            border-left: 0.25em solid #3b434b;
            color: #8b949e;
            padding: 0 1em;
        }
        
        .markdown-body hr {
            background-color: #21262d;
            border: 0;
            height: 0.25em;
        }
        
        .markdown-body table tr {
            background-color: #0d1117;
            border-top: 1px solid #21262d;
        }
        
        .markdown-body table tr:nth-child(2n) {
            background-color: #161b22;
        }
        
        .markdown-body table th,
        .markdown-body table td {
            border: 1px solid #30363d;
            padding: 6px 13px;
        }
        
        /* GitHub alert boxes styling */
        .markdown-body .note,
        .markdown-body .tip,
        .markdown-body .important,
        .markdown-body .warning,
        .markdown-body .caution {
            padding: 16px;
            margin-bottom: 16px;
            border-left: 4px solid;
            border-radius: 6px;
        }
        
        .markdown-body .note {
            background: rgba(31, 111, 235, 0.1);
            border-left-color: #1f6feb;
        }
        
        .markdown-body .important {
            background: rgba(163, 113, 247, 0.1);
            border-left-color: #a371f7;
        }
    </style>';
}

/**
 * Basic Markdown to HTML parser
 * @deprecated Use Marked.js instead
 */
function lpdh_parse_markdown($markdown)
{
    // This function is deprecated and kept for backward compatibility
    // The admin page now uses Marked.js library for accurate GitHub-style rendering
    return htmlspecialchars($markdown, ENT_QUOTES, 'UTF-8');
}

/**
 * Theme Settings Page for Admin
 */
function lpdh_register_theme_settings()
{
    add_submenu_page(
        'lpdh-main',
        'LPDH Theme Settings',
        'Theme Settings',
        'manage_lpdh_content',
        'lpdh-theme-settings',
        'lpdh_theme_settings_render'
    );
}
add_action('admin_menu', 'lpdh_register_theme_settings');

function lpdh_theme_settings_render()
{
    if (!lpdh_can_manage_content())
        return;

    // Save Settings
    if (isset($_POST['lpdh_theme_action']) && $_POST['lpdh_theme_action'] == 'save') {
        check_admin_referer('lpdh_theme_settings_save');
        update_option('lpdh_active_theme', sanitize_text_field($_POST['lpdh_active_theme']));
        update_option('lpdh_deck_editor_page_id', intval($_POST['lpdh_deck_editor_page_id']));
        update_option('lpdh_profile_editor_page_id', intval($_POST['lpdh_profile_editor_page_id']));
        update_option('lpdh_stats_page_id', intval($_POST['lpdh_stats_page_id']));
        update_option('lpdh_login_register_page_id', intval($_POST['lpdh_login_register_page_id']));
        update_option('lpdh_roulette_page_id', intval($_POST['lpdh_roulette_page_id']));

        // Save Social Links
        update_option('lpdh_instagram_link', esc_url($_POST['lpdh_instagram_link']));
        update_option('lpdh_discord_link', esc_url($_POST['lpdh_discord_link']));
        update_option('lpdh_facebook_link', esc_url($_POST['lpdh_facebook_link']));
        update_option('lpdh_x_link', esc_url($_POST['lpdh_x_link']));

        // Hook for additional settings save actions
        do_action('lpdh_after_theme_settings_save');

        // Save Custom Logo
        update_option('lpdh_custom_logo_id', intval($_POST['lpdh_custom_logo_id']));

        // Save ELO Settings
        update_option('lpdh_elo_k_factor_divide_by_game', isset($_POST['lpdh_elo_k_factor_divide_by_game']) ? 1 : 0);

        // Save Custom Logo (if provided)
        if (isset($_POST['lpdh_custom_logo_id'])) {
            update_option('lpdh_custom_logo_id', intval($_POST['lpdh_custom_logo_id']));
        }

        // Save Login Customization Toggle
        update_option('lpdh_enable_custom_login', isset($_POST['lpdh_enable_custom_login']) ? 1 : 0);

        echo '<div class="updated"><p>Theme settings saved!</p></div>';
    }

    $active_theme = get_option('lpdh_active_theme', 'default');
    $deck_editor_page_id = get_option('lpdh_deck_editor_page_id', 0);
    $profile_editor_page_id = get_option('lpdh_profile_editor_page_id', 0);
    $stats_page_id = get_option('lpdh_stats_page_id', 0);
    $custom_logo_id = get_option('lpdh_custom_logo_id', 0);

    ?>
    <div class="wrap">
        <h1>LPDH Theme Settings</h1>
        <form method="post">
            <?php wp_nonce_field('lpdh_theme_settings_save'); ?>
            <input type="hidden" name="lpdh_theme_action" value="save">

            <table class="form-table">
                <tr>
                    <th scope="row">Active Theme</th>
                    <td>
                        <select name="lpdh_active_theme">
                            <option value="default" <?php selected($active_theme, 'default'); ?>>Bootscore Default</option>
                            <option value="vaporwave" <?php selected($active_theme, 'vaporwave'); ?>>Vaporwave (80s Neon)
                            </option>
                            <option value="vaporwave-green" <?php selected($active_theme, 'vaporwave-green'); ?>>Vaporwave
                                Green (Neon Forest)
                            </option>
                            <option value="lost-wood" <?php selected($active_theme, 'lost-wood'); ?>>Lost Wood (Forest)
                            </option>
                        </select>
                        <p class="description">Select the aesthetic for the entire platform.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Custom Login Page</th>
                    <td>
                        <label>
                            <input type="checkbox" name="lpdh_enable_custom_login" value="1" <?php checked(get_option('lpdh_enable_custom_login', 0), 1); ?>>
                            Enable Admin custom login
                        </label>
                        <p class="description">Activate the split-screen design with background image for the login page.
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Custom Logo</th>
                    <td>
                        <?php
                        $logo_url = '';
                        if ($custom_logo_id) {
                            $logo_url = wp_get_attachment_url($custom_logo_id);
                        }
                        ?>
                        <div class="logo-upload-container">
                            <input type="hidden" name="lpdh_custom_logo_id" id="lpdh_custom_logo_id"
                                value="<?php echo esc_attr($custom_logo_id); ?>">
                            <div class="logo-preview" id="logo-preview" style="margin-bottom: 10px;">
                                <?php if ($logo_url): ?>
                                    <img src="<?php echo esc_url($logo_url); ?>" alt="Logo Preview"
                                        style="max-width: 300px; height: auto; display: block;">
                                <?php endif; ?>
                            </div>
                            <button type="button" class="button" id="upload_logo_button">
                                <?php echo $logo_url ? 'Change Logo' : 'Upload Logo'; ?>
                            </button>
                            <?php if ($logo_url): ?>
                                <button type="button" class="button" id="remove_logo_button">Remove Logo</button>
                            <?php endif; ?>
                        </div>
                        <p class="description">Upload a custom logo for your site. If set, this will override the logo from
                            Customizer.</p>
                        <script>
                            jQuery(document).ready(function ($) {
                                var mediaUploader;

                                $('#upload_logo_button').click(function (e) {
                                    e.preventDefault();

                                    if (mediaUploader) {
                                        mediaUploader.open();
                                        return;
                                    }

                                    mediaUploader = wp.media({
                                        title: 'Choose Logo',
                                        button: {
                                            text: 'Use this logo'
                                        },
                                        multiple: false,
                                        library: {
                                            type: 'image'
                                        }
                                    });

                                    mediaUploader.on('select', function () {
                                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                                        $('#lpdh_custom_logo_id').val(attachment.id);
                                        $('#logo-preview').html('<img src="' + attachment.url + '" alt="Logo Preview" style="max-width: 300px; height: auto; display: block;">');
                                        $('#upload_logo_button').text('Change Logo');
                                        if ($('#remove_logo_button').length === 0) {
                                            $('#upload_logo_button').after('<button type="button" class="button" id="remove_logo_button">Remove Logo</button>');
                                            $('#remove_logo_button').click(function (e) {
                                                e.preventDefault();
                                                $('#lpdh_custom_logo_id').val('');
                                                $('#logo-preview').html('');
                                                $('#upload_logo_button').text('Upload Logo');
                                                $(this).remove();
                                            });
                                        }
                                    });

                                    mediaUploader.open();
                                });

                                $('#remove_logo_button').click(function (e) {
                                    e.preventDefault();
                                    $('#lpdh_custom_logo_id').val('');
                                    $('#logo-preview').html('');
                                    $('#upload_logo_button').text('Upload Logo');
                                    $(this).remove();
                                });
                            });
                        </script>
                    </td>
                </tr>
            </table>

            <hr>
            <h2>Pages Configuration</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Select Deck Editor Page</th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'lpdh_deck_editor_page_id',
                            'selected' => $deck_editor_page_id,
                            'show_option_none' => '-- Select Page --',
                            'option_none_value' => '0'
                        ));
                        ?>
                        <p class="description">Select the page that uses the "Deck Editor" template.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Select Profile Editor Page</th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'lpdh_profile_editor_page_id',
                            'selected' => $profile_editor_page_id,
                            'show_option_none' => '-- Select Page --',
                            'option_none_value' => '0'
                        ));
                        ?>
                        <p class="description">Select the page that uses the "User Profile Editor" template.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Select Statistics Page</th>
                    <td>
                        <?php
                        wp_dropdown_pages(array(
                            'name' => 'lpdh_stats_page_id',
                            'selected' => $stats_page_id,
                            'show_option_none' => '-- Select Page --',
                            'option_none_value' => '0'
                        ));
                        ?>
                        <p class="description">Select the page that uses the "User Statistics" template.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Select Login/Register Page</th>
                    <td>
                        <?php
                        $login_register_page_id = get_option('lpdh_login_register_page_id', 0);
                        wp_dropdown_pages(array(
                            'name' => 'lpdh_login_register_page_id',
                            'selected' => $login_register_page_id,
                            'show_option_none' => '-- Select Page --',
                            'option_none_value' => '0'
                        ));
                        ?>
                        <p class="description">Select the page that uses the "Registration Page" template.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Select Commander Roulette Page</th>
                    <td>
                        <?php
                        $roulette_page_id = get_option('lpdh_roulette_page_id', 0);
                        wp_dropdown_pages(array(
                            'name' => 'lpdh_roulette_page_id',
                            'selected' => $roulette_page_id,
                            'show_option_none' => '-- Select Page --',
                            'option_none_value' => '0'
                        ));
                        ?>
                        <p class="description">Select the page that uses the "Commander Roulette" template.</p>
                    </td>
                </tr>

            </table>

            <hr>
            <h2>Socials</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Instagram URL</th>
                    <td>
                        <input type="url" name="lpdh_instagram_link"
                            value="<?php echo esc_url(get_option('lpdh_instagram_link')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Discord URL</th>
                    <td>
                        <input type="url" name="lpdh_discord_link"
                            value="<?php echo esc_url(get_option('lpdh_discord_link')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Facebook URL</th>
                    <td>
                        <input type="url" name="lpdh_facebook_link"
                            value="<?php echo esc_url(get_option('lpdh_facebook_link')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">X (Twitter) URL</th>
                    <td>
                        <input type="url" name="lpdh_x_link" value="<?php echo esc_url(get_option('lpdh_x_link')); ?>"
                            class="regular-text">
                    </td>
                </tr>
            </table>

            <hr>
            <h2>ELO Settings</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">CALCULATE ELO: K Factor / Game Played?</th>
                    <td>
                        <input type="checkbox" name="lpdh_elo_k_factor_divide_by_game" value="1" <?php checked(get_option('lpdh_elo_k_factor_divide_by_game', 1), 1); ?>>
                        <p class="description">If active, the ELO K-factor (32) will be divided by the number of matches
                            played in the tournament.</p>
                    </td>
                </tr>
            </table>

            <hr>
            <?php do_action('lpdh_after_theme_settings_row'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}




/**
 * LPDH Theme Setting Helpers
 */

function lpdh_get_active_theme()
{
    return get_option('lpdh_active_theme', 'default');
}


function lpdh_get_profile_editor_url()
{
    $page_id = get_option('lpdh_profile_editor_page_id');
    return $page_id ? get_permalink($page_id) : admin_url('profile.php');
}

function lpdh_get_user_profile_url($user_id)
{
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    return $user_id ? get_author_posts_url($user_id) : home_url();
}

/**
 * Get Stats URL with universal permalink support
 */
function lpdh_get_stats_url($user_id = null)
{
    $page_id = get_option('lpdh_stats_page_id');
    $base_url = $page_id ? get_permalink($page_id) : home_url('/user-stats/');

    if (!$user_id) {
        return $base_url;
    }

    $user_data = get_userdata($user_id);
    $slug = ($user_data) ? $user_data->user_nicename : '';

    if (get_option('permalink_structure') && $slug) {
        // Pretty Permalinks: /user-stats/slug/
        return user_trailingslashit(trailingslashit($base_url) . $slug);
    } else {
        // Plain Permalinks: ?user_id=123 (backwards compatible) or ?player_slug=slug
        return add_query_arg('user_id', $user_id, $base_url);
    }
}

/**
 * Register Player Slug Query Var
 */
function lpdh_register_player_query_vars($vars)
{
    $vars[] = 'player_slug';
    return $vars;
}
add_filter('query_vars', 'lpdh_register_player_query_vars');

/**
 * Add Rewrite Rule for Player Stats Slug
 */
function lpdh_add_player_stats_rewrite_rules()
{
    $page_id = get_option('lpdh_stats_page_id');
    if (!$page_id) return;

    $post = get_post($page_id);
    if (!$post) return;

    $slug = $post->post_name;
    add_rewrite_rule(
        '^' . $slug . '/([^/]+)/?$',
        'index.php?page_id=' . $page_id . '&player_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'lpdh_add_player_stats_rewrite_rules');

function lpdh_get_login_register_url($section = '')
{
    $page_id = get_option('lpdh_login_register_page_id');
    $url = $page_id ? get_permalink($page_id) : wp_login_url();

    if ($page_id && $section === 'login') {
        $url = add_query_arg('login', 'true', $url);
    } elseif ($page_id && $section === 'lostpassword') {
        $url = add_query_arg('action', 'lostpassword', $url);
    }

    return $url;
}

/**
 * Apply Theme Body Class
 */
function lpdh_apply_theme_body_class($classes)
{
    $active_theme = get_option('lpdh_active_theme', 'default');
    if ($active_theme !== 'default') {
        $classes[] = 'theme-' . $active_theme;
    }
    return $classes;
}
add_filter('body_class', 'lpdh_apply_theme_body_class');

/**
 * Get Extra Attributions
 */
function lpdh_get_extra_attributions()
{
    $attributions = [
        'Made with <a href="#" id="hearts">&hearts;</a> by <a class="fw-bold" href="https://linktr.ee/cellicom" target="_blank">cellicom</a>',
        '<a href="https://github.com/cellicom/lpdh_wp" target="_blank" rel="noopener">View this project on Github</a>',
        '<a href="https://www.vecteezy.com/free-vector/vaporwave-grid" target="_blank" rel="noopener">Vaporwave Grid Vectors by Vecteezy</a>'
    ];

    if (empty($attributions)) {
        return '';
    }

    $output = '<div class="extra-attributions d-flex flex-column flex-md-row flex-wrap align-items-center justify-content-center gap-2 small mt-2 mt-md-0">';

    foreach ($attributions as $index => $attr) {
        $output .= '<span class="attribution-item">' . $attr . '</span>';
        if ($index < count($attributions) - 1) {
            $output .= '<span class="d-none d-md-inline opacity-50">|</span>';
        }
    }

    $output .= '</div>';

    return $output;
}
/**
 * Get Social Links HTML for Footer
 */
function lpdh_get_social_links()
{
    $socials = [
        'instagram' => [
            'link' => get_option('lpdh_instagram_link'),
            'icon' => 'fa-brands fa-instagram',
            'label' => 'Instagram'
        ],
        'discord' => [
            'link' => get_option('lpdh_discord_link'),
            'icon' => 'fa-brands fa-discord',
            'label' => 'Discord'
        ],
        'facebook' => [
            'link' => get_option('lpdh_facebook_link'),
            'icon' => 'fa-brands fa-facebook',
            'label' => 'Facebook'
        ],
        'x' => [
            'link' => get_option('lpdh_x_link'),
            'icon' => 'fa-brands fa-x-twitter',
            'label' => 'X'
        ],
    ];

    $output = '';
    $has_links = false;

    foreach ($socials as $key => $social) {
        if (!empty($social['link'])) {
            $has_links = true;
            break;
        }
    }

    if (!$has_links) {
        return '';
    }

    $output .= '<div class="footer-socials">';
    foreach ($socials as $key => $social) {
        if (!empty($social['link'])) {
            $output .= sprintf(
                '<a href="%s" target="_blank" rel="noopener" class="social-%s" aria-label="%s"><i class="%s"></i></a>',
                esc_url($social['link']),
                esc_attr($key),
                esc_attr($social['label']),
                esc_attr($social['icon'])
            );
        }
    }
    $output .= '</div>';

    return $output;
}


/**
 * Hide Admin Bar for Players, show for Administrators.
 */
add_filter('show_admin_bar', 'lpdh_manage_admin_bar');
function lpdh_manage_admin_bar($show)
{
    if (lpdh_can_manage_content()) {
        return true;
    }

    if (current_user_can('player')) {
        return false;
    }

    return $show;
}




/**
 * Add Private Profile field to user profile page
 */
function lpdh_add_private_profile_field($user)
{
    $private_profile = get_user_meta($user->ID, 'private_profile', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="private_profile">Private Profile</label></th>
            <td>
                <input type="checkbox" name="private_profile" id="private_profile" value="1" <?php checked('1', $private_profile); ?> />
                <span class="description">Your profile will become private hiding the user detail page.</span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('personal_options', 'lpdh_add_private_profile_field');

/**
 * Save Private Profile field
 */
function lpdh_save_private_profile_field($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'private_profile', isset($_POST['private_profile']) ? '1' : '0');
}
add_action('personal_options_update', 'lpdh_save_private_profile_field');
add_action('edit_user_profile_update', 'lpdh_save_private_profile_field');

require_once get_stylesheet_directory() . '/inc/admin-help-guide.php';


/**
 * Load Custom Post Type templates from "page-templates" subdirectory.
 */
function lpdh_load_cpt_templates($template)
{
    // Check for Single CPT
    if (is_single()) {
        global $post;
        $type = $post->post_type;
        $custom_template = get_stylesheet_directory() . '/page-templates/single-' . $type . '.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    // Check for CPT Archive
    if (is_post_type_archive()) {
        $type = get_query_var('post_type');
        if (is_array($type)) {
            $type = reset($type);
        }
        $custom_template = get_stylesheet_directory() . '/page-templates/archive-' . $type . '.php';
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter('template_include', 'lpdh_load_cpt_templates', 99);

/**
 * PWA Service Worker Registration
 */
function lpdh_register_sw()
{
    ?>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('<?php echo get_stylesheet_directory_uri(); ?>/sw.js')
                    .then(function (registration) {
                        console.log('PWA: ServiceWorker registration successful with scope: ', registration.scope);
                    }, function (err) {
                        console.log('PWA: ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
    <?php
}
add_action('wp_footer', 'lpdh_register_sw');
