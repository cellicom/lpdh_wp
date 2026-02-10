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

// Include Place CPT
require_once get_stylesheet_directory() . '/inc/place.php';

// Include FAQ CPT
require_once get_stylesheet_directory() . '/inc/faq.php';

// Include Email System
require_once get_stylesheet_directory() . '/inc/email.php';

// Include Event CPT
require_once get_stylesheet_directory() . '/inc/events.php';


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
 * Customize admin users list columns
 */
function lpdh_customize_user_columns($columns)
{
    unset($columns['posts']); // Hide Posts column
    $columns['decks'] = __('Decks', 'text_domain'); // Add Decks column
    return $columns;
}
add_filter('manage_users_columns', 'lpdh_customize_user_columns');

/**
 * Populate Decks column in admin users list
 */
function lpdh_populate_user_decks_column($output, $column_name, $user_id)
{
    if ($column_name === 'decks') {
        $count = count_user_posts($user_id, 'deck');
        if ($count > 0) {
            // Link to the user's decks in admin
            $url = admin_url('edit.php?post_type=deck&author=' . $user_id);
            return '<a href="' . esc_url($url) . '">' . $count . '</a>';
        }
        return '0';
    }
    return $output;
}
add_filter('manage_users_custom_column', 'lpdh_populate_user_decks_column', 10, 3);


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
 * Adjust deck list column widths
 */
function lpdh_deck_list_column_widths()
{
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-deck') {
        echo '<style>
            .column-title { width: 35% !important; }
            .column-commander { width: 35% !important; }
            .column-decklist { width: 40px !important; text-align: center; }
        </style>';
    }
}
add_action('admin_head', 'lpdh_deck_list_column_widths');

/**
 * Adjust banned card list column widths
 */





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

function getTitleFromAcfBox($box, $id)
{
    if (!empty($box['titolo'])) {
        $cta = $box['titolo'];
    } else {
        $cta = $box['acf_fc_layout'] . $id;
    }

    return $cta;
}

function getUrlHashtagFromAcfBox($box, $id, $hash = '')
{
    if (!empty($box['titolo'])) {
        $url = $hash . sanitize_title_for_query(strtolower($box['titolo']) . $id);
    } else {
        $url = null;
    }

    return $url;
}

/* do not remove
function og_printFileImg($icon, $color = null)
{
    ?>
    <svg class="icon"
        <?php if (! empty($color)) { ?>
            style="fill: <?php echo $color; ?>;"
        <?php } ?>
    >
        <use xlink:href="<?php getBootstrapIcon($icon); ?>"></use>
    </svg>
    <?php
}
 */


/**
 * Add "Go to Homepage" link to admin bar
 */
/*
function add_homepage_link_to_admin_bar($wp_admin_bar) {
    $args = array(
        'id'    => 'go-to-homepage',
        'title' => '<span class="ab-icon dashicons dashicons-admin-home"></span> ' . __('Go to Homepage', 'bootscore'),
        'href'  => home_url('/'),
        'meta'  => array(
            'class' => 'go-to-homepage-link',
            'title' => __('Go to Homepage', 'bootscore'),
        ),
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'add_homepage_link_to_admin_bar', 999);
*/


/**
 * Enqueue assets for Player Stats page
 */
