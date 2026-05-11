<?php
/**
 * Deck Custom Post Type
 *
 * Handles registration, ACF fields, admin columns,
 * player role & capabilities, and deck-specific restrictions.
 *
 * @package Bootscore Child
 */

// Exit if accessed directly
defined('ABSPATH') || exit;


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
        'rewrite' => array(
            'slug' => 'player/%player%/deck',
            'with_front' => false
        ),
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

function lpdh_get_deck_editor_url()
{
    $page_id = get_option('lpdh_deck_editor_page_id');
    return $page_id ? get_permalink($page_id) : home_url('/deck-editor/');
}

/**
 * Check if a deck contains banned cards
 */
function lpdh_is_deck_legal($deck_id)
{
    $banned_data = lpdh_get_banned_cards_data();
    if (empty($banned_data)) {
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
        if (isset($banned_data[$card])) {
            $combined_with = $banned_data[$card]['combined_with'];
            
            if (empty($combined_with)) {
                // If the banned card has no 'combined_with' restrictions, it's banned outright
                return false;
            } else {
                // It is banned ONLY if combined with one of these specific cards
                foreach ($combined_with as $cw_card) {
                    if (in_array($cw_card, $deck_cards)) {
                        return false; // Found the specific combination
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Register %player% rewrite tag for Deck CPT
 */
function lpdh_add_deck_rewrite_tag() {
    add_rewrite_tag('%player%', '([^/]+)');
}
add_action('init', 'lpdh_add_deck_rewrite_tag', 10);

/**
 * Filter Deck permalinks to include author nicename
 */
function lpdh_deck_post_type_link($post_link, $post) {
    if ($post->post_type === 'deck') {
        $author = get_userdata($post->post_author);
        if ($author) {
            $post_link = str_replace('%player%', $author->user_nicename, $post_link);
        } else {
            // Fallback for posts without authors (though rare)
            $post_link = str_replace('%player%', 'unknown', $post_link);
        }
    }
    return $post_link;
}
add_filter('post_type_link', 'lpdh_deck_post_type_link', 10, 2);

/**
 * Redirect legacy Deck URLs to the new structure
 * From /deck/slug/ to /player/user/deck/slug/
 */
function lpdh_redirect_old_deck_urls() {
    if (is_singular('deck')) {
        $requested_url = $_SERVER['REQUEST_URI'];
        $home_path = parse_url(home_url(), PHP_URL_PATH) ?: '';
        $relative_url = ltrim(str_replace($home_path, '', $requested_url), '/');
        
        // If the URL starts with deck/ (old structure) and NOT player/ (new structure)
        if (0 === strpos($relative_url, 'deck/') && strpos($relative_url, 'player/') === false) {
            $new_url = get_permalink();
            if ($new_url && strpos($new_url, '/player/') !== false) {
                wp_redirect($new_url, 301);
                exit;
            }
        }
    }
}
add_action('template_redirect', 'lpdh_redirect_old_deck_urls');
