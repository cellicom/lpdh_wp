<?php
/**
 * @package Bootscore Child
 *
 * @version 6.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Enqueue scripts and styles
 */
add_action('wp_enqueue_scripts', 'bootscore_child_enqueue_styles');
function bootscore_child_enqueue_styles()
{

    // Fonts CSS
    $modified_fontsCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/css/fonts.css'));
    wp_enqueue_style('fonts', get_stylesheet_directory_uri() . '/assets/css/fonts.css', array(), $modified_fontsCss);

    // Compiled main.css (depends on parent-style and fonts to load after font definitions)
    $modified_bootscoreChildCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/css/main.css'));
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
}

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
 * Assegnazione delle capabilities 'leaderboard' solo all'Amministratore.
 * Questo assicura che solo gli admin possano gestire questo CPT.
 */
function add_leaderboard_caps_to_admin()
{
    $role = get_role('administrator');

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

        foreach ($caps as $cap) {
            if (!$role->has_cap($cap)) {
                $role->add_cap($cap);
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
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
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
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
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
                'label' => 'Featured Image Partner',
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

endif;

/**
 * Add custom columns to Deck admin list
 */
function deck_custom_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['decklist'] = 'Decklist';
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
        case 'decklist':
            $decklist = get_field('field_decklist', $post_id);
            if ($decklist) {
                echo '<a href="' . esc_url($decklist) . '" target="_blank" rel="noopener">External Link</a>';
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
 * Restrict admin menu for players - show only Dashboard, Profile, and Decks
 * Administrators with player role see everything (admin has priority)
 */
function restrict_admin_menu_for_players()
{
    // If user is admin, show everything
    if (current_user_can('administrator')) {
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

        // Banned Card CPT
        remove_menu_page('edit.php?post_type=banned_card');
    }
}
add_action('admin_menu', 'restrict_admin_menu_for_players', 999);

/**
 * Hide admin bar items for players (frontend)
 */
function hide_admin_bar_items_for_players($wp_admin_bar)
{
    if (current_user_can('administrator')) {
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
    if (current_user_can('administrator')) {
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

            // Redirect to dashboard
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'redirect_players_from_restricted_pages');

/**
 * Register Custom Post Type "Banned Card"
 * Solo gli amministratori possono gestire questo CPT
 */
function register_banned_card_post_type()
{
    $labels = array(
        'name' => 'Banned Cards',
        'singular_name' => 'Banned Card',
        'menu_name' => 'Banned Cards',
        'name_admin_bar' => 'Banned Card',
        'archives' => 'Banned Cards Archive',
        'attributes' => 'Banned Card Attributes',
        'parent_item_colon' => 'Parent Banned Card:',
        'all_items' => 'All Banned Cards',
        'add_new_item' => 'Add New Banned Card',
        'add_new' => 'Add New',
        'new_item' => 'New Banned Card',
        'edit_item' => 'Edit Banned Card',
        'update_item' => 'Update Banned Card',
        'view_item' => 'View Banned Card',
        'view_items' => 'View Banned Cards',
        'search_items' => 'Search Banned Card',
        'not_found' => 'No banned cards found',
        'not_found_in_trash' => 'No banned cards in Trash',
        'featured_image' => 'Featured Image',
        'set_featured_image' => 'Set featured image',
        'remove_featured_image' => 'Remove featured image',
        'use_featured_image' => 'Use as featured image',
        'insert_into_item' => 'Insert into banned card',
        'uploaded_to_this_item' => 'Uploaded to this banned card',
        'items_list' => 'Banned cards list',
        'items_list_navigation' => 'Banned cards list navigation',
        'filter_items_list' => 'Filter banned cards list',
    );

    $args = array(
        'label' => 'Banned Card',
        'description' => 'Custom Post Type to manage banned cards',
        'labels' => $labels,
        'supports' => array('title', 'editor', 'thumbnail'),
        'hierarchical' => false,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 21,
        'menu_icon' => 'dashicons-dismiss',
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => true,
        'can_export' => true,
        'has_archive' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'capability_type' => 'post',
        'show_in_rest' => true,
        'rest_base' => 'banned_cards',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('banned_card', $args);
}
add_action('init', 'register_banned_card_post_type', 0);

/**
 * Register ACF Field Group for Banned Card Custom Post Type
 */
if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_banned_card_custom_fields',
        'title' => 'Banned Card Fields',
        'fields' => array(
            array(
                'key' => 'field_scryfall_link',
                'label' => 'Scryfall Link',
                'name' => 'scryfall_link',
                'type' => 'url',
                'instructions' => 'Enter card link on Scryfall',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'https://scryfall.com/card/...',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'banned_card',
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
 * Add custom columns to Banned Card admin list
 */
function banned_card_custom_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['scryfall_link'] = 'Scryfall Link';
    $new_columns['shortcode'] = 'Shortcode';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_banned_card_posts_columns', 'banned_card_custom_columns');

/**
 * Populate custom columns data for Banned Card
 */
function banned_card_custom_columns_data($column, $post_id)
{
    switch ($column) {
        case 'scryfall_link':
            $scryfall_link = get_field('field_scryfall_link', $post_id);
            if ($scryfall_link) {
                echo '<a href="' . esc_url($scryfall_link) . '" target="_blank" rel="noopener">Scryfall Link</a>';
            } else {
                echo '-';
            }
            break;
        case 'shortcode':
            echo '<code style="cursor: pointer; background: #f0f0f1; padding: 3px 5px; border-radius: 3px; border: 1px solid #ccd0d4;" onclick="navigator.clipboard.writeText(this.innerText); alert(\'Shortcode copied!\');">[banned_card id="' . $post_id . '" align="left"]</code>';
            break;
    }
}
add_action('manage_banned_card_posts_custom_column', 'banned_card_custom_columns_data', 10, 2);

/**
 * Hide Banned Card menu from non-administrators
 */
function hide_banned_card_menu_from_players()
{
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=banned_card');
    }
}
add_action('admin_menu', 'hide_banned_card_menu_from_players', 999);

/**
 * Restrict access to Banned Card admin pages for non-administrators
 */
function restrict_banned_card_admin_access()
{
    // Check if we're on banned_card post type admin pages
    if (!current_user_can('administrator')) {
        $current_screen = get_current_screen();

        if ($current_screen && $current_screen->post_type === 'banned_card') {
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'restrict_banned_card_admin_access', 999);

/**
 * Hide Banned Card from admin bar for non-administrators
 */
function hide_banned_card_admin_bar($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-banned_card');
    }
}
add_action('admin_bar_menu', 'hide_banned_card_admin_bar', 999);

/**
 * Remove Banned Card from "New" menu in admin bar for non-admins
 */
function remove_banned_card_from_new_menu($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        foreach ($wp_admin_bar->get_nodes() as $id => $node) {
            if (strpos($id, 'new-banned_card') !== false) {
                $wp_admin_bar->remove_node($id);
            }
        }
    }
}
add_action('admin_bar_menu', 'remove_banned_card_from_new_menu', 999);

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

    // If user is admin, show all tabs
    if (current_user_can('administrator')) {
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

    // If user is admin, do nothing
    if (current_user_can('administrator')) {
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
    if (current_user_can('administrator')) {
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
    $new_columns['author'] = 'Author';
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
        case 'author':
            $author_id = get_post_field('post_author', $post_id);
            $author_user = get_userdata($author_id);
            if ($author_user) {
                echo '<a href="' . esc_url(get_edit_user_link($author_id)) . '">' . esc_html($author_user->display_name) . '</a>';
            }
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
 * Hide Place menu from non-administrators
 */
function hide_place_menu_from_players()
{
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=place');
    }
}
add_action('admin_menu', 'hide_place_menu_from_players', 999);

/**
 * Restrict access to Place admin pages for non-administrators
 */
function restrict_place_admin_access()
{
    // Check if we're on place post type admin pages
    if (!current_user_can('administrator')) {
        $current_screen = get_current_screen();

        if ($current_screen && $current_screen->post_type === 'place') {
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'restrict_place_admin_access', 999);

/**
 * Hide Place from admin bar for non-administrators
 */
function hide_place_admin_bar($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-place');
    }
}
add_action('admin_bar_menu', 'hide_place_admin_bar', 999);

/**
 * Remove Place from "New" menu in admin bar for non-admins
 */
function remove_place_from_new_menu($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        foreach ($wp_admin_bar->get_nodes() as $id => $node) {
            if (strpos($id, 'new-place') !== false) {
                $wp_admin_bar->remove_node($id);
            }
        }
    }
}
add_action('admin_bar_menu', 'remove_place_from_new_menu', 999);

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
 * Hide FAQ menu from non-administrators
 */
function hide_faq_menu_from_players()
{
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=faq');
    }
}
add_action('admin_menu', 'hide_faq_menu_from_players', 999);

/**
 * Restrict access to FAQ admin pages for non-administrators
 */
function restrict_faq_admin_access()
{
    // Check if we're on faq post type admin pages
    if (!current_user_can('administrator')) {
        $current_screen = get_current_screen();

        if ($current_screen && $current_screen->post_type === 'faq') {
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'restrict_faq_admin_access', 999);

/**
 * Hide FAQ from admin bar for non-administrators
 */
function hide_faq_admin_bar($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-faq');
    }
}
add_action('admin_bar_menu', 'hide_faq_admin_bar', 999);

/**
 * Remove FAQ from "New" menu in admin bar for non-admins
 */
function remove_faq_from_new_menu($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        foreach ($wp_admin_bar->get_nodes() as $id => $node) {
            if (strpos($id, 'new-faq') !== false) {
                $wp_admin_bar->remove_node($id);
            }
        }
    }
}
add_action('admin_bar_menu', 'remove_faq_from_new_menu', 999);

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
        'menu_position' => 24,
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
        'key' => 'group_event_custom_fields',
        'title' => 'Event Fields',
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
 * Add custom columns to Event admin list
 */
function event_custom_columns($columns)
{
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['event_place'] = 'Place';
    $new_columns['event_date'] = 'Date';
    $new_columns['event_fb_link'] = 'Facebook Event';
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
                echo esc_html($event_place->post_title);
            } else {
                echo '-';
            }
            break;
        case 'event_date':
            $event_date = get_field('field_event_date', $post_id);
            if ($event_date) {
                echo esc_html(date_i18n('d/m/Y H:i', strtotime($event_date)));
            } else {
                echo '-';
            }
            break;
        case 'event_fb_link':
            $event_fb_link = get_field('field_event_fb_link', $post_id);
            if ($event_fb_link) {
                echo '<a href="' . esc_url($event_fb_link) . '" target="_blank" rel="noopener">Link Facebook</a>';
            } else {
                echo '-';
            }
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
        $query->set('meta_key', 'field_event_date');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'event_column_orderby');

/**
 * Hide Event menu from non-administrators
 */
function hide_event_menu_from_players()
{
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=event');
    }
}
add_action('admin_menu', 'hide_event_menu_from_players', 999);

/**
 * Restrict access to Event admin pages for non-administrators
 */
function restrict_event_admin_access()
{
    // Check if we're on event post type admin pages
    if (!current_user_can('administrator')) {
        $current_screen = get_current_screen();

        if ($current_screen && $current_screen->post_type === 'event') {
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action('admin_init', 'restrict_event_admin_access', 999);

/**
 * Hide Event from admin bar for non-administrators
 */
function hide_event_admin_bar($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-event');
    }
}
add_action('admin_bar_menu', 'hide_event_admin_bar', 999);

/**
 * Remove Event from "New" menu in admin bar for non-admins
 */
function remove_event_from_new_menu($wp_admin_bar)
{
    if (!current_user_can('administrator')) {
        foreach ($wp_admin_bar->get_nodes() as $id => $node) {
            if (strpos($id, 'new-event') !== false) {
                $wp_admin_bar->remove_node($id);
            }
        }
    }
}
add_action('admin_bar_menu', 'remove_event_from_new_menu', 999);

/**
 * Auto-fill ranking name field when player is selected
 * Uses AJAX to get the user's display_name from WordPress
 */
function event_ranking_auto_fill_name()
{
    ?>
    <script type="text/javascript">
        (function ($) {
            // ACF user action - this handles the AJAX loaded user data
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

                        // Populate fields
                        if (ranking.pos !== undefined) $row.find('[data-name="pos"] input').val(ranking.pos);
                        if (ranking.name !== undefined) $row.find('[data-name="name"] input').val(ranking.name);
                        if (ranking.points !== undefined) $row.find('[data-name="points"] input').val(ranking.points);
                        if (ranking.win !== undefined) $row.find('[data-name="win"] input').val(ranking.win);
                        if (ranking.draw !== undefined) $row.find('[data-name="draw"] input').val(ranking.draw);
                        if (ranking.lose !== undefined) $row.find('[data-name="lose"] input').val(ranking.lose);
                        if (ranking.via !== undefined) $row.find('[data-name="via"] input').val(ranking.via);
                        if (ranking.deck !== undefined) $row.find('[data-name="deck"] input').val(ranking.deck);
                        if (ranking.player_deck_id !== undefined) $row.find('[data-name="player_deck_id"] input').val(ranking.player_deck_id);
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
            $found_user = null;

            // Try to parse "Name Sur." (Firstname + 3 chars of Lastname + dot)
            $parts = explode(' ', $search_name);
            if (count($parts) > 1) {
                $last_chunk = end($parts);
                // Check if it ends with dot and has length 4 (3 chars + dot) e.g. "Mar."
                if (substr($last_chunk, -1) === '.') {
                    $short_last = substr($last_chunk, 0, -1); // "Mar"
                    $first_part = implode(' ', array_slice($parts, 0, -1)); // "Angelo"

                    foreach ($users as $user) {
                        // Check first name exact match and last name starts with short_last
                        if (strcasecmp($user->first_name, $first_part) === 0 && stripos($user->last_name, $short_last) === 0) {
                            $found_user = $user;
                            break;
                        }
                    }
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

require_once get_stylesheet_directory() . '/function-schema-color.php';

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
    if (current_user_can('administrator') && isset($_GET['stats_user_id'])) {
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
    $events_query = new WP_Query(array(
        'post_type' => 'event',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'meta_key' => 'event_date',
        'orderby' => 'meta_value',
        'order' => 'ASC' // Chronological for ELO calc
    ));

    // Collect available years
    $available_years = array();
    if ($events_query->have_posts()) {
        foreach ($events_query->posts as $p) {
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

    // ELO Tracking
    $player_elos = array();
    $elo_history_labels = array();
    $elo_history_data = array();

    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $event_id = get_the_ID();

            // Filter by year
            $event_date_raw = get_field('event_date', $event_id);
            $event_year = $event_date_raw ? date('Y', strtotime($event_date_raw)) : '';

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
                        $player_elos[$name] = 1200;
                    }
                    $event_participants_names[] = $name;
                    $total_event_elo += $player_elos[$name];
                }
                $avg_elo = count($event_participants_names) > 0 ? $total_event_elo / count($event_participants_names) : 1200;

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
                            $actual_score = $wins + ($draws * 0.5);
                            $expected_score_rate = 1 / (1 + pow(10, ($avg_elo - $current_elo) / 400));
                            $expected_score = $expected_score_rate * $games_played;
                            $k_factor = 32;

                            $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                            $rank_score = ($total_players > 1) ? ($total_players - $pos) / ($total_players - 1) : 1;
                            $position_adjustment = 20 * ($rank_score - 0.5);

                            $player_elos[$name] = $current_elo + $k_factor * ($actual_score - $expected_score) + $position_adjustment;
                        }
                    }

                    if ($p_id == $user_id) {
                        // Found the user
                        $user_found_in_event = true;

                        // Collect Yearly Stats (Global)
                        if ($event_year) {
                            if (!isset($yearly_stats[$event_year])) {
                                $yearly_stats[$event_year] = array('wins' => 0, 'total' => 0);
                            }
                            $m_win = intval(isset($rank['win']) ? $rank['win'] : 0);
                            $m_draw = intval(isset($rank['draw']) ? $rank['draw'] : 0);
                            $m_lose = intval(isset($rank['lose']) ? $rank['lose'] : 0);
                            $yearly_stats[$event_year]['wins'] += $m_win;
                            $yearly_stats[$event_year]['total'] += ($m_win + $m_draw + $m_lose);
                        }

                        // Filter for main stats
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

                        // Add to events list
                        $player_events[] = array(
                            'event_post' => get_post($event_id),
                            'ranking' => $rank,
                            'event_date' => $event_date_raw,
                            'total_players' => $total_players
                        );

                        // Track ELO history for chart
                        if (!empty($name)) {
                            $elo_history_labels[] = $event_date_raw ? date('d/m/y', strtotime($event_date_raw)) : 'Event ' . count($elo_history_labels);
                            $elo_history_data[] = round($player_elos[$name]);
                        }
                    }
                }
            }
        }
        wp_reset_postdata();
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

    // Render HTML
    ?>
    <div class="wrap">
        <h1>Player Stats: <?php echo esc_html($target_user ? $target_user->display_name : 'Unknown User'); ?></h1>

        <form method="get" action="" style="margin: 20px 0;">
            <input type="hidden" name="page" value="player-stats">

            <?php if (current_user_can('administrator')): ?>
                <div
                    style="margin-bottom: 15px; padding: 15px; background: #fff; border: 1px solid #ccd0d4; border-left: 4px solid #2271b1; display: inline-block;">
                    <label for="stats_user_id" style="font-weight: bold; margin-right: 10px;">Select Player (Admin):</label>
                    <?php
                    wp_dropdown_users(array(
                        'name' => 'stats_user_id',
                        'selected' => $user_id,
                        'show_option_none' => 'Select User',
                        'show' => 'display_name_with_login',
                        'class' => '',
                    ));
                    ?>
                    <input type="submit" class="button" value="View">
                </div>
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

        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">
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
    if (current_user_can('administrator')) {
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
                    $player_elos[$name] = 1200; // ELO Base
                }
                $event_participants_names[] = $name;
                $total_event_elo += $player_elos[$name];
            }

            $avg_elo = count($event_participants_names) > 0 ? $total_event_elo / count($event_participants_names) : 1200;

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
                        'elo' => 1200
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
                    $actual_score = $wins + ($draws * 0.5);
                    $expected_score_rate = 1 / (1 + pow(10, ($avg_elo - $current_elo) / 400));
                    $expected_score = $expected_score_rate * $games_played;
                    $k_factor = 32 / $games_played; // K-factor standard / Game Played

                    $new_elo = $current_elo + $k_factor * ($actual_score - $expected_score);

                    // Position Adjustment
                    $rank_score = ($total_players > 1) ? ($total_players - $pos) / ($total_players - 1) : 1;
                    $position_adjustment = 20 * ($rank_score - 0.5);
                    $new_elo += $position_adjustment;

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

    $events = get_posts($args);

    // 1. Calcolo Classifica Attuale
    $result = lpdh_calculate_rankings_data($events);

    // 2. Calcolo Classifica Precedente (per il trend)
    // Escludiamo l'ultimo torneo per vedere come è cambiata la classifica dopo l'ultimo evento
    $previous_events = $events;
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
            $profile_url = get_author_posts_url($current_user->ID);
            $logout_url = wp_logout_url(home_url());
            $my_decks_url = admin_url('edit.php?post_type=deck');
            $avatar = get_avatar($current_user->ID, 24, '', '', array('class' => 'rounded-circle me-2', 'style' => 'width: 24px; height: 24px;'));

            // Voce utente con Avatar e Nome
            $items .= '<li class="menu-item menu-item-has-children dropdown user-menu-item">';
            $items .= '<a href="' . esc_url($profile_url) . '" class="nav-link d-flex align-items-center">';
            $items .= $avatar . esc_html($current_user->display_name);
            $items .= '</a>';

            // Desktop Dropdown
            $items .= '<ul class="dropdown-menu dropdown-menu-end d-none d-lg-block">';
            $items .= '<li class="menu-item"><a href="' . esc_url($my_decks_url) . '" class="dropdown-item"><i class="fas fa-layer-group me-2"></i>My Decks</a></li>';
            $items .= '<li class="menu-item"><a href="' . esc_url($logout_url) . '" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>';
            $items .= '</ul>';

            // Mobile Flat List (visible on small screens)
            $items .= '<ul class="d-lg-none list-unstyled ms-3 mt-2">';
            $items .= '<li class="menu-item mb-2"><a href="' . esc_url($my_decks_url) . '" class="nav-link p-0"><i class="fas fa-layer-group me-2"></i>My Decks</a></li>';
            $items .= '<li class="menu-item"><a href="' . esc_url($logout_url) . '" class="nav-link p-0 text-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>';
            $items .= '</ul>';

            $items .= '</li>';

        } else {
            // Voce Login (porta a pagina login personalizzata)
            $login_url = home_url('/login');
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
 * Custom CSS for Single Post Layout and Archive Images
 */
function lpdh_custom_layout_styles()
{
    $custom_css = "
        /* Single Post: Featured Image Left with Text Wrap */
        .single-post .single-post-featured-image {
            float: left;
            margin-right: 2rem;
            max-width: 50%;
        }
        
        @media (max-width: 768px) {
            .single-post .single-post-featured-image {
                float: none;
                margin-right: 0;
                max-width: 100%;
                width: 100%;
            }
        }

        /* Article List: Image Height & Fit */
        .blog .card-img-top,
        .archive .card-img-top,
        .search .card-img-top {
            height: 350px;
            object-fit: cover;
        }

        /* Related Posts Image Height */
        .related-posts .card-img-top {
            height: 200px;
            object-fit: cover;
        }
    ";
    wp_add_inline_style('main', $custom_css);
}
add_action('wp_enqueue_scripts', 'lpdh_custom_layout_styles', 20);

/**
 * Ordina archivio Banned Card per data decrescente
 */
function bootscore_child_banned_card_archive_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('banned_card')) {
        $query->set('orderby', 'date');
        $query->set('order', 'DESC');
        $query->set('posts_per_page', -1);
    }
}
add_action('pre_get_posts', 'bootscore_child_banned_card_archive_query');

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
function lpdh_banned_card_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'name' => '',
        'id' => '',
        'align' => 'left',
    ), $atts, 'banned_card');

    $args = array(
        'post_type' => 'banned_card',
        'posts_per_page' => 1,
        'post_status' => 'publish',
    );

    if (!empty($atts['id'])) {
        $args['p'] = intval($atts['id']);
    } elseif (!empty($atts['name'])) {
        $args['title'] = sanitize_text_field($atts['name']);
    } else {
        return '';
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $query->the_post();

        ob_start();
        ?>
        <div class="banned-cards-list mx-auto" style="max-width: 900px;">
            <?php get_template_part('template-parts/shortcode-banned-card', null, array('align' => $atts['align'])); ?>
        </div>
        <?php
        $output = ob_get_clean();

        wp_reset_postdata();
        return $output;
    }

    return '';
}
add_shortcode('banned_card', 'lpdh_banned_card_shortcode');

/**
 * Theme Settings Page for Admin
 */
function lpdh_register_theme_settings()
{
    add_theme_page(
        'LPDH Theme Settings',
        'Theme Settings',
        'manage_options',
        'lpdh-theme-settings',
        'lpdh_theme_settings_render'
    );
}
add_action('admin_menu', 'lpdh_register_theme_settings');

function lpdh_theme_settings_render()
{
    if (!current_user_can('manage_options'))
        return;

    // Save Settings
    if (isset($_POST['lpdh_theme_action']) && $_POST['lpdh_theme_action'] == 'save') {
        check_admin_referer('lpdh_theme_settings_save');
        update_option('lpdh_active_theme', sanitize_text_field($_POST['lpdh_active_theme']));
        echo '<div class="updated"><p>Theme settings saved!</p></div>';
    }

    $active_theme = get_option('lpdh_active_theme', 'default');
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
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
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
 * Add Shortcode Metabox to Banned Card CPT
 */
function lpdh_add_banned_card_metabox()
{
    add_meta_box(
        'lpdh_banned_card_shortcode',
        'Banned Card Shortcode',
        'lpdh_render_banned_card_shortcode_metabox',
        'banned_card',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'lpdh_add_banned_card_metabox');

/**
 * Render Shortcode Metabox
 */
function lpdh_render_banned_card_shortcode_metabox($post)
{
    ?>
    <div class="lpdh-metabox-content" style="padding: 10px 0;">
        <div style="margin-bottom: 15px;">
            <label for="lpdh_shortcode_align"
                style="display: block; margin-bottom: 5px; font-weight: 600;">Alignment:</label>
            <select id="lpdh_shortcode_align" style="width: 100%;">
                <option value="right" selected>Right</option>
                <option value="left">Left</option>
            </select>
        </div>
        <div>
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Shortcode:</label>
            <div style="display: flex; gap: 5px; align-items: center;">
                <input type="text" id="lpdh_banned_card_shortcode_input"
                    value='[banned_card id="<?php echo $post->ID; ?>" align="right"]' readonly
                    style="flex-grow: 1; background: #f0f0f1; cursor: pointer; border-color: #ccd0d4;"
                    onclick="this.select();">
                <button type="button" class="button button-secondary" id="lpdh_copy_shortcode" title="Copy Shortcode"
                    style="padding: 0 8px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <span class="dashicons dashicons-clipboard" style="font-size: 18px; width: 18px; height: 18px;"></span>
                </button>
            </div>
        </div>
    </div>
    <script>
        (function () {
            const select = document.getElementById('lpdh_shortcode_align');
            const input = document.getElementById('lpdh_banned_card_shortcode_input');
            const btn = document.getElementById('lpdh_copy_shortcode');
            const postId = '<?php echo $post->ID; ?>';

            if (!select || !input || !btn) return;

            select.addEventListener('change', function () {
                input.value = '[banned_card id="' + postId + '" align="' + this.value + '"]';
            });

            btn.addEventListener('click', function () {
                input.select();
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        const icon = btn.querySelector('.dashicons');
                        icon.classList.remove('dashicons-clipboard');
                        icon.classList.add('dashicons-yes');
                        btn.style.borderColor = '#46b450';
                        btn.style.color = '#46b450';

                        setTimeout(() => {
                            icon.classList.remove('dashicons-yes');
                            icon.classList.add('dashicons-clipboard');
                            btn.style.borderColor = '';
                            btn.style.color = '';
                        }, 2000);
                    }
                } catch (err) {
                    console.error('Copy failed', err);
                }
            });

            // Auto-select on focus
            input.addEventListener('focus', function () {
                this.select();
            });
        })();
    </script>
    <?php
}

/**
 * AJAX handler for searching banned_card posts
 */
function lpdh_ajax_search_banned_cards()
{
    check_ajax_referer('lpdh_banned_card_search', 'nonce');

    $search = isset($_GET['term']) ? sanitize_text_field($_GET['term']) : '';

    $args = array(
        'post_type' => 'banned_card',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        's' => $search
    );

    $query = new WP_Query($args);
    $results = array();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $results[] = array(
                'id' => get_the_ID(),
                'label' => get_the_title(),
                'value' => get_the_title()
            );
        }
    }
    wp_reset_postdata();

    wp_send_json($results);
}
add_action('wp_ajax_lpdh_search_banned_cards', 'lpdh_ajax_search_banned_cards');

/**
 * Add Shortcode Generator Metabox to Posts
 */
function lpdh_add_post_shortcode_metabox()
{
    add_meta_box(
        'lpdh_post_banned_card_generator',
        'Banned Card Shortcode Generator',
        'lpdh_render_post_shortcode_metabox',
        'post',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'lpdh_add_post_shortcode_metabox');

/**
 * Render Post Shortcode Generator Metabox
 */
function lpdh_render_post_shortcode_metabox($post)
{
    // Standard WP styles for autocomplete
    wp_enqueue_script('jquery-ui-autocomplete');
    ?>
    <div class="lpdh-generator-content" style="padding: 10px 0;">
        <div style="margin-bottom: 12px;">
            <label for="lpdh_card_search" style="display: block; margin-bottom: 5px; font-weight: 600;">Search Card:</label>
            <input type="text" id="lpdh_card_search" placeholder="Type card name..." style="width: 100%;">
            <input type="hidden" id="lpdh_selected_card_id" value="">
        </div>

        <div style="margin-bottom: 12px;">
            <label for="lpdh_gen_align" style="display: block; margin-bottom: 5px; font-weight: 600;">Alignment:</label>
            <select id="lpdh_gen_align" style="width: 100%;">
                <option value="right">Right</option>
                <option value="left" selected>Left</option>
            </select>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Shortcode:</label>
            <div style="display: flex; gap: 5px; align-items: center;">
                <input type="text" id="lpdh_gen_shortcode_input" value="" readonly placeholder="Select a card..."
                    style="flex-grow: 1; background: #f0f0f1; cursor: pointer; border-color: #ccd0d4;"
                    onclick="this.select();">
                <button type="button" class="button button-secondary" id="lpdh_copy_gen_shortcode" title="Copy"
                    style="padding: 0 8px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <span class="dashicons dashicons-clipboard" style="font-size: 18px; width: 18px; height: 18px;"></span>
                </button>
            </div>
        </div>

        <button type="button" class="button button-primary" id="lpdh_add_to_editor"
            style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 5px;">
            <span class="dashicons dashicons-plus-alt"
                style="font-size: 18px; width: 18px; height: 18px; margin-top: 2px;"></span>
            Add to Content
        </button>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            const $search = $('#lpdh_card_search');
            const $cardId = $('#lpdh_selected_card_id');
            const $align = $('#lpdh_gen_align');
            const $input = $('#lpdh_gen_shortcode_input');
            const $copyBtn = $('#lpdh_copy_gen_shortcode');
            const $addBtn = $('#lpdh_add_to_editor');

            function updateShortcode() {
                const id = $cardId.val();
                if (id) {
                    $input.val('[banned_card id="' + id + '" align="' + $align.val() + '"]');
                } else {
                    $input.val('');
                }
            }

            $search.autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: ajaxurl,
                        dataType: "json",
                        data: {
                            action: 'lpdh_search_banned_cards',
                            term: request.term,
                            nonce: '<?php echo wp_create_nonce("lpdh_banned_card_search"); ?>'
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $cardId.val(ui.item.id);
                    updateShortcode();
                }
            });

            $align.on('change', updateShortcode);

            $copyBtn.on('click', function () {
                if (!$input.val()) return;
                $input.select();
                document.execCommand('copy');

                const $icon = $(this).find('.dashicons');
                $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
                $(this).css({ borderColor: '#46b450', color: '#46b450' });

                setTimeout(() => {
                    $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
                    $(this).css({ borderColor: '', color: '' });
                }, 2000);
            });

            $addBtn.on('click', function (e) {
                e.preventDefault();
                const shortcode = $input.val();
                if (!shortcode) {
                    alert('Please select a card first.');
                    return;
                }

                // 1. Try Classic Editor (TinyMCE) first - most common fallback if Gutenberg is disabled
                if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
                    tinyMCE.activeEditor.execCommand('mceInsertContent', false, shortcode);
                    return;
                }

                // 2. Try Gutenberg (Block Editor)
                if (typeof wp !== 'undefined' && wp.data && wp.blocks) {
                    // Check if block editor is actually enqueued and available
                    const blockEditor = (wp.data.select('core/block-editor') || wp.data.select('core/editor'));
                    if (blockEditor) {
                        const dispatcher = (wp.data.dispatch('core/block-editor') || wp.data.dispatch('core/editor'));
                        if (dispatcher && dispatcher.insertBlocks) {
                            try {
                                const block = wp.blocks.createBlock('core/shortcode', { text: shortcode });
                                if (block) {
                                    dispatcher.insertBlocks([block]);
                                    return;
                                }
                            } catch (err) {
                                // Only log if it's not a common "no editor" scenario
                            }
                        }
                    }
                }

                // 3. Fallback to textarea (code editor or simple content area)
                const $content = $('#content');
                if ($content.length) {
                    const cursorPos = $content.prop('selectionStart') || 0;
                    const text = $content.val();
                    $content.val(text.substring(0, cursorPos) + shortcode + text.substring(cursorPos));
                } else {
                    alert('Could not find editor content area.');
                }
            });
        });
    </script>
    <style>
        .ui-autocomplete {
            z-index: 100000 !important;
            background: #fff;
            border: 1px solid #ccd0d4;
            max-height: 200px;
            overflow-y: auto;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .ui-menu-item-wrapper {
            padding: 8px 12px;
            cursor: pointer;
        }

        .ui-state-active,
        .ui-state-focus {
            background-color: #2271b1 !important;
            color: #fff !important;
            margin: 0 !important;
        }
    </style>
    <?php
}
