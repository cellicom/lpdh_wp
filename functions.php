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

// Include Email Template System
require_once get_stylesheet_directory() . '/email-templates/class-email-template.php';
require_once get_stylesheet_directory() . '/email-templates/functions-email.php';


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
 * AJAX handler for email preview
 */
function lpdh_ajax_preview_email()
{
    check_ajax_referer('lpdh_email_preview', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this feature.'));
    }

    $template_type = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'new-user-welcome';
    $theme_override = isset($_GET['theme']) ? sanitize_text_field($_GET['theme']) : '';

    // Temporarily override theme if requested
    if ($theme_override) {
        add_filter('option_lpdh_active_theme', function () use ($theme_override) {
            return $theme_override;
        });
    }

    // Get sample data
    $sample_data = lpdh_get_sample_email_data(1);

    // Render template
    lpdh_render_email_preview($template_type, $sample_data, true);
    exit;
}
add_action('wp_ajax_lpdh_preview_email', 'lpdh_ajax_preview_email');

/**
 * AJAX handler for sending test email
 */
function lpdh_ajax_send_test_email()
{
    check_ajax_referer('lpdh_send_test_email', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to send test emails.'));
    }

    $recipient = isset($_POST['test_email']) ? sanitize_email($_POST['test_email']) : '';
    $template_type = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : 'new-user-welcome';
    $theme_override = isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : '';

    if (empty($recipient) || !is_email($recipient)) {
        wp_send_json_error(array('message' => 'Please provide a valid email address.'));
    }

    // Temporarily override theme if requested
    if ($theme_override) {
        add_filter('option_lpdh_active_theme', function () use ($theme_override) {
            return $theme_override;
        });
    }

    // Get sample data
    $sample_data = lpdh_get_sample_email_data(1);

    // Define subject based on template type
    $subjects = array(
        'new-user-welcome' => '[TEST] Your Account Credentials',
        'admin-new-user-notification' => '[TEST] New User Registered',
    );

    $subject = isset($subjects[$template_type]) ? $subjects[$template_type] : '[TEST] Email Template';

    // Send email
    $sent = lpdh_send_templated_email($recipient, $subject, $template_type, $sample_data);

    if ($sent) {
        wp_send_json_success(array(
            'message' => 'Test email sent successfully to ' . $recipient . '!'
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Failed to send test email. Please check your email configuration.'
        ));
    }
}
add_action('wp_ajax_lpdh_send_test_email', 'lpdh_ajax_send_test_email');


/**
 * Registrazione Custom Post Type: Leaderboard
 */
function register_leaderboard_cpt()
{
    $labels = array(
        'name' => _x('Leaderboards', 'Post Type General Name', 'text_domain'),
        'singular_name' => _x('Leaderboard', 'Post Type Singular Name', 'text_domain'),
        'menu_name' => __('Leaderboards', 'text_domain'),
        'name_admin_bar' => __('Leaderboard', 'text_domain'),
        'archives' => __('Leaderboard Archive', 'text_domain'),
        'attributes' => __('Leaderboard Attributes', 'text_domain'),
        'parent_item_colon' => __('Parent Leaderboard:', 'text_domain'),
        'all_items' => __('All Leaderboards', 'text_domain'),
        'add_new_item' => __('Add New Leaderboard', 'text_domain'),
        'add_new' => __('Add New', 'text_domain'),
        'new_item' => __('New Leaderboard', 'text_domain'),
        'edit_item' => __('Edit Leaderboard', 'text_domain'),
        'update_item' => __('Update Leaderboard', 'text_domain'),
        'view_item' => __('View Leaderboard', 'text_domain'),
        'view_items' => __('View Leaderboards', 'text_domain'),
        'search_items' => __('Search Leaderboard', 'text_domain'),
        'not_found' => __('Not found', 'text_domain'),
        'not_found_in_trash' => __('Not found in Trash', 'text_domain'),
    );

    $args = array(
        'label' => __('Leaderboard', 'text_domain'),
        'labels' => $labels,
        'supports' => array('title'), // Solo titolo come richiesto
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-editor-ol',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        // Impostazioni di sicurezza per limitare l'accesso
        'capability_type' => 'leaderboard',
        'map_meta_cap' => true,
    );
    register_post_type('leaderboard', $args);
}
add_action('init', 'register_leaderboard_cpt', 0);


/**
 * Assegnazione delle capabilities 'leaderboard' agli Amministratori e Co-Amministratori.
 */
function add_leaderboard_caps_to_admin()
{
    $roles = array('administrator', 'co_administrator');

    foreach ($roles as $role_slug) {
        $role = get_role($role_slug);

        if ($role) {
            $caps = array(
                'edit_leaderboard',
                'read_leaderboard',
                'delete_leaderboard',
                'edit_leaderboards',
                'edit_others_leaderboards',
                'publish_leaderboards',
                'read_private_leaderboards',
                'delete_leaderboards',
                'delete_private_leaderboards',
                'delete_published_leaderboards',
                'delete_others_leaderboards',
                'edit_private_leaderboards',
                'edit_published_leaderboards',
            );

            // Also ensure both have the new custom LPDH caps
            $caps[] = 'view_lpdh_help_guide';
            $caps[] = 'manage_lpdh_content';

            foreach ($caps as $cap) {
                if (!$role->has_cap($cap)) {
                    $role->add_cap($cap);
                }
            }
        }
    }
}
add_action('admin_init', 'add_leaderboard_caps_to_admin');

/**
 * Registrazione campi ACF: Year e Rankings JSON
 */
if (function_exists('acf_add_local_field_group')):

    // Generiamo dinamicamente una lista di anni (es. da 5 anni fa a 1 anno nel futuro)
    $years = array();
    $current_year = intval(date('Y'));
    for ($i = $current_year - 5; $i <= $current_year + 1; $i++) {
        $years[$i] = $i;
    }

    acf_add_local_field_group(array(
        'key' => 'group_leaderboard_fields',
        'title' => 'Leaderboard Details',
        'fields' => array(
            array(
                'key' => 'field_leaderboard_year',
                'label' => 'Year',
                'name' => 'year',
                'type' => 'select',
                'instructions' => 'Select the reference year.',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => $years,
                'default_value' => $current_year,
                'allow_null' => 0,
                'multiple' => 0,
                'ui' => 1,
                'ajax' => 0,
                'return_format' => 'value',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_leaderboard_rankings_json',
                'label' => 'Rankings JSON',
                'name' => 'rankings_json',
                'type' => 'textarea',
                'instructions' => 'Enter ranking data in JSON format here.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '100',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'maxlength' => '',
                'rows' => 10,
                'new_lines' => '', // Nessuna formattazione automatica per preservare il JSON
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'leaderboard',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));

endif;


/**
 * Register Custom Post Type "Deck"
 */
function register_deck_post_type()
{
    $labels = array(
        'name' => 'Decks',
        'singular_name' => 'Deck',
        'menu_name' => 'Decks',
        'name_admin_bar' => 'Deck',
        'archives' => 'Deck Archive',
        'attributes' => 'Deck Attributes',
        'parent_item_colon' => 'Parent Deck:',
        'all_items' => 'All Decks',
        'add_new_item' => 'Add New Deck',
        'add_new' => 'Add New',
        'new_item' => 'New Deck',
        'edit_item' => 'Edit Deck',
        'update_item' => 'Update Deck',
        'view_item' => 'View Deck',
        'view_items' => 'View Decks',
        'search_items' => 'Search Deck',
        'not_found' => 'No decks found',
        'not_found_in_trash' => 'No decks in Trash',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'insert_into_item' => 'Insert into deck',
        'uploaded_to_this_item' => 'Uploaded to this deck',
        'items_list' => 'Deck list',
        'items_list_navigation' => 'Deck list navigation',
        'filter_items_list' => 'Filter deck list',
    );

    $args = array(
        'label' => 'Deck',
        'description' => 'Custom Post Type to manage decks',
        'labels' => $labels,
        'supports' => array('title', 'thumbnail', 'author'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-category',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rest_base' => 'decks',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('deck', $args);
}
add_action('init', 'register_deck_post_type', 0);

/**
 * Add custom "player" role based on author capabilities
 */
function add_player_role()
{
    remove_role('player'); // Remove if exists
    add_role(
        'player',
        'Player',
        array(
            'read' => true,
            'upload_files' => true,
        )
    );
}
add_action('init', 'add_player_role', 5);

/**
 * Grant author-level capabilities to player role on init
 */
function grant_player_capabilities()
{
    $role = get_role('player');
    if ($role) {
        $role->add_cap('edit_posts');
        $role->add_cap('edit_published_posts');
        $role->add_cap('publish_posts');
        $role->add_cap('delete_posts');
        $role->add_cap('delete_published_posts');
        $role->add_cap('read');
    }
}
add_action('init', 'grant_player_capabilities', 15);

/**
 * Register ACF Field Group for Deck Custom Post Type
 */
if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_deck_custom_fields',
        'title' => 'Deck Fields',
        'fields' => array(
            array(
                'key' => 'field_commander',
                'label' => 'Commander',
                'name' => 'commander',
                'type' => 'text',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => 'scryfall-autocomplete',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_partner',
                'label' => 'Partner\Background',
                'name' => 'partner',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => 'scryfall-autocomplete',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
            ),
            array(
                'key' => 'field_private_deck',
                'label' => 'Private Deck',
                'name' => 'private_deck',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => 'This deck will become private and decklists will be hidden.',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array(
                'key' => 'field_decklist',
                'label' => 'Decklist (External Link)',
                'name' => 'decklist',
                'type' => 'url',
                'instructions' => 'Enter external decklist URL',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'https://example.com/decklist',
            ),
            array(
                'key' => 'field_decklist_text',
                'label' => 'Decklist (Text List)',
                'name' => 'decklist_text',
                'type' => 'textarea',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'maxlength' => '',
                'rows' => '',
                'new_lines' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'deck',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));

    acf_add_local_field_group(array(
        'key' => 'group_deck_partner_image',
        'title' => 'Featured Image Partner',
        'fields' => array(
            array(
                'key' => 'field_featured_image_partner',
                'label' => '',
                'name' => 'featured_image_partner',
                'type' => 'image',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'deck',
                ),
            ),
        ),
        'menu_order' => 10,
        'position' => 'side',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));




    /**
     * Admin JS to force Title for Decks
     */
    function lpdh_admin_deck_validation_js()
    {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'deck')
            return;
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#publish, #save-post').on('click', function (e) {
                    var title = $('#title').val();
                    if (!title || title.trim().length === 0) {
                        alert('Deck title is mandatory!');
                        $('#title').focus();
                        $('#major-publishing-actions .spinner').removeClass('is-active');
                        $('#publish, #save-post').removeClass('disabled');
                        return false;
                    }
                });
            });
        </script>
        <?php
    }
    add_action('admin_footer', 'lpdh_admin_deck_validation_js');

endif;

/**
 * Add custom columns to Deck admin list
 */
function deck_custom_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['commander'] = 'Commander';
    $new_columns['decklist'] = 'Link';
    $new_columns['author'] = 'Author';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_deck_posts_columns', 'deck_custom_columns');

/**
 * Populate custom columns data
 */
function deck_custom_columns_data($column, $post_id)
{
    switch ($column) {
        case 'commander':
            $commander = get_field('field_commander', $post_id);
            $partner = get_field('field_partner', $post_id);
            if ($commander) {
                $scryfall_url_commander = 'https://scryfall.com/search?q="' . urlencode($commander) . '"';
                echo '<a href="' . esc_url($scryfall_url_commander) . '" target="_blank" rel="noopener">' . esc_html($commander) . '</a>';
                if ($partner) {
                    $scryfall_url_partner = 'https://scryfall.com/search?q="' . urlencode($partner) . '"';
                    echo '<br><a href="' . esc_url($scryfall_url_partner) . '" target="_blank" rel="noopener">' . esc_html($partner) . '</a>';
                }
            } else {
                echo '-';
            }
            break;
        case 'decklist':
            $decklist = get_field('field_decklist', $post_id);
            if ($decklist) {
                echo '<a href="' . esc_url($decklist) . '" target="_blank" rel="noopener"><span class="dashicons dashicons-external" style="color: black;"></span></a>';
            } else {
                echo '-';
            }
            break;
        case 'author':
            $author_id = get_post_field('post_author', $post_id);
            $author_user = get_userdata($author_id);
            if ($author_user) {
                echo '<a href="' . esc_url(get_edit_user_link($author_id)) . '">' . esc_html($author_user->display_name) . '</a>';
            }
            break;
    }
}
add_action('manage_deck_posts_custom_column', 'deck_custom_columns_data', 10, 2);

/**
 * Make custom columns sortable
 */
function deck_sortable_columns($columns)
{
    $columns['decklist'] = 'decklist';
    return $columns;
}
add_filter('manage_edit-deck_sortable_columns', 'deck_sortable_columns');

/**
 * Handle custom column sorting
 */
function deck_column_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('decklist' == $orderby) {
        $query->set('meta_key', 'field_decklist');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'deck_column_orderby');

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
 * Remove "All" and "Published" tabs for players in deck list
 * Only show "Mine" tab for players, administrators see everything
 */
function restrict_deck_list_tabs_for_players($views)
{
    // Only apply to deck post type
    global $post_type;
    if ($post_type !== 'deck') {
        return $views;
    }

    // If user is admin/co-admin, show all tabs
    if (lpdh_can_manage_content()) {
        return $views;
    }

    // If user is player, only show "Mine" tab
    if (current_user_can('player')) {
        // Keep only the "Mine" tab
        $mine_count = isset($views['mine']) ? $views['mine'] : '';
        return array('mine' => $mine_count);
    }

    return $views;
}
add_filter('views_edit-deck', 'restrict_deck_list_tabs_for_players');

/**
 * Hide "All" and "Published" views via inline styles for players in deck list
 * This ensures tabs are hidden even if the filter doesn't catch them
 */
function hide_deck_views_for_players()
{
    // Only apply to deck post type in admin
    if (!is_admin()) {
        return;
    }

    global $post_type, $pagenow;

    // Only on deck list page
    if ($pagenow !== 'edit.php' || $post_type !== 'deck') {
        return;
    }

    // If user is admin/co-admin, do nothing
    if (lpdh_can_manage_content()) {
        return;
    }

    // If user is player, hide all views except "Mine"
    ?>

    <?php
}
add_action('admin_head', 'hide_deck_views_for_players', 20);

/**
 * Restrict deck list query to show only own decks for players
 * Administrators see all decks
 */
function restrict_deck_list_query_for_players($query)
{
    // Only in admin and main query
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    global $pagenow;

    // Only for deck post type list
    if ($pagenow === 'edit.php' && $query->get('post_type') === 'deck') {
        // If user is admin, do nothing (see all)
        if (current_user_can('administrator')) {
            return;
        }

        // If user is player, restrict to own posts
        if (current_user_can('player')) {
            $query->set('author', get_current_user_id());
        }
    }
}
add_action('pre_get_posts', 'restrict_deck_list_query_for_players');

/**
 * Redirect players from dashboard to deck list
 * Administrators see normal dashboard (admin has priority)
 */
function redirect_players_to_deck_list()
{
    // If user is admin, do nothing (admin sees normal dashboard)
    if (lpdh_can_manage_content()) {
        return;
    }

    // If user is player, redirect from dashboard to deck list
    if (current_user_can('player')) {
        $current_screen = get_current_screen();

        // Only redirect if on dashboard main page
        if ($current_screen && $current_screen->id === 'dashboard') {
            wp_redirect(admin_url('edit.php?post_type=deck'));
            exit;
        }
    }
}
add_action('current_screen', 'redirect_players_to_deck_list');

/**
 * Register Custom Post Type "Place"
 * Solo gli amministratori possono gestire questo CPT
 */
function register_place_post_type()
{
    $labels = array(
        'name' => 'Places',
        'singular_name' => 'Place',
        'menu_name' => 'Places',
        'name_admin_bar' => 'Place',
        'archives' => 'Places Archive',
        'attributes' => 'Place Attributes',
        'parent_item_colon' => 'Parent Place:',
        'all_items' => 'All Places',
        'add_new_item' => 'Add New Place',
        'add_new' => 'Add New',
        'new_item' => 'New Place',
        'edit_item' => 'Edit Place',
        'update_item' => 'Update Place',
        'view_item' => 'View Place',
        'view_items' => 'View Places',
        'search_items' => 'Search Place',
        'not_found' => 'No places found',
        'not_found_in_trash' => 'No places in Trash',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'insert_into_item' => 'Insert into place',
        'uploaded_to_this_item' => 'Uploaded to this place',
        'items_list' => 'Places list',
        'items_list_navigation' => 'Places list navigation',
        'filter_items_list' => 'Filter places list',
    );

    $args = array(
        'label' => 'Place',
        'description' => 'Custom Post Type to manage places',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail', 'author'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 22,
        'menu_icon' => 'dashicons-location',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rest_base' => 'places',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('place', $args);
}
add_action('init', 'register_place_post_type', 0);

/**
 * Register ACF Field Group for Place Custom Post Type
 */
if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_place_custom_fields',
        'title' => 'Place Fields',
        'fields' => array(
            array(
                'key' => 'field_place_address',
                'label' => 'Address',
                'name' => 'place_address',
                'type' => 'text',
                'instructions' => 'Enter place address',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '123 Example St, City',
            ),
            array(
                'key' => 'field_place_homepage',
                'label' => 'Website',
                'name' => 'place_homepage',
                'type' => 'url',
                'instructions' => 'Enter place website link',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'https://example.com',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'place',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));

endif;

/**
 * Add custom columns to Place admin list
 */
function place_custom_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['place_address'] = 'Address';
    $new_columns['place_homepage'] = 'Website';
    $new_columns['events'] = 'Events';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_place_posts_columns', 'place_custom_columns');

/**
 * Populate custom columns data for Place
 */
function place_custom_columns_data($column, $post_id)
{
    switch ($column) {
        case 'place_address':
            $place_address = get_field('field_place_address', $post_id);
            if ($place_address) {
                echo esc_html($place_address);
            } else {
                echo '-';
            }
            break;
        case 'place_homepage':
            $place_homepage = get_field('field_place_homepage', $post_id);
            if ($place_homepage) {
                echo '<a href="' . esc_url($place_homepage) . '" target="_blank" rel="noopener">' . esc_html($place_homepage) . '</a>';
            } else {
                echo '-';
            }
            break;
        case 'events':
            $count = new WP_Query(array(
                'post_type' => 'event',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'event_place',
                        'value' => $post_id,
                        'compare' => '='
                    )
                ),
                'fields' => 'ids'
            ));
            echo intval($count->found_posts);
            break;
    }
}
add_action('manage_place_posts_custom_column', 'place_custom_columns_data', 10, 2);

/**
 * Make custom columns sortable for Place
 */
function place_sortable_columns($columns)
{
    $columns['place_address'] = 'place_address';
    return $columns;
}
add_filter('manage_edit-place_sortable_columns', 'place_sortable_columns');

/**
 * Handle custom column sorting for Place
 */
function place_column_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('place_address' == $orderby) {
        $query->set('meta_key', 'field_place_address');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'place_column_orderby');


/**
 * Register Custom Post Type "FAQ"
 * Solo gli amministratori possono gestire questo CPT
 */
