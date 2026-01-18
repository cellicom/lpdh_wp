<?php
/**
 * Template for displaying author archive (User Profile)
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header();

$author = get_queried_object();
$author_id = $author->ID;
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">

            <!-- User Header -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-8 text-center">
                    <div class="author-avatar mb-3 d-inline-block">
                        <?php echo get_avatar($author_id, 150, '', '', array('class' => 'rounded-circle shadow border')); ?>
                    </div>
                    <h1 class="author-title mb-1"><?php echo esc_html($author->display_name); ?></h1>
                    <p class="text-muted mb-3">@<?php echo esc_html($author->user_login); ?></p>

                    <?php if (get_the_author_meta('description', $author_id)): ?>
                        <div class="author-description mx-auto" style="max-width: 600px;">
                            <?php echo wp_kses_post(get_the_author_meta('description', $author_id)); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Social Links -->
                    <div class="author-social mt-3">
                        <?php
                        $socials = array(
                            'user_url' => 'fas fa-globe',
                            'facebook' => 'fab fa-facebook',
                            'twitter' => 'fab fa-twitter',
                            'instagram' => 'fab fa-instagram',
                            'linkedin' => 'fab fa-linkedin',
                            'github' => 'fab fa-github',
                            'youtube' => 'fab fa-youtube',
                            'discord' => 'fab fa-discord',
                        );

                        foreach ($socials as $key => $icon) {
                            $url = get_the_author_meta($key, $author_id);
                            if ($url) {
                                echo '<a href="' . esc_url($url) . '" class="text-decoration-none text-muted mx-2" target="_blank" rel="noopener"><i class="' . esc_attr($icon) . ' fa-lg"></i></a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php
            // Calculate Stats
            $wins = 0;
            $last_places = 0;
            $attendances = 0;

            // Query all events
            $events_args = array(
                'post_type' => 'event',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'fields' => 'ids', // Performance optimization
            );
            $events_query = new WP_Query($events_args);

            if ($events_query->have_posts()) {
                foreach ($events_query->posts as $event_id) {

                    // Check Rankings
                    $rankings = get_field('event_ranking', $event_id);
                    if (is_array($rankings) && !empty($rankings)) {
                        $total_players = count($rankings);
                        foreach ($rankings as $index => $rank) {
                            $player_id = 0;
                            if (isset($rank['player_id'])) {
                                if (is_array($rank['player_id']) && isset($rank['player_id']['ID'])) {
                                    $player_id = $rank['player_id']['ID'];
                                } elseif (is_object($rank['player_id'])) {
                                    $player_id = $rank['player_id']->ID;
                                } else {
                                    $player_id = $rank['player_id'];
                                }
                            }

                            if ($player_id == $author_id) {
                                $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                                if ($pos === 1) {
                                    $wins++;
                                }
                                // Check for last place (last index in repeater)
                                if ($index === $total_players - 1) {
                                    $last_places++;
                                }
                            }
                        }
                    }

                    // Check Survey (Attendance)
                    $survey = get_field('survey', $event_id);
                    if (is_array($survey)) {
                        foreach ($survey as $row) {
                            $u_id = 0;
                            if (isset($row['user'])) {
                                $u_data = $row['user'];
                                $u_id = is_array($u_data) ? $u_data['ID'] : (is_object($u_data) ? $u_data->ID : $u_data);
                            }

                            if ($u_id == $author_id) {
                                $attendances++;
                                break; // Count once per event
                            }
                        }
                    }
                }
            }

            // Query Decks
            $decks_args = array(
                'post_type' => 'deck',
                'posts_per_page' => -1,
                'author' => $author_id,
                'post_status' => 'publish',
            );
            $decks_query = new WP_Query($decks_args);
            $deck_count = $decks_query->found_posts;
            ?>

            <!-- Stats Grid -->
            <div class="row mb-5 g-4 justify-content-center">
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="display-4 fw-bold text-primary mb-0"><?php echo $wins; ?></h3>
                            <p class="text-muted mb-0">Wins</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="display-4 fw-bold text-danger mb-0"><?php echo $last_places; ?> 🤡</h3>
                            <p class="text-muted mb-0">Last Places</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="display-4 fw-bold text-success mb-0"><?php echo $attendances; ?></h3>
                            <p class="text-muted mb-0">Event Attendance</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="display-4 fw-bold text-info mb-0"><?php echo $deck_count; ?></h3>
                            <p class="text-muted mb-0">Decks</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (is_user_logged_in() && get_current_user_id() == $author_id): ?>
                <div class="row justify-content-center mb-5">
                    <div class="col-auto">
                        <a href="<?php echo admin_url('admin.php?page=player-stats'); ?>" class="btn btn-primary btn-lg"><i
                                class="fas fa-chart-bar me-2"></i> View my stats</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Decks List -->
            <?php if ($decks_query->have_posts()): ?>
                <div class="decks-section pt-4 border-top">
                    <h2 class="mb-4 text-center">Decks</h2>
                    <div class="row g-4">
                        <?php while ($decks_query->have_posts()):
                            $decks_query->the_post(); ?>
                            <?php get_template_part('template-parts/card', 'deck', ['show_author' => false]); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif;
            wp_reset_postdata(); ?>

        </div>
    </main>
</div>



<?php get_footer(); ?>