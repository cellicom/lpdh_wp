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
                    
                    <?php if (get_the_author_meta('description', $author_id)) : ?>
                        <div class="author-description mx-auto" style="max-width: 600px;">
                            <?php echo wp_kses_post(get_the_author_meta('description', $author_id)); ?>
                        </div>
                    <?php endif; ?>
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
                            <p class="text-muted mb-0">Vittorie</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="display-4 fw-bold text-danger mb-0"><?php echo $last_places; ?> 🤡</h3>
                            <p class="text-muted mb-0">Ultimi posti</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center border-0 shadow-sm bg-body-tertiary">
                        <div class="card-body d-flex flex-column justify-content-center">
                            <h3 class="display-4 fw-bold text-success mb-0"><?php echo $attendances; ?></h3>
                            <p class="text-muted mb-0">Presenze Eventi</p>
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

            <!-- Decks List -->
            <?php if ($decks_query->have_posts()) : ?>
                <div class="decks-section pt-4 border-top">
                    <h2 class="mb-4 text-center">I miei Decks</h2>
                    <div class="row g-4">
                        <?php while ($decks_query->have_posts()) : $decks_query->the_post(); ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm border-0 deck-card">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>" class="d-block overflow-hidden rounded-top">
                                            <?php the_post_thumbnail('medium_large', array('class' => 'card-img-top object-fit-cover transition-transform', 'style' => 'height: 220px;')); ?>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php the_permalink(); ?>" class="d-block overflow-hidden rounded-top bg-light d-flex align-items-center justify-content-center" style="height: 220px;">
                                            <i class="fas fa-layer-group fa-3x text-muted"></i>
                                        </a>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title mb-2">
                                            <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark stretched-link">
                                                <?php the_title(); ?>
                                            </a>
                                        </h5>
                                        <?php 
                                        $commander = get_field('commander');
                                        if ($commander) : ?>
                                            <p class="card-text small text-muted mb-0">
                                                <i class="fas fa-user-shield me-1"></i> <?php echo esc_html($commander); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; wp_reset_postdata(); ?>

        </div>
    </main>
</div>

<style>
.transition-transform {
    transition: transform 0.3s ease;
}
.deck-card:hover .transition-transform {
    transform: scale(1.05);
}
</style>

<?php get_footer(); ?>