function register_faq_post_type()
{
    $labels = array(
        'name' => 'FAQ',
        'singular_name' => 'FAQ',
        'menu_name' => 'FAQ',
        'name_admin_bar' => 'FAQ',
        'archives' => 'FAQ Archive',
        'attributes' => 'FAQ Attributes',
        'parent_item_colon' => 'Parent FAQ:',
        'all_items' => 'All FAQs',
        'add_new_item' => 'Add New FAQ',
        'add_new' => 'Add New',
        'new_item' => 'New FAQ',
        'edit_item' => 'Edit FAQ',
        'update_item' => 'Update FAQ',
        'view_item' => 'View FAQ',
        'view_items' => 'View FAQs',
        'search_items' => 'Search FAQ',
        'not_found' => 'No FAQs found',
        'not_found_in_trash' => 'No FAQs in Trash',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'insert_into_item' => 'Insert into FAQ',
        'uploaded_to_this_item' => 'Uploaded to this FAQ',
        'items_list' => 'FAQ list',
        'items_list_navigation' => 'FAQ list navigation',
        'filter_items_list' => 'Filter FAQ list',
    );

    $args = array(
        'label' => 'FAQ',
        'description' => 'Custom Post Type to manage FAQs',
        'labels' => $labels,
        'supports' => array('title', 'editor'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 23,
        'menu_icon' => 'dashicons-editor-help',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rest_base' => 'faq',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('faq', $args);
}
add_action('init', 'register_faq_post_type', 0);


/**
 * Register Custom Post Type "Event"
 * Solo gli amministratori possono gestire questo CPT
 */
function register_event_post_type()
{
    $labels = array(
        'name' => 'Events',
        'singular_name' => 'Event',
        'menu_name' => 'Events',
        'name_admin_bar' => 'Event',
        'archives' => 'Events Archive',
        'attributes' => 'Event Attributes',
        'parent_item_colon' => 'Parent Event:',
        'all_items' => 'All Events',
        'add_new_item' => 'Add New Event',
        'add_new' => 'Add New',
        'new_item' => 'New Event',
        'edit_item' => 'Edit Event',
        'update_item' => 'Update Event',
        'view_item' => 'View Event',
        'view_items' => 'View Events',
        'search_items' => 'Search Event',
        'not_found' => 'No events found',
        'not_found_in_trash' => 'No events in Trash',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'insert_into_item' => 'Insert into event',
        'uploaded_to_this_item' => 'Uploaded to this event',
        'items_list' => 'Events list',
        'items_list_navigation' => 'Events list navigation',
        'filter_items_list' => 'Filter events list',
    );

    $args = array(
        'label' => 'Event',
        'description' => 'Custom Post Type to manage events',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 4,
        'menu_icon' => 'dashicons-calendar',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rest_base' => 'events',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('event', $args);
}
add_action('init', 'register_event_post_type', 0);

/**
 * Register ACF Field Group for Event Custom Post Type
 */
if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_event_details',
        'title' => 'Event Details',
        'fields' => array(
            array(
                'key' => 'field_event_place',
                'label' => 'Place',
                'name' => 'event_place',
                'type' => 'post_object',
                'instructions' => 'Select event place',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'post_type' => array(
                    0 => 'place',
                ),
                'taxonomy' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'return_format' => 'object',
                'ui' => 1,
            ),
            array(
                'key' => 'field_event_date',
                'label' => 'Date',
                'name' => 'event_date',
                'type' => 'date_time_picker',
                'instructions' => 'Select event date and time',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'display_format' => 'd/m/Y H:i',
                'return_format' => 'Y-m-d H:i:s',
                'first_day' => 1,
            ),
            array(
                'key' => 'field_event_fb_link',
                'label' => 'Link Facebook Event',
                'name' => 'event_fb_link',
                'type' => 'url',
                'instructions' => 'Enter Facebook event link',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'https://facebook.com/events/...',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'event',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));

    acf_add_local_field_group(array(
        'key' => 'group_event_rankings',
        'title' => 'Ranking Fields',
        'fields' => array(
            array(
                'key' => 'field_event_rankings_json',
                'label' => 'Rankings JSON',
                'name' => 'event_rankings_json',
                'type' => 'textarea',
                'instructions' => 'Enter rankings in JSON format',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '[{"pos":1,"via":"40,7%","win":2,"deck":"Gut + Inspiring Leader","draw":0,"lose":1,"name":"Angelo Mar.","points":6},...]',
                'maxlength' => '',
                'rows' => 10,
                'new_lines' => '',
            ),
            array(
                'key' => 'field_event_ranking',
                'label' => 'Ranking',
                'name' => 'event_ranking',
                'type' => 'repeater',
                'instructions' => 'Add player rankings. Fill fields manually or import from JSON above.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => 'field_ranking_pos',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => 'Add Ranking',
                'sub_fields' => array(
                    array(
                        'key' => 'field_ranking_pos',
                        'label' => 'Pos.',
                        'name' => 'pos',
                        'type' => 'number',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '8',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => 1,
                        'max' => '',
                        'step' => 1,
                    ),
                    array(
                        'key' => 'field_ranking_player_id',
                        'label' => 'Player',
                        'name' => 'player_id',
                        'type' => 'user',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '20',
                            'class' => '',
                            'id' => '',
                        ),
                        'role' => array(
                            0 => 'player',
                        ),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'return_format' => 'array',
                    ),
                    array(
                        'key' => 'field_ranking_name',
                        'label' => 'Name',
                        'name' => 'name',
                        'type' => 'text',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '20',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => 'Player name',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_ranking_points',
                        'label' => 'Pt.',
                        'name' => 'points',
                        'type' => 'number',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '10',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => 0,
                        'max' => '',
                        'step' => 1,
                    ),
                    array(
                        'key' => 'field_ranking_win',
                        'label' => 'Wins',
                        'name' => 'win',
                        'type' => 'number',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '8',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => 0,
                        'max' => '',
                        'step' => 1,
                    ),
                    array(
                        'key' => 'field_ranking_lose',
                        'label' => 'Loses',
                        'name' => 'lose',
                        'type' => 'number',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '8',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => 0,
                        'max' => '',
                        'step' => 1,
                    ),
                    array(
                        'key' => 'field_ranking_draw',
                        'label' => 'Draws',
                        'name' => 'draw',
                        'type' => 'number',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '8',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => 0,
                        'max' => '',
                        'step' => 1,
                    ),
                    array(
                        'key' => 'field_ranking_via',
                        'label' => 'Via%',
                        'name' => 'via',
                        'type' => 'text',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '10',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => '%',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_ranking_deck',
                        'label' => 'Deck',
                        'name' => 'deck',
                        'type' => 'text',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'placeholder' => 'Deck name',
                        'maxlength' => '',
                    ),
                    array(
                        'key' => 'field_ranking_player_deck_id',
                        'label' => 'Deck ID',
                        'name' => 'player_deck_id',
                        'type' => 'number',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => 'acf-hidden',
                            'id' => '',
                        ),
                        'default_value' => '',
                    ),
                ),
            ),
            array(
                'key' => 'field_event_survey',
                'label' => 'Survey (Participants)',
                'name' => 'survey',
                'type' => 'repeater',
                'instructions' => 'List of users who participated',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => 'Add User',
                'sub_fields' => array(
                    array(
                        'key' => 'field_survey_user',
                        'label' => 'User',
                        'name' => 'user',
                        'type' => 'user',
                        'required' => 1,
                        'return_format' => 'id',
                        'allow_null' => 0,
                        'multiple' => 0,
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'event',
                ),
            ),
        ),
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));

endif;

/**
 * Add custom columns to Event admin list
 */
function event_custom_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['event_place'] = 'Place';
    $new_columns['event_date'] = 'Date';
    $new_columns['event_fb_link'] = 'FB';
    $new_columns['event_winner'] = 'Winner';
    $new_columns['event_players'] = 'Players';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_event_posts_columns', 'event_custom_columns');

/**
 * Populate custom columns data for Event
 */
function event_custom_columns_data($column, $post_id)
{
    switch ($column) {
        case 'event_place':
            $event_place = get_field('field_event_place', $post_id);
            if ($event_place) {
                $edit_link = get_edit_post_link($event_place->ID);
                echo '<a href="' . esc_url($edit_link) . '">' . esc_html($event_place->post_title) . '</a>';
            } else {
                echo '-';
            }
            break;
        case 'event_date':
            $event_date = get_field('field_event_date', $post_id);
            if ($event_date) {
                echo esc_html(date_i18n('d/m/Y', strtotime($event_date))) . '<br>' . esc_html(date_i18n('H:i', strtotime($event_date)));
            } else {
                echo '-';
            }
            break;
        case 'event_fb_link':
            $event_fb_link = get_field('field_event_fb_link', $post_id);
            if ($event_fb_link) {
                echo '<a href="' . esc_url($event_fb_link) . '" target="_blank" rel="noopener" style="color:#1877F2;"><span class="dashicons dashicons-facebook"></span></a>';
            } else {
                echo '-';
            }
            break;
        case 'event_winner':
            $all_rankings = array();

            // USE ONLY THE REPEATER FIELD (Ignore JSON as per user request)
            $repeater_sources = array('field_event_ranking', 'event_ranking', 'field_ranking', 'ranking');
            foreach ($repeater_sources as $source) {
                $repeater_data = get_field($source, $post_id);
                if (is_array($repeater_data) && !empty($repeater_data)) {
                    $all_rankings = $repeater_data;
                    break;
                }
            }

            $winner_data = null;
            if (is_array($all_rankings) && !empty($all_rankings)) {
                // Try to find the winner (pos=1) supporting multiple possible keys for position
                foreach ($all_rankings as $rank) {
                    $pos = null;
                    $pos_keys = array('pos', 'field_ranking_pos', 'position', 'rank', 'ranking', 'place');
                    foreach ($pos_keys as $pk) {
                        if (isset($rank[$pk]) && ($rank[$pk] == 1 || $rank[$pk] === '1')) {
                            $pos = 1;
                            break;
                        }
                    }

                    if ($pos == 1) {
                        $winner_data = $rank;
                        break;
                    }
                }
                // Fallback to first entry if no pos=1 found
                if (!$winner_data) {
                    $winner_data = $all_rankings[0];
                }
            }

            if ($winner_data) {
                // Robust extraction of user ID from multiple possible keys
                $raw_id = null;
                $keys_to_check = array('player_id', 'user_id', 'field_ranking_player_id', 'ID', 'userid', 'player', 'user');
                foreach ($keys_to_check as $key) {
                    if (isset($winner_data[$key]) && !empty($winner_data[$key])) {
                        $val = $winner_data[$key];
                        // If it's a numeric ID, or an object/array with ID/id, we accept it as ID source
                        if (is_numeric($val) || (is_array($val) && (isset($val['ID']) || isset($val['id']))) || (is_object($val) && (isset($val->ID) || isset($val->id)))) {
                            $raw_id = $val;
                            break;
                        }
                    }
                }

                $user_id = 0;
                if ($raw_id) {
                    if (is_array($raw_id)) {
                        if (isset($raw_id['ID'])) {
                            $user_id = $raw_id['ID'];
                        } elseif (isset($raw_id['id'])) {
                            $user_id = $raw_id['id'];
                        } else {
                            $first = reset($raw_id);
                            if (is_array($first) && (isset($first['ID']) || isset($first['id']))) {
                                $user_id = isset($first['ID']) ? $first['ID'] : $first['id'];
                            } elseif (is_numeric($first)) {
                                $user_id = intval($first);
                            }
                        }
                    } elseif (is_object($raw_id)) {
                        $user_id = isset($raw_id->ID) ? $raw_id->ID : (isset($raw_id->id) ? $raw_id->id : 0);
                    } elseif (is_numeric($raw_id)) {
                        $user_id = intval($raw_id);
                    }
                }

                $name = '-';
                $name_keys = array('name', 'field_ranking_name', 'display_name', 'player_name', 'player', 'user_name');
                foreach ($name_keys as $nk) {
                    if (isset($winner_data[$nk]) && !empty($winner_data[$nk]) && is_string($winner_data[$nk])) {
                        $name = $winner_data[$nk];
                        break;
                    }
                }

                if ($user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $display_name = $user->display_name;
                        $edit_link = get_edit_user_link($user_id);
                        echo '<a href="' . esc_url($edit_link) . '">' . esc_html($display_name) . '</a>';
                    } else {
                        echo esc_html($name);
                    }
                } else {
                    echo esc_html($name);
                }
            } else {
                echo '-';
            }
            break;
        case 'event_players':
            $all_rankings = array();
            $repeater_sources = array('field_event_ranking', 'event_ranking', 'field_ranking', 'ranking');
            foreach ($repeater_sources as $source) {
                $repeater_data = get_field($source, $post_id);
                if (is_array($repeater_data) && !empty($repeater_data)) {
                    $all_rankings = $repeater_data;
                    break;
                }
            }
            echo is_array($all_rankings) ? count($all_rankings) : 0;
            break;
    }
}
add_action('manage_event_posts_custom_column', 'event_custom_columns_data', 10, 2);

/**
 * Make custom columns sortable for Event
 */
function event_sortable_columns($columns)
{
    $columns['event_date'] = 'event_date';
    return $columns;
}
add_filter('manage_edit-event_sortable_columns', 'event_sortable_columns');

/**
 * Handle custom column sorting for Event
 */
function event_column_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    $orderby = $query->get('orderby');

    if ('event_date' == $orderby) {
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value');
    }

    // Set default order for events in admin if not specified
    if ($query->get('post_type') == 'event' && empty($orderby)) {
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value');
        $query->set('order', 'DESC');
    }
}
add_action('pre_get_posts', 'event_column_orderby');

/**
 * Hide Event menu from non-administrators
 */
function hide_event_menu_from_players()
{
    if (!lpdh_can_manage_content()) {
        remove_menu_page('edit.php?post_type=event');
    }
}
add_action('admin_menu', 'hide_event_menu_from_players', 999);

/**
 * Adjust event list column widths
 */
function lpdh_event_list_column_widths()
{
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-event') {
        echo '<style>
            .column-title { width: 25%; }
            .column-event_players { width: 60px !important; text-align: center; }
            .column-event_fb_link { width: 40px !important; text-align: center; }
            .column-event_date { width: 100px !important; }
        </style>';
    }
}
add_action('admin_head', 'lpdh_event_list_column_widths');

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
 * Restrict access to Event admin pages for non-administrators
 */
