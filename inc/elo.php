<?php

/**
 * LPDH ELO Calculation System
 * 
 * Contains all functions related to ELO rating calculations for tournament rankings.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Default ELO rating for new players
define('LPDH_DEFAULT_ELO', 1500);

/**
 * Calculate ELO for a player based on match results and tournament position.
 *
 * @param float $current_elo Current ELO of the player.
 * @param int $wins Number of wins in the event.
 * @param int $draws Number of draws in the event.
 * @param int $losses Number of losses in the event.
 * @param float $avg_elo Average ELO of the event participants.
 * @param int $pos Final position of the player in the event.
 * @param int $total_players Total number of players in the event.
 * @return array Array containing 'new_elo', 'k_factor', 'expected_score', and 'position_adjustment'.
 */
function lpdh_calculate_elo($current_elo, $wins, $draws, $losses, $avg_elo, $pos, $total_players)
{
    $games_played = $wins + $draws + $losses;

    if ($games_played <= 0) {
        return array(
            'new_elo' => $current_elo,
            'k_factor' => 0,
            'expected_score' => 0,
            'position_adjustment' => 0
        );
    }

    $elo_result = lpdh_perform_elo_math($current_elo, $wins, $draws, $losses, $avg_elo, $pos, $total_players);

    return $elo_result;
}

/**
 * Internal helper for ELO math to keep lpdh_calculate_elo clean.
 */
function lpdh_perform_elo_math($current_elo, $wins, $draws, $losses, $avg_elo, $pos, $total_players)
{
    $games_played = $wins + $draws + $losses;
    $actual_score = $wins + ($draws * 0.5);
    $expected_score_rate = 1 / (1 + pow(10, ($avg_elo - $current_elo) / 400));
    $expected_score = $expected_score_rate * $games_played;

    // K-factor logic based on theme setting
    $k_factor_divide = get_option('lpdh_elo_k_factor_divide_by_game', 1);
    $k_factor = ($k_factor_divide) ? 32 / $games_played : 32;

    // Position Adjustment (rewarding top finishes)
    $rank_score = ($total_players > 1) ? ($total_players - $pos) / ($total_players - 1) : 1;
    $position_adjustment = 20 * ($rank_score - 0.5);

    $new_elo = $current_elo + $k_factor * ($actual_score - $expected_score) + $position_adjustment;

    return array(
        'new_elo' => $new_elo,
        'k_factor' => $k_factor,
        'expected_score' => $expected_score,
        'position_adjustment' => $position_adjustment
    );
}

/**
 * Get final ELO ratings from the previous year's leaderboard.
 * 
 * @param int $current_year The year currently being calculated.
 * @return array Map of [player_key => elo]
 */
function lpdh_get_previous_year_elos($current_year)
{
    $prev_year = intval($current_year) - 1;
    $elos = array();

    $args = array(
        'post_type' => 'leaderboard',
        'posts_per_page' => 1,
        'post_status' => 'publish',
        'meta_query' => array(
            array(
                'key' => 'year',
                'value' => $prev_year,
                'compare' => '='
            )
        )
    );

    $prev_lb = get_posts($args);

    if (!empty($prev_lb)) {
        $json = get_field('field_leaderboard_rankings_json', $prev_lb[0]->ID);
        if ($json) {
            $data = json_decode($json, true);
            if (is_array($data)) {
                foreach ($data as $entry) {
                    $u_id = isset($entry['user_id']) ? $entry['user_id'] : 0;
                    $name = isset($entry['name']) ? $entry['name'] : '';
                    $elo = isset($entry['elo']) ? $entry['elo'] : LPDH_DEFAULT_ELO;

                    $key = $u_id ? 'user_' . $u_id : $name;
                    if ($key) {
                        $elos[$key] = $elo;
                    }
                }
            }
        }
    }

    return $elos;
}
