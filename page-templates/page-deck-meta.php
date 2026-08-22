<?php
/**
 * Template Name: Deck Meta
 * Template for displaying overall deck statistics and meta on the frontend
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Get available years for event date filtering
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
            if (!in_array($y, $available_years)) {
                $available_years[] = $y;
            }
        }
    }
    rsort($available_years);
}

$selected_year = isset($_GET['stats_year']) ? sanitize_text_field($_GET['stats_year']) : 'global';

// Fetch events to compute deck meta
$event_args = array(
    'post_type'      => 'event',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'meta_key'       => 'event_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
);
$events = get_posts($event_args);
$deck_stats = array();

// ponytail: single pass over events to aggregate stats
foreach ($events as $event) {
    $event_id = $event->ID;
    
    if ((bool) get_field('exclude_from_annual_leaderboard', $event_id)) {
        continue;
    }
    
    if ($selected_year !== 'global') {
        $event_date_raw = get_field('event_date', $event_id);
        $event_year = $event_date_raw ? date('Y', strtotime($event_date_raw)) : '';
        if ($event_year !== $selected_year) {
            continue;
        }
    }
    
    $rankings = get_field('event_ranking', $event_id);
    if (empty($rankings) || !is_array($rankings)) {
        $json = get_field('event_rankings_json', $event_id);
        if (!empty($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $rankings = $decoded;
            }
        }
    }
    if (!is_array($rankings)) {
        continue;
    }
    
    foreach ($rankings as $rank) {
        $deck_id = isset($rank['player_deck_id']) ? intval($rank['player_deck_id']) : 0;
        if (!$deck_id) {
            continue;
        }
        
        $pos    = isset($rank['pos'])  ? intval($rank['pos'])  : 0;
        $m_win  = isset($rank['win'])  ? intval($rank['win'])  : 0;
        $m_draw = isset($rank['draw']) ? intval($rank['draw']) : 0;
        $m_lose = isset($rank['lose']) ? intval($rank['lose']) : 0;
        
        if (!isset($deck_stats[$deck_id])) {
            $deck_stats[$deck_id] = array('wins' => 0, 'match_wins' => 0, 'match_draws' => 0, 'match_losses' => 0, 'attendance' => 0);
        }
        $deck_stats[$deck_id]['attendance']++;
        if ($pos === 1) {
            $deck_stats[$deck_id]['wins']++;
        }
        $deck_stats[$deck_id]['match_wins']   += $m_win;
        $deck_stats[$deck_id]['match_draws']  += $m_draw;
        $deck_stats[$deck_id]['match_losses'] += $m_lose;
    }
}

$deck_table = array();
foreach ($deck_stats as $d_id => $ds) {
    $total_matches = $ds['match_wins'] + $ds['match_draws'] + $ds['match_losses'];
    $win_rate      = $total_matches > 0 ? round(($ds['match_wins'] / $total_matches) * 100, 1) : 0;
    
    $deck_post     = get_post($d_id);
    if (!$deck_post) {
        continue;
    }
    
    $author_id     = $deck_post->post_author;
    $author_data   = $author_id ? get_userdata($author_id) : null;
    $author_name   = $author_data ? $author_data->display_name : '-';
    
    $commander     = get_field('commander', $d_id);
    $partner       = get_field('partner', $d_id);
    $commander_display = $partner ? trim($commander) . ' / ' . trim($partner) : trim($commander);
    if (empty($commander_display)) {
        $commander_display = '-';
    }
    
    // Fetch commander images for split circle or single circle avatar
    $icon_html = '';
    $popover_content = '';
    $cmdr_img_url = get_commander_image($d_id);
    $partner_img_url = get_partner_image($d_id);

    if ($cmdr_img_url && $partner_img_url) {
        $icon_html = '<div class="position-relative overflow-hidden rounded-circle" style="width: 40px; height: 40px;">';
        $icon_html .= '<img src="' . esc_url($cmdr_img_url) . '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; left: 0; top: 0; clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);">';
        $icon_html .= '<img src="' . esc_url($partner_img_url) . '" style="width: 100%; height: 100%; object-fit: cover; position: absolute; left: 0; top: 0; clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);">';
        $icon_html .= '</div>';
        $popover_content = '<div class=\'d-flex\'><img src=\'' . esc_url($cmdr_img_url) . '\' class=\'me-1 rounded cmdr-popover-img\'><img src=\'' . esc_url($partner_img_url) . '\' class=\'rounded cmdr-popover-img\'></div>';
    } elseif ($cmdr_img_url) {
        $icon_html = '<img src="' . esc_url($cmdr_img_url) . '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">';
        $popover_content = '<img src=\'' . esc_url($cmdr_img_url) . '\' class=\'rounded cmdr-popover-img-large\'>';
    }

    if ($icon_html && $popover_content) {
        $icon_html = '<a tabindex="0" class="text-decoration-none d-inline-block me-2 flex-shrink-0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-html="true" data-bs-custom-class="deck-popover" data-bs-content="' . $popover_content . '">' . $icon_html . '</a>';
    } elseif ($icon_html) {
        $icon_html = '<div class="d-inline-block me-2 flex-shrink-0">' . $icon_html . '</div>';
    }

    $deck_table[] = array(
        'id'           => $d_id,
        'title'        => $deck_post->post_title,
        'permalink'    => get_permalink($d_id),
        'commander'    => $commander_display,
        'icon_html'    => $icon_html,
        'author'       => $author_name,
        'author_url'   => $author_id ? get_author_posts_url($author_id) : '',
        'wins'         => $ds['wins'],
        'match_wins'   => $ds['match_wins'],
        'match_losses' => $ds['match_losses'],
        'match_draws'  => $ds['match_draws'],
        'win_rate'     => $win_rate,
        'attendance'   => $ds['attendance'],
    );
}

// ponytail: Sort by tournament wins DESC by default
usort($deck_table, function ($a, $b) {
    if ($b['wins'] === $a['wins']) {
        if ($b['win_rate'] === $a['win_rate']) {
            return $b['attendance'] <=> $a['attendance'];
        }
        return $b['win_rate'] <=> $a['win_rate'];
    }
    return $b['wins'] <=> $a['wins'];
});

// Dynamically add ELO leaderboard class to body to inherit the same style
add_filter('body_class', function ($classes) {
    $classes[] = 'page-template-page-elo-leaderboard';
    return $classes;
});

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            
                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                                <div class="d-flex align-items-center justify-content-center small mt-2 text-white">
                                    <i class="fas fa-calendar-alt me-1" style="color: #2ed573;"></i> 
                                    <select class="leaderboard-city-select" onchange="const u = new window.URL(window.location.href); u.searchParams.set('stats_year', this.value); window.location.href = u.toString();">
                                        <option value="global" <?php selected($selected_year, 'global'); ?>>Global</option>
                                        <?php foreach ($available_years as $y) : ?>
                                            <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                                                <?php echo esc_html($y); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <span class="ms-1">Deck Statistics</span>
                                </div>
                            </header>

                            <div class="entry-content mb-4">
                                <?php the_content(); ?>
                            </div>

                            <?php if (!empty($deck_table)) : ?>
                                <div class="leaderboard-rankings mb-5">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="deckMetaTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                                    <th scope="col" class="sortable" style="cursor: pointer;">Deck <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="sortable" style="cursor: pointer;">Player <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Tournament Wins">🥇 <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Match Wins">W <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Match Losses">L <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Match Draws">D <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Win Rate">Win Rate <i class="fas fa-sort small ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;" title="Attendance">Attendance <i class="fas fa-sort small ms-1"></i></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                foreach ($deck_table as $index => $deck) :
                                                    $pos = $index + 1;
                                                    
                                                    // Class for top 3
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
                                                        <td data-value="<?php echo esc_attr($deck['title']); ?>">
                                                            <div class="d-flex align-items-center">
                                                                <?php echo $deck['icon_html']; ?>
                                                                <div>
                                                                    <a href="<?php echo esc_url($deck['permalink']); ?>" class="text-decoration-none text-white fw-bold">
                                                                        <?php echo esc_html($deck['title']); ?>
                                                                    </a>
                                                                    <?php if ($deck['commander'] !== '-') : ?>
                                                                        <div class="small text-white opacity-75"><?php echo esc_html($deck['commander']); ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td data-value="<?php echo esc_attr($deck['author']); ?>">
                                                            <?php if ($deck['author_url']) : ?>
                                                                <a href="<?php echo esc_url($deck['author_url']); ?>" class="text-decoration-none text-white fw-bold">
                                                                    <?php echo esc_html($deck['author']); ?>
                                                                </a>
                                                            <?php else : ?>
                                                                <span class="fw-bold text-white"><?php echo esc_html($deck['author']); ?></span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($deck['wins']); ?>">
                                                            <?php echo esc_html($deck['wins']); ?>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($deck['match_wins']); ?>">
                                                            <span class="text-success"><?php echo esc_html($deck['match_wins']); ?></span>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($deck['match_losses']); ?>">
                                                            <span class="text-danger"><?php echo esc_html($deck['match_losses']); ?></span>
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($deck['match_draws']); ?>">
                                                            <span class="text-info"><?php echo esc_html($deck['match_draws']); ?></span>
                                                        </td>
                                                        <td class="text-center fw-bold" data-value="<?php echo esc_attr($deck['win_rate']); ?>">
                                                            <?php echo esc_html($deck['win_rate']); ?>%
                                                        </td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($deck['attendance']); ?>">
                                                            <?php echo esc_html($deck['attendance']); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <script>
                                    // ponytail: Simple vanilla JS sorting to avoid heavy dependencies/plugins.
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const table = document.getElementById('deckMetaTable');
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

                                                    // Update top 3 classes
                                                    row.classList.remove('rank-gold', 'rank-silver', 'rank-bronze');
                                                    if (index === 0) row.classList.add('rank-gold');
                                                    else if (index === 1) row.classList.add('rank-silver');
                                                    else if (index === 2) row.classList.add('rank-bronze');

                                                    tbody.appendChild(row);
                                                });
                                            });
                                        });

                                        // Initialize Bootstrap Popovers
                                        if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
                                            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
                                            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                                                return new bootstrap.Popover(popoverTriggerEl)
                                            })
                                        }
                                    });
                                </script>
                            <?php else : ?>
                                <div class="alert alert-warning text-center">
                                    No deck data found for the selected filter.
                                </div>
                            <?php endif; ?>

                        </article>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>