function restrict_event_admin_access()
{
    // Check if we're on event post type admin pages
    if (!lpdh_can_manage_content()) {
        $current_screen = get_current_screen();

        if ($current_screen && $current_screen->post_type === 'event') {
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'restrict_event_admin_access', 999);

/**
 * Auto-fill ranking name field when player is selected
 * Uses AJAX to get the user's display_name from WordPress
 */
function event_ranking_auto_fill_name()
{
    ?>
    <script type="text/javascript">
        (function ($) {
            // ACF user action - this ha        ndles the AJAX loaded user data
            if (typeof acf !== 'undefined') {
                acf.add_action('user', function (userData, $el) {
                    if (userData && userData.display_name) {
                        var $row = $el.closest('tr.acf-row');
                        var $nameField = $row.find('input[name*="field_ranking_name"]');
                        //Non aggiornare più il display name
                        //$nameField.val(userData.display_name);
                    }
                });
            }

            // Populate Rankings button functionality
            $(document).on('click', '#populate-rankings-btn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var $jsonField = $('#acf-field_event_rankings_json');
                var jsonData = $jsonField.val();

                if (!jsonData) {
                    console.warn('Nessun dato JSON presente nel campo Rankings JSON');
                    return;
                }

                try {
                    var rankings = JSON.parse(jsonData);

                    if (!Array.isArray(rankings) || rankings.length === 0) {
                        console.warn('Il formato JSON non è valido o è vuoto');
                        return;
                    }

                    // Find the ranking repeater
                    var $repeater = $('[data-name="event_ranking"]');
                    var $addButton = $repeater.find('.acf-button[data-event="add-row"]');

                    if (!$repeater.length || !$addButton.length) {
                        console.warn('Repeater rankings non trovato');
                        return;
                    }

                    var $tbody = $repeater.find('tbody');

                    // Clear existing rows
                    $tbody.find('.acf-row:not(.acf-clone)').remove();

                    // Use ACF's native add row functionality to ensure correct initialization (including nonces)
                    rankings.forEach(function (ranking) {
                        // Trigger add row
                        $addButton.trigger('click');

                        // Get the new row (last one)
                        var $row = $tbody.find('.acf-row:not(.acf-clone)').last();

                        // Map fields supporting both new format (filed_ranking_...) and old format (...)
                        var pos = ranking.field_ranking_pos !== undefined ? ranking.field_ranking_pos : ranking.pos;
                        var name = ranking.field_ranking_name !== undefined ? ranking.field_ranking_name : ranking.name;
                        var points = ranking.field_ranking_points !== undefined ? ranking.field_ranking_points : ranking.points;
                        var win = ranking.field_ranking_win !== undefined ? ranking.field_ranking_win : ranking.win;
                        var draw = ranking.field_ranking_draw !== undefined ? ranking.field_ranking_draw : ranking.draw;
                        var lose = ranking.field_ranking_lose !== undefined ? ranking.field_ranking_lose : ranking.lose;
                        var via = ranking.field_ranking_via !== undefined ? ranking.field_ranking_via : ranking.via;
                        var deck = ranking.field_ranking_deck !== undefined ? ranking.field_ranking_deck : (ranking.deck !== undefined ? ranking.deck : "");
                        var player_id = ranking.field_ranking_player_id !== undefined ? ranking.field_ranking_player_id : ranking.player_id;
                        var player_deck_id = ranking.field_ranking_player_deck_id !== undefined ? ranking.field_ranking_player_deck_id : (ranking.player_deck_id !== undefined ? ranking.player_deck_id : "");

                        // Populate UI fields
                        if (pos !== undefined) $row.find('[data-name="pos"] input').val(pos);
                        if (name !== undefined) $row.find('[data-name="name"] input').val(name);
                        if (points !== undefined) $row.find('[data-name="points"] input').val(points);
                        if (win !== undefined) $row.find('[data-name="win"] input').val(win);
                        if (draw !== undefined) $row.find('[data-name="draw"] input').val(draw);
                        if (lose !== undefined) $row.find('[data-name="lose"] input').val(lose);
                        if (via !== undefined) $row.find('[data-name="via"] input').val(via);
                        if (deck !== undefined) $row.find('[data-name="deck"] input').val(deck);
                        if (player_id !== undefined) {
                            var $userSelect = $row.find('[data-name="player_id"] select');
                            if ($userSelect.length && player_id) {
                                // For ACF user fields, we might need a more complex way to set it if it's Select2
                                // but if it's a standard select, this works. Usually ACF uses Select2.
                                // We'll try basic val() first, if it fails we might need acf.getField().val()
                            }
                            $row.find('[data-name="player_id"] input[type="hidden"]').val(player_id);
                        }
                        if (player_deck_id !== undefined) $row.find('[data-name="player_deck_id"] input').val(player_deck_id);
                    });

                } catch (err) {
                    console.error('Errore nel parsing JSON:', err);
                }
            });

            // Sync Players button functionality
            $(document).on('click', '#sync-players-btn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var $repeater = $('[data-name="event_ranking"]');
                var $rows = $repeater.find('.acf-row:not(.acf-clone)');
                var $msg = $('#sync-players-msg');

                $msg.hide().css('color', '');

                if (!$rows.length) {
                    $msg.text('No rows found in rankings.').css('color', '#d63638').show();
                    setTimeout(function () { $msg.fadeOut(); }, 5000);
                    return;
                }

                $btn.prop('disabled', true).text('Syncing...');

                var namesToSync = [];
                $rows.each(function (index) {
                    var $row = $(this);
                    var name = $row.find('[data-name="name"] input').val();

                    if (name) {
                        namesToSync.push({
                            row_index: index,
                            name: name
                        });
                    }
                });

                if (namesToSync.length === 0) {
                    $btn.prop('disabled', false).text('Sync Player');
                    return;
                }

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'sync_ranking_players',
                        names: namesToSync,
                        nonce: '<?php echo wp_create_nonce('sync_ranking_players_nonce'); ?>'
                    },
                    success: function (response) {
                        if (response.success && response.data) {
                            var matchCount = 0;
                            response.data.forEach(function (match) {
                                var $row = $rows.eq(match.row_index);
                                var $select = $row.find('[data-name="player_id"] select');

                                if ($select.length && match.user_id) {
                                    // If it's a Select2/AJAX field, we might need to add the option tag if it doesn't exist
                                    if ($select.find('option[value="' + match.user_id + '"]').length === 0) {
                                        $select.append(new Option(match.display_name, match.user_id, true, true));
                                    }
                                    $select.val(match.user_id).trigger('change');
                                    matchCount++;
                                }
                            });
                            $msg.text('Sync completed: ' + matchCount + ' players matched.').css('color', '#46b450').show();
                        } else {
                            $msg.text('No players found.').css('color', '#d63638').show();
                        }
                    },
                    error: function () {
                        $msg.text('Error during synchronization.').css('color', '#d63638').show();
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Sync Player');
                        setTimeout(function () { $msg.fadeOut(); }, 5000);
                    }
                });
            });

            // Clear Rankings button functionality
            $(document).on('click', '#clear-rankings-btn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var $repeater = $('[data-name="event_ranking"]');

                if (!$repeater.length) {
                    console.warn('Repeater rankings non trovato');
                    return;
                }

                var $tbody = $repeater.find('tbody');

                // Remove all existing rows
                $tbody.find('.acf-row').remove();

                // Trigger ACF update for the repeater
                acf.doAction('remove', $tbody);

                // Update row numbers
                $tbody.find('.acf-row-number').each(function (index) {
                    $(this).text(index + 1);
                });
            });

        })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'event_ranking_auto_fill_name');

/**
 * Add "Populate Rankings" and "Clear Rankings" buttons after rankings_json field
 */
function add_populate_rankings_button()
{
    ?>
    <script type="text/javascript">
        (function ($) {
            // Add buttons after rankings_json field
            function addButtons() {
                var $jsonField = $('#acf-field_event_rankings_json');

                if ($jsonField.length && !$('#populate-rankings-btn').length) {
                    $jsonField.after(
                        '<button type="button" id="populate-rankings-btn" class="button button-primary" style="margin-top:5px; margin-right:5px;">Populate Rankings</button>' +
                        '<button type="button" id="sync-players-btn" class="button button-secondary" style="margin-top:5px; margin-right:5px;">Sync Player</button>' +
                        '<button type="button" id="clear-rankings-btn" class="button button-secondary" style="margin-top:5px;">Clear Rankings</button>' +
                        '<span id="sync-players-msg" style="margin-left: 10px; font-weight: bold; display: none;"></span>'
                    );
                }
            }

            // Run on load and after ACF ready
            $(document).ready(function () {
                setTimeout(addButtons, 100);
            });

            acf.add_action('ready', addButtons);
        })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_head', 'add_populate_rankings_button');

/**
 * AJAX handler to sync ranking players based on name
 */
function ajax_sync_ranking_players()
{
    check_ajax_referer('sync_ranking_players_nonce', 'nonce');

    $names = isset($_POST['names']) ? $_POST['names'] : array();
    $matches = array();

    if (!empty($names)) {
        // Get all users with role 'player'
        $users = get_users(array(
            'role' => 'player',
        ));

        foreach ($names as $item) {
            $search_name = trim($item['name']);
            $search_name_clean = rtrim($search_name, '.'); // Remove trailing dot if present
            $found_user = null;

            foreach ($users as $user) {
                // Combine names for comparison
                $full_name = trim($user->first_name . ' ' . $user->last_name);
                $display_name = $user->display_name;

                // Try to match based on display name or combined first/last name
                // Checking if the candidate name starts with the search string
                if (
                    (stripos($display_name, $search_name_clean) === 0) ||
                    (stripos($full_name, $search_name_clean) === 0)
                ) {
                    $found_user = $user;
                    break;
                }
            }

            if ($found_user) {
                $matches[] = array(
                    'row_index' => $item['row_index'],
                    'user_id' => $found_user->ID,
                    'display_name' => $found_user->display_name
                );
            }
        }
    }

    wp_send_json_success($matches);
}
add_action('wp_ajax_sync_ranking_players', 'ajax_sync_ranking_players');

/**
 * Server-side approach - populate name from player_id on save
 * This ensures the name is always filled even if JS fails
 */
function event_populate_ranking_name_on_save($post_id)
{
    // Only process events
    if (get_post_type($post_id) !== 'event') {
        return $post_id;
    }

    // Only run for front-end saves or AJAX (to avoid infinite loop)
    if (wp_doing_ajax() || !is_admin()) {
        return $post_id;
    }

    // Get all ranking rows from post meta
    $rankings = get_post_meta($post_id, 'event_ranking', true);

    if (is_array($rankings) && !empty($rankings)) {
        $updated = false;

        foreach ($rankings as $index => &$ranking) {
            // If player_id is set but name is empty, populate it
            if (!empty($ranking['player_id']) && empty($ranking['name'])) {
                $user_data = get_userdata($ranking['player_id']);
                if ($user_data) {
                    $ranking['name'] = $user_data->display_name;
                    $updated = true;
                }
            }
        }

        // Update the meta with the modified data
        if ($updated) {
            update_post_meta($post_id, 'event_ranking', $rankings);
        }
    }

    return $post_id;
}
add_action('acf/save_post', 'event_populate_ranking_name_on_save', 20);

/**
 * AJAX handler to get user's decks
 * Returns decks as JSON for populating the player_deck select field
 */
function ajax_get_user_decks()
{
    check_ajax_referer('get_user_decks_nonce', 'nonce');

    $user_id = intval($_POST['user_id']);

    if ($user_id) {
        $decks = get_posts(array(
            'post_type' => 'deck',
            'author' => $user_id,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));

        if ($decks) {
            $deck_data = array();
            foreach ($decks as $deck) {
                $deck_data[] = array(
                    'ID' => $deck->ID,
                    'post_title' => $deck->post_title,
                );
            }
            wp_send_json_success($deck_data);
        } else {
            wp_send_json_success(array());
        }
    } else {
        wp_send_json_error(array('message' => 'Invalid User ID'));
    }
}
add_action('wp_ajax_get_user_decks', 'ajax_get_user_decks');

require_once get_stylesheet_directory() . '/inc/function-schema-color.php';

/**
 * Add AJAX handler for populating player_deck based on player_id selection
 * This script adds a temporary select dropdown with search below the deck field
 */
function event_ranking_populate_player_deck()
{
    ?>

    <script type="text/javascript">
        (function ($) {
            // Function to add temporary select with search below deck field
            function addDeckSelector($row, playerId) {
                // Remove any existing deck selector
                $row.find('.temp-deck-selector').remove();

                // Find the deck field
                var $deckField = $row.find('.acf-field-ranking-deck');
                var $deckIdField = $row.find('.acf-field-ranking-player-deck-id');

                if (!$deckField.length || !playerId) {
                    return;
                }

                // Create temporary select element with Select2
                var $selector = $(
                    '<div class="temp-deck-selector" style="margin-top:8px;">' +
                    '<label style="display:block;font-size:12px;color:#666;margin-bottom:4px;">Select deck:</label>' +
                    '<select class="deck-quick-select" style="width:100%;">' +
                    '<option value="">-- Search deck --</option>' +
                    '</select>' +
                    '</div>'
                );

                // Find the deck input and populate when selection changes
                var $deckInput = $deckField.find('input[type="text"]');
                var $deckIdInput = $deckIdField.find('input[type="number"]');

                $selector.find('select').on('change', function () {
                    var selectedDeckId = $(this).val();
                    var selectedDeckTitle = $(this).find('option:selected').text();

                    if (selectedDeckId) {
                        $deckInput.val(selectedDeckTitle);
                        $deckIdInput.val(selectedDeckId);
                    }
                });

                // Add selector after the deck field
                $deckField.append($selector);

                // Initialize Select2 on the new select
                $selector.find('select').select2({
                    placeholder: '-- Search deck --',
                    allowClear: true,
                    width: '100%'
                });

                // Fetch decks for this player
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'get_user_decks',
                        user_id: playerId,
                        nonce: '<?php echo wp_create_nonce('get_user_decks_nonce'); ?>'
                    },
                    beforeSend: function () {
                        $selector.find('select').html('<option value="">Loading...</option>');
                    },
                    success: function (response) {
                        var $select = $selector.find('select');
                        $select.html('<option value="">-- Search deck --</option>');

                        if (response.success && response.data && response.data.length > 0) {
                            $.each(response.data, function (index, deck) {
                                $select.append(
                                    $('<option></option>')
                                        .val(deck.ID)
                                        .text(deck.post_title)
                                );
                            });
                        } else {
                            $select.html('<option value="">No decks available</option>');
                        }

                        // Refresh Select2
                        $select.trigger('change.select2');
                    },
                    error: function () {
                        $selector.find('select').html('<option value="">Loading error</option>');
                        $selector.find('select').trigger('change.select2');
                    }
                });
            }

            // Handler for player_id selection in ranking repeater
            $(document).on('change', 'select[name*="field_ranking_player_id"]', function (e) {
                var $row = $(this).closest('tr.acf-row');
                var playerId = $(this).val();

                // Update the deck selector for this row
                addDeckSelector($row, playerId);
            });

            // Handle ACF's ready/append events
            acf.add_action('ready append', function ($el) {
                // Find all player_id selects in ranking rows
                $el.find('select[name*="field_ranking_player_id"]').each(function () {
                    var $row = $(this).closest('tr.acf-row');
                    var playerId = $(this).val();

                    if (playerId) {
                        addDeckSelector($row, playerId);
                    }
                });
            });

            // Clean up deck selector when row is removed
            $(document).on('acf/remove', '.acf-row', function () {
                $(this).find('.temp-deck-selector').remove();
            });

        })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'event_ranking_populate_player_deck');

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
 * Imposta il numero di post per pagina nell'archivio Eventi
 */
function bootscore_child_event_posts_per_page($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('event')) {
        $query->set('posts_per_page', 12);
    }
}
add_action('pre_get_posts', 'bootscore_child_event_posts_per_page');

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
 * Handle event participation survey via AJAX
 */
