<?php
/**
 * Template for displaying single leaderboard
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <?php while (have_posts()):
                        the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                                <?php
                                $year = get_field('field_leaderboard_year');
                                if (get_the_modified_date()): ?>
                                    <div class="text-muted small mt-2">
                                        <i class="fas fa-clock me-1"></i> Last updated: <?php the_modified_date('d/m/Y H:i'); ?>
                                    </div>
                                <?php endif; ?>
                            </header>

                            <div class="entry-content mb-5">
                                <?php the_content(); ?>
                            </div>

                            <?php
                            $rankings_json = get_field('field_leaderboard_rankings_json');
                            $rankings = json_decode($rankings_json, true);

                            if (is_array($rankings) && !empty($rankings)): ?>

                                <?php
                                // Calcolo Statistiche per i riquadri
                                $best_points = null;
                                $best_first = null;
                                $best_attendance = null;
                                $best_last = null;

                                foreach ($rankings as $r) {
                                    // Punti (già ordinato, ma per sicurezza)
                                    if (!$best_points || $r['points'] > $best_points['points'])
                                        $best_points = $r;
                                    // Primi posti
                                    if (!$best_first || $r['first'] > $best_first['first'])
                                        $best_first = $r;
                                    // Presenze
                                    if (!$best_attendance || $r['count'] > $best_attendance['count'])
                                        $best_attendance = $r;
                                    // Ultimi posti
                                    if (!$best_last || $r['last'] > $best_last['last'])
                                        $best_last = $r;
                                }

                                // Miglior Torneo (più presenze)
                                $best_event = null;
                                $max_event_players = 0;
                                if ($year) {
                                    $events = get_posts(array(
                                        'post_type' => 'event',
                                        'posts_per_page' => -1,
                                        'meta_query' => array(
                                            array(
                                                'key' => 'event_date',
                                                'value' => array($year . '-01-01 00:00:00', $year . '-12-31 23:59:59'),
                                                'compare' => 'BETWEEN',
                                                'type' => 'DATETIME'
                                            )
                                        )
                                    ));

                                    foreach ($events as $evt) {
                                        $evt_rankings = get_field('event_ranking', $evt->ID);
                                        $count = is_array($evt_rankings) ? count($evt_rankings) : 0;
                                        if ($count > $max_event_players) {
                                            $max_event_players = $count;
                                            $best_event = $evt;
                                        }
                                    }
                                }

                                // Helper per link profilo
                                function get_player_link_html($p)
                                {
                                    $name = esc_html($p['name']);
                                    if (!empty($p['user_id'])) {
                                        $user_info = get_userdata($p['user_id']);
                                        if ($user_info) {
                                            $name = esc_html($user_info->display_name);
                                        }
                                        return '<a href="' . esc_url(get_author_posts_url($p['user_id'])) . '" class="fw-bold text-decoration-none">' . $name . '</a>';
                                    }
                                    return '<span class="fw-bold">' . $name . '</span>';
                                }
                                ?>

                                <div
                                    class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3 mb-3 justify-content-center text-center">
                                    <!-- Miglior player con più punti -->
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border-0 bg-light">
                                            <div class="card-body">
                                                <div class="small text-muted text-uppercase mb-1">Score</div>
                                                <div class="mb-1"><?php echo get_player_link_html($best_points); ?></div>
                                                <div class="h4 mb-0 text-primary"><?php echo $best_points['points']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Miglior player con primi posti -->
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border-0 bg-light">
                                            <div class="card-body">
                                                <div class="small text-muted text-uppercase mb-1">🥇 Wins</div>
                                                <div class="mb-1"><?php echo get_player_link_html($best_first); ?></div>
                                                <div class="h4 mb-0 text-warning"><?php echo $best_first['first']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Miglior player con presenze -->
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border-0 bg-light">
                                            <div class="card-body">
                                                <div class="small text-muted text-uppercase mb-1">Attendance</div>
                                                <div class="mb-1"><?php echo get_player_link_html($best_attendance); ?></div>
                                                <div class="h4 mb-0 text-info"><?php echo $best_attendance['count']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Miglior player con più ultimi posti -->
                                    <div class="col">
                                        <div class="card h-100 shadow-sm border-0 bg-light">
                                            <div class="card-body">
                                                <div class="small text-muted text-uppercase mb-1">🤡 Last Places</div>
                                                <div class="mb-1"><?php echo get_player_link_html($best_last); ?></div>
                                                <div class="h4 mb-0 text-danger"><?php echo $best_last['last']; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-5 justify-content-center text-center">
                                    <!-- Miglior torneo -->
                                    <?php if ($best_event):
                                        $place = get_field('field_event_place', $best_event->ID);
                                        $date = get_field('field_event_date', $best_event->ID);
                                        ?>
                                        <div class="col-12">
                                            <div class="card h-100 shadow-sm border-0 bg-light">
                                                <div class="card-body">
                                                    <div class="small text-muted text-uppercase mb-1">Best Tournament</div>
                                                    <div class="mb-1 fw-bold"><a
                                                            href="<?php echo get_permalink($best_event->ID); ?>"
                                                            class="text-decoration-none"><?php echo esc_html($best_event->post_title); ?></a>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <?php echo date_i18n('d/m/Y', strtotime($date)); ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <?php echo $place ? esc_html($place->post_title) : '-'; ?>
                                                    </div>
                                                    <div class="fw-bold mt-1"><?php echo $max_event_players; ?> Players</div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="leaderboard-rankings mb-5">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="leaderboardTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" class="text-center leaderboard-col-num"
                                                        onclick="sortTable(0)">#</th>
                                                    <th scope="col" class="text-center leaderboard-col-medal"
                                                        onclick="sortTable(1)"></th>
                                                    <th scope="col" class="text-start leaderboard-header-sortable"
                                                        onclick="sortTable(2)">Player</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-points"
                                                        onclick="sortTable(3)">Punti</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-winrate"
                                                        onclick="sortTable(4)">Win Rate</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-played"
                                                        onclick="sortTable(5)">Played</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-points"
                                                        onclick="sortTable(6)">Wins</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-points"
                                                        onclick="sortTable(7)">Draws</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-points"
                                                        onclick="sortTable(8)">Losses</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-points"
                                                        onclick="sortTable(9)">Survey</th>
                                                    <th scope="col"
                                                        class="text-center leaderboard-header-sortable leaderboard-col-points"
                                                        onclick="sortTable(10)">Attendance</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($rankings as $index => $rank):
                                                    $pos = $index + 1;
                                                    $name = isset($rank['name']) ? $rank['name'] : '';
                                                    $points = isset($rank['points']) ? $rank['points'] : 0;
                                                    $elo = isset($rank['elo']) ? $rank['elo'] : 1200;
                                                    $win = isset($rank['win']) ? $rank['win'] : 0;
                                                    $draw = isset($rank['draw']) ? $rank['draw'] : 0;
                                                    $lose = isset($rank['lose']) ? $rank['lose'] : 0;
                                                    $count = isset($rank['count']) ? $rank['count'] : 0;
                                                    $first = isset($rank['first']) ? $rank['first'] : 0;
                                                    $last = isset($rank['last']) ? $rank['last'] : 0;
                                                    $user_id = isset($rank['user_id']) ? $rank['user_id'] : 0;
                                                    $trend = isset($rank['trend']) ? $rank['trend'] : 0;

                                                    // Fallback: try to find user by display name if ID is missing
                                                    if (!$user_id && !empty($name)) {
                                                        $user = get_users(array(
                                                            'search' => $name,
                                                            'search_columns' => array('display_name'),
                                                            'number' => 1
                                                        ));
                                                        if (!empty($user)) {
                                                            $user_id = $user[0]->ID;
                                                        }
                                                    }

                                                    if ($user_id) {
                                                        $user_info = get_userdata($user_id);
                                                        if ($user_info) {
                                                            $name = $user_info->display_name;
                                                        }
                                                    }

                                                    $player_profile_url = $user_id ? get_author_posts_url($user_id) : '';

                                                    // Colors for top 3
                                                    $row_class = '';
                                                    if ($pos === 1) {
                                                        $row_class = 'rank-gold';
                                                    } elseif ($pos === 2) {
                                                        $row_class = 'rank-silver';
                                                    } elseif ($pos === 3) {
                                                        $row_class = 'rank-bronze';
                                                    }

                                                    // Trend Icon
                                                    $trend_icon = '';
                                                    if ($trend === 'new') {
                                                        $trend_icon = '<span class="badge bg-info text-dark ms-2" style="font-size: 0.6em;">NEW</span>';
                                                    } elseif ($trend > 0) {
                                                        $trend_icon = '<span class="text-success ms-2 small" title="Up by ' . $trend . ' positions"><i class="fas fa-arrow-up"></i> ' . $trend . '</span>';
                                                    } elseif ($trend < 0) {
                                                        $trend_icon = '<span class="text-danger ms-2 small" title="Down by ' . abs($trend) . ' positions"><i class="fas fa-arrow-down"></i> ' . abs($trend) . '</span>';
                                                    }
                                                    ?>
                                                    <tr class="<?php echo esc_attr($row_class); ?>">
                                                        <td class="text-center fw-bold"><?php echo $pos; ?></td>
                                                        <td data-value="<?php echo esc_attr($name); ?>">
                                                            <?php
                                                            $avatar = get_avatar($user_id ? $user_id : 0, 24, 'mp', '', array('class' => 'rounded-circle me-2', 'style' => 'width: 24px; height: 24px;'));

                                                            if ($player_profile_url) {
                                                                echo '<a href="' . esc_url($player_profile_url) . '" class="text-decoration-none text-reset d-flex align-items-center">' . $avatar . esc_html($name) . $trend_icon . '</a>';
                                                            } else {
                                                                echo '<div class="d-flex align-items-center">' . $avatar . esc_html($name) . $trend_icon . '</div>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center fw-bold"
                                                            data-value="<?php echo esc_attr($points); ?>">
                                                            <?php echo esc_html($points); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($elo); ?>">
                                                            <?php echo esc_html($elo); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($win); ?>"><span
                                                                class="text-success"><?php echo esc_html($win); ?></span></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($draw); ?>"><span
                                                                class="text-secondary"><?php echo esc_html($draw); ?></span></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($lose); ?>"><span
                                                                class="text-danger"><?php echo esc_html($lose); ?></span></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($first); ?>">
                                                            <?php echo esc_html($first); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($last); ?>">
                                                            <?php echo esc_html($last); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($count); ?>">
                                                            <?php echo esc_html($count); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const table = document.getElementById('leaderboardTable');
                                        const headers = table.querySelectorAll('th.sortable');
                                        const tbody = table.querySelector('tbody');

                                        headers.forEach(header => {
                                            header.addEventListener('click', () => {
                                                const rows = Array.from(tbody.querySelectorAll('tr'));
                                                const isAsc = header.classList.contains('asc');
                                                const colIndex = header.cellIndex;

                                                headers.forEach(h => {
                                                    h.classList.remove('asc', 'desc');
                                                    h.querySelector('i').className = 'fas fa-sort small text-muted ms-1';
                                                });

                                                header.classList.toggle('asc', !isAsc);
                                                header.classList.toggle('desc', isAsc);
                                                header.querySelector('i').className = isAsc ? 'fas fa-sort-down small text-muted ms-1' : 'fas fa-sort-up small text-muted ms-1';

                                                rows.sort((a, b) => {
                                                    let aVal = a.children[colIndex].dataset.value;
                                                    let bVal = b.children[colIndex].dataset.value;

                                                    let aNum = parseFloat(aVal);
                                                    let bNum = parseFloat(bVal);

                                                    if (!isNaN(aNum) && !isNaN(bNum)) {
                                                        return isAsc ? aNum - bNum : bNum - aNum;
                                                    }
                                                    return isAsc ? aVal.localeCompare(bVal) : bVal.localeCompare(aVal);
                                                });

                                                rows.forEach((row, index) => {
                                                    row.children[0].textContent = index + 1;

                                                    // Update row classes for top 3
                                                    row.classList.remove('rank-gold', 'rank-silver', 'rank-bronze');
                                                    if (index === 0) row.classList.add('rank-gold');
                                                    else if (index === 1) row.classList.add('rank-silver');
                                                    else if (index === 2) row.classList.add('rank-bronze');

                                                    tbody.appendChild(row);
                                                });
                                            });
                                        });
                                    });
                                </script>
                            <?php endif; ?>

                        </article>

                    <?php endwhile; ?>

                </div>
            </div>
        </div>

    </main>
</div>

</main>
</div>

<?php get_footer(); ?>