function lpdh_player_stats_enqueue()
{
    if (isset($_GET['page']) && $_GET['page'] === 'player-stats') {
        wp_enqueue_style('select2', get_stylesheet_directory_uri() . '/assets/css/select2.min.css');
        wp_enqueue_script('select2', get_stylesheet_directory_uri() . '/assets/js/select2.min.js', ['jquery'], '4.1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'lpdh_player_stats_enqueue');

/**
 * Add Stats page for Players
 */
function register_stats_page()
{
    add_menu_page(
        'Stats',
        'Stats',
        'read',
        'player-stats',
        'render_player_stats_page',
        'dashicons-chart-bar',
        2
    );
}
add_action('admin_menu', 'register_stats_page');

function render_player_stats_page()
{
    $user_id = get_current_user_id();

    // Admin override: allow viewing other users' stats
    if (lpdh_can_manage_content() && isset($_GET['stats_user_id'])) {
        $user_id = intval($_GET['stats_user_id']);
    }

    $target_user = get_userdata($user_id);

    // Initialize stats
    $total_attendance = 0;
    $total_wins = 0;
    $total_last_places = 0;
    $deck_usage_counts = array();
    $deck_performance = array(); // deck_id => [wins, match_wins, match_draws, match_losses, attendance]
    $player_events = array();
    $yearly_stats = array(); // year => [wins, total]

    // Get all events
    $event_args = array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'event_date',
        'orderby' => 'meta_value',
        'order' => 'ASC' // Chronological for ELO calc
    );

    // Collect available years (needed for filter UI)
    $events_years_query = new WP_Query($event_args);
    $available_years = array();
    if ($events_years_query->have_posts()) {
        foreach ($events_years_query->posts as $p) {
            $d = get_field('event_date', $p->ID);
            if ($d) {
                $y = date('Y', strtotime($d));
                if (!in_array($y, $available_years)) {
                    $available_years[] = $y;
                }
            }
        }
        rsort($available_years);
    }

    $selected_year = isset($_GET['stats_year']) ? $_GET['stats_year'] : 'global';

    // --- Optimization: Filter main query if not global ---
    if ($selected_year !== 'global') {
        $sel_y = intval($selected_year);
        $prev_y = $sel_y - 1;
        $next_y = $sel_y + 1;

        $event_args['meta_query'] = array(
            array(
                'key' => 'event_date',
                'value' => array($prev_y . '-01-01', $next_y . '-12-31'),
                'compare' => 'BETWEEN',
                'type' => 'DATE'
            )
        );
    }
    $events_query = new WP_Query($event_args);

    // ELO Tracking
    $player_elos = array();
    $elo_history_labels = array();
    $elo_history_data = array();
    $last_processed_year = '';
    $elo_starts_added = array();

    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $event_id = get_the_ID();

            // Filter by year
            $event_date_raw = get_field('event_date', $event_id);
            $event_year = $event_date_raw ? date('Y', strtotime($event_date_raw)) : '';

            // --- Yearly ELO Reset ---
            if ($event_year && $event_year !== $last_processed_year) {
                $player_elos = array(); // Reset all to LPDH_DEFAULT_ELO
                $last_processed_year = $event_year;
            }

            $rankings = get_field('event_ranking', $event_id);

            if (is_array($rankings)) {
                $total_players = count($rankings);

                // ELO Pre-calculation for this event (Average ELO)
                $event_participants_names = array();
                $total_event_elo = 0;
                foreach ($rankings as $rank) {
                    $name = isset($rank['name']) ? trim($rank['name']) : '';
                    if (empty($name)) {
                        $pid = isset($rank['player_id']) ? $rank['player_id'] : 0;
                        if (is_array($pid) && isset($pid['ID']))
                            $pid = $pid['ID'];
                        elseif (is_object($pid))
                            $pid = $pid->ID;

                        if ($pid) {
                            $u = get_userdata($pid);
                            if ($u)
                                $name = $u->display_name;
                        }
                    }
                    if (empty($name))
                        continue;

                    if (!isset($player_elos[$name])) {
                        $player_elos[$name] = LPDH_DEFAULT_ELO;
                    }
                    $event_participants_names[] = $name;
                    $total_event_elo += $player_elos[$name];
                }
                $avg_elo = count($event_participants_names) > 0 ? $total_event_elo / count($event_participants_names) : LPDH_DEFAULT_ELO;

                // Process Rankings for Stats & ELO Update
                $user_found_in_event = false;
                foreach ($rankings as $index => $rank) {
                    $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
                    // Handle user array or ID
                    $p_id = 0;
                    if (is_array($player_id_field) && isset($player_id_field['ID'])) {
                        $p_id = $player_id_field['ID'];
                    } elseif (is_object($player_id_field)) {
                        $p_id = $player_id_field->ID;
                    } else {
                        $p_id = $player_id_field;
                    }

                    // Resolve name for ELO tracking
                    $name = isset($rank['name']) ? trim($rank['name']) : '';
                    if (empty($name) && $p_id) {
                        $u = get_userdata($p_id);
                        if ($u)
                            $name = $u->display_name;
                    }

                    // ELO Update Logic
                    if (!empty($name)) {
                        $current_elo = $player_elos[$name];
                        $wins = intval(isset($rank['win']) ? $rank['win'] : 0);
                        $draws = intval(isset($rank['draw']) ? $rank['draw'] : 0);
                        $losses = intval(isset($rank['lose']) ? $rank['lose'] : 0);
                        $games_played = $wins + $draws + $losses;

                        if ($games_played > 0) {
                            $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                            $elo_data = lpdh_calculate_elo($current_elo, $wins, $draws, $losses, $avg_elo, $pos, $total_players);

                            $player_elos[$name] = $elo_data['new_elo'];
                        }
                    }

                    if ($p_id == $user_id) {
                        // Found the user
                        $user_found_in_event = true;

                        // --- Yearly Stats (for Win Rate Trend) ---
                        if ($event_year) {
                            $y_val = intval($event_year);
                            // If not global, only collect for prev, current, next
                            if ($selected_year === 'global' || ($y_val >= $prev_y && $y_val <= $next_y)) {
                                if (!isset($yearly_stats[$event_year])) {
                                    $yearly_stats[$event_year] = array('wins' => 0, 'total' => 0);
                                }
                                $m_win = intval(isset($rank['win']) ? $rank['win'] : 0);
                                $m_draw = intval(isset($rank['draw']) ? $rank['draw'] : 0);
                                $m_lose = intval(isset($rank['lose']) ? $rank['lose'] : 0);
                                $yearly_stats[$event_year]['wins'] += $m_win;
                                $yearly_stats[$event_year]['total'] += ($m_win + $m_draw + $m_lose);
                            }
                        }

                        // Add to raw history for Elo Chart
                        if (!empty($name)) {
                            // Only add to Elo chart if global OR if it matches the selected year
                            if ($selected_year === 'global' || $event_year === $selected_year) {
                                // First tournament of the year injection
                                if ($event_year && !isset($elo_starts_added[$event_year])) {
                                    $elo_history_labels[] = '01/01/' . date('y', strtotime($event_date_raw));
                                    $elo_history_data[] = LPDH_DEFAULT_ELO;
                                    $elo_starts_added[$event_year] = true;
                                }
                                $elo_history_labels[] = $event_date_raw ? date('d/m/y', strtotime($event_date_raw)) : 'Event ' . count($elo_history_labels);
                                $elo_history_data[] = round($player_elos[$name]);
                            }
                        }

                        // Filter for main summary stats
                        if ($selected_year !== 'global' && $event_year !== $selected_year) {
                            continue;
                        }

                        $total_attendance++;

                        $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                        if ($pos === 1) {
                            $total_wins++;
                        }

                        if ($index === $total_players - 1) {
                            $total_last_places++;
                        }

                        $deck_id = isset($rank['player_deck_id']) ? intval($rank['player_deck_id']) : 0;

                        // Track deck usage
                        if ($deck_id) {
                            if (!isset($deck_usage_counts[$deck_id])) {
                                $deck_usage_counts[$deck_id] = 0;
                            }
                            $deck_usage_counts[$deck_id]++;

                            // Track deck performance
                            if (!isset($deck_performance[$deck_id])) {
                                $deck_performance[$deck_id] = array(
                                    'wins' => 0,
                                    'match_wins' => 0,
                                    'match_draws' => 0,
                                    'match_losses' => 0,
                                    'attendance' => 0
                                );
                            }

                            $deck_performance[$deck_id]['attendance']++;
                            if ($pos === 1) {
                                $deck_performance[$deck_id]['wins']++;
                            }
                            $deck_performance[$deck_id]['match_wins'] += intval(isset($rank['win']) ? $rank['win'] : 0);
                            $deck_performance[$deck_id]['match_draws'] += intval(isset($rank['draw']) ? $rank['draw'] : 0);
                            $deck_performance[$deck_id]['match_losses'] += intval(isset($rank['lose']) ? $rank['lose'] : 0);
                        }

                        // Add to events list (for the table)
                        $player_events[] = array(
                            'event_post' => get_post($event_id),
                            'ranking' => $rank,
                            'event_date' => $event_date_raw,
                            'total_players' => $total_players
                        );
                    }
                }
            }
        }
        wp_reset_postdata();
    }

    // --- Global ELO Aggregation by Year ---
    if ($selected_year === 'global' && !empty($elo_history_data)) {
        $year_points = array();
        foreach ($elo_history_labels as $idx => $label) {
            $parts = explode('/', $label);
            if (count($parts) === 3) {
                $y = '20' . $parts[2];
                $year_points[$y] = $elo_history_data[$idx];
            }
        }
        $elo_history_labels = array_keys($year_points);
        $elo_history_data = array_values($year_points);
    }

    // Reverse events for display (Newest first)
    $player_events = array_reverse($player_events);

    // Prepare Chart Data
    $chart_labels = array();
    $chart_data = array();

    if (!empty($deck_usage_counts)) {
        // Sort by usage descending
        arsort($deck_usage_counts);

        foreach ($deck_usage_counts as $d_id => $count) {
            $chart_labels[] = get_the_title($d_id);
            $chart_data[] = $count;
        }
    }

    // Prepare Line Chart Data (Win Rate Trend)
    ksort($yearly_stats);
    $line_labels = array();
    $line_data = array();

    foreach ($yearly_stats as $y => $data) {
        $line_labels[] = $y;
        $rate = $data['total'] > 0 ? round(($data['wins'] / $data['total']) * 100, 1) : 0;
        $line_data[] = $rate;
    }

    // Most used deck
    $most_used_deck_name = '-';
    $most_used_deck_id = 0;
    if (!empty($deck_usage_counts)) {
        $most_used_deck_id = array_keys($deck_usage_counts, max($deck_usage_counts))[0];
        $most_used_deck_post = get_post($most_used_deck_id);
        if ($most_used_deck_post) {
            $most_used_deck_name = $most_used_deck_post->post_title;
        }
    }

    // Get user's decks for the table
    $paged_decks = isset($_GET['paged_decks']) ? max(1, intval($_GET['paged_decks'])) : 1;
    $decks_per_page = 5;

    $args_decks = array(
        'post_type' => 'deck',
        'author' => $user_id,
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $user_decks = get_posts($args_decks);

    // Prepare deck data for display
    $display_decks = array();
    foreach ($user_decks as $deck) {
        $d_id = $deck->ID;
        $stats = isset($deck_performance[$d_id]) ? $deck_performance[$d_id] : array(
            'wins' => 0,
            'match_wins' => 0,
            'match_draws' => 0,
            'match_losses' => 0,
            'attendance' => 0
        );

        $commander = get_field('commander', $d_id);
        $partner = get_field('partner', $d_id);

        $display_decks[] = array(
            'id' => $d_id,
            'title' => $deck->post_title,
            'commander' => $commander,
            'partner' => $partner,
            'stats' => $stats
        );
    }

    // Pagination for Decks
    $total_decks = count($display_decks);
    $total_deck_pages = ceil($total_decks / $decks_per_page);
    $offset_decks = ($paged_decks - 1) * $decks_per_page;
    $current_page_decks = array_slice($display_decks, $offset_decks, $decks_per_page);

    // Pagination for Events
    $paged_events = isset($_GET['paged_events']) ? max(1, intval($_GET['paged_events'])) : 1;
    $events_per_page = 5;
    $total_events = count($player_events);
    $total_event_pages = ceil($total_events / $events_per_page);
    $offset_events = ($paged_events - 1) * $events_per_page;
    $current_page_events = array_slice($player_events, $offset_events, $events_per_page);

    // --- Centralized Stats Source ---
    $player_stats = lpdh_get_player_stats($user_id, $selected_year);

    // Override manual counts with official leaderboard data
    $total_attendance = $player_stats['event_count'];
    $total_wins = $player_stats['win_count'];
    $total_last_places = $player_stats['clown_count'];
    $display_elo = $player_stats['elo'];

    // 2. Achievements
    $total_achievements = wp_count_posts('achievement')->publish;
    $unlocked_achievements_count = 0;
    if (function_exists('lpdh_get_user_achievements')) {
        $unlocked_list = lpdh_get_user_achievements($user_id);
        if ($selected_year !== 'global') {
            foreach ($unlocked_list as $item) {
                $u_date = isset($item['date_unlocked_ts']) ? date('Y', $item['date_unlocked_ts']) : '';
                if ($u_date === $selected_year) {
                    $unlocked_achievements_count++;
                }
            }
        } else {
            $unlocked_achievements_count = count($unlocked_list);
        }
    }

    // 3. Roulette
    $lifetime_spins = intval(get_user_meta($user_id, 'lpdh_lifetime_spins', true));

    // 4. Decks Owned (Filtered)
    $stats_decks_count = $player_stats['deck_count'];

    // Render HTML
    ?>
        <div class="wrap">
            <h1>Player Stats: <?php echo esc_html($target_user ? $target_user->display_name : 'Unknown User'); ?></h1>

            <form method="get" action="" style="margin: 20px 0;">
                <input type="hidden" name="page" value="player-stats">

                <?php if (current_user_can('administrator')): ?>
                        <div
                            style="margin-bottom: 15px; padding: 15px; border: 1px solid #ccd0d4; border-left: 4px solid #2271b1; display: inline-block; width: 100%; box-sizing: border-box;">
                            <label for="stats_user_id"
                                style="font-weight: bold; margin-right: 10px; display: block; margin-bottom: 5px;">Select Player
                                (Admin):</label>
                            <?php
                            $all_users = get_users(['orderby' => 'display_name']);
                            ?>
                            <select name="stats_user_id" id="stats_user_id" class="lpdh-stats-select2" onchange="this.form.submit()"
                                style="width: 100%;">
                                <option value="">Select User</option>
                                <?php foreach ($all_users as $u): ?>
                                        <option value="<?php echo $u->ID; ?>" <?php selected($user_id, $u->ID); ?>>
                                            <?php echo esc_html($u->display_name . ' (' . $u->user_login . ')'); ?>
                                        </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <style>
                            /* Select2 Responsive Fixes */
                            .select2-container {
                                width: 300px !important;
                                max-width: 100%;
                            }

                            @media screen and (max-width: 782px) {
                                .select2-container {
                                    width: 100% !important;
                                    margin-bottom: 10px;
                                }

                                h1 {
                                    word-break: break-all;
                                }
                            }
                        </style>
                        <script>
                            jQuery(document).ready(function ($) {
                                $('.lpdh-stats-select2').select2({
                                    width: 'resolve',
                                    placeholder: "Select a Player"
                                });
                            });
                        </script>
                        <br>
                <?php endif; ?>

                <label for="stats_year" style="font-weight: bold; margin-right: 10px;">Filter by year:</label>
                <select name="stats_year" id="stats_year" onchange="this.form.submit()">
                    <option value="global" <?php selected($selected_year, 'global'); ?>>Global</option>
                    <?php foreach ($available_years as $y): ?>
                            <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                                <?php echo esc_html($y); ?>
                            </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px;">
                <!-- Box 1: Riepilogo -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h2 class="title">Summary</h2>
                    <div style="margin-top: 15px;">
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr>
                                    <th scope="row">Tournament Attendance</th>
                                    <td><?php echo $total_attendance; ?> 🙋</td>
                                </tr>
                                <tr>
                                    <th scope="row">Wins (1st place)</th>
                                    <td><?php echo $total_wins; ?> 🏆</td>
                                </tr>
                                <tr>
                                    <th scope="row">Last Places</th>
                                    <td><?php echo $total_last_places; ?> 🤡</td>
                                </tr>
                                <tr>
                                    <th scope="row">Calculated Elo</th>
                                    <td><?php echo $display_elo; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Achievements</th>
                                    <td><?php echo $unlocked_achievements_count; ?> / <?php echo $total_achievements; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Roulette Spins</th>
                                    <td><?php echo $lifetime_spins; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Decks Owned</th>
                                    <td><?php echo $stats_decks_count; ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Most Used Deck</th>
                                    <td>
                                        <?php if ($most_used_deck_id): ?>
                                                <a
                                                    href="<?php echo get_edit_post_link($most_used_deck_id); ?>"><?php echo esc_html($most_used_deck_name); ?></a>
                                        <?php else: ?>
                                                <?php echo esc_html($most_used_deck_name); ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Box 2: Mazzi più usati -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h2 class="title">Most Used Decks</h2>
                    <canvas id="deckUsageChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
                <!-- Box 3: Andamento Win Rate -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h2 class="title">Win Rate Trend</h2>
                    <canvas id="winRateChart" style="max-height: 300px;"></canvas>
                </div>

                <!-- Box 4: Andamento ELO -->
                <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                    <h2 class="title">ELO Trend</h2>
                    <canvas id="eloChart" style="max-height: 300px;"></canvas>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    // Pie Chart
                    var ctx = document.getElementById('deckUsageChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: <?php echo json_encode($chart_labels); ?>,
                            datasets: [{
                                data: <?php echo json_encode($chart_data); ?>,
                                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#76A346', '#FDB45C', '#949FB1', '#4D5360'],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } }
                        }
                    });

                    // Line Chart
                    var ctxLine = document.getElementById('winRateChart').getContext('2d');
                    new Chart(ctxLine, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($line_labels); ?>,
                            datasets: [{
                                label: 'Win Rate %',
                                data: <?php echo json_encode($line_data); ?>,
                                borderColor: '#36A2EB',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: { callback: function (value) { return value + "%" } }
                                }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });

                    // ELO Chart
                    var ctxElo = document.getElementById('eloChart').getContext('2d');
                    new Chart(ctxElo, {
                        type: 'line',
                        data: {
                            labels: <?php echo json_encode($elo_history_labels); ?>,
                            datasets: [{
                                label: 'ELO',
                                data: <?php echo json_encode($elo_history_data); ?>,
                                borderColor: '#8e44ad',
                                backgroundColor: 'rgba(142, 68, 173, 0.2)',
                                tension: 0.3,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    });
                });
            </script>


            <hr style="margin: 30px 0;">

            <h2>My Decks</h2>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th>Deck</th>
                        <th>Tournament Wins</th>
                        <th>Match Wins</th>
                        <th>Match Draws</th>
                        <th>Match Losses</th>
                        <th>Win Rate</th>
                        <th>Attendance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($current_page_decks)): ?>
                            <?php foreach ($current_page_decks as $deck):
                                $total_matches = $deck['stats']['match_wins'] + $deck['stats']['match_draws'] + $deck['stats']['match_losses'];
                                $win_rate = $total_matches > 0 ? round(($deck['stats']['match_wins'] / $total_matches) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td>
                                            <strong><a
                                                    href="<?php echo get_edit_post_link($deck['id']); ?>"><?php echo esc_html($deck['title']); ?></a></strong>
                                            <?php if ($deck['commander']): ?>
                                                    <br>
                                                    <span class="description">
                                                        <?php echo esc_html($deck['commander']); ?>
                                                        <?php if ($deck['partner'])
                                                            echo ' (' . esc_html($deck['partner']) . ')'; ?>
                                                    </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $deck['stats']['wins']; ?></td>
                                        <td><?php echo $deck['stats']['match_wins']; ?></td>
                                        <td><?php echo $deck['stats']['match_draws']; ?></td>
                                        <td><?php echo $deck['stats']['match_losses']; ?></td>
                                        <td><?php echo $win_rate; ?>%</td>
                                        <td><?php echo $deck['stats']['attendance']; ?></td>
                                    </tr>
                            <?php endforeach; ?>
                    <?php else: ?>
                            <tr>
                                <td colspan="7">No decks found.</td>
                            </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Pagination Decks
            if ($total_deck_pages > 1) {
                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged_decks', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_deck_pages,
                    'current' => $paged_decks,
                    'add_args' => array('paged_events' => $paged_events) // Keep event page
                ));
                if ($page_links) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
                }
            }
            ?>

            <hr style="margin: 30px 0;">

            <h2>Event History</h2>
            <table class="wp-list-table widefat fixed striped table-view-list">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date and Place</th>
                        <th>Position</th>
                        <th>Participants</th>
                        <th>Deck Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($current_page_events)): ?>
                            <?php foreach ($current_page_events as $event_data):
                                $event_post = $event_data['event_post'];
                                $rank = $event_data['ranking'];
                                $event_date = $event_data['event_date'];
                                $total_players = $event_data['total_players'];

                                $place_obj = get_field('field_event_place', $event_post->ID);
                                $place_name = $place_obj ? $place_obj->post_title : '-';

                                $deck_id = isset($rank['player_deck_id']) ? $rank['player_deck_id'] : 0;
                                $deck_name_manual = isset($rank['deck']) ? $rank['deck'] : '';
                                $deck_name = '-';
                                if ($deck_id) {
                                    $d_post = get_post($deck_id);
                                    if ($d_post)
                                        $deck_name = $d_post->post_title;
                                }

                                $pos_style = '';
                                if ($rank['pos'] == 1)
                                    $pos_style = 'color: #D4AF37; font-weight: bold;';
                                elseif ($rank['pos'] == 2)
                                    $pos_style = 'color: #A9A9A9; font-weight: bold;';
                                elseif ($rank['pos'] == 3)
                                    $pos_style = 'color: #CD7F32; font-weight: bold;';
                                ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo get_permalink($event_post->ID); ?>" target="_blank">
                                                <?php echo esc_html($event_post->post_title); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            if ($event_date)
                                                echo date_i18n('d/m/Y', strtotime($event_date));
                                            echo '<br><span class="description">' . esc_html($place_name) . '</span>';
                                            ?>
                                        </td>
                                        <td><span style="<?php echo $pos_style; ?>"><?php echo esc_html($rank['pos']); ?></span></td>
                                        <td><?php echo $total_players; ?></td>
                                        <td>
                                            <?php if ($deck_id): ?>
                                                    <a href="<?php echo get_edit_post_link($deck_id); ?>">
                                                        <?php echo esc_html($deck_name); ?>
                                                    </a>
                                            <?php elseif (!empty($deck_name_manual)): ?>
                                                    <?php echo esc_html($deck_name_manual); ?>
                                            <?php else: ?>
                                                    -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                            <?php endforeach; ?>
                    <?php else: ?>
                            <tr>
                                <td colspan="5">No events found.</td>
                            </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Pagination Events
            if ($total_event_pages > 1) {
                $page_links = paginate_links(array(
                    'base' => add_query_arg('paged_events', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_event_pages,
                    'current' => $paged_events,
                    'add_args' => array('paged_decks' => $paged_decks) // Keep deck page
                ));
                if ($page_links) {
                    echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
                }
            }
            ?>
        </div>
        <?php
}

