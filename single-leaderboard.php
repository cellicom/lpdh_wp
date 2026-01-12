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

                    <?php while ( have_posts() ) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                                
                                <?php
                                $year = get_field('field_leaderboard_year');
                                if ($year) : ?>
                                    <div class="text-muted">
                                        <span class="badge bg-secondary"><?php echo esc_html($year); ?></span>
                                    </div>
                                <?php endif; ?>
                            </header>

                            <div class="entry-content mb-5">
                                <?php the_content(); ?>
                            </div>

                            <?php
                            $rankings_json = get_field('field_leaderboard_rankings_json');
                            $rankings = json_decode($rankings_json, true);
                            
                            if ( is_array($rankings) && !empty($rankings) ) : ?>
                                <div class="leaderboard-rankings mb-5">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle" id="leaderboardTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                                    <th scope="col" class="sortable" style="cursor: pointer;">Giocatore <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">Punti <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">W <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">D <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">L <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">🥇 <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">🤡 <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                    <th scope="col" class="text-center sortable" style="cursor: pointer;">Presenze <i class="fas fa-sort small text-muted ms-1"></i></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                foreach ( $rankings as $index => $rank ) : 
                                                    $pos = $index + 1;
                                                    $name = isset($rank['name']) ? $rank['name'] : '';
                                                    $points = isset($rank['points']) ? $rank['points'] : 0;
                                                    $win = isset($rank['win']) ? $rank['win'] : 0;
                                                    $draw = isset($rank['draw']) ? $rank['draw'] : 0;
                                                    $lose = isset($rank['lose']) ? $rank['lose'] : 0;
                                                    $count = isset($rank['count']) ? $rank['count'] : 0;
                                                    $first = isset($rank['first']) ? $rank['first'] : 0;
                                                    $last = isset($rank['last']) ? $rank['last'] : 0;
                                                    $user_id = isset($rank['user_id']) ? $rank['user_id'] : 0;
                                                    
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

                                                    $player_profile_url = $user_id ? get_author_posts_url($user_id) : '';
                                                    
                                                    // Colors for top 3
                                                    $row_class = '';
                                                    if ( $pos === 1 ) {
                                                        $row_class = 'rank-gold';
                                                    } elseif ( $pos === 2 ) {
                                                        $row_class = 'rank-silver';
                                                    } elseif ( $pos === 3 ) {
                                                        $row_class = 'rank-bronze';
                                                    }
                                                ?>
                                                    <tr class="<?php echo esc_attr($row_class); ?>">
                                                        <td class="text-center fw-bold"><?php echo $pos; ?></td>
                                                        <td data-value="<?php echo esc_attr($name); ?>">
                                                            <?php 
                                                            $avatar = get_avatar($user_id ? $user_id : 0, 24, 'mp', '', array('class' => 'rounded-circle me-2', 'style' => 'width: 24px; height: 24px;'));
                                                            
                                                            if ( $player_profile_url ) {
                                                                echo '<a href="' . esc_url($player_profile_url) . '" class="text-decoration-none text-reset d-flex align-items-center">' . $avatar . esc_html($name) . '</a>';
                                                            } else {
                                                                echo '<div class="d-flex align-items-center">' . $avatar . esc_html($name) . '</div>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-center fw-bold" data-value="<?php echo esc_attr($points); ?>"><?php echo esc_html($points); ?></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($win); ?>"><span class="text-success"><?php echo esc_html($win); ?></span></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($draw); ?>"><span class="text-secondary"><?php echo esc_html($draw); ?></span></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($lose); ?>"><span class="text-danger"><?php echo esc_html($lose); ?></span></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($first); ?>"><?php echo esc_html($first); ?></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($last); ?>"><?php echo esc_html($last); ?></td>
                                                        <td class="text-center" data-value="<?php echo esc_attr($count); ?>"><?php echo esc_html($count); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <script>
                                document.addEventListener('DOMContentLoaded', function() {
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
                                            
                                            rows.forEach(row => tbody.appendChild(row));
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

<style>
.rank-gold td { color: #d4af37; font-weight: bold; }
.rank-silver td { color: #8a8a8a; font-weight: bold; }
.rank-bronze td { color: #cd7f32; font-weight: bold; }
.leaderboard-rankings a:hover { cursor: pointer; text-decoration: underline !important; }
</style>

<?php get_footer(); ?>
