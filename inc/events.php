<?php
/**
 * Event CPT Functions
 * Registration, ACF fields, admin columns, rankings, OCR, sharing.
 *
 * @package lpdh-wordpress
 */

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
                'key' => 'field_event_city',
                'label' => 'City',
                'name' => 'event_city',
                'type' => 'select',
                'instructions' => 'Select a city to filter available places',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'default_value' => false,
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'ajax' => 0,
                'return_format' => 'value',
                'placeholder' => 'Select a city...',
            ),
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
                'key' => 'field_event_code',
                'label' => 'Code Event',
                'name' => 'event_code',
                'type' => 'text',
                'instructions' => 'Enter the alphanumeric code for the Companion App registration.',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'e.g. LPDH2024',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
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
                'key'               => 'field_event_exclude_annual',
                'label'             => 'Exclude from Annual Leaderboard',
                'name'              => 'exclude_from_annual_leaderboard',
                'type'              => 'true_false',
                'instructions'      => 'If checked, this event will NOT be counted in the annual leaderboard calculation.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
                'message'       => '',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
            ),
            array(
                'key'               => 'field_event_exclude_elo',
                'label'             => 'Exclude from ELO Leaderboard',
                'name'              => 'exclude_from_elo_leaderboard',
                'type'              => 'true_false',
                'instructions'      => 'If checked, this event will NOT affect players\' ELO ratings.',
                'required'          => 0,
                'conditional_logic' => 0,
                'wrapper'           => array(
                    'width' => '50',
                    'class' => '',
                    'id'    => '',
                ),
                'message'       => '',
                'default_value' => 0,
                'ui'            => 1,
                'ui_on_text'    => 'Yes',
                'ui_off_text'   => 'No',
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
    $new_columns['event_code'] = 'Code';
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
        case 'event_code':
            $event_code = get_field('field_event_code', $post_id);
            if ($event_code) {
                echo '<span class="lpdh-copyable-code" style="cursor:pointer; background:#f0f0f0; border:1px solid #ccc; padding:2px 5px; border-radius:3px; font-family:monospace; display:inline-block;" title="Click to copy" onclick="lpdhCopyCodeToList(this, event, \'' . esc_attr($event_code) . '\')">';
                echo esc_html($event_code);
                echo ' <span class="dashicons dashicons-clipboard" style="font-size:14px; width:14px; height:14px; vertical-align:middle;"></span>';
                echo '</span>';
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
            .column-event_code { width: 100px !important; }
        </style>';
    }
}
add_action('admin_head', 'lpdh_event_list_column_widths');

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
    $event_code = get_field('field_event_code', $post->ID);

    // Format date if it exists
    if ($date) {
        $date_formatted = date('d/m/Y H:i', strtotime($date));
    } else {
        $date_formatted = 'TBA';
    }

    $share_text = $title . "\n" . $date_formatted . " @ " . $place_name . "\n";
    if ($event_code) {
        $share_text .= "Code: " . $event_code . "\n";
    }
    $share_text .= $permalink;

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

                // Get Code from ACF
                var eventCode = '';
                if (typeof acf !== 'undefined') {
                    var codeField = acf.getField('field_event_code');
                    if (codeField) {
                        eventCode = codeField.val();
                    }
                }

                var newText = title + "\n" + date + " @ " + place + "\n";
                if (eventCode) {
                    newText += "Code: " + eventCode + "\n";
                }
                newText += permalink;
                $textarea.val(newText);
            }

            window.lpdhCopyShareText = function (e) {
                // Update text before copying
                updateShareText();

                var copyText = document.getElementById("lpdh-share-text");
                if (!copyText) return;
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                navigator.clipboard.writeText(copyText.value).then(function () {
                    var $btn = $(e.currentTarget);
                    var originalHtml = $btn.html();
                    $btn.html('<span class="dashicons dashicons-yes" style="margin-top: 5px; font-size: 16px;"></span> Copied!');
                    $btn.addClass('button-primary').removeClass('button-secondary');
                    setTimeout(function () {
                        $btn.html(originalHtml).addClass('button-secondary').removeClass('button-primary');
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
 * Event Admin Global Scripts
 * Ensures copy functions are available on both list and edit pages
 */
function lpdh_event_admin_global_scripts() {
    $screen = get_current_screen();
    if ($screen && ($screen->post_type === 'event' || $screen->id === 'edit-event')) {
        ?>
        <script>
            (function ($) {
                // Admin List Copy Function
                window.lpdhCopyCodeToList = function (el, e, code) {
                    if (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    
                    var cleanCode = code.trim();
                    navigator.clipboard.writeText(cleanCode).then(function () {
                        var $el = $(el);
                        var $icon = $el.find('.dashicons');
                        
                        // Visual feedback: green check icon and background
                        $el.attr('style', $el.attr('style') + ' background-color: #d1e7dd !important; border-color: #a3cfbb !important; color: #0f5132 !important;');
                        $icon.removeClass('dashicons-clipboard').addClass('dashicons-yes').attr('style', $icon.attr('style') + ' color: #0f5132 !important;');
                        
                        setTimeout(function () {
                            // Restore styles - removing the added important parts
                            var style = $el.attr('style');
                            style = style.replace(' background-color: #d1e7dd !important; border-color: #a3cfbb !important; color: #0f5132 !important;', '');
                            $el.attr('style', style);
                            
                            $icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
                            var iconStyle = $icon.attr('style');
                            iconStyle = iconStyle.replace(' color: #0f5132 !important;', '');
                            $icon.attr('style', iconStyle);
                        }, 1500);
                    });
                };
            })(jQuery);
        </script>
        <style>
            #lpdh-copy-event-code .dashicons {
                font-size: 16px;
                width: 16px;
                height: 16px;
                margin-right: 4px;
                vertical-align: middle;
            }
        </style>
        <?php
    }
}
add_action('admin_footer', 'lpdh_event_admin_global_scripts');

/**
 * Retrieve unique cities from Place custom post type, sorted alphabetically.
 */
function lpdh_get_unique_place_cities()
{
    global $wpdb;
    $cities = $wpdb->get_col("
        SELECT DISTINCT TRIM(meta_value)
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = 'place_city' 
          AND pm.meta_value IS NOT NULL 
          AND TRIM(meta_value) != '' 
          AND p.post_type = 'place' 
          AND p.post_status = 'publish'
        ORDER BY TRIM(meta_value) ASC
    ");
    return array_filter($cities);
}

/**
 * Populate City choices in Event field dynamically
 */
function lpdh_populate_event_city_choices($field)
{
    $cities = lpdh_get_unique_place_cities();
    $field['choices'] = array();
    if (!empty($cities)) {
        foreach ($cities as $city) {
            $field['choices'][$city] = $city;
        }
    }
    return $field;
}
add_filter('acf/load_field/name=event_city', 'lpdh_populate_event_city_choices');

/**
 * Filter available places in event_place field by selected city
 */
function lpdh_filter_places_by_city($args, $field, $post_id)
{
    if (isset($_REQUEST['event_city']) && !empty($_REQUEST['event_city'])) {
        $city = sanitize_text_field($_REQUEST['event_city']);
        
        if (!isset($args['meta_query'])) {
            $args['meta_query'] = array();
        }
        
        $args['meta_query'][] = array(
            'key' => 'place_city',
            'value' => $city,
            'compare' => '='
        );
    }
    return $args;
}
add_filter('acf/fields/post_object/query/name=event_place', 'lpdh_filter_places_by_city', 20, 3);
add_filter('acf/fields/post_object/query/key=field_event_place', 'lpdh_filter_places_by_city', 20, 3);
add_filter('acf/fields/post_object/query', 'lpdh_filter_places_by_city', 20, 3);

/**
 * Enqueue Select2 filtering JS for Event edit screen
 */
function lpdh_event_city_filter_js()
{
    ?>
    <script type="text/javascript">
        (function($) {
            $(document).ready(function() {
                if ($('[id*="event_city"]').length === 0 && $('[name*="event_city"]').length === 0) {
                    return;
                }
                
                console.log('LPDH Event City Filter Script Loaded.');

                // When the city selection changes, clear the Place selection
                $(document).on('change', '[id*="event_city"], [name*="event_city"]', function() {
                    var cityVal = $(this).val();
                    console.log('City changed to:', cityVal, '- Clearing Place field.');
                    var $place = $('[id*="event_place"], [name*="event_place"]');
                    $place.val('').trigger('change');
                    
                    // If ACF Select2 is used, we can also use ACF API to clear it
                    if (typeof acf !== 'undefined') {
                        var placeField = acf.getField('field_event_place');
                        if (placeField) {
                            placeField.val('');
                        }
                    }
                });

                // Intercept ACF post_object AJAX queries to append selected city
                if ($.ajaxPrefilter) {
                    $.ajaxPrefilter(function(options, originalOptions, jqXHR) {
                        if (options.data && (
                            options.data.indexOf('action=acf%2Ffields%2Fpost_object%2Fquery') !== -1 ||
                            options.data.indexOf('action=acf/fields/post_object/query') !== -1
                        )) {
                            var cityVal = $('[id*="event_city"]').val() || $('[name*="event_city"]').val();
                            console.log('Intercepted ACF post_object query. Filtering by city:', cityVal);
                            if (cityVal) {
                                options.data += '&event_city=' + encodeURIComponent(cityVal);
                            }
                        }
                    });
                }
            });
        })(jQuery);
    </script>
    <?php
}
add_action('admin_footer', 'lpdh_event_city_filter_js');