function ajax_toggle_event_participation()
{
    // Verify nonce
    check_ajax_referer('event_participation_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in.']);
    }

    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $user_id = get_current_user_id();

    if (!$event_id) {
        wp_send_json_error(['message' => 'Invalid event ID.']);
    }

    // Get current survey data
    $survey = get_field('survey', $event_id);
    if (!is_array($survey)) {
        $survey = [];
    }

    $found_index = -1;
    foreach ($survey as $index => $row) {
        // Check if user exists in repeater (assuming return_format is ID)
        $stored_user = is_array($row['user']) ? $row['user']['ID'] : (is_object($row['user']) ? $row['user']->ID : $row['user']);

        if ($stored_user == $user_id) {
            $found_index = $index;
            break;
        }
    }

    $action = '';
    if ($found_index >= 0) {
        // Remove user
        unset($survey[$found_index]);
        $survey = array_values($survey); // Re-index
        $action = 'removed';
    } else {
        // Add user
        $survey[] = ['user' => $user_id];
        $action = 'added';
    }

    // Update the field
    update_field('survey', $survey, $event_id);

    wp_send_json_success(['action' => $action, 'count' => count($survey)]);
}
add_action('wp_ajax_toggle_event_participation', 'ajax_toggle_event_participation');

/**
 * Add "Update Survey" button before survey repeater in Event CPT
 */
function add_update_survey_button_script()
{
    ?>
    <script type="text/javascript">
        (function ($) {
            // Add button before survey field
            function addUpdateSurveyButton() {
                // Target specific field by key to be safe
                var $surveyField = $('.acf-field[data-key="field_event_survey"]');

                if ($surveyField.length && !$('#update-survey-btn').length) {
                    // Append to the label area of the survey field
                    $surveyField.find('> .acf-label').append(
                        '<div class="update-survey-controls" style="margin-top: 10px;">' +
                        '<button type="button" id="update-survey-btn" class="button button-primary">Update Survey</button>' +
                        '<span id="update-survey-msg" style="margin-left: 10px; font-weight: bold; display: none;"></span>' +
                        '<p class="description" style="margin-top: 5px;">Automatically adds players from rankings to the survey field if they are not already present.</p>' +
                        '</div>'
                    );
                }
            }

            // Run on load and after ACF ready
            $(document).ready(function () {
                setTimeout(addUpdateSurveyButton, 500);
            });

            if (typeof acf !== 'undefined') {
                acf.add_action('ready', addUpdateSurveyButton);
            }

            // Handle click
            $(document).on('click', '#update-survey-btn', function (e) {
                e.preventDefault();

                var $msg = $('#update-survey-msg');
                // Reset message
                $msg.hide().css('color', '');

                var players = [];

                // Get players from rankings
                var $rankingRows = $('[data-name="event_ranking"] .acf-row:not(.acf-clone)');
                $rankingRows.each(function () {
                    var $field = $(this).find('[data-name="player_id"]');
                    var $select = $field.find('select');
                    var val = $select.val();

                    // Try to get text for the option
                    var text = '';
                    if ($select.length && val) {
                        text = $select.find('option[value="' + val + '"]').text();
                    }

                    // Fallback if text is empty (get it from the name field)
                    if (!text) {
                        var $nameInput = $(this).find('[data-name="name"] input');
                        if ($nameInput.length) {
                            text = $nameInput.val();
                        }
                    }

                    if (val) {
                        players.push({ id: val, text: text || 'User ' + val });
                    }
                });

                // Get existing survey users
                var existingIds = [];
                var $surveyRepeater = $('.acf-field[data-key="field_event_survey"]');
                var $surveyRows = $surveyRepeater.find('.acf-row:not(.acf-clone)');
                $surveyRows.each(function () {
                    // Check both select and hidden input (for different ACF versions/settings)
                    var $field = $(this).find('[data-key="field_survey_user"]');
                    var $input = $field.find('select');
                    if (!$input.length) $input = $field.find('input[type="hidden"]');
                    var val = $input.val();
                    if (val) {
                        existingIds.push(val);
                    }
                });

                // Filter new users
                var newPlayers = players.filter(function (player) {
                    return existingIds.indexOf(player.id) === -1;
                });

                // Remove duplicates
                var uniquePlayers = [];
                var uniqueIds = [];
                $.each(newPlayers, function (i, el) {
                    if ($.inArray(el.id, uniqueIds) === -1) {
                        uniqueIds.push(el.id);
                        uniquePlayers.push(el);
                    }
                });
                newPlayers = uniquePlayers;

                if (newPlayers.length === 0) {
                    $msg.text('All players are already present.').css('color', '#d63638').show();
                    return;
                }

                // Add rows using DOM manipulation with delay to ensure fields are ready
                var $addButton = $surveyRepeater.find('.acf-button[data-event="add-row"]');

                if ($addButton.length) {
                    var addedCount = 0;

                    function addNextUser(index) {
                        if (index >= newPlayers.length) {
                            $msg.text('Successfully added ' + addedCount + ' users!').css('color', '#46b450').show();
                            setTimeout(function () { $msg.fadeOut(); }, 5000);
                            return;
                        }

                        var player = newPlayers[index];
                        $addButton.click();

                        // Wait a tick for DOM update and ACF initialization
                        setTimeout(function () {
                            var $newRow = $surveyRepeater.find('.acf-row:not(.acf-clone)').last();
                            var $field = $newRow.find('[data-key="field_survey_user"]');
                            var $select = $field.find('select');

                            if ($select.length) {
                                // If it's a Select2/AJAX field, we might need to add the option tag if it doesn't exist
                                if ($select.find('option[value="' + player.id + '"]').length === 0) {
                                    $select.append(new Option(player.text, player.id, true, true));
                                }
                                $select.val(player.id).trigger('change');
                            } else {
                                // Fallback for hidden input
                                $field.find('input[type="hidden"]').val(player.id).trigger('change');
                            }

                            addedCount++;
                            addNextUser(index + 1);
                        }, 200);
                    }

                    addNextUser(0);
                } else {
                    alert('Error: Cannot find "Add Row" button.');
                }
            });

        })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'add_update_survey_button_script');

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
 * Add "Update Leaderboard" button for Leaderboard CPT
 */
function add_update_leaderboard_button()
{
    $screen = get_current_screen();
    if (!$screen || $screen->post_type !== 'leaderboard') {
        return;
    }
    ?>
    <script type="text/javascript">
        (function ($) {
            function addUpdateLeaderboardButton() {
                var $jsonField = $('.acf-field[data-key="field_leaderboard_rankings_json"]');

                if ($jsonField.length && !$('#update-leaderboard-btn').length) {
                    $jsonField.find('.acf-input').append(
                        '<button type="button" id="update-leaderboard-btn" class="button button-primary" style="margin-top:10px;">Update Leaderboard</button>' +
                        '<span id="update-leaderboard-msg" style="margin-left: 10px; font-weight: bold; display: none;"></span>'
                    );
                }
            }

            $(document).ready(function () {
                setTimeout(addUpdateLeaderboardButton, 500);
            });

            if (typeof acf !== 'undefined') {
                acf.add_action('ready', addUpdateLeaderboardButton);
            }

            $(document).on('click', '#update-leaderboard-btn', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var $msg = $('#update-leaderboard-msg');
                var $yearField = $('.acf-field[data-key="field_leaderboard_year"] select');
                var year = $yearField.val();

                if (!year) {
                    alert('Select a year before updating.');
                    return;
                }

                $btn.prop('disabled', true).text('Updating...');
                $msg.hide();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'update_leaderboard_rankings',
                        year: year,
                        post_id: <?php echo get_the_ID() ? get_the_ID() : 0; ?>,
                        nonce: '<?php echo wp_create_nonce('update_leaderboard_nonce'); ?>'
                    },
                    success: function (response) {
                        if (response.success) {
                            var $textarea = $('.acf-field[data-key="field_leaderboard_rankings_json"] textarea');
                            $textarea.val(JSON.stringify(response.data, null, 2));
                            $msg.text('Leaderboard updated!').css('color', '#46b450').show();
                        } else {
                            $msg.text('Error: ' + (response.data || 'Unknown')).css('color', '#d63638').show();
                        }
                    },
                    error: function () {
                        $msg.text('Connection error.').css('color', '#d63638').show();
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Update Leaderboard');
                        setTimeout(function () { $msg.fadeOut(); }, 5000);
                    }
                });
            });
        })(jQuery);
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'add_update_leaderboard_button');

/**
 * Helper function to calculate rankings from a list of events
 */
function lpdh_calculate_rankings_data($events)
{
    $general = array();
    $player_elos = array();

    foreach ($events as $event) {
        $rankings = get_field('event_ranking', $event->ID);

        if (is_array($rankings)) {
            $total_players = count($rankings);

            // Passaggio 1: Calcola ELO medio del torneo (forza del campo)
            $event_participants_names = array();
            $total_event_elo = 0;

            foreach ($rankings as $rank) {
                $name = isset($rank['name']) ? trim($rank['name']) : '';
                // Risoluzione nome se mancante (logica semplificata per pre-calcolo)
                if (empty($name)) {
                    $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : 0;
                    if (!empty($player_id_field)) {
                        $uid = is_array($player_id_field) ? $player_id_field['ID'] : $player_id_field;
                        $u = get_userdata($uid);
                        if ($u)
                            $name = $u->display_name;
                    }
                }

                if (empty($name))
                    continue;

                if (!isset($player_elos[$name])) {
                    $player_elos[$name] = LPDH_DEFAULT_ELO; // ELO Base
                }
                $event_participants_names[] = $name;
                $total_event_elo += $player_elos[$name];
            }

            $avg_elo = count($event_participants_names) > 0 ? $total_event_elo / count($event_participants_names) : LPDH_DEFAULT_ELO;

            foreach ($rankings as $rank) {
                $name = isset($rank['name']) ? trim($rank['name']) : '';
                $user_id = 0;

                $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : 0;
                if (!empty($player_id_field)) {
                    if (is_array($player_id_field) && isset($player_id_field['ID'])) {
                        $user_id = $player_id_field['ID'];
                    } elseif (is_numeric($player_id_field)) {
                        $user_id = $player_id_field;
                    }
                }

                if (empty($name) && $user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $name = $user->display_name;
                    }
                }

                if (empty($name))
                    continue;

                if (!isset($general[$name])) {
                    $general[$name] = array(
                        'name' => $name,
                        'user_id' => $user_id,
                        'points' => 0,
                        'win' => 0,
                        'lose' => 0,
                        'draw' => 0,
                        'count' => 0,
                        'first' => 0,
                        'last' => 0,
                        'elo' => LPDH_DEFAULT_ELO
                    );
                } else {
                    // Update user_id if it was missing and now we have it
                    if (empty($general[$name]['user_id']) && $user_id) {
                        $general[$name]['user_id'] = $user_id;
                    }
                }

                $wins = intval(isset($rank['win']) ? $rank['win'] : 0);
                $draws = intval(isset($rank['draw']) ? $rank['draw'] : 0);
                $losses = intval(isset($rank['lose']) ? $rank['lose'] : 0);

                $general[$name]['points'] += intval(isset($rank['points']) ? $rank['points'] : 0);
                $general[$name]['win'] += $wins;
                $general[$name]['lose'] += $losses;
                $general[$name]['draw'] += $draws;
                $general[$name]['count']++;

                $pos = intval(isset($rank['pos']) ? $rank['pos'] : 0);
                if ($pos === 1) {
                    $general[$name]['first']++;
                }
                if ($pos === $total_players) {
                    $general[$name]['last']++;
                }

                // Calcolo ELO
                $current_elo = $player_elos[$name];
                $games_played = $wins + $draws + $losses;

                if ($games_played > 0) {
                    $elo_data = lpdh_calculate_elo($current_elo, $wins, $draws, $losses, $avg_elo, $pos, $total_players);
                    $new_elo = $elo_data['new_elo'];

                    $player_elos[$name] = $new_elo;
                }

                $general[$name]['elo'] = round($player_elos[$name]);
            }
        }
    }

    $result = array_values($general);

    usort($result, function ($a, $b) {
        return $b['points'] - $a['points'];
    });

    return $result;
}

/**
 * AJAX handler to calculate and update leaderboard rankings
 */
