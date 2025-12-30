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
function bootscore_child_enqueue_styles() {

  // Compiled main.css
  $modified_bootscoreChildCss = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/css/main.css'));
  wp_enqueue_style('main', get_stylesheet_directory_uri() . '/assets/css/main.css', array('parent-style'), $modified_bootscoreChildCss);

  // style.css
  wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
  
  // custom.js
  // Get modification time. Enqueue file with modification date to prevent browser from loading cached scripts when file content changes. 
  $modificated_CustomJS = date('YmdHi', filemtime(get_stylesheet_directory() . '/assets/js/custom.js'));
  wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/assets/js/custom.js', array('jquery'), $modificated_CustomJS, false, true);
}

/**
 * Register Custom Post Type "Deck"
 */
function register_deck_post_type() {
    $labels = array(
        'name'                  => 'Decks',
        'singular_name'         => 'Deck',
        'menu_name'             => 'Decks',
        'name_admin_bar'        => 'Deck',
        'archives'              => 'Archivio Decks',
        'attributes'            => 'Attributi Deck',
        'parent_item_colon'     => 'Deck genitore:',
        'all_items'             => 'Tutti i Decks',
        'add_new_item'          => 'Aggiungi nuovo Deck',
        'add_new'               => 'Aggiungi nuovo',
        'new_item'              => 'Nuovo Deck',
        'edit_item'             => 'Modifica Deck',
        'update_item'           => 'Aggiorna Deck',
        'view_item'             => 'Visualizza Deck',
        'view_items'            => 'Visualizza Decks',
        'search_items'          => 'Cerca Deck',
        'not_found'             => 'Nessun deck trovato',
        'not_found_in_trash'    => 'Nessun deck nel cestino',
        'featured_image'        => 'Immagine in evidenza',
        'set_featured_image'    => 'Imposta immagine in evidenza',
        'remove_featured_image' => 'Rimuovi immagine in evidenza',
        'use_featured_image'    => 'Usa come immagine in evidenza',
        'insert_into_item'      => 'Inserisci in deck',
        'uploaded_to_this_item' => 'Caricato in questo deck',
        'items_list'            => 'Lista dec',
        'items_list_navigation' => 'Navigazione lista dec',
        'filter_items_list'     => 'Filtra lista dec',
    );

    $args = array(
        'label'                 => 'Deck',
        'description'           => 'Custom Post Type per gestire i deck',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'author'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 20,
        'menu_icon'             => 'dashicons-playlist-video',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'decks',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('deck', $args);
}
add_action('init', 'register_deck_post_type', 0);

/**
 * Add custom "player" role based on author capabilities
 */
function add_player_role() {
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
function grant_player_capabilities() {
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
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_deck_custom_fields',
        'title' => 'Campi Deck',
        'fields' => array(
            array(
                'key' => 'field_decklist',
                'label' => 'Decklist (Link Esterno)',
                'name' => 'decklist',
                'type' => 'url',
                'instructions' => 'Inserisci l\'URL esterno della decklist',
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

endif;

/**
 * Add custom columns to Deck admin list
 */
function deck_custom_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['decklist'] = 'Decklist';
    $new_columns['author'] = 'Autore';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_deck_posts_columns', 'deck_custom_columns');

/**
 * Populate custom columns data
 */
function deck_custom_columns_data($column, $post_id) {
    switch ($column) {
        case 'decklist':
            $decklist = get_field('field_decklist', $post_id);
            if ($decklist) {
                echo '<a href="' . esc_url($decklist) . '" target="_blank" rel="noopener">Link Esterno</a>';
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
function deck_sortable_columns($columns) {
    $columns['decklist'] = 'decklist';
    return $columns;
}
add_filter('manage_edit-deck_sortable_columns', 'deck_sortable_columns');

/**
 * Handle custom column sorting
 */
function deck_column_orderby($query) {
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
function restrict_admin_menu_for_players() {
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
function hide_admin_bar_items_for_players($wp_admin_bar) {
    if (current_user_can('administrator')) {
        return;
    }
    
    if (current_user_can('player')) {
        // Keep only user-related items, remove others
        $nodes_to_keep = array('user-info', 'edit-profile', 'logout');
        
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
function redirect_players_from_restricted_pages() {
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
function register_banned_card_post_type() {
    $labels = array(
        'name'                  => 'Banned Cards',
        'singular_name'         => 'Banned Card',
        'menu_name'             => 'Banned Cards',
        'name_admin_bar'        => 'Banned Card',
        'archives'              => 'Archivio Banned Cards',
        'attributes'            => 'Attributi Banned Card',
        'parent_item_colon'     => 'Banned Card genitore:',
        'all_items'             => 'Tutti i Banned Cards',
        'add_new_item'          => 'Aggiungi nuovo Banned Card',
        'add_new'               => 'Aggiungi nuovo',
        'new_item'              => 'Nuovo Banned Card',
        'edit_item'             => 'Modifica Banned Card',
        'update_item'           => 'Aggiorna Banned Card',
        'view_item'             => 'Visualizza Banned Card',
        'view_items'            => 'Visualizza Banned Cards',
        'search_items'          => 'Cerca Banned Card',
        'not_found'             => 'Nessun banned card trovato',
        'not_found_in_trash'    => 'Nessun banned card nel cestino',
        'featured_image'        => 'Immagine in evidenza',
        'set_featured_image'    => 'Imposta immagine in evidenza',
        'remove_featured_image' => 'Rimuovi immagine in evidenza',
        'use_featured_image'    => 'Usa come immagine in evidenza',
        'insert_into_item'      => 'Inserisci in banned card',
        'uploaded_to_this_item' => 'Caricato in questo banned card',
        'items_list'            => 'Lista banned cards',
        'items_list_navigation' => 'Navigazione lista banned cards',
        'filter_items_list'     => 'Filtra lista banned cards',
    );

    $args = array(
        'label'                 => 'Banned Card',
        'description'           => 'Custom Post Type per gestire le carte bannate',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 21,
        'menu_icon'             => 'dashicons-dismiss',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'banned_cards',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('banned_card', $args);
}
add_action('init', 'register_banned_card_post_type', 0);

/**
 * Register ACF Field Group for Banned Card Custom Post Type
 */
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_banned_card_custom_fields',
        'title' => 'Campi Banned Card',
        'fields' => array(
            array(
                'key' => 'field_scryfall_link',
                'label' => 'Scryfall Link',
                'name' => 'scryfall_link',
                'type' => 'url',
                'instructions' => 'Inserisci il link alla carta su Scryfall',
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
function banned_card_custom_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['scryfall_link'] = 'Scryfall Link';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_banned_card_posts_columns', 'banned_card_custom_columns');

/**
 * Populate custom columns data for Banned Card
 */
function banned_card_custom_columns_data($column, $post_id) {
    switch ($column) {
        case 'scryfall_link':
            $scryfall_link = get_field('field_scryfall_link', $post_id);
            if ($scryfall_link) {
                echo '<a href="' . esc_url($scryfall_link) . '" target="_blank" rel="noopener">Link Scryfall</a>';
            } else {
                echo '-';
            }
            break;
    }
}
add_action('manage_banned_card_posts_custom_column', 'banned_card_custom_columns_data', 10, 2);

/**
 * Hide Banned Card menu from non-administrators
 */
function hide_banned_card_menu_from_players() {
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=banned_card');
    }
}
add_action('admin_menu', 'hide_banned_card_menu_from_players', 999);

/**
 * Restrict access to Banned Card admin pages for non-administrators
 */
function restrict_banned_card_admin_access() {
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
function hide_banned_card_admin_bar($wp_admin_bar) {
    if (!current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-banned_card');
    }
}
add_action('admin_bar_menu', 'hide_banned_card_admin_bar', 999);

/**
 * Remove Banned Card from "New" menu in admin bar for non-admins
 */
function remove_banned_card_from_new_menu($wp_admin_bar) {
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
function restrict_deck_list_tabs_for_players($views) {
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
function hide_deck_views_for_players() {
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
    <style>
        .subsubsub li:not(.mine) {
            display: none !important;
        }
        .subsubsub li.mine a {
            color: #000;
            font-weight: 600;
        }
        .subsubsub li.mine::before {
            content: "Visualizzazione: ";
            color: #646970;
        }
    </style>
    <?php
}
add_action('admin_head', 'hide_deck_views_for_players', 20);

/**
 * Register Custom Post Type "Place"
 * Solo gli amministratori possono gestire questo CPT
 */
function register_place_post_type() {
    $labels = array(
        'name'                  => 'Places',
        'singular_name'         => 'Place',
        'menu_name'             => 'Places',
        'name_admin_bar'        => 'Place',
        'archives'              => 'Archivio Places',
        'attributes'            => 'Attributi Place',
        'parent_item_colon'     => 'Place genitore:',
        'all_items'             => 'Tutti i Places',
        'add_new_item'          => 'Aggiungi nuovo Place',
        'add_new'               => 'Aggiungi nuovo',
        'new_item'              => 'Nuovo Place',
        'edit_item'             => 'Modifica Place',
        'update_item'           => 'Aggiorna Place',
        'view_item'             => 'Visualizza Place',
        'view_items'            => 'Visualizza Places',
        'search_items'          => 'Cerca Place',
        'not_found'             => 'Nessun place trovato',
        'not_found_in_trash'    => 'Nessun place nel cestino',
        'featured_image'        => 'Immagine in evidenza',
        'set_featured_image'    => 'Imposta immagine in evidenza',
        'remove_featured_image' => 'Rimuovi immagine in evidenza',
        'use_featured_image'    => 'Usa come immagine in evidenza',
        'insert_into_item'      => 'Inserisci in place',
        'uploaded_to_this_item' => 'Caricato in questo place',
        'items_list'            => 'Lista places',
        'items_list_navigation' => 'Navigazione lista places',
        'filter_items_list'     => 'Filtra lista places',
    );

    $args = array(
        'label'                 => 'Place',
        'description'           => 'Custom Post Type per gestire i luoghi',
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'author'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 22,
        'menu_icon'             => 'dashicons-location',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rest_base'             => 'places',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );

    register_post_type('place', $args);
}
add_action('init', 'register_place_post_type', 0);

/**
 * Register ACF Field Group for Place Custom Post Type
 */
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_place_custom_fields',
        'title' => 'Campi Place',
        'fields' => array(
            array(
                'key' => 'field_place_address',
                'label' => 'Indirizzo',
                'name' => 'place_address',
                'type' => 'text',
                'instructions' => 'Inserisci l\'indirizzo del luogo',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'Via example 123, Città',
            ),
            array(
                'key' => 'field_place_homepage',
                'label' => 'Sito Web',
                'name' => 'place_homepage',
                'type' => 'url',
                'instructions' => 'Inserisci il link al sito web del luogo',
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
function place_custom_columns($columns) {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = $columns['title'];
    $new_columns['place_address'] = 'Indirizzo';
    $new_columns['place_homepage'] = 'Sito Web';
    $new_columns['author'] = 'Autore';
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter('manage_place_posts_columns', 'place_custom_columns');

/**
 * Populate custom columns data for Place
 */
function place_custom_columns_data($column, $post_id) {
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
function place_sortable_columns($columns) {
    $columns['place_address'] = 'place_address';
    return $columns;
}
add_filter('manage_edit-place_sortable_columns', 'place_sortable_columns');

/**
 * Handle custom column sorting for Place
 */
function place_column_orderby($query) {
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
function hide_place_menu_from_players() {
    if (!current_user_can('administrator')) {
        remove_menu_page('edit.php?post_type=place');
    }
}
add_action('admin_menu', 'hide_place_menu_from_players', 999);

/**
 * Restrict access to Place admin pages for non-administrators
 */
function restrict_place_admin_access() {
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
function hide_place_admin_bar($wp_admin_bar) {
    if (!current_user_can('administrator')) {
        $wp_admin_bar->remove_node('new-place');
    }
}
add_action('admin_bar_menu', 'hide_place_admin_bar', 999);

/**
 * Remove Place from "New" menu in admin bar for non-admins
 */
function remove_place_from_new_menu($wp_admin_bar) {
    if (!current_user_can('administrator')) {
        foreach ($wp_admin_bar->get_nodes() as $id => $node) {
            if (strpos($id, 'new-place') !== false) {
                $wp_admin_bar->remove_node($id);
            }
        }
    }
}
add_action('admin_bar_menu', 'remove_place_from_new_menu', 999);
