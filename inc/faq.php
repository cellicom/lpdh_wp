<?php
/**
 * FAQ Custom Post Type
 *
 * Handles registration and archive ordering.
 *
 * @package Bootscore Child
 */

// Exit if accessed directly
defined('ABSPATH') || exit;


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