function ajax_update_leaderboard_rankings()
{
    check_ajax_referer('update_leaderboard_nonce', 'nonce');

    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

    if (!$year) {
        wp_send_json_error('Invalid year');
    }

    $args = array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'event_date',
        'orderby' => 'meta_value',
        'order' => 'ASC', // Ordine cronologico per calcolo ELO
        'meta_query' => array(
            array(
                'key' => 'event_date',
                'value' => array($year . '-01-01 00:00:00', $year . '-12-31 23:59:59'),
                'compare' => 'BETWEEN',
                'type' => 'DATETIME'
            )
        )
    );

    $all_events = get_posts($args);

    // Filter events to only include those that actually happened (have rankings)
    $valid_events = array();
    foreach ($all_events as $e) {
        $rank_data = get_field('event_ranking', $e->ID);
        if (!empty($rank_data) && is_array($rank_data)) {
            $valid_events[] = $e;
        }
    }

    // 1. Calcolo Classifica Attuale (Basata solo sui tornei validi)
    $result = lpdh_calculate_rankings_data($valid_events);

    // 2. Calcolo Classifica Precedente (per il trend)
    // Escludiamo l'ultimo torneo VALIDO per vedere come è cambiata la classifica dopo l'ultimo evento reale
    $previous_events = $valid_events;
    if (count($previous_events) > 0) {
        array_pop($previous_events);
    }
    $previous_result = lpdh_calculate_rankings_data($previous_events);

    // Mappa posizioni precedenti
    $prev_rank_map = array();
    foreach ($previous_result as $idx => $p) {
        $prev_rank_map[$p['name']] = $idx + 1;
    }

    // Calcola Trend
    foreach ($result as $idx => &$p) {
        $current_rank = $idx + 1;
        if (isset($prev_rank_map[$p['name']])) {
            $prev = $prev_rank_map[$p['name']];
            $p['trend'] = $prev - $current_rank; // Positivo = salito (es. era 5, ora 2 => +3)
        } else {
            $p['trend'] = 'new';
        }
    }

    if ($post_id) {
        update_field('field_leaderboard_rankings_json', json_encode($result), $post_id);
        // Aggiorna la data di modifica del post
        wp_update_post(array(
            'ID' => $post_id,
            'post_modified' => current_time('mysql'),
            'post_modified_gmt' => current_time('mysql', 1)
        ));
    }

    wp_send_json_success($result);
}
add_action('wp_ajax_update_leaderboard_rankings', 'ajax_update_leaderboard_rankings');

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
 * Ordina archivio Leaderboard per anno decrescente
 */
function bootscore_child_leaderboard_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('leaderboard')) {
        $query->set('meta_key', 'year');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'DESC');
        $query->set('posts_per_page', -1);
    }
}
add_action('pre_get_posts', 'bootscore_child_leaderboard_archive_query');

/**
 * Register custom query var for Place search
 */
function lpdh_register_place_query_vars($vars)
{
    $vars[] = 'place_q';
    return $vars;
}
add_filter('query_vars', 'lpdh_register_place_query_vars');

/**
 * Handle Place search (Title OR Address)
 */
function lpdh_place_search_join($join, $query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('place') && $query->get('place_q')) {
        global $wpdb;
        $join .= " LEFT JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = 'place_address') ";
    }
    return $join;
}
add_filter('posts_join', 'lpdh_place_search_join', 10, 2);

function lpdh_place_search_where($where, $query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('place') && $query->get('place_q')) {
        global $wpdb;
        $search_term = $wpdb->esc_like($query->get('place_q'));
        // Search in Title OR place_address meta
        $where .= " AND ({$wpdb->posts}.post_title LIKE '%{$search_term}%' OR {$wpdb->postmeta}.meta_value LIKE '%{$search_term}%') ";
    }
    return $where;
}
add_filter('posts_where', 'lpdh_place_search_where', 10, 2);

/**
 * Ensure distinct results for Place search
 */
function lpdh_place_search_distinct($distinct, $query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('place') && $query->get('place_q')) {
        return "DISTINCT";
    }
    return $distinct;
}
add_filter('posts_distinct', 'lpdh_place_search_distinct', 10, 2);

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
 * Ordina archivio FAQ per data crescente (ordine di inserimento)
 */
function bootscore_child_faq_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('faq')) {
        $query->set('orderby', 'date');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', -1);
    }
}
add_action('pre_get_posts', 'bootscore_child_faq_archive_query');

/**
 * Ordina archivio Place per data crescente (ordine di inserimento)
 */
function bootscore_child_place_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('place')) {
        $query->set('orderby', 'date');
        $query->set('order', 'ASC');
    }
}
add_action('pre_get_posts', 'bootscore_child_place_archive_query');

/**
 * Filtra archivio Eventi per Anno e Place
 */
function bootscore_child_event_archive_filter($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('event')) {
        $meta_query = array('relation' => 'AND');

        if (isset($_GET['event_year']) && !empty($_GET['event_year'])) {
            $year = intval($_GET['event_year']);
            $meta_query[] = array(
                'key' => 'event_date',
                'value' => array($year . '-01-01 00:00:00', $year . '-12-31 23:59:59'),
                'compare' => 'BETWEEN',
                'type' => 'DATETIME'
            );
        }

        if (isset($_GET['event_place_id']) && !empty($_GET['event_place_id'])) {
            $place_id = intval($_GET['event_place_id']);
            $meta_query[] = array(
                'key' => 'event_place',
                'value' => $place_id,
                'compare' => '='
            );
        }

        if (count($meta_query) > 1) {
            $query->set('meta_query', $meta_query);
        }

        // Set default order by event_date DESC
        $query->set('meta_key', 'event_date');
        $query->set('orderby', 'meta_value');
        $query->set('order', 'DESC');
    }
}
add_action('pre_get_posts', 'bootscore_child_event_archive_filter');

/**
 * AJAX handler to update player deck in event ranking
 */
function ajax_update_player_deck_ranking()
{
    check_ajax_referer('update_player_deck_nonce', 'nonce');

    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $row_index = isset($_POST['row_index']) ? intval($_POST['row_index']) : -1;
    $deck_id = isset($_POST['deck_id']) ? intval($_POST['deck_id']) : 0;
    $user_id = get_current_user_id();

    if (!$event_id || $row_index < 0 || !$user_id) {
        wp_send_json_error('Invalid data');
    }

    // Get current rankings
    $rankings = get_field('field_event_ranking', $event_id, false);

    if (!isset($rankings[$row_index])) {
        wp_send_json_error('Ranking row not found');
    }

    // Verify user permission (must be the player in the row)
    $rank_player = $rankings[$row_index]['player_id'];
    $rank_player_id = 0;

    if (is_array($rank_player) && isset($rank_player['ID'])) {
        $rank_player_id = $rank_player['ID'];
    } elseif (is_object($rank_player)) {
        $rank_player_id = $rank_player->ID;
    } else {
        $rank_player_id = intval($rank_player);
    }

    if ($rank_player_id !== $user_id && !current_user_can('administrator') && !current_user_can('player')) {
        wp_send_json_error('Permission denied');
    }

    // Get Deck Name
    $deck_title = '';
    if ($deck_id) {
        $deck_post = get_post($deck_id);
        if ($deck_post && $deck_post->post_type === 'deck') {
            $deck_title = $deck_post->post_title;
        }
    }

    // Check if value is actually changing to avoid update_field returning false on no change
    if (isset($rankings[$row_index]['player_deck_id']) && $rankings[$row_index]['player_deck_id'] == $deck_id) {
        wp_send_json_success();
    }

    // Update the specific row
    $rankings[$row_index]['player_deck_id'] = $deck_id;
    $rankings[$row_index]['deck'] = $deck_title;

    // Update the field
    update_field('field_event_ranking', $rankings, $event_id);

    // Verify the update
    $updated_rankings = get_field('field_event_ranking', $event_id, false);

    // Double check with simpler name if first check failed or returned empty
    if (empty($updated_rankings)) {
        $updated_rankings = get_field('event_ranking', $event_id, false);
    }

    if (isset($updated_rankings[$row_index]['player_deck_id']) && $updated_rankings[$row_index]['player_deck_id'] == $deck_id) {
        // Also update the JSON version of rankings (event_rankings_json) to keep it in sync
        // IMPORTANT: We use the local $rankings array because we manually set the 'deck' title in it above.
        // Fetching from DB ($updated_rankings) might return the old 'deck' text value due to race conditions or caching.
        $json_rankings = $rankings;

        // Ensure we are saving clean values
        foreach ($json_rankings as &$row) {
            // Ensure numeric values are numbers if needed, but ACF usually returns strings or mixed.
            // The placeholder suggests simple JSON.
        }

        $json_string = json_encode($json_rankings);
        update_field('field_event_rankings_json', $json_string, $event_id);

        // Fallback for JSON field plain meta update if needed
        update_post_meta($event_id, 'event_rankings_json', $json_string);

        wp_send_json_success();
    } else {
        // Fallback: Try manual meta update if ACF failed (assuming Distributed Meta)
        // Try both potential naming conventions
        $manual_update_1 = update_post_meta($event_id, 'field_event_ranking_' . $row_index . '_player_deck_id', $deck_id);
        $manual_update_2 = update_post_meta($event_id, 'event_ranking_' . $row_index . '_player_deck_id', $deck_id);

        // Manual Update for Deck Title (TEXT FIELD) which is also missed if ACF fails
        update_post_meta($event_id, 'field_event_ranking_' . $row_index . '_deck', $deck_title);
        update_post_meta($event_id, 'event_ranking_' . $row_index . '_deck', $deck_title);

        // Manual Update for JSON Field
        // We utilize the local $rankings array which has the correct values
        $json_rankings = $rankings;
        $json_string = json_encode($json_rankings);

        // Attempt ACF update for JSON
        update_field('field_event_rankings_json', $json_string, $event_id);
        // Force Meta update for JSON
        update_post_meta($event_id, 'event_rankings_json', $json_string);

        // Final verification check
        $final_check_1 = get_post_meta($event_id, 'field_event_ranking_' . $row_index . '_player_deck_id', true);
        $final_check_2 = get_post_meta($event_id, 'event_ranking_' . $row_index . '_player_deck_id', true);

        // Verify just the ID as a proxy for success
        if ($final_check_1 == $deck_id || $final_check_2 == $deck_id) {
            wp_send_json_success();
            return;
        }

        wp_send_json_error('Update failed');
    }
}
add_action('wp_ajax_update_player_deck_ranking', 'ajax_update_player_deck_ranking');

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
 * Register Email Test Admin Page as submenu under LPDH
 */
function lpdh_register_email_test_page()
{
    add_submenu_page(
        'lpdh-main',
        'Email Test',
        'Email Test',
        'manage_options',
        'lpdh-email-test',
        'lpdh_render_email_test_page'
    );
}
add_action('admin_menu', 'lpdh_register_email_test_page');

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
 * Render Email Test Admin Page
 */
