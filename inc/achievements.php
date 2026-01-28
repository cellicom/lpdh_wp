<?php
/**
 * Achievements System for LPDH
 *
 * Implements 'achievement' Custom Post Type, Stats Calculation, and User Achievement Management.
 */

if (!defined('ABSPATH')) {
    exit;
}

// -----------------------------------------------------------------------------
// 1. Custom Post Type: Achievement
// -----------------------------------------------------------------------------

function lpdh_register_achievement_cpt()
{
    $labels = [
        'name'               => 'Achievements',
        'singular_name'      => 'Achievement',
        'menu_name'          => 'Achievements',
        'add_new'            => 'Add New',
        'add_new_item'       => 'Add New Achievement',
        'edit_item'          => 'Edit Achievement',
        'new_item'           => 'New Achievement',
        'view_item'          => 'View Achievement',
        'search_items'       => 'Search Achievements',
        'not_found'          => 'No achievements found',
        'not_found_in_trash' => 'No achievements found in Trash',
    ];

    $args = [
        'labels'              => $labels,
        'public'              => false, // Not queryable on frontend directly as pages
        'show_ui'             => true, // Show in Admin
        'show_in_menu'        => true,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-awards',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => ['title', 'editor'], // Editor for description
        'show_in_rest'        => true, // Block editor support if needed
    ];

    register_post_type('achievement', $args);
}
add_action('init', 'lpdh_register_achievement_cpt');


// -----------------------------------------------------------------------------
// 2. ACF Fields (Programmatic)
// -----------------------------------------------------------------------------

if (function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'group_achievement_data',
        'title' => 'Achievement Data',
        'fields' => array(
            // Row 1: Logic
            array(
                'key' => 'field_ach_condition_type',
                'label' => 'Condition Based On',
                'name' => 'condition_type',
                'type' => 'select',
                'instructions' => 'Which stat determines this achievement?',
                'required' => 1,
                'wrapper' => array(
                    'width' => '33',
                ),
                'choices' => array(
                    'deck_count' => 'Number of Decks Created',
                    'win_count' => 'Number of Wins',
                    'event_count' => 'Events Attended',
                    'days_registered' => 'Days Since Registration',
                    'global_elo' => 'Global Elo (Future Check)',
                ),
            ),
            array(
                'key' => 'field_ach_operator',
                'label' => 'Operator',
                'name' => 'operator',
                'type' => 'select',
                'instructions' => 'Choose how to compare the user stat with the threshold value.',
                'required' => 1,
                'wrapper' => array(
                    'width' => '33',
                ),
                'choices' => array(
                    '>' => 'Greater than (>)',
                    '>=' => 'Greater than or Equal (>=)',
                    '=' => 'Equals (=)',
                    '<=' => 'Less than or Equal (<=)',
                    '<' => 'Less than (<)',
                    'CONTAINS' => 'Contains (Text)',
                    'EQUALS' => 'Equals (Text)',
                ),
                'default_value' => '>=',
            ),
            array(
                'key' => 'field_ach_value',
                'label' => 'Threshold Value',
                'name' => 'value',
                'type' => 'text',
                'instructions' => 'Numeric or user text value.',
                'required' => 1,
                'wrapper' => array(
                    'width' => '33',
                ),
                'default_value' => '0',
            ),

            // Row 2: Design
            array(
                'key' => 'field_ach_icon',
                'label' => 'Icon Class',
                'name' => 'icon',
                'type' => 'font-awesome',
                'instructions' => 'Pick an icon.',
                'wrapper' => array(
                    'width' => '33',
                ),
                'default_value' => 'fa-medal',
                'return_format' => 'class',
            ),
            array(
                'key' => 'field_ach_color_hex',
                'label' => 'Color (Hex)',
                'name' => 'color_hex',
                'type' => 'color_picker',
                'instructions' => 'Background/Icon Color',
                'wrapper' => array(
                    'width' => '33',
                ),
                'default_value' => '#ffd700',
            ),
            array(
                'key' => 'field_ach_color_class',
                'label' => 'Color (Class)',
                'name' => 'color_class',
                'type' => 'text',
                'instructions' => 'CSS Class Suffix (e.g. gold, silver)',
                'wrapper' => array(
                    'width' => '33',
                ),
                'default_value' => '',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'achievement',
                ),
            ),
        ),
    ));

endif;


// -----------------------------------------------------------------------------
// 3. User Stats Check Logic
// -----------------------------------------------------------------------------

/**
 * Returns user stats, calculating them only if necessary.
 * This function tries to be efficient.
 *
 * @param int $user_id
 * @return array ['deck_count', 'win_count', 'event_count', 'days_registered', 'global_elo']
 */
