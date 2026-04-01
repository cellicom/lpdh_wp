<?php

/**
 * LPDH Banned Card Custom Post Type
 * 
 * Contains all functions related to the Banned Card custom post type.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Helper function to retrieve banned card names
 */
function lpdh_get_banned_card_names()
{
    $banned_cards_query = new WP_Query(array(
        'post_type' => 'banned_card',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));

    $banned_card_names = array();
    if ($banned_cards_query->have_posts()) {
        foreach ($banned_cards_query->posts as $post_id) {
            $banned_card_names[] = get_the_title($post_id);
        }
    }

    // Normalize names for simpler JS comparison (lowercase)
    return array_map('strtolower', $banned_card_names);
}

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
    $new_columns['scryfall_link'] = 'Link';
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
                echo '<a href="' . esc_url($scryfall_link) . '" target="_blank" rel="noopener" style="text-decoration: none;"><span class="dashicons dashicons-external" style="color: black;"></span></a>';
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
 * Localize banned card names for JavaScript autocomplete
 */
function lpdh_localize_banned_cards()
{
    if (wp_script_is('scryfall-autocomplete-core', 'registered')) {
        $banned_card_names = lpdh_get_banned_card_names();
        wp_localize_script('scryfall-autocomplete-core', 'LPDH_Banned_Cards', $banned_card_names);
    }
}
add_action('wp_enqueue_scripts', 'lpdh_localize_banned_cards');
add_action('admin_enqueue_scripts', 'lpdh_localize_banned_cards');

/**
 * Adjust column widths for banned_card admin list
 */
function lpdh_banned_card_list_column_widths()
{
    $screen = get_current_screen();
    if ($screen && $screen->id === 'edit-banned_card') {
        echo '<style>
            .column-scryfall_link { width: 40px !important; text-align: center; }
        </style>';
    }
}
add_action('admin_head', 'lpdh_banned_card_list_column_widths');

/**
 * Customize banned_card archive query
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
 * Banned Card Shortcode
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
 * Add metabox to banned_card edit screen with shortcode generator
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
 * Add shortcode generator metabox to Post edit screen
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
            /* background: #fff; */
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