function lpdh_render_email_test_page()
{
    // Security check - admin only
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this page.'));
    }

    // Get sample data
    $sample_data = lpdh_get_sample_email_data(1);

    // Get available templates
    $templates = array(
        'new-user-welcome' => 'New User Welcome Email',
        'admin-new-user-notification' => 'Admin New User Notification',
    );

    // Get available themes
    $themes = array(
        'default' => 'Bootscore (Default)',
        'vaporwave' => 'Vaporwave',
        'vaporwave-green' => 'Vaporwave Green',
        'lost-wood' => 'Lost Wood',
    );

    // Current selections
    $selected_template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'new-user-welcome';
    $selected_theme = isset($_GET['theme_override']) ? sanitize_text_field($_GET['theme_override']) : get_option('lpdh_active_theme', 'default');
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-email"></span> Email Template Testing</h1>

        <!-- Test Controls -->
        <div class="card" style="max-width: none;">
            <h2>Template Settings</h2>
            <form method="get" id="email-test-form">
                <input type="hidden" name="page" value="lpdh-email-test">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="template">Email Template</label></th>
                        <td>
                            <select name="template" id="template" class="regular-text">
                                <?php foreach ($templates as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_template, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="theme_override">Theme Override</label></th>
                        <td>
                            <select name="theme_override" id="theme_override" class="regular-text">
                                <?php foreach ($themes as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_theme, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Current active theme:
                                <strong><?php echo esc_html(ucwords(str_replace('-', ' ', get_option('lpdh_active_theme', 'default')))); ?></strong>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Update Preview', 'primary', 'submit', false); ?>
                <button type="button" class="button" id="reset-btn">Reset</button>
            </form>
        </div>

        <!-- Preview Section -->
        <div class="card" style="max-width: none; margin-top: 20px;">
            <h2>Email Preview</h2>
            <div style="border: 1px solid #ddd; background: #f9f9f9; padding: 10px;">
                <iframe id="email-preview-iframe" style="width: 100%; min-height: 600px; border: none; background: white;"
                    src="<?php echo esc_url(add_query_arg(array(
                        'action' => 'lpdh_preview_email',
                        'template' => $selected_template,
                        'theme' => $selected_theme,
                        'nonce' => wp_create_nonce('lpdh_email_preview')
                    ), admin_url('admin-ajax.php'))); ?>">
                </iframe>
            </div>
        </div>

        <!-- Send Test Email Section -->
        <div class="card" style="max-width: none; margin-top: 20px;">
            <h2>Send Test Email</h2>
            <p>Send the currently previewed email template to a test email address using the current theme override setting.
            </p>
            <div id="email-send-result"></div>
            <form id="send-test-email-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="test_email">Recipient Email Address</label></th>
                        <td>
                            <input type="email" id="test_email" name="test_email"
                                value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" class="regular-text"
                                required>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="template" value="<?php echo esc_attr($selected_template); ?>">
                <input type="hidden" name="theme" value="<?php echo esc_attr($selected_theme); ?>">
                <input type="hidden" name="action" value="lpdh_send_test_email">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('lpdh_send_test_email'); ?>">
                <?php submit_button('Send Test Email', 'primary', 'submit', false, array('id' => 'send-test-btn')); ?>
            </form>
            <p class="description">
                <span class="dashicons dashicons-info"></span>
                <strong>Note:</strong> Using sample data from user ID: 1
                (<?php echo esc_html($sample_data['user_login']); ?>)
            </p>
        </div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            // Reset button
            $('#reset-btn').on('click', function () {
                window.location.href = '?page=lpdh-email-test';
            });

            // Update iframe when form changes
            $('#email-test-form').on('submit', function (e) {
                e.preventDefault();
                const template = $('#template').val();
                const theme = $('#theme_override').val();
                const iframeSrc = '<?php echo admin_url('admin-ajax.php'); ?>?' +
                    'action=lpdh_preview_email&' +
                    'template=' + template + '&' +
                    'theme=' + theme + '&' +
                    'nonce=<?php echo wp_create_nonce('lpdh_email_preview'); ?>';

                $('#email-preview-iframe').attr('src', iframeSrc);

                // Update form action values
                $('input[name="template"]').val(template);
                $('input[name="theme"]').val(theme);

                // Update URL
                window.history.pushState({}, '', '?page=lpdh-email-test&template=' + template + '&theme_override=' + theme);
            });

            // Send test email
            $('#send-test-email-form').on('submit', function (e) {
                e.preventDefault();

                const $btn = $('#send-test-btn');
                const $result = $('#email-send-result');
                const formData = $(this).serialize();

                // Disable button
                $btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none;margin:0 5px 0 0;"></span>Sending...');
                $result.empty();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            $result.html(
                                '<div class="notice notice-success"><p><span class="dashicons dashicons-yes"></span> ' + response.data.message + '</p></div>'
                            );
                        } else {
                            $result.html(
                                '<div class="notice notice-error"><p><span class="dashicons dashicons-warning"></span> ' + response.data.message + '</p></div>'
                            );
                        }
                    },
                    error: function () {
                        $result.html(
                            '<div class="notice notice-error"><p><span class="dashicons dashicons-warning"></span> An error occurred while sending the email.</p></div>'
                        );
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('Send Test Email');
                    }
                });
            });
        });
    </script>

    <style>
        .card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        #email-send-result .notice {
            margin: 10px 0;
        }

        #email-send-result .dashicons {
            vertical-align: middle;
        }
    </style>
    <?php
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
 * Add OCR Ranking Generator Metabox to Events
 */
function lpdh_add_event_ocr_metabox()
{
    add_meta_box(
        'lpdh_event_ocr_generator',
        'OCR Ranking Generator (Screenshot to JSON)',
        'lpdh_render_event_ocr_metabox',
        'event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lpdh_add_event_ocr_metabox');

/**
 * Render Event OCR Ranking Generator Metabox
 */
function lpdh_render_event_ocr_metabox($post)
{
    // Enqueue Tesseract.js (v5)
    wp_enqueue_script('tesseract-js', get_stylesheet_directory_uri() . '/assets/js/tesseract.min.js', array(), '5.0.2', true);
    ?>
    <div style="padding: 15px;">
        <div
            style="margin-bottom: 15px; padding: 12px; border-left: 4px solid #ffb300; border-radius: 4px; font-size: 14px; line-height: 1.4; color: #856404;">
            <strong><span class="dashicons dashicons-warning" style="vertical-align: text-top; margin-right: 5px;"></span>
                Warning:</strong> The OCR recognition system may not be 100% accurate. Please carefully verify the generated
            data in the table below before applying it to the ranking JSON.
        </div>
        <div
            style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px; padding: 10px; border: 1px solid #ccd0d4; border-radius: 4px;">
            <span style="font-weight: 600;">Device Type:</span>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                <input type="radio" name="lpdh_ocr_device" value="automatic" checked> Automatic
            </label>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                <input type="radio" name="lpdh_ocr_device" value="android"> Android
            </label>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 5px;">
                <input type="radio" name="lpdh_ocr_device" value="iphone"> iPhone
            </label>
        </div>

        <div id="lpdh-ocr-container"
            style="border: 2px dashed #ccd0d4; padding: 30px; text-align: center; cursor: pointer; border-radius: 4px;">
            <span class="dashicons dashicons-upload"
                style="font-size: 40px; width: 40px; height: 40px; color: #2271b1;"></span>
            <p style="margin: 10px 0; font-size: 14px;"><strong>Drag & Drop</strong> ranking screenshot here or <a href="#"
                    id="lpdh-ocr-browse">browse files</a></p>
            <input type="file" id="lpdh-ocr-file-input" style="display: none;" accept="image/*">
        </div>

        <div id="lpdh-ocr-progress-container" style="display: none; margin-top: 15px;">
            <div style="height: 10px; background: #e0e0e0; border-radius: 5px; overflow: hidden; margin-bottom: 5px;">
                <div id="lpdh-ocr-progress-bar"
                    style="width: 0%; height: 100%; background: #2271b1; transition: width 0.2s ease;"></div>
            </div>
            <p id="lpdh-ocr-status" style="font-size: 11px; color: #666; font-style: italic;">Initializing Tesseract...</p>
        </div>

        <div id="lpdh-ocr-results" style="display: none; margin-top: 20px; text-align: left;">
            <h4 id="lpdh-ocr-title" style="margin-top: 0; border-bottom: 1px solid #ddd; padding-bottom: 10px;">OCR
                Candidates</h4>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd;">
                <table class="wp-list-table widefat fixed striped" style="border: none;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">Pos</th>
                            <th>Player Name</th>
                            <th style="width: 40px;">Pts</th>
                            <th style="width: 80px;">W-L-D</th>
                            <th style="width: 60px;">Via%</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody id="lpdh-ocr-tbody">
                        <!-- Rows populated via JS -->
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 15px; display: flex; gap: 10px;">
                <button type="button" id="lpdh-ocr-apply" class="button button-primary">Apply to Ranking JSON</button>
                <button type="button" id="lpdh-ocr-reset" class="button button-secondary">Clear and Restart</button>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            const $container = $('#lpdh-ocr-container');
            const $input = $('#lpdh-ocr-file-input');
            const $browse = $('#lpdh-ocr-browse');
            const $progress = $('#lpdh-ocr-progress-container');
            const $bar = $('#lpdh-ocr-progress-bar');
            const $status = $('#lpdh-ocr-status');
            const $results = $('#lpdh-ocr-results');
            const $tbody = $('#lpdh-ocr-tbody');
            const $apply = $('#lpdh-ocr-apply');
            const $reset = $('#lpdh-ocr-reset');
            const $jsonField = $('#acf-field_event_rankings_json');

            // Drag & Drop Handlers
            $container.on('dragover dragenter', function (e) {
                e.preventDefault();
                $(this).css({ background: '#f0f6fb', borderColor: '#2271b1' });
            }).on('dragleave dragend drop', function (e) {
                if (e.type === 'drop') {
                    e.preventDefault();
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length) processFile(files[0]);
                }
                $(this).css({ borderColor: '#ccd0d4' });
            });

            $browse.click(e => { e.preventDefault(); $input.click(); });
            $input.change(e => { if (e.target.files.length) processFile(e.target.files[0]); });

            async function processFile(file) {
                $progress.show();
                $results.hide();
                $tbody.empty();

                try {
                    // Pre-process image using Canvas to improve OCR (Grayscale + Contrast)
                    const img = new Image();
                    img.src = URL.createObjectURL(file);
                    await new Promise(resolve => img.onload = resolve);

                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;

                    // Specific pre-processing based on device selection
                    let device = $('input[name="lpdh_ocr_device"]:checked').val();

                    // Automatic Detection: iPhone vs Android heuristic
                    if (device === 'automatic') {
                        const ratio = img.height / img.width;

                        // Range for modern tall iPhones (X to 15) is very specific: ~2.16-2.17
                        // Androids vary more, often 2.22 (Sony/tall) or 2.0 (standard).
                        device = (ratio > 2.14 && ratio < 2.18) ? 'iphone' : 'android';

                        // Refinement: Scan for characterist selection colors
                        // iPhone uses Orange highlight, Android uses Purple highlight
                        const scanCanvas = document.createElement('canvas');
                        scanCanvas.width = 1; scanCanvas.height = img.height;
                        const scanCtx = scanCanvas.getContext('2d');
                        scanCtx.drawImage(img, Math.floor(img.width * 0.5), 0, 1, img.height, 0, 0, 1, img.height);
                        const data = scanCtx.getImageData(0, 0, 1, img.height).data;

                        let seenOrange = false; let seenPurple = false;
                        for (let i = 0; i < data.length; i += 4) {
                            const r = data[i], g = data[i + 1], b = data[i + 2];
                            // Orange detection (iPhone): R>180, G~100, B<100
                            if (r > 180 && g > 80 && g < 150 && b < 100) seenOrange = true;
                            // Purple detection (Android): R~100, G~100, B>200
                            if (r > 80 && r < 150 && g > 80 && g < 150 && b > 200) seenPurple = true;
                        }

                        if (seenOrange && !seenPurple) device = 'iphone';
                        if (seenPurple && !seenOrange) device = 'android';

                        console.log('LPDH OCR: Auto-detect (Ratio:', ratio.toFixed(3), 'Orange:', seenOrange, 'Purple:', seenPurple, ') ->', device);
                    }

                    // Update UI title with device type
                    const deviceLabel = device === 'iphone' ? 'iPhone' : 'Android';
                    $('#lpdh-ocr-title').text('OCR Candidates (' + deviceLabel + ')');

                    if (device === 'iphone') {
                        // Balanced boost for dark mode: too much contrast/brightness "washes out" character details
                        ctx.filter = 'grayscale(1) contrast(2.5) brightness(1.1)';
                    } else {
                        // Original stable value for Android
                        ctx.filter = 'grayscale(1) contrast(1.5)';
                    }
                    ctx.drawImage(img, 0, 0);

                    const processedBlob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png'));
                    const processedFile = new File([processedBlob], "processed.png", { type: "image/png" });

                    const worker = await Tesseract.createWorker('eng+ita', 1, {
                        logger: m => {
                            if (m.status === 'recognizing text') {
                                $bar.css('width', (m.progress * 100) + '%');
                                $status.text('Recognizing... ' + Math.round(m.progress * 100) + '%');
                            } else {
                                $status.text(m.status);
                            }
                        }
                    });

                    // Use the processed image
                    const { data: { text, lines } } = await worker.recognize(processedFile);
                    await worker.terminate();
                    URL.revokeObjectURL(img.src);

                    console.log('--- OCR FULL TEXT START ---');
                    console.log(text);
                    console.log('--- OCR FULL TEXT END ---');

                    parseLines(lines);
                    $progress.hide();
                    $results.show();
                } catch (err) {
                    console.error(err);
                    alert('OCR failed: ' + err.message);
                    $progress.hide();
                }
            }

            function parseLines(lines) {
                const parsed = [];
                let lastPos = 0;
                const device = $('input[name="lpdh_ocr_device"]:checked').val();

                lines.forEach(lineObj => {
                    let text = lineObj.text.trim();
                    if (!text || text.length < 3) return;

                    console.log('--- Evaluating line:', text);

                    // Skip headers and known junk
                    const skipKeywords = ['DOPO', 'TURNO', 'RANKING', 'EVENTO', 'ROUND', 'TABLE', 'V-S-P', '%VIA', '%VP', 'INCONTRO', 'GIOCATORE', 'CLASSIFICA', 'PUNTI', 'POS '];
                    const upperText = text.toUpperCase();

                    // clock filter (e.g. ":31") - check for colon followed by digits at start
                    if (/^[:;]\d+/.test(text.trim())) {
                        console.log('Line skipped (clock/time noise)');
                        return;
                    }

                    if (skipKeywords.some(kw => upperText.includes(kw))) {
                        console.log('Line skipped (header/junk match)');
                        return;
                    }

                    // Pre-cleaning: remove artifacts at start and normalize spaces
                    text = text.replace(/^[^\w\(]+/, '').replace(/[\|｜\[\]]/g, ' ').replace(/\s+/g, ' ').trim();

                    // Specific trailing noise cleanup (important for iPhone)
                    // Remove random characters at the end that aren't part of a percentage or record
                    text = text.replace(/[^0-9%.\-]+$/, '').trim();

                    try {
                        let pos = '', name = '', points = '0', win = '0', draw = '0', lose = '0', via = '0%';

                        // A. Get Position (Forward Search)
                        const posMatch = text.match(/^(\d+)/);
                        if (posMatch) {
                            pos = posMatch[1];
                            text = text.substring(posMatch[0].length).trim();
                        } else {
                            const lazyPosMatch = text.match(/^\D*(\d+)/);
                            if (lazyPosMatch) {
                                pos = lazyPosMatch[1];
                                text = text.substring(text.indexOf(pos) + pos.length).trim();
                            }
                        }
                        if ((!pos || isNaN(pos)) && device === 'iphone') {
                            pos = (lastPos + 1).toString();
                        }
                        lastPos = parseInt(pos) || lastPos;

                        // B. Find the start of data tokens (Numbers/Records/Percentages)
                        // We look for where the name ends and the numeric data begins.
                        const dataStartMatch = text.match(/(\s\d+[\s\-\.\–\—]|\s\d+$)/);
                        let nameRaw = text;
                        let tail = '';

                        if (dataStartMatch) {
                            const idx = text.indexOf(dataStartMatch[1]);
                            nameRaw = text.substring(0, idx).trim();
                            tail = text.substring(idx).trim();
                        }

                        // C. Name Cleanup
                        nameRaw = nameRaw.replace(/^[\s\.\,\)\-]+/, '').replace(/(\.\.\.|\.\s+).*$/, '').replace(/[^a-zA-Z\s\'].*$/, '').replace(/\.*$/, '').trim();
                        const nameParts = nameRaw.split(/\s+/);
                        if (nameParts.length >= 2) {
                            const firstName = nameParts[0];
                            const rest = nameRaw.substring(nameRaw.indexOf(firstName) + firstName.length).trim();
                            let formattedSurname = '';
                            let letterCount = 0;
                            for (let i = 0; i < rest.length; i++) {
                                const char = rest[i];
                                formattedSurname += char;
                                if (/[a-zA-Z]/.test(char)) letterCount++;
                                if (letterCount === 3) break;
                            }
                            name = firstName + ' ' + formattedSurname + '.';
                        } else {
                            name = nameRaw;
                        }

                        // D. Tokenization of Tail (Points, Record, Via)
                        // Split tail into tokens: dashed records, percentages, or numbers
                        const tokens = tail.split(/[\s\–\—]+/).filter(t => t.length > 0);
                        let foundPoints = false;
                        let foundRecord = false;
                        let foundVia = false;

                        tokens.forEach(token => {
                            // 1. Check for Record (W-D-L) e.g. "2-1-1" or "2.1.1"
                            const recordMatch = token.match(/^(\d+)[\-\.\/](\d+)[\-\.\/](\d+)$/);
                            if (!foundRecord && recordMatch) {
                                win = recordMatch[1];
                                lose = recordMatch[2];
                                draw = recordMatch[3];
                                foundRecord = true;
                                return;
                            }

                            // 2. Check for Percentage (Via)
                            // We pick the FIRST percentage token we find after the record (or in general)
                            if (!foundVia && (token.includes('%') || token.includes('.') || token.includes(','))) {
                                const numVal = token.replace(',', '.').replace('%', '');
                                if (!isNaN(parseFloat(numVal)) && (token.includes('%') || (numVal.includes('.') && numVal.length >= 3))) {
                                    let v = parseFloat(numVal);
                                    if (device === 'iphone' && v > 100) v = v / 10;
                                    via = (v % 1 === 0 ? v.toFixed(0) : v.toFixed(1)) + '%';
                                    foundVia = true;
                                    return;
                                }
                            }

                            // 3. Check for Points (First plain number that isn't position)
                            if (!foundPoints && /^\d+$/.test(token)) {
                                points = token;
                                foundPoints = true;
                                return;
                            }

                            // 4. Predictive Record Split (iPhone 3-digit merge)
                            if (!foundRecord && /^\d{3}$/.test(token) && device === 'iphone' && !foundPoints) {
                                win = token[0]; lose = token[1]; draw = token[2];
                                foundRecord = true;
                            }
                        });

                        if (name && name.length >= 3 && pos) {
                            parsed.push({ pos, name, points, win, draw, lose, via });
                        }
                    } catch (e) {
                        console.error('Line parse error:', e, text);
                    }
                });

                if (parsed.length) {
                    parsed.forEach((item, i) => {
                        const row = `
                        <tr>
                            <td><input type="text" class="ocr-pos" value="${item.pos}" style="width:100%;"></td>
                            <td><input type="text" class="ocr-name" value="${item.name}" style="width:100%;"></td>
                            <td><input type="text" class="ocr-points" value="${item.points}" style="width:100%;"></td>
                            <td>
                                <input type="text" class="ocr-win" value="${item.win}" style="width:20px;">-
                                <input type="text" class="ocr-lose" value="${item.lose}" style="width:20px;">-
                                <input type="text" class="ocr-draw" value="${item.draw}" style="width:20px;">
                            </td>
                            <td><input type="text" class="ocr-via" value="${item.via}" style="width:100%;"></td>
                            <td><button type="button" class="ocr-del" style="background:none; border:none; color:red; cursor:pointer;">&times;</button></td>
                        </tr>
                    `;
                        $tbody.append(row);
                    });
                } else {
                    $tbody.append('<tr><td colspan="6" style="text-align:center;">Could not parse any clear ranking rows. Please verify image quality.</td></tr>');
                }
            }

            $tbody.on('click', '.ocr-del', function () { $(this).closest('tr').remove(); });

            $reset.click(() => {
                $input.val('');
                $tbody.empty();
                $results.hide();
                $progress.hide();
            });

            $apply.click(() => {
                const data = [];
                $tbody.find('tr').each(function () {
                    const $row = $(this);
                    if ($row.find('.ocr-name').length) {
                        data.push({
                            field_ranking_pos: $row.find('.ocr-pos').val(),
                            field_ranking_name: $row.find('.ocr-name').val(),
                            field_ranking_points: $row.find('.ocr-points').val(),
                            field_ranking_win: $row.find('.ocr-win').val(),
                            field_ranking_draw: $row.find('.ocr-draw').val(),
                            field_ranking_lose: $row.find('.ocr-lose').val(),
                            field_ranking_via: $row.find('.ocr-via').val()
                        });
                    }
                });

                if (data.length) {
                    $jsonField.val(JSON.stringify(data, null, 2)).trigger('change');

                    // Show success feedback
                    const $icon = $apply.prev().length ? $apply.prev() : $('<span class="dashicons dashicons-yes" style="color:#46b450; margin-right:5px;"></span>').insertBefore($apply);
                    $apply.text('Done!');
                    setTimeout(() => {
                        $apply.text('Apply to Ranking JSON');
                        $icon.remove();
                    }, 2000);

                    // Scroll to the JSON field and highlight it
                    $('html, body').animate({
                        scrollTop: $jsonField.offset().top - 100
                    }, 500);
                    $jsonField.css('border-color', '#46b450');
                    setTimeout(() => $jsonField.css('border-color', ''), 3000);
                }
            });
        });
    </script>
    <style>
        #lpdh-ocr-results input {
            padding: 2px 5px;
            height: 26px;
            font-size: 12px;
        }

        #lpdh-ocr-results td {
            padding: 4px;
            vertical-align: middle;
        }
    </style>
    <?php
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
 * Get Instagram Generator URL for Event
 */
