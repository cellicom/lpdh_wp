<?php
/**
 * Place Custom Post Type
 *
 * Handles registration, ACF fields, admin columns,
 * search functionality, and archive ordering.
 *
 * @package Bootscore Child
 */

// Exit if accessed directly
defined('ABSPATH') || exit;


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
                'key' => 'field_place_city',
                'label' => 'City',
                'name' => 'place_city',
                'type' => 'text',
                'instructions' => 'Enter place city',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '50',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => 'City',
            ),
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
    $new_columns['place_city'] = 'City';
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
        case 'place_city':
            $place_city = get_field('field_place_city', $post_id);
            if ($place_city) {
                echo esc_html($place_city);
            } else {
                echo '-';
            }
            break;
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
    $columns['place_city'] = 'place_city';
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

    if ('place_city' == $orderby) {
        $query->set('meta_key', 'field_place_city');
        $query->set('orderby', 'meta_value');
    } elseif ('place_address' == $orderby) {
        $query->set('meta_key', 'field_place_address');
        $query->set('orderby', 'meta_value');
    }
}
add_action('pre_get_posts', 'place_column_orderby');


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
