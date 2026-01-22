<?php
/**
 * Template Name: User Statistics
 * Template for displaying user statistics on the frontend
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Access control: Only logged in users
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

$user_id = get_current_user_id();

// Admin/Moderator override: allow viewing other users' stats via URL
if ((current_user_can('administrator') || current_user_can('editor')) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
}

$target_user = get_userdata($user_id);
if (!$target_user) {
    wp_redirect(home_url());
    exit;
}

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

$current_year = date('Y');
if (!in_array($current_year, $available_years)) {
    $available_years[] = $current_year;
    rsort($available_years);
}

$selected_year = isset($_GET['stats_year']) ? $_GET['stats_year'] : $current_year;

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
                    if ($u)
                        $name = $u->display_name;
                }

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
                    // Collect Yearly Stats
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

                    if ($selected_year !== 'global' && $event_year !== $selected_year) {
                        continue;
                    }

                    $total_attendance++;
                    $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                    if ($pos === 1)
                        $total_wins++;
                    if ($index === $total_players - 1)
                        $total_last_places++;

                    $deck_id = isset($rank['player_deck_id']) ? intval($rank['player_deck_id']) : 0;
                    if ($deck_id) {
                        if (!isset($deck_usage_counts[$deck_id]))
                            $deck_usage_counts[$deck_id] = 0;
                        $deck_usage_counts[$deck_id]++;

                        if (!isset($deck_performance[$deck_id])) {
                            $deck_performance[$deck_id] = array('wins' => 0, 'match_wins' => 0, 'match_draws' => 0, 'match_losses' => 0, 'attendance' => 0);
                        }
                        $deck_performance[$deck_id]['attendance']++;
                        if ($pos === 1)
                            $deck_performance[$deck_id]['wins']++;
                        $deck_performance[$deck_id]['match_wins'] += intval(isset($rank['win']) ? $rank['win'] : 0);
                        $deck_performance[$deck_id]['match_draws'] += intval(isset($rank['draw']) ? $rank['draw'] : 0);
                        $deck_performance[$deck_id]['match_losses'] += intval(isset($rank['lose']) ? $rank['lose'] : 0);
                    }

                    $player_events[] = array(
                        'event_id' => $event_id,
                        'ranking' => $rank,
                        'event_date' => $event_date_raw,
                        'total_players' => $total_players
                    );

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

$player_events = array_reverse($player_events);

// Chart Data
$chart_labels = array();
$chart_data = array();
if (!empty($deck_usage_counts)) {
    arsort($deck_usage_counts);
    foreach ($deck_usage_counts as $d_id => $count) {
        $chart_labels[] = get_the_title($d_id);
        $chart_data[] = $count;
    }
}

// Most used deck
$most_used_deck_name = '-';
$most_used_deck_id = 0;
if (!empty($deck_usage_counts)) {
    $most_used_deck_id = array_keys($deck_usage_counts, max($deck_usage_counts))[0];
    $most_used_deck_name = get_the_title($most_used_deck_id);
}

// Win Rate Trend Data
ksort($yearly_stats);
$line_labels = array();
$line_data = array();
foreach ($yearly_stats as $y => $data) {
    $line_labels[] = $y;
    $rate = $data['total'] > 0 ? round(($data['wins'] / $data['total']) * 100, 1) : 0;
    $line_data[] = $rate;
}

// --- Pagination Logic ---
$items_per_page = 5;

// Decks Pagination
$paged_decks = isset($_GET['p_decks']) ? max(1, intval($_GET['p_decks'])) : 1;
$args_decks = array(
    'post_type' => 'deck',
    'author' => $user_id,
    'posts_per_page' => $items_per_page,
    'paged' => $paged_decks,
    'orderby' => 'date',
    'order' => 'DESC'
);
$decks_query = new WP_Query($args_decks);
$user_decks = $decks_query->posts;
$total_deck_pages = $decks_query->max_num_pages;

// Events Pagination
$paged_events = isset($_GET['p_events']) ? max(1, intval($_GET['p_events'])) : 1;
$total_player_events = count($player_events);
$total_event_pages = ceil($total_player_events / $items_per_page);
$paged_player_events = array_slice($player_events, ($paged_events - 1) * $items_per_page, $items_per_page);

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">
            <header class="stats-header mb-5 text-center">
                <h1 class="display-6 fw-bold text-primary">
                    <i class="fas fa-chart-line me-2"></i>Player Stats:
                    <?php echo esc_html($target_user->display_name); ?>
                </h1>
                <p class="">Analyze your performance and deck history.</p>
            </header>

            <!-- Filters -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-4">
                    <form method="get" action="<?php echo esc_url(home_url('/')); ?>"
                        class="d-flex align-items-center mb-0">
                        <?php if (isset($_GET['page_id'])): ?>
                            <input type="hidden" name="page_id" value="<?php echo intval($_GET['page_id']); ?>">
                        <?php endif; ?>
                        <?php if (isset($_GET['user_id'])): ?>
                            <input type="hidden" name="user_id" value="<?php echo intval($_GET['user_id']); ?>">
                        <?php endif; ?>
                        <label for="stats_year" class="me-3 fw-bold text-nowrap">Year:</label>
                        <select name="stats_year" id="stats_year" class="form-select" onchange="this.form.submit()">
                            <option value="global" <?php selected($selected_year, 'global'); ?>>Global</option>
                            <?php foreach ($available_years as $y): ?>
                                <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                                    <?php echo esc_html($y); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card h-100 bg-transparent text-white border-primary shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-uppercase text-primary small fw-bold mb-3">Attendance</h6>
                            <div class="display-4 fw-bold"><?php echo $total_attendance; ?></div>
                            <div class="small  mt-2">Tournaments Joined</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 bg-transparent text-white border-success shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-uppercase text-success small fw-bold mb-3">Wins</h6>
                            <div class="display-4 fw-bold"><?php echo $total_wins; ?> 🏆</div>
                            <div class="small  mt-2">1st Place Finishes</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 bg-transparent text-white border-danger shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-uppercase text-danger small fw-bold mb-3">Last Places</h6>
                            <div class="display-4 fw-bold"><?php echo $total_last_places; ?> 🤡</div>
                            <div class="small mt-2">Clown Addicted Awards</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card h-100 bg-transparent text-white border-info shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-uppercase text-info small fw-bold mb-3">Most Used Deck</h6>
                            <div class="h3 fw-bold mb-0">
                                <?php if ($most_used_deck_id): ?>
                                    <a href="<?php echo get_permalink($most_used_deck_id); ?>"
                                        class="text-info text-decoration-none">
                                        <?php echo esc_html($most_used_deck_name); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo esc_html($most_used_deck_name); ?>
                                <?php endif; ?>
                            </div>
                            <div class="small  mt-2">Favorite Strategy</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Container -->
            <div class="stats-charts-container bg-white bg-opacity-25 p-4 rounded-4 shadow-sm mb-5">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <h4 class="mb-4 text-center"><i class="fas fa-pie-chart me-2"></i>Deck Usage</h4>
                        <div style="height: 300px;">
                            <canvas id="deckUsageChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h4 class="mb-4 text-center"><i class="fas fa-percentage me-2"></i>Win Rate Trend</h4>
                        <div style="height: 300px;">
                            <canvas id="winRateChart"></canvas>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h4 class="mb-4 text-center"><i class="fas fa-chart-line me-2"></i>ELO History</h4>
                        <div style="height: 300px;">
                            <canvas id="eloChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables -->
            <div class="row g-4">
                <!-- Decks Table -->
                <div class="col-12">
                    <div id="decks-partial" class="deck-performance-ajax deck-performance mb-5">
                        <h3 class="mb-3 border-bottom pb-2"><i class="fas fa-layer-group me-2"></i>Deck Performance</h3>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle bg-transparent">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Deck</th>
                                        <th class="text-center">Wins (1st)</th>
                                        <th class="text-center">M. Wins</th>
                                        <th class="text-center">M. Draws</th>
                                        <th class="text-center">M. Losses</th>
                                        <th class="text-center">Win Rate</th>
                                        <th class="text-center">Attendance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($user_decks):
                                        foreach ($user_decks as $deck):
                                            $d_id = $deck->ID;
                                            $stats = isset($deck_performance[$d_id]) ? $deck_performance[$d_id] : array('wins' => 0, 'match_wins' => 0, 'match_draws' => 0, 'match_losses' => 0, 'attendance' => 0);
                                            $total_matches = $stats['match_wins'] + $stats['match_draws'] + $stats['match_losses'];
                                            $win_rate = $total_matches > 0 ? round(($stats['match_wins'] / $total_matches) * 100, 1) : 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo get_permalink($d_id); ?>"
                                                        class="text-info fw-bold text-decoration-none">
                                                        <?php echo esc_html($deck->post_title); ?>
                                                    </a>
                                                </td>
                                                <td class="text-center"><?php echo $stats['wins']; ?></td>
                                                <td class="text-center"><?php echo $stats['match_wins']; ?></td>
                                                <td class="text-center"><?php echo $stats['match_draws']; ?></td>
                                                <td class="text-center"><?php echo $stats['match_losses']; ?></td>
                                                <td class="text-center">
                                                    <span
                                                        class="fw-bold <?php echo $win_rate >= 50 ? 'text-success' : 'text-white-50'; ?>">
                                                        <?php echo $win_rate; ?>%
                                                    </span>
                                                </td>
                                                <td class="text-center"><?php echo $stats['attendance']; ?></td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No decks found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Decks Pagination -->
                        <?php if ($total_deck_pages > 1): ?>
                            <div class="mt-3 d-flex justify-content-center">
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php
                                        $deck_links = paginate_links(array(
                                            'base' => add_query_arg('p_decks', '%#%'),
                                            'format' => '',
                                            'current' => $paged_decks,
                                            'total' => $total_deck_pages,
                                            'type' => 'array',
                                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                                        ));
                                        if ($deck_links) {
                                            foreach ($deck_links as $link) {
                                                $active = strpos($link, 'current') !== false ? 'active' : '';
                                                echo '<li class="page-item ' . $active . '">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Event History Table -->
                <div class="col-12">
                    <div id="events-partial" class="event-history-ajax event-history mb-5">
                        <h3 class="mb-3 border-bottom pb-2"><i class="fas fa-history me-2"></i>Event History</h3>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle bg-transparent">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Event</th>
                                        <th>Date & Place</th>
                                        <th class="text-center">Position</th>
                                        <th class="text-center">Total Players</th>
                                        <th>Deck Used</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($paged_player_events):
                                        foreach ($paged_player_events as $event_data):
                                            $event_id = $event_data['event_id'];
                                            $rank = $event_data['ranking'];
                                            $place_obj = get_field('field_event_place', $event_id);
                                            ?>
                                            <?php
                                            $pos = $rank['pos'];
                                            $total_p = $event_data['total_players'];
                                            $row_class = '';
                                            if ($pos == 1)
                                                $row_class = 'rank-gold';
                                            elseif ($pos == 2)
                                                $row_class = 'rank-silver';
                                            elseif ($pos == 3)
                                                $row_class = 'rank-bronze';

                                            $display_pos = ($pos == $total_p && $total_p > 1) ? '🤡' : $pos . '°';
                                            ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td><a href="<?php echo get_permalink($event_id); ?>"
                                                        class="text-primary text-decoration-none fw-bold"><?php echo get_the_title($event_id); ?></a>
                                                </td>
                                                <td>
                                                    <div class="small fw-bold">
                                                        <?php echo date_i18n('d M Y', strtotime($event_data['event_date'])); ?>
                                                    </div>
                                                    <div class="small opacity-75">
                                                        <?php echo $place_obj ? esc_html($place_obj->post_title) : '-'; ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($pos >= 1 && $pos <= 3):
                                                        $badge_class = ($pos == 1) ? 'bg-gold' : (($pos == 2) ? 'bg-silver' : 'bg-bronze');
                                                        ?>
                                                        <span class="badge <?php echo $badge_class; ?> text-dark px-2">
                                                            <?php echo $display_pos; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="fw-bold opacity-75"><?php echo $display_pos; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center"><?php echo $event_data['total_players']; ?></td>
                                                <td>
                                                    <?php
                                                    $d_id = isset($rank['player_deck_id']) ? $rank['player_deck_id'] : 0;
                                                    if ($d_id): ?>
                                                        <a href="<?php echo get_permalink($d_id); ?>"
                                                            class="text-info text-decoration-none small"><?php echo get_the_title($d_id); ?></a>
                                                    <?php else:
                                                        echo '<span class=" italic small">' . (isset($rank['deck']) ? esc_html($rank['deck']) : '-') . '</span>';
                                                    endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No event history found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Events Pagination -->
                        <?php if ($total_event_pages > 1): ?>
                            <div class="mt-3 d-flex justify-content-center">
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php
                                        $event_links = paginate_links(array(
                                            'base' => add_query_arg('p_events', '%#%'),
                                            'format' => '',
                                            'current' => $paged_events,
                                            'total' => $total_event_pages,
                                            'type' => 'array',
                                            'prev_text' => '<i class="fas fa-chevron-left"></i>',
                                            'next_text' => '<i class="fas fa-chevron-right"></i>',
                                        ));
                                        if ($event_links) {
                                            foreach ($event_links as $link) {
                                                $active = strpos($link, 'current') !== false ? 'active' : '';
                                                echo '<li class="page-item ' . $active . '">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Chart JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.1)' },
                    ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                }
            }
        };

        // Deck Usage (Pie)
        new Chart(document.getElementById('deckUsageChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_data); ?>,
                    backgroundColor: ['#ff71ce', '#01cdfe', '#b967ff', '#05ffa1', '#fffb96', '#00ffff', '#ff0033', '#00ff00', '#74ee15', '#f5d300'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { color: 'rgba(255, 255, 255, 0.8)', usePointStyle: true } }
                }
            }
        });

        // Win Rate (Line)
        new Chart(document.getElementById('winRateChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($line_labels); ?>,
                datasets: [{
                    label: 'Win Rate %',
                    data: <?php echo json_encode($line_data); ?>,
                    borderColor: '#05ffa1',
                    backgroundColor: 'rgba(5, 255, 161, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#05ffa1'
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    ...commonOptions.scales,
                    y: { ...commonOptions.scales.y, beginAtZero: true, max: 100 }
                }
            }
        });

        // ELO Trend (Line)
        new Chart(document.getElementById('eloChart'), {
            type: 'line',
            data: {
                labels: <?php echo json_encode($elo_history_labels); ?>,
                datasets: [{
                    label: 'ELO Ranking',
                    data: <?php echo json_encode($elo_history_data); ?>,
                    borderColor: '#ff71ce',
                    backgroundColor: 'rgba(255, 113, 206, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ff71ce'
                }]
            },
            options: commonOptions
        });
    });

    // --- AJAX Pagination Logic ---
    document.addEventListener('click', function (e) {
        const link = e.target.closest('.pagination a');
        if (!link) return;

        // Determine which section we are in
        const section = link.closest('#decks-partial') ? '#decks-partial' : (link.closest('#events-partial') ? '#events-partial' : null);
        if (!section) return;

        e.preventDefault();
        const url = link.href;

        // Visual feedback
        const container = document.querySelector(section);
        container.style.opacity = '0.5';
        container.style.transition = 'opacity 0.2s ease-in-out';

        fetch(url)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector(section);

                if (newContent) {
                    container.innerHTML = newContent.innerHTML;
                    container.style.opacity = '1';

                    // Update URL without reload
                    history.pushState(null, '', url);
                }
            })
            .catch(err => {
                console.error('AJAX pagination error:', err);
                window.location.href = url; // Fallback to normal navigation
            });
    });

    // Handle back/forward buttons
    window.onpopstate = function () {
        window.location.reload();
    };
</script>

<style>
    .bg-gold {
        background-color: #ffd700 !important;
        box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
    }

    .bg-silver {
        background-color: #c0c0c0 !important;
        box-shadow: 0 0 10px rgba(192, 192, 192, 0.5);
    }

    .bg-bronze {
        background-color: #cd7f32 !important;
        box-shadow: 0 0 10px rgba(205, 127, 50, 0.5);
    }

    .stats-charts-container canvas {
        filter: drop-shadow(0 0 10px rgba(0, 0, 0, 0.5));
    }
</style>

<?php get_footer(); ?>