function lpdh_get_instagram_generator_url($event_id)
{
    // Return admin page URL instead of frontend page
    return admin_url('admin.php?page=lpdh-instagram-generator&ig_event_id=' . intval($event_id));
}

/**
 * Add Share Event Metabox to Events
 */
function lpdh_add_event_share_metabox()
{
    add_meta_box(
        'lpdh_event_share',
        'Share Event',
        'lpdh_render_event_share_metabox',
        'event',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'lpdh_add_event_share_metabox', 9);

/**
 * Render Share Event Metabox
 */
function lpdh_render_event_share_metabox($post)
{
    $title = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8');
    $date = get_field('event_date', $post->ID);
    $place_obj = get_field('event_place', $post->ID);
    $place_name = $place_obj ? $place_obj->post_title : 'TBA';
    $permalink = get_permalink($post->ID);

    // Format date if it exists
    if ($date) {
        $date_formatted = date('d/m/Y H:i', strtotime($date));
    } else {
        $date_formatted = 'TBA';
    }

    $share_text = $title . "\n" . $date_formatted . " @ " . $place_name . "\n" . $permalink;

    echo '<div class="lpdh-share-box">';
    echo '<p class="description" style="margin-bottom: 8px;">Copy this text to share the event on social media or groups:</p>';
    echo '<textarea id="lpdh-share-text" readonly data-permalink="' . esc_attr($permalink) . '" style="width: 100%; height: 85px; margin-bottom: 10px; font-family: monospace; font-size: 12px; background: #f9f9f9; border: 1px solid #ddd; padding: 8px; border-radius: 4px; resize: none;">' . esc_textarea($share_text) . '</textarea>';
    echo '<button type="button" class="button button-secondary" style="width: 100%;" onclick="lpdhCopyShareText(event)">';
    echo '<span class="dashicons dashicons-clipboard" style="margin-top: 5px; font-size: 16px;"></span> ';
    echo 'Copy Text</button>';
    echo '</div>';

    ?>
    <script>


        (function ($) {
            function htmlEntityDecode(str) {
                return $('<textarea/>').html(str).text();
            }

            function updateShareText() {
                var $textarea = $('#lpdh-share-text');
                if (!$textarea.length) return;

                var permalink = $textarea.data('permalink');
                var title = htmlEntityDecode($('#title').val() || '');

                // Get Date from ACF
                var date = 'TBA';
                if (typeof acf !== 'undefined') {
                    var dateField = acf.getField('field_event_date');
                    if (dateField) {
                        var rawDate = dateField.val();
                        if (rawDate) {
                            try {
                                // ACF date time picker value is usually Y-m-d H:i:s
                                var d = new Date(rawDate.replace(/-/g, "/"));
                                if (!isNaN(d.getTime())) {
                                    var day = ("0" + d.getDate()).slice(-2);
                                    var month = ("0" + (d.getMonth() + 1)).slice(-2);
                                    var year = d.getFullYear();
                                    var hours = ("0" + d.getHours()).slice(-2);
                                    var mins = ("0" + d.getMinutes()).slice(-2);
                                    date = day + "/" + month + "/" + year + " " + hours + ":" + mins;
                                }
                            } catch (e) { }
                        }
                    }

                    // Get Place from ACF
                    var place = 'TBA';
                    var placeField = acf.getField('field_event_place');
                    if (placeField) {
                        // Try to get selection text from select2
                        var selection = placeField.$el.find('.select2-selection__rendered').attr('title');
                        if (!selection) selection = placeField.$el.find('.acf-selection').text();
                        if (!selection) selection = placeField.$el.find('span.select2-selection__rendered').text();

                        if (selection) {
                            place = selection.replace('Remove', '').trim();
                            // If it still says "Select event place" or similar, use TBA
                            if (place.toLowerCase().indexOf('select') !== -1) place = 'TBA';
                        }
                    }
                }

                var newText = title + "\n" + date + " @ " + place + "\n" + permalink;
                $textarea.val(newText);
            }

            window.lpdhCopyShareText = function (e) {
                // Update text before copying
                updateShareText();

                var copyText = document.getElementById("lpdh-share-text");
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value).then(function () {
                    var btn = e.currentTarget;
                    var originalHtml = btn.innerHTML;
                    btn.innerHTML = '<span class="dashicons dashicons-yes" style="margin-top: 5px; font-size: 16px;"></span> Copied!';
                    setTimeout(function () {
                        btn.innerHTML = originalHtml;
                    }, 2000);
                });
            };
        })(jQuery);
    </script>
    <style>
        .lpdh-share-box textarea:focus {
            outline: none;
            border-color: #ddd;
            box-shadow: none;
        }
    </style>
    <?php
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