function lpdh_get_user_stats($user_id)
{
    // 1. Days Registered
    $user_data = get_userdata($user_id);
    $registered = $user_data ? strtotime($user_data->user_registered) : time();
    $days_since_reg = floor((time() - $registered) / (60 * 60 * 24));

    // 2. Decks Count
    $deck_count = count_user_posts($user_id, 'deck', true); // public only? or all? Assuming public for badge.

    // 3. Events & Wins (Heavy Query)
    // We try to fetch from transient or meta first ideally, but here we do the query.
    // Optimization: Only run this if we actually need it for a Locked achievement check?
    // For simplicity, we run it. To optimize: could cache this in user meta 'lpdh_stats_cache' for 1 hour.
    
    $events_attended = 0;
    $win_count = 0;

    $events_query = new WP_Query([
        'post_type' => 'event',
        'posts_per_page' => -1,
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

    if ($events_query->have_posts()) {
        foreach ($events_query->posts as $e_id) {
            $rankings = get_field('event_ranking', $e_id);
            if (is_array($rankings)) {
                foreach ($rankings as $rank) {
                    $p_id = 0;
                    $p_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
                    if (is_array($p_id_field) && isset($p_id_field['ID'])) $p_id = $p_id_field['ID'];
                    elseif (is_object($p_id_field)) $p_id = $p_id_field->ID;
                    elseif (is_numeric($p_id_field)) $p_id = intval($p_id_field);

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

    return [
        'deck_count' => $deck_count,
        'win_count' => $win_count,
        'event_count' => $events_attended,
        'days_registered' => $days_since_reg,
        'global_elo' => 0 // Not implemented yet
    ];
}

/**
 * Checks a single condition against a value.
 */
function lpdh_check_achievement_condition($user_val, $operator, $target_val)
{
    // Ensure numeric comparison if values are numeric
    if (is_numeric($user_val) && is_numeric($target_val)) {
        $user_val = floatval($user_val);
        $target_val = floatval($target_val);
    }

    switch ($operator) {
        case '>': return $user_val > $target_val;
        case '>=': return $user_val >= $target_val;
        case '=': return $user_val == $target_val;
        case '<=': return $user_val <= $target_val;
        case '<': return $user_val < $target_val;
        default: return false;
    }
}


// -----------------------------------------------------------------------------
// 4. Main Function: Get User Achievements
// -----------------------------------------------------------------------------

/**
 * Returns user achievements, handling CPTs and Caching.
 *
 * @param int $user_id
 * @return array Array of formatted achievement arrays needed for frontend.
 */
function lpdh_get_user_achievements($user_id)
{
    // Get all manually awarded / cached unlocks
    $unlocked_ids = get_user_meta($user_id, 'lpdh_unlocked_achievements', true);
    if (!is_array($unlocked_ids)) {
        $unlocked_ids = [];
    }

    // Get All Achievement Posts
    $all_achievements = get_posts([
        'post_type' => 'achievement',
        'posts_per_page' => -1,
        'post_status' => 'publish' // or 'any' if you want private ones?
    ]);

    $newly_unlocked = false;
    $final_list = [];

    // Identify which achievements are NOT yet unlocked
    $locked_achievements = [];
    foreach ($all_achievements as $post) {
        if (in_array($post->ID, $unlocked_ids)) {
            // Already unlocked
            $final_list[] = lpdh_format_achievement($post);
        } else {
            $locked_achievements[] = $post;
        }
    }

    // If there are locked achievements, we need to calculate stats
    if (!empty($locked_achievements)) {
        $stats = lpdh_get_user_stats($user_id);

        foreach ($locked_achievements as $post) {
            $cond_type = get_field('condition_type', $post->ID);
            $operator = get_field('operator', $post->ID);
            $target_val = get_field('value', $post->ID);

            // Get relevant user stat
            $user_val = isset($stats[$cond_type]) ? $stats[$cond_type] : 0;

            if (lpdh_check_achievement_condition($user_val, $operator, $target_val)) {
                // Unlocked!
                $unlocked_ids[] = $post->ID;
                $final_list[] = lpdh_format_achievement($post);
                $newly_unlocked = true;
            }
        }
    }

    // Update Cache if changed
    if ($newly_unlocked) {
        update_user_meta($user_id, 'lpdh_unlocked_achievements', array_unique($unlocked_ids));
    }

    return $final_list;
}

/**
 * Format post object into array expected by frontend
 */
function lpdh_format_achievement($post)
{
    $icon = get_field('icon', $post->ID);

    // Fix for ACF Font Awesome returning full HTML element despite return_format setting
    // Usage in frontend expects just the class string.
    if (is_string($icon) && strpos($icon, '<i') !== false) {
        preg_match('/class=["\']([^"\']+)["\']/', $icon, $matches);
        if (isset($matches[1])) {
            $icon = $matches[1];
        } else {
            // Fallback: try to just strip HTML, though mostly empty for icons
            $icon = trim(strip_tags($icon));
        }
    }

    return [
        'id' => $post->ID,
        'title' => $post->post_title,
        'description' => $post->post_content,
        'icon' => $icon,
        'color_hex' => get_field('color_hex', $post->ID),
        'color_class' => get_field('color_class', $post->ID),
        // Fallback for current frontend if it expects 'color'
        'color' => get_field('color_class', $post->ID) ?: 'gold' 
    ];
}


// -----------------------------------------------------------------------------
// 5. Admin Interface: Manual Management
// -----------------------------------------------------------------------------

function lpdh_render_user_achievements_admin($user)
{
    $all_achievements = get_posts([
        'post_type' => 'achievement',
        'posts_per_page' => -1,
    ]);

    $unlocked_ids = get_user_meta($user->ID, 'lpdh_unlocked_achievements', true);
    if (!is_array($unlocked_ids)) $unlocked_ids = [];

    ?>
    <h3><?php _e('User Achievements', 'text_domain'); ?></h3>
    <table class="form-table">
        <tr>
            <th><?php _e('Unlocked Achievements', 'text_domain'); ?></th>
            <td>
                <?php if (empty($all_achievements)): ?>
                    <p>No achievements defined yet.</p>
                <?php else: ?>
                    <fieldset>
                        <?php foreach ($all_achievements as $ach): ?>
                            <label for="ach_<?php echo $ach->ID; ?>" style="display:block; margin-bottom:5px;">
                                <input type="checkbox" 
                                       name="lpdh_manual_achievements[]" 
                                       id="ach_<?php echo $ach->ID; ?>" 
                                       value="<?php echo $ach->ID; ?>" 
                                       <?php checked(in_array($ach->ID, $unlocked_ids)); ?> />
                                <strong><?php echo esc_html($ach->post_title); ?></strong> 
                                <span class="description">- <?php echo esc_html(get_field('condition_type', $ach->ID) . ' ' . get_field('operator', $ach->ID) . ' ' . get_field('value', $ach->ID)); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="description">
                        Check to verify manually. System will also auto-check these based on stats on profile visit. <br>
                        <strong>Note:</strong> Unchecking a valid achievement might not stick if the user visits their profile and logic re-runs!
                    </p>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'lpdh_render_user_achievements_admin');
add_action('edit_user_profile', 'lpdh_render_user_achievements_admin');

function lpdh_save_user_achievements_admin($user_id)
{
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    // We only save what is POSTed. 
    // If empty keys are post, it means none checked? Careful with checkboxes not sent when empty.
    // 'lpdh_manual_achievements' will be set if at least one consists.
    
    // However, if the admin is saving the profile, we should respect the checkbox state.
    // Issue: If we uncheck all, $_POST['lpdh_manual_achievements'] is unset using standard HTML form behavior.
    
    // We can assume if we are on this page, we should update.
    // But wordpress hooks run on other updates too. We should check if we are in the correct context? 
    // Usually 'edit_user' context is fine.

    // Better: We check if the nonce or a marker is present, OR we just accept that if the field is missing but we are saving, it means empty?
    // Actually, WP admins forms verify nonces. We can check if `lpdh_manual_achievements` exists. 
    // BUT! if user unchecks ALL, the key won't exist.
    
    // Let's rely on standard behavior: we act only if we see our fields or we know we are editing profile.
    // To be safe against accidental wiping if fields are hidden/not rendered:
    // We won't wipe unless we are sure. But simplest approach for now:
    
    if (isset($_POST['lpdh_manual_achievements']) && is_array($_POST['lpdh_manual_achievements'])) {
        $ids = array_map('intval', $_POST['lpdh_manual_achievements']);
        update_user_meta($user_id, 'lpdh_unlocked_achievements', $ids);
    } elseif (isset($_POST['action']) && ($_POST['action'] == 'update' || $_POST['action'] == 'profile')) {
        // If we are saving the profile and the array is NOT set, implies all unchecked.
        update_user_meta($user_id, 'lpdh_unlocked_achievements', []);
    }
}
add_action('personal_options_update', 'lpdh_save_user_achievements_admin');
add_action('edit_user_profile_update', 'lpdh_save_user_achievements_admin');