/**
 * Add "View Stats" link to User row actions for Administrators
 */
function add_stats_link_to_user_row($actions, $user)
{
    if (lpdh_can_manage_content()) {
        $url = add_query_arg(
            array(
                'page' => 'player-stats',
                'stats_user_id' => $user->ID
            ),
            admin_url('admin.php')
        );
        $actions['view_stats'] = '<a href="' . esc_url($url) . '">View Stats</a>';
    }
    return $actions;
}
add_filter('user_row_actions', 'add_stats_link_to_user_row', 10, 2);




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
 * Get Commander Image URL
 */
function get_commander_image($post_id)
{
    // 1. Featured Image
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'medium_large');
    }

    // 2. Scryfall via Commander Name
    $commander_name = get_field('commander', $post_id);
    if ($commander_name) {
        $scryfall_img = lpdh_get_scryfall_image_url($post_id, $commander_name);
        if ($scryfall_img && $scryfall_img !== 'error') {
            return $scryfall_img;
        }
    }

    // 3. Fallback
    return get_stylesheet_directory_uri() . '/assets/img/minimal_card_back.jpg';
}

/**
 * Get Partner Image URL
 */
function get_partner_image($post_id)
{
    // 1. Featured Image Partner
    $partner_img = get_field('featured_image_partner', $post_id);
    if ($partner_img) {
        return $partner_img['sizes']['medium_large'] ?? $partner_img['url'];
    }

    // 2. Scryfall via Partner Name
    $partner_name = get_field('partner', $post_id);
    if ($partner_name) {
        $scryfall_img = lpdh_get_scryfall_image_url($post_id, $partner_name);
        if ($scryfall_img && $scryfall_img !== 'error') {
            return $scryfall_img;
        }
    }

    // 3. Fallback (only if partner exists)
    if ($partner_img || $partner_name) {
        return get_stylesheet_directory_uri() . '/assets/img/minimal_card_back.jpg';
    }

    return false;
}


