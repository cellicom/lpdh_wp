<?php
/**
 * Leaderboard Custom Post Type
 *
 * Handles registration, capabilities, ACF fields,
 * admin update button, rankings calculation, and archive ordering.
 *
 * @package Bootscore Child
 */

// Exit if accessed directly
defined('ABSPATH') || exit;


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
            }) (jQuery);
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
