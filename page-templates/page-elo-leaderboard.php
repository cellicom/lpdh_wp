<?php
/**
 * Template Name: ELO Leaderboard
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

                            <?php
                            $cities = lpdh_get_unique_place_cities();
                            $current_city = '';
                            
                            if (isset($_GET['city'])) {
                                $current_city = sanitize_text_field($_GET['city']);
                            } elseif (is_user_logged_in()) {
                                $current_city = get_user_meta(get_current_user_id(), 'preferred_city', true);
                            }
                            
                            if (empty($current_city)) {
                                $current_city = 'Global';
                            }
                            ?>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                                <div class="d-flex align-items-center justify-content-center small mt-2 text-white">
                                    <i class="<?php echo ($current_city === 'Global') ? 'fas fa-globe-americas' : 'fas fa-map-marker-alt'; ?> me-1" style="color: <?php echo ($current_city === 'Global') ? '#2ed573' : '#ff4757'; ?>;"></i> 
                                    <select class="leaderboard-city-select" onchange="const u = new window.URL(window.location.href); u.searchParams.set('city', this.value); window.location.href = u.toString();">
                                        <option value="Global" <?php selected($current_city, 'Global'); ?>>Global</option>
                                        <?php foreach ($cities as $c): ?>
                                            <option value="<?php echo esc_attr($c); ?>" <?php selected($current_city, $c); ?>>
                                                <?php echo esc_html($c); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="ms-1">Player Rankings</span>
                                </div>
                            </header>

                            <div class="entry-content mb-4">
                                <?php the_content(); ?>
                            </div>



                            <?php
                            // Fetch all published events ordered by date ASC to ensure correct ELO calculation
                            $event_args = array(
                                'post_type' => 'event',
                                'posts_per_page' => -1,
                                'post_status' => 'publish',
                                'meta_key' => 'event_date',
                                'orderby' => 'meta_value',
                                'order' => 'ASC',
                            );

                            // Apply city filter if it's not Global
                            if ($current_city !== 'Global' && !empty($current_city)) {
                                $place_ids = lpdh_get_place_ids_by_city($current_city);
                                // Se non ci sono place in quella città, passiamo array vuoto o un valore impossibile 0 per ritornare zero eventi
                                if (empty($place_ids)) {
                                    $place_ids = array(0);
                                }
                                $event_args['meta_query'] = array(
                                    array(
                                        'key' => 'event_place',
                                        'value' => $place_ids,
                                        'compare' => 'IN'
                                    )
                                );
                            }

                            $all_events = get_posts($event_args);

                            // Use the existing helper to calculate statistics
                            $rankings = lpdh_calculate_rankings_data($all_events);

                            // Re-sort by ELO descending for this specific view
                            usort($rankings, function ($a, $b) {
                                if ($b['elo'] == $a['elo']) {
                                    return $b['win'] - $a['win']; // Tie-breaker: match wins
                                }
                                return $b['elo'] - $a['elo'];
                            });

                            if (is_array($rankings) && !empty($rankings)): ?>

                                <div class="leaderboard-rankings mb-5">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="eloLeaderboardTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                                    <th scope="col" class="sortable" style="cursor: pointer;">Player <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">ELO <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Tournament Wins">🥇 <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Last Places">🤡 <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Match Wins">W <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Match Losses">L <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Match Draws">D <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">Attendance <i class="fas fa-sort small ms-1"></i></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($rankings as $index => $rank):
                                                    $pos = $index + 1;
                                                    $name = isset($rank['name']) ? $rank['name'] : '';
                                                    $elo = isset($rank['elo']) ? $rank['elo'] : LPDH_DEFAULT_ELO;
                                                    $win = isset($rank['win']) ? $rank['win'] : 0;
                                                    $draw = isset($rank['draw']) ? $rank['draw'] : 0;
                                                    $lose = isset($rank['lose']) ? $rank['lose'] : 0;
                                                    $first = isset($rank['first']) ? $rank['first'] : 0;
                                                    $last = isset($rank['last']) ? $rank['last'] : 0;
                                                    $count = isset($rank['count']) ? $rank['count'] : 0;
                                                    $user_id = isset($rank['user_id']) ? $rank['user_id'] : 0;

                                                    // Feature: Use Display Name if available
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
                                                    ?>
                                                    <tr class="<?php echo esc_attr($row_class); ?>">
                                                        <td class="text-center fw-bold"><?php echo $pos; ?></td>
                                                        <td data-value="<?php echo esc_attr($name); ?>">
                                                            <?php
                                                            $avatar = get_avatar($user_id ? $user_id : 0, 24, '', '', array('class' => 'rounded-circle me-2', 'style' => 'width: 24px; height: 24px;'));

                                                            if ($player_profile_url) {
                                                                echo '<a href="' . esc_url($player_profile_url) . '" class="text-decoration-none text-reset d-flex align-items-center fw-bold">' . $avatar . esc_html($name) . '</a>';
                                                            } else {
                                                                echo '<div class="d-flex align-items-center fw-bold">' . $avatar . esc_html($name) . '</div>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center fw-bold" data-value="<?php echo esc_attr($elo); ?>">
                                                            <?php echo esc_html($elo); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($first); ?>">
                                                            <?php echo esc_html($first); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($last); ?>">
                                                            <?php echo esc_html($last); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($win); ?>">
                                                            <span class="text-success"><?php echo esc_html($win); ?></span>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($lose); ?>">
                                                            <span class="text-danger"><?php echo esc_html($lose); ?></span>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($draw); ?>">
                                                            <span class="text-info"><?php echo esc_html($draw); ?></span>
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
                                        const table = document.getElementById('eloLeaderboardTable');
                                        const headers = table.querySelectorAll('th.sortable');
                                        const tbody = table.querySelector('tbody');

                                        headers.forEach(header => {
                                            header.addEventListener('click', () => {
                                                const rows = Array.from(tbody.querySelectorAll('tr'));
                                                const isAsc = header.classList.contains('asc');
                                                const colIndex = header.cellIndex;

                                                headers.forEach(h => {
                                                    h.classList.remove('asc', 'desc');
                                                    h.querySelector('i').className = 'fas fa-sort small ms-1';
                                                });

                                                header.classList.toggle('asc', !isAsc);
                                                header.classList.toggle('desc', isAsc);
                                                header.querySelector('i').className = isAsc ? 'fas fa-sort-down small ms-1' : 'fas fa-sort-up small ms-1';

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

<?php get_footer(); ?>
