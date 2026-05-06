<?php
/**
 * Player Stats Module
 */

/**
 * Enqueue assets for Stats pages
 */
function lpdh_player_stats_enqueue()
{
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    if ($page === 'player-stats' || $page === 'deck-stats') {
        wp_enqueue_style('select2', get_stylesheet_directory_uri() . '/assets/css/select2.min.css');
        wp_enqueue_script('select2', get_stylesheet_directory_uri() . '/assets/js/select2.min.js', ['jquery'], '4.1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'lpdh_player_stats_enqueue');

/**
 * Register Stats parent menu with Players Stats and Decks Stats subpages
 */
function register_stats_page()
{
    // Parent menu — keeps slug 'player-stats' so existing form actions stay valid
    add_menu_page(
        'Stats',
        'Stats',
        'read',
        'player-stats',
        'render_player_stats_page',
        'dashicons-chart-bar',
        2
    );

    // Players Stats — replaces the auto-generated "Stats > Stats" duplicate entry
    add_submenu_page(
        'player-stats',
        'Players Stats',
        'Players Stats',
        'read',
        'player-stats',
        'render_player_stats_page'
    );

    // Decks Stats — new subpage
    add_submenu_page(
        'player-stats',
        'Decks Stats',
        'Decks Stats',
        'read',
        'deck-stats',
        'render_deck_stats_page'
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
    if ($selected_year !== 'global') {
        $player_elos = lpdh_get_previous_year_elos($selected_year);
    }
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

            // Respect exclusion flags
            $exclude_annual = (bool) get_field('exclude_from_annual_leaderboard', $event_id);
            $exclude_elo    = (bool) get_field('exclude_from_elo_leaderboard', $event_id);

            // If excluded from annual leaderboard, skip this event entirely
            if ($exclude_annual) {
                continue;
            }

            $rankings = get_field('event_ranking', $event_id);

            // Fallback al JSON se il repeater è vuoto
            if (empty($rankings) || !is_array($rankings)) {
                $json_rankings = get_field('event_rankings_json', $event_id);
                if (!empty($json_rankings)) {
                    $decoded = json_decode($json_rankings, true);
                    if (is_array($decoded)) {
                        $rankings = $decoded;
                    }
                }
            }

            if (is_array($rankings)) {
                $total_players = count($rankings);

                // ELO Pre-calculation for this event (Average ELO)
                $event_participants_names = array();
                $total_event_elo = 0;
                foreach ($rankings as $rank) {
                    $name = isset($rank['name']) ? trim($rank['name']) : '';
                    $p_id = 0;
                    $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : 0;
                    if (!empty($player_id_field)) {
                        if (is_array($player_id_field) && isset($player_id_field['ID'])) {
                            $p_id = $player_id_field['ID'];
                        } elseif (is_numeric($player_id_field)) {
                            $p_id = $player_id_field;
                        }
                    }

                    if (empty($name) && $p_id) {
                        $u = get_userdata($p_id);
                        if ($u)
                            $name = $u->display_name;
                    }

                    if (empty($name))
                        continue;

                    $p_key = $p_id ? 'user_' . $p_id : $name;

                    if (!isset($player_elos[$p_key])) {
                        $player_elos[$p_key] = LPDH_DEFAULT_ELO;
                    }
                    $event_participants_names[] = $p_key;
                    $total_event_elo += $player_elos[$p_key];
                }
                $avg_elo = count($event_participants_names) > 0 ? $total_event_elo / count($event_participants_names) : LPDH_DEFAULT_ELO;

                // Process Rankings for Stats & ELO Update
                $user_found_in_event = false;
                foreach ($rankings as $index => $rank) {
                    $player_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
                    $p_id = 0;
                    if (is_array($player_id_field) && isset($player_id_field['ID'])) {
                        $p_id = $player_id_field['ID'];
                    } elseif (is_object($player_id_field)) {
                        $p_id = $player_id_field->ID;
                    } else {
                        $p_id = $player_id_field;
                    }

                    $name = isset($rank['name']) ? trim($rank['name']) : '';
                    if (empty($name) && $p_id) {
                        $u = get_userdata($p_id);
                        if ($u) $name = $u->display_name;
                    }

                    $p_key = $p_id ? 'user_' . $p_id : $name;

                    // 1. Chart & Yearly Stats Logic (Must be BEFORE update for Jan 1st carry-over)
                    if ($p_id == $user_id) {
                        $user_found_in_event = true;

                        if ($event_year) {
                            $y_val = intval($event_year);
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

                        if ($event_year && !isset($elo_starts_added[$event_year])) {
                            if ($selected_year === 'global' || $event_year === $selected_year) {
                                $elo_history_labels[] = '01/01/' . date('y', strtotime($event_date_raw));
                                // Try ID key first, then name key
                                $start_prev_val = isset($player_elos[$p_key]) ? $player_elos[$p_key] : (isset($player_elos[$name]) ? $player_elos[$name] : LPDH_DEFAULT_ELO);
                                $elo_history_data[] = round($start_prev_val);
                                $elo_starts_added[$event_year] = true;
                            }
                        }
                    }

                    // 2. ELO Update Logic (Everyone, unless event is excluded from ELO)
                    if (!empty($name) && !$exclude_elo) {
                        $event_y_int = intval($event_year);
                        $sel_y_int = ($selected_year === 'global') ? 0 : intval($selected_year);

                        if ($selected_year === 'global' || $event_y_int >= $sel_y_int) {
                            // Try ID key first, then name key for update base
                            $current_e_val = isset($player_elos[$p_key]) ? $player_elos[$p_key] : (isset($player_elos[$name]) ? $player_elos[$name] : LPDH_DEFAULT_ELO);
                            $wins = intval(isset($rank['win']) ? $rank['win'] : 0);
                            $draws = intval(isset($rank['draw']) ? $rank['draw'] : 0);
                            $losses = intval(isset($rank['lose']) ? $rank['lose'] : 0);
                            $games_played = $wins + $draws + $losses;

                            if ($games_played > 0) {
                                $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                                $elo_calc_data = lpdh_calculate_elo($current_e_val, $wins, $draws, $losses, $avg_elo, $pos, $total_players);
                                $player_elos[$p_key] = $elo_calc_data['new_elo'];
                            }
                        }
                    }

                    // 3. User Summary Stats & Result Point
                    if ($p_id == $user_id) {
                        if (!empty($name)) {
                            if ($selected_year === 'global' || $event_year === $selected_year) {
                                $elo_history_labels[] = $event_date_raw ? date('d/m/y', strtotime($event_date_raw)) : 'Event ' . count($elo_history_labels);
                                $elo_history_data[] = round($player_elos[$p_key]);
                            }
                        }

                        if ($selected_year !== 'global' && $event_year !== $selected_year) {
                            continue;
                        }

                        $total_attendance++;
                        $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                        if ($pos === 1) $total_wins++;
                        if ($index === $total_players - 1) $total_last_places++;

                        $deck_id = isset($rank['player_deck_id']) ? intval($rank['player_deck_id']) : 0;
                        if ($deck_id) {
                            if (!isset($deck_usage_counts[$deck_id])) $deck_usage_counts[$deck_id] = 0;
                            $deck_usage_counts[$deck_id]++;
                            if (!isset($deck_performance[$deck_id])) {
                                $deck_performance[$deck_id] = array('wins' => 0, 'match_wins' => 0, 'match_draws' => 0, 'match_losses' => 0, 'attendance' => 0);
                            }
                            $deck_performance[$deck_id]['attendance']++;
                            if ($pos === 1) $deck_performance[$deck_id]['wins']++;
                            $deck_performance[$deck_id]['match_wins'] += intval(isset($rank['win']) ? $rank['win'] : 0);
                            $deck_performance[$deck_id]['match_draws'] += intval(isset($rank['draw']) ? $rank['draw'] : 0);
                            $deck_performance[$deck_id]['match_losses'] += intval(isset($rank['lose']) ? $rank['lose'] : 0);
                        }

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
        <h1>Player Stats:
            <?php echo esc_html($target_user ? $target_user->display_name : 'Unknown User'); ?>
        </h1>

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
                                <td>
                                    <?php echo $total_attendance; ?> 🙋
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Wins (1st place)</th>
                                <td>
                                    <?php echo $total_wins; ?> 🏆
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Last Places</th>
                                <td>
                                    <?php echo $total_last_places; ?> 🤡
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Calculated Elo</th>
                                <td>
                                    <?php echo $display_elo; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Achievements</th>
                                <td>
                                    <?php echo $unlocked_achievements_count; ?> /
                                    <?php echo $total_achievements; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Roulette Spins</th>
                                <td>
                                    <?php echo $lifetime_spins; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Decks Owned</th>
                                <td>
                                    <?php echo $stats_decks_count; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Most Used Deck</th>
                                <td>
                                    <?php if ($most_used_deck_id): ?>
                                        <a href="<?php echo get_edit_post_link($most_used_deck_id); ?>">
                                            <?php echo esc_html($most_used_deck_name); ?>
                                        </a>
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

        <h2 style="display: flex; justify-content: space-between; align-items: center;">
            My Decks
            <span style="font-size: 14px; font-weight: normal; background: #e0e0e0; padding: 4px 10px; border-radius: 20px; color: #333;"><?php echo $total_decks; ?></span>
        </h2>
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
                                <strong><a href="<?php echo get_edit_post_link($deck['id']); ?>">
                                        <?php echo esc_html($deck['title']); ?>
                                    </a></strong>
                                <?php if ($deck['commander']): ?>
                                    <br>
                                    <span class="description">
                                        <?php echo esc_html($deck['commander']); ?>
                                        <?php if ($deck['partner'])
                                            echo ' (' . esc_html($deck['partner']) . ')'; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $deck['stats']['wins']; ?>
                            </td>
                            <td>
                                <?php echo $deck['stats']['match_wins']; ?>
                            </td>
                            <td>
                                <?php echo $deck['stats']['match_draws']; ?>
                            </td>
                            <td>
                                <?php echo $deck['stats']['match_losses']; ?>
                            </td>
                            <td>
                                <?php echo $win_rate; ?>%
                            </td>
                            <td>
                                <?php echo $deck['stats']['attendance']; ?>
                            </td>
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

        <h2 style="display: flex; justify-content: space-between; align-items: center;">
            Event History
            <span style="font-size: 14px; font-weight: normal; background: #e0e0e0; padding: 4px 10px; border-radius: 20px; color: #333;"><?php echo $total_events; ?></span>
        </h2>
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
                            <td><span style="<?php echo $pos_style; ?>">
                                    <?php echo esc_html($rank['pos']); ?>
                                </span></td>
                            <td>
                                <?php echo $total_players; ?>
                            </td>
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
 * Checks a single condition against a value.
 */

/**
 * Render Decks Stats admin page
 */
function render_deck_stats_page()
{
    // Collect available years from all events
    $event_args_years = array(
        'post_type'      => 'event',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );
    $events_years_query = new WP_Query($event_args_years);
    $available_years = array();
    if ($events_years_query->have_posts()) {
        foreach ($events_years_query->posts as $p) {
            $d = get_field('event_date', $p->ID);
            if ($d) {
                $y = date('Y', strtotime($d));
                if (!in_array($y, $available_years)) $available_years[] = $y;
            }
        }
        rsort($available_years);
    }

    $selected_year = isset($_GET['stats_year']) ? sanitize_text_field($_GET['stats_year']) : 'global';

    // Build event query with optional year filter
    $event_args = array(
        'post_type'      => 'event',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'meta_key'       => 'event_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );
    if ($selected_year !== 'global') {
        $sel_y = intval($selected_year);
        $event_args['meta_query'] = array(array(
            'key'     => 'event_date',
            'value'   => array(($sel_y - 1) . '-01-01', ($sel_y + 1) . '-12-31'),
            'compare' => 'BETWEEN',
            'type'    => 'DATE',
        ));
    }
    $events_query = new WP_Query($event_args);

    // Aggregate deck stats & commander counts
    $deck_stats      = array(); // deck_id => [wins, match_wins, match_draws, match_losses, attendance]
    $commander_counts = array(); // "Commander / Partner" => count

    if ($events_query->have_posts()) {
        while ($events_query->have_posts()) {
            $events_query->the_post();
            $event_id = get_the_ID();

            $event_date_raw = get_field('event_date', $event_id);
            $event_year = $event_date_raw ? date('Y', strtotime($event_date_raw)) : '';

            if ((bool) get_field('exclude_from_annual_leaderboard', $event_id)) continue;
            if ($selected_year !== 'global' && $event_year !== $selected_year) continue;

            $rankings = get_field('event_ranking', $event_id);
            if (empty($rankings) || !is_array($rankings)) {
                $json = get_field('event_rankings_json', $event_id);
                if (!empty($json)) {
                    $decoded = json_decode($json, true);
                    if (is_array($decoded)) $rankings = $decoded;
                }
            }
            if (!is_array($rankings)) continue;

            foreach ($rankings as $rank) {
                $deck_id = isset($rank['player_deck_id']) ? intval($rank['player_deck_id']) : 0;
                if (!$deck_id) continue;

                $pos    = isset($rank['pos'])  ? intval($rank['pos'])  : 0;
                $m_win  = isset($rank['win'])  ? intval($rank['win'])  : 0;
                $m_draw = isset($rank['draw']) ? intval($rank['draw']) : 0;
                $m_lose = isset($rank['lose']) ? intval($rank['lose']) : 0;

                if (!isset($deck_stats[$deck_id])) {
                    $deck_stats[$deck_id] = array('wins' => 0, 'match_wins' => 0, 'match_draws' => 0, 'match_losses' => 0, 'attendance' => 0);
                }
                $deck_stats[$deck_id]['attendance']++;
                if ($pos === 1) $deck_stats[$deck_id]['wins']++;
                $deck_stats[$deck_id]['match_wins']   += $m_win;
                $deck_stats[$deck_id]['match_draws']  += $m_draw;
                $deck_stats[$deck_id]['match_losses'] += $m_lose;


            }
        }
        wp_reset_postdata();
    }

    // Count commanders from all uploaded decks
    $all_decks = get_posts(array(
        'post_type'      => 'deck',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids' // Optimization: get only IDs
    ));

    foreach ($all_decks as $deck_id) {
        $commander = get_field('commander', $deck_id);
        if ($commander) {
            $partner = get_field('partner', $deck_id);
            $cmd_key = $partner ? trim($commander) . ' / ' . trim($partner) : trim($commander);
            if (!isset($commander_counts[$cmd_key])) {
                $commander_counts[$cmd_key] = 0;
            }
            $commander_counts[$cmd_key]++;
        }
    }

    // Top 10 commanders by appearances
    arsort($commander_counts);
    $top_commanders = array_slice($commander_counts, 0, 10, true);

    // Pie chart: all decks sorted by attendance DESC
    $sorted_for_pie = $deck_stats;
    uasort($sorted_for_pie, function ($a, $b) { return $b['attendance'] - $a['attendance']; });
    $pie_labels = array();
    $pie_data   = array();
    foreach ($sorted_for_pie as $d_id => $ds) {
        $raw_title   = html_entity_decode(get_the_title($d_id), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $short_title = mb_strlen($raw_title) > 22 ? mb_substr($raw_title, 0, 20) . '…' : $raw_title;
        $pie_labels[] = $short_title;
        $pie_data[]   = $ds['attendance'];
    }

    // Commander pie chart labels (top 10, truncated)
    $cmd_pie_labels = array();
    $cmd_pie_data   = array();
    foreach ($top_commanders as $cmd => $count) {
        $raw_cmd   = html_entity_decode($cmd, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $short_cmd = mb_strlen($raw_cmd) > 28 ? mb_substr($raw_cmd, 0, 26) . '…' : $raw_cmd;
        $cmd_pie_labels[] = $short_cmd;
        $cmd_pie_data[]   = $count;
    }

    // Build table data sorted by win rate DESC (default)
    $deck_table = array();
    foreach ($deck_stats as $d_id => $ds) {
        $total_matches = $ds['match_wins'] + $ds['match_draws'] + $ds['match_losses'];
        $win_rate      = $total_matches > 0 ? round(($ds['match_wins'] / $total_matches) * 100, 1) : 0;
        $deck_post     = get_post($d_id);
        $author_id     = $deck_post ? $deck_post->post_author : 0;
        $author_data   = $author_id ? get_userdata($author_id) : null;
        $deck_table[]  = array(
            'id'           => $d_id,
            'title'        => $deck_post ? $deck_post->post_title : '(Deleted)',
            'commander'    => get_field('commander', $d_id),
            'partner'      => get_field('partner', $d_id),
            'author'       => $author_data ? $author_data->display_name : '-',
            'author_id'    => $author_id,
            'wins'         => $ds['wins'],
            'match_wins'   => $ds['match_wins'],
            'match_draws'  => $ds['match_draws'],
            'match_losses' => $ds['match_losses'],
            'attendance'   => $ds['attendance'],
            'win_rate'     => $win_rate,
        );
    }
    usort($deck_table, function ($a, $b) { return $b['win_rate'] <=> $a['win_rate']; });

    // Pagination: 10 decks per page
    $paged         = isset($_GET['paged_decks']) ? max(1, intval($_GET['paged_decks'])) : 1;
    $per_page      = 10;
    $total_decks   = count($deck_table);
    $total_pages   = ceil($total_decks / $per_page);
    $offset        = ($paged - 1) * $per_page;
    $current_decks = array_slice($deck_table, $offset, $per_page);

    // Chart.js colors
    $chart_colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#76A346','#E7E9ED','#FDB45C','#949FB1','#4D5360','#FF6B6B','#45B7D1','#96CEB4','#FFEAA7','#DDA0DD','#98D8C8','#F7DC6F','#BB8FCE','#82E0AA'];
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Decks Stats', 'text_domain'); ?></h1>

        <form method="get" action="" style="margin: 20px 0;">
            <input type="hidden" name="page" value="deck-stats">
            <label for="ds_stats_year" style="font-weight: bold; margin-right: 10px;"><?php esc_html_e('Filter by year:', 'text_domain'); ?></label>
            <select name="stats_year" id="ds_stats_year" onchange="this.form.submit()">
                <option value="global" <?php selected($selected_year, 'global'); ?>><?php esc_html_e('Global', 'text_domain'); ?></option>
                <?php foreach ($available_years as $y): ?>
                    <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>><?php echo esc_html($y); ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 30px;">

            <!-- Top Commanders Pie Chart -->
            <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                <h2 class="title"><?php esc_html_e('Top Commanders', 'text_domain'); ?></h2>
                <?php if (!empty($top_commanders)): ?>
                    <canvas id="ds_commanderPieChart" style="max-height: 350px;"></canvas>
                <?php else: ?>
                    <p style="color:#666; margin-top:15px;"><?php esc_html_e('No data available.', 'text_domain'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Deck Usage Pie Chart -->
            <div class="card" style="flex: 1; min-width: 300px; padding: 20px;">
                <h2 class="title"><?php esc_html_e('Deck Usage', 'text_domain'); ?></h2>
                <canvas id="ds_deckUsagePieChart" style="max-height: 350px;"></canvas>
            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var colors = <?php echo json_encode(array_slice(array_merge($chart_colors, $chart_colors, $chart_colors), 0, max(count($pie_data), count($cmd_pie_data)))); ?>;

            // Commander Pie Chart
            var ctxCmd = document.getElementById('ds_commanderPieChart');
            if (ctxCmd) {
                new Chart(ctxCmd.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode($cmd_pie_labels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($cmd_pie_data); ?>,
                            backgroundColor: colors.slice(0, <?php echo count($cmd_pie_data); ?>),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }

            // Deck Usage Pie Chart
            var ctxDeck = document.getElementById('ds_deckUsagePieChart');
            if (ctxDeck) {
                new Chart(ctxDeck.getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: <?php echo json_encode($pie_labels); ?>,
                        datasets: [{
                            data: <?php echo json_encode($pie_data); ?>,
                            backgroundColor: colors.slice(0, <?php echo count($pie_data); ?>),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        });
        </script>

        <hr style="margin: 30px 0;">

        <h2><?php esc_html_e('All Decks', 'text_domain'); ?></h2>
        <p style="color:#666;"><?php printf(_n('%d deck found.', '%d decks found.', $total_decks, 'text_domain'), $total_decks); ?></p>

        <table class="wp-list-table widefat fixed striped table-view-list" id="ds-decks-table">
            <thead>
                <tr>
                    <th class="ds-sortable" data-col="0" style="cursor:pointer;">Deck ↕</th>
                    <th class="ds-sortable" data-col="1" style="cursor:pointer; text-align:center; width:130px;">Tournament Wins ↕</th>
                    <th class="ds-sortable" data-col="2" style="cursor:pointer; text-align:center; width:110px;">Match Wins ↕</th>
                    <th class="ds-sortable" data-col="3" style="cursor:pointer; text-align:center; width:110px;">Match Draws ↕</th>
                    <th class="ds-sortable" data-col="4" style="cursor:pointer; text-align:center; width:115px;">Match Losses ↕</th>
                    <th class="ds-sortable ds-sorted-desc" data-col="5" style="cursor:pointer; text-align:center; width:100px;">Win Rate ▼</th>
                    <th class="ds-sortable" data-col="6" style="cursor:pointer; text-align:center; width:100px;">Attendance ↕</th>
                </tr>
            </thead>
            <tbody id="ds-decks-tbody">
                <?php if (!empty($current_decks)): ?>
                    <?php foreach ($current_decks as $deck): ?>
                        <tr>
                            <td>
                                <strong><a href="<?php echo esc_url(get_edit_post_link($deck['id'])); ?>"><?php echo esc_html($deck['title']); ?></a></strong>
                                <?php if ($deck['commander']): ?>
                                    <br><span class="description">
                                        <?php echo esc_html($deck['commander']); ?>
                                        <?php if ($deck['partner']) echo ' / ' . esc_html($deck['partner']); ?>
                                    </span>
                                <?php endif; ?>
                                <br><span class="description" style="color:#aaa;">
                                    <?php if ($deck['author_id']): ?>
                                        <a href="<?php echo esc_url(get_author_posts_url($deck['author_id'])); ?>" target="_blank"><?php echo esc_html($deck['author']); ?></a>
                                    <?php else: ?>
                                        <?php echo esc_html($deck['author']); ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td style="text-align:center;"><?php echo intval($deck['wins']); ?></td>
                            <td style="text-align:center;"><?php echo intval($deck['match_wins']); ?></td>
                            <td style="text-align:center;"><?php echo intval($deck['match_draws']); ?></td>
                            <td style="text-align:center;"><?php echo intval($deck['match_losses']); ?></td>
                            <td style="text-align:center;"><?php echo esc_html($deck['win_rate']); ?>%</td>
                            <td style="text-align:center;"><?php echo intval($deck['attendance']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7"><?php esc_html_e('No deck data found.', 'text_domain'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        if ($total_pages > 1) {
            $page_links = paginate_links(array(
                'base'      => add_query_arg('paged_decks', '%#%'),
                'format'    => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total'     => $total_pages,
                'current'   => $paged,
                'add_args'  => array('stats_year' => $selected_year),
            ));
            if ($page_links) {
                echo '<div class="tablenav"><div class="tablenav-pages">' . $page_links . '</div></div>';
            }
        }
        ?>

        <script>
        (function () {
            var table = document.getElementById('ds-decks-table');
            if (!table) return;
            var headers = table.querySelectorAll('th.ds-sortable');
            var tbody   = document.getElementById('ds-decks-tbody');
            var sortState = { col: 5, dir: -1 }; // default: win_rate DESC

            function cellVal(row, col) {
                var cell = row.cells[col];
                if (!cell) return '';
                var txt = cell.innerText.replace('%', '').trim().split('\n')[0];
                var n = parseFloat(txt);
                return isNaN(n) ? txt.toLowerCase() : n;
            }

            function applySort(col, dir) {
                var rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort(function (a, b) {
                    var va = cellVal(a, col), vb = cellVal(b, col);
                    if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir;
                    return va < vb ? -dir : va > vb ? dir : 0;
                });
                rows.forEach(function (r) { tbody.appendChild(r); });
                headers.forEach(function (h) {
                    h.classList.remove('ds-sorted-asc', 'ds-sorted-desc');
                    h.innerText = h.innerText.replace(' ▲', '').replace(' ▼', '').replace(' ↕', '') + ' ↕';
                });
                headers[col].classList.add(dir === 1 ? 'ds-sorted-asc' : 'ds-sorted-desc');
                headers[col].innerText = headers[col].innerText.replace(' ↕', '') + (dir === 1 ? ' ▲' : ' ▼');
            }

            headers.forEach(function (th) {
                th.addEventListener('click', function () {
                    var col = parseInt(th.getAttribute('data-col'));
                    var dir = (sortState.col === col) ? -sortState.dir : -1;
                    sortState = { col: col, dir: dir };
                    applySort(col, dir);
                });
            });
        })();
        </script>

    </div>
    <?php
}