/**
 * Ordina archivio Banned Card per data decrescente
 */







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
 * Shortcode [banned_card] to display a card exactly like in the banlist
 */


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
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
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
 * LPDH Theme Setting Helpers
 */

function lpdh_get_active_theme()
{
    return get_option('lpdh_active_theme', 'default');
}

function lpdh_get_deck_editor_url()
{
    $page_id = get_option('lpdh_deck_editor_page_id');
    return $page_id ? get_permalink($page_id) : home_url('/deck-editor/');
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

function lpdh_get_stats_url($user_id = null)
{
    $page_id = get_option('lpdh_stats_page_id');
    $url = $page_id ? get_permalink($page_id) : admin_url('admin.php?page=player-stats');

    if ($user_id) {
        $url = add_query_arg('user_id', $user_id, $url);
    }

    return $url;
}

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
        'Made with <span style="color: red !important; cursor: pointer;">&hearts;</span> by <a class="fw-bold" href="https://linktr.ee/cellicom" target="_blank">cellicom</a>',
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
    // (type:creature type:legendary) (game:paper) rarity:uncommon
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
    if (!$user_id)
        return array('remaining' => 0, 'limit' => 3, 'is_admin' => false);

    $today = date('Y-m-d');
    $last_spin_date = get_user_meta($user_id, 'lpdh_last_spin_date', true);
    $spins_today = intval(get_user_meta($user_id, 'lpdh_spins_today', true));

    if ($last_spin_date !== $today) {
        $spins_today = 0; // It will be effectively 0, though not updated in DB until spin
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

// Ensure lpdh_roulette_vars includes AJAX URL and Nonce
add_filter('wp_enqueue_scripts', function () {
    if (is_page_template('page-templates/page-roulette.php')) {
        // This is handled in the main enqueue function, but we need to ensure localizer has these new vars.
        // We actually need to modify the EXISTING wp_localize_script call in bootscore_child_enqueue_styles
        // or just accept we'll add them there.
        // Let's modify the ORIGINAL wp_localize_script block in functions.php instead of adding a filter here.
    }
});
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
 * Check if a deck contains banned cards
 */
function lpdh_is_deck_legal($deck_id)
{
    $banned_names = lpdh_get_banned_card_names();
    if (empty($banned_names)) {
        return true;
    }

    $commander = get_field('commander', $deck_id);
    $partner = get_field('partner', $deck_id);
    $decklist_text = get_field('decklist_text', $deck_id);

    $deck_cards = array();
    if (!empty($commander)) {
        $deck_cards[] = strtolower(trim($commander));
    }
    if (!empty($partner)) {
        $deck_cards[] = strtolower(trim($partner));
    }

    if (!empty($decklist_text)) {
        $lines = explode("\n", $decklist_text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line))
                continue;

            // Remove quantity (e.g., "1 Sol Ring" -> "Sol Ring")
            $card_name = preg_replace('/^\d+x?\s+/', '', $line);
            $deck_cards[] = strtolower(trim($card_name));
        }
    }

    foreach ($deck_cards as $card) {
        if (in_array($card, $banned_names)) {
            return false;
        }
    }

    return true;
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
 * Centralized function to get player statistics from various sources.
 * Primarily uses the Leaderboard CPT for performance and accuracy.
 */
function lpdh_get_player_stats($user_id, $year = 'global')
{
    static $stats_cache = [];
    $cache_key = $user_id . '_' . $year;
    if (isset($stats_cache[$cache_key]))
        return $stats_cache[$cache_key];

    $user_data = get_userdata($user_id);
    $registered = $user_data ? strtotime($user_data->user_registered) : time();

    // Comparison date for 'days_since_reg'
    $comparison_time = time();
    if ($year !== 'global') {
        $comparison_year = intval($year);
        $comparison_time = strtotime($comparison_year . '-12-31 23:59:59');
    }

    $days_since_reg = floor(($comparison_time - $registered) / (60 * 60 * 24));
    if ($days_since_reg < 0)
        $days_since_reg = 0;

    // 1. Decks Count
    $deck_args = [
        'post_type' => 'deck',
        'author' => $user_id,
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ];
    if ($year !== 'global') {
        $deck_args['date_query'] = [['year' => $year]];
    }
    $user_decks = get_posts($deck_args);
    $deck_count = count($user_decks);

    // 2. Deck with Banned Cards
    $deck_with_banned = 0;
    if (function_exists('lpdh_get_banned_card_names')) {
        $banned_cards = lpdh_get_banned_card_names();
        if (!empty($banned_cards) && !empty($user_decks)) {
            foreach ($user_decks as $d_id) {
                $list_text = get_field('decklist_text', $d_id);
                $commander = get_field('commander', $d_id);
                $partner = get_field('partner', $d_id);

                if (is_object($commander))
                    $commander = $commander->post_title;
                if (is_object($partner))
                    $partner = $partner->post_title;

                $full_check_text = strtolower($list_text . ' ' . $commander . ' ' . $partner);

                if (!empty($full_check_text)) {
                    foreach ($banned_cards as $card) {
                        if (strpos($full_check_text, $card) !== false) {
                            $deck_with_banned++;
                            break;
                        }
                    }
                }
            }
        }
    }

    // 3. Spinned the Wheel (Global for now)
    $spinned_wheel_count = intval(get_user_meta($user_id, 'lpdh_lifetime_spins', true));

    // 4. Events, Wins, Clowns & Elo from Leaderboard(s)
    $events_attended = 0;
    $win_count = 0;
    $clown_count = 0;
    $final_elo = 0;
    $total_points = 0;

    $leaderboard_args = [
        'post_type' => 'leaderboard',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];

    if ($year !== 'global') {
        $leaderboard_args['meta_query'] = [
            [
                'key' => 'year',
                'value' => $year
            ]
        ];
    }

    $lb_posts = get_posts($leaderboard_args);

    if (!empty($lb_posts)) {
        foreach ($lb_posts as $lb_post) {
            $json = get_field('rankings_json', $lb_post->ID);
            if (!$json)
                continue;

            $lb_data = json_decode($json, true);
            if (!is_array($lb_data))
                continue;

            foreach ($lb_data as $entry) {
                $e_id = isset($entry['user_id']) ? $entry['user_id'] : (isset($entry['id']) ? $entry['id'] : 0);
                $e_name = isset($entry['name']) ? $entry['name'] : '';

                if (($e_id && $e_id == $user_id) || ($e_name && $user_data && strcasecmp($e_name, $user_data->display_name) === 0)) {
                    $events_attended += isset($entry['count']) ? intval($entry['count']) : 0;
                    $win_count += isset($entry['first']) ? intval($entry['first']) : 0;
                    $clown_count += isset($entry['last']) ? intval($entry['last']) : 0;
                    $total_points += isset($entry['points']) ? intval($entry['points']) : 0;

                    $current_entry_elo = isset($entry['elo']) ? round($entry['elo']) : 0;
                    if ($year === 'global') {
                        if ($current_entry_elo > $final_elo)
                            $final_elo = $current_entry_elo;
                    } else {
                        $final_elo = $current_entry_elo;
                    }
                    break;
                }
            }
        }
    }

    $res = [
        'deck_count' => $deck_count,
        'win_count' => $win_count,
        'event_count' => $events_attended,
        'clown_count' => $clown_count,
        'deck_with_banned' => $deck_with_banned,
        'spinned_wheel_count' => $spinned_wheel_count,
        'days_registered' => $days_since_reg,
        'elo' => $final_elo,
        'points' => $total_points
    ];

    $stats_cache[$cache_key] = $res;
    return $res;
}

/**
 * Checks a single condition against a value.
 */
function lpdh_check_stat_condition($user_val, $operator, $target_val)
{
    if (is_numeric($user_val) && is_numeric($target_val)) {
        $user_val = floatval($user_val);
        $target_val = floatval($target_val);
    }
    switch ($operator) {
        case '>':
            return $user_val > $target_val;
        case '>=':
            return $user_val >= $target_val;
        case '=':
            return $user_val == $target_val;
        case '<=':
            return $user_val <= $target_val;
        case '<':
            return $user_val < $target_val;
        default:
            return false;
    }
}


/**
 * Load Custom Post Type templates from "templates" subdirectory.
 */
function lpdh_load_cpt_templates($template)
{
    // Check for Single CPT
    if (is_single()) {
        global $post;
        $type = $post->post_type;
        $custom_template = get_stylesheet_directory() . '/templates/single-' . $type . '.php';
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
        $custom_template = get_stylesheet_directory() . '/templates/archive-' . $type . '.php';
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
