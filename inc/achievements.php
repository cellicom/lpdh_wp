<?php
/**
 * Achievements System for LPDH
 *
 * Use lpdh_get_user_achievements($user_id) to retrieve an array of unlocked badges.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Returns an array of achievements unlocked by the user.
 *
 * @param int $user_id
 * @return array Array of achievement objects: ['id', 'title', 'description', 'icon', 'color']
 */
function lpdh_get_user_achievements($user_id)
{
    $achievements = [];

    // Calculate Stats (simplified logic extracted from page-stats.php)
    $decks = get_posts([
        'post_type' => 'deck',
        'author' => $user_id,
        'posts_per_page' => -1,
        'fields' => 'ids',
        'post_status' => 'publish'
    ]);
    $deck_count = count($decks);

    // Get user registration date
    $user_data = get_userdata($user_id);
    $registered_date = $user_data ? strtotime($user_data->user_registered) : time();
    $days_since_reg = (time() - $registered_date) / (60 * 60 * 24);

    // Win count (requires querying events, simplified for performance)
    // We ideally should cache this or query optimized custom tables, but for now we use WP_Query
    // We only count 1st places
    $win_count = 0;
    // Note: extensive querying for every profile load might be heavy. 
    // Recommended to cache this value in user_meta in a production environment.
    // For now, we implement a lightweight check or assume data is available.
    // To avoid querying ALL events every time, we might skip event-based achievements logic 
    // in this initial version or do a light query if possible.

    // Let's rely on 'lpdh_user_stats_wins' user meta if it existed, otherwise skip complex event queries for now 
    // OR do a quick query if the dataset is small. 
    // Given the context of page-stats.php, we know how to query. Let's do a simplified query.

    $events_query = new WP_Query([
        'post_type' => 'event',
        'posts_per_page' => -1, // Potentially heavy, beware
        'post_status' => 'publish',
        'fields' => 'ids',
        'meta_query' => [
            [
                'key' => 'event_ranking',
                'value' => '"player_id";i:' . $user_id,
                'compare' => 'LIKE'
            ]
        ]
    ]);

    $events_attended = 0;
    if ($events_query->have_posts()) {
        foreach ($events_query->posts as $e_id) {
            $rankings = get_field('event_ranking', $e_id);
            if (is_array($rankings)) {
                foreach ($rankings as $rank) {
                    $p_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
                    $p_id = 0;
                    if (is_array($p_id_field) && isset($p_id_field['ID']))
                        $p_id = $p_id_field['ID'];
                    elseif (is_object($p_id_field))
                        $p_id = $p_id_field->ID;
                    elseif (is_numeric($p_id_field))
                        $p_id = intval($p_id_field);

                    if ($p_id == $user_id) {
                        $events_attended++;
                        if (isset($rank['pos']) && intval($rank['pos']) === 1) {
                            $win_count++;
                        }
                    }
                }
            }
        }
    }


    // --- Define Achievements ---

    // 1. Brewer Bronze
    if ($deck_count >= 5) {
        $achievements[] = [
            'id' => 'brewer_bronze',
            'title' => 'Brewer',
            'description' => 'Created 5+ Decks',
            'icon' => 'fa-hammer',
            'color' => 'bronze' // CSS class suffix
        ];
    }

    // 2. Brewer Gold
    if ($deck_count >= 10) {
        $achievements[] = [
            'id' => 'brewer_gold',
            'title' => 'Master Brewer',
            'description' => 'Created 10+ Decks',
            'icon' => 'fa-flask',
            'color' => 'gold'
        ];
    }

    // 3. Veteran
    if ($days_since_reg > 365) {
        $achievements[] = [
            'id' => 'veteran',
            'title' => 'Veteran',
            'description' => 'Member for 1+ Year',
            'icon' => 'fa-medal',
            'color' => 'silver'
        ];
    }

    // 4. First Win
    if ($win_count >= 1) {
        $achievements[] = [
            'id' => 'first_blood',
            'title' => 'Champion',
            'description' => 'Won an Event',
            'icon' => 'fa-trophy',
            'color' => 'gold'
        ];
    }

    // 5. Frequent Player
    if ($events_attended >= 5) {
        $achievements[] = [
            'id' => 'regular',
            'title' => 'Regular',
            'description' => 'Played in 5+ Events',
            'icon' => 'fa-users',
            'color' => 'bronze'
        ];
    }

    return $achievements;
}
