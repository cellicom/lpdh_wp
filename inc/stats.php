<?php
/**
 * Player Stats Module
 */

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
 * Centralized Player Stats retrieval
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
