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
        'public'              => true, // Queryable for single/archive templates
        'has_archive'         => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 25,
        'menu_icon'           => 'dashicons-awards',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => ['title', 'editor'],
        'show_in_rest'        => true,
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
                    'manual' => 'Manual Achievement',
                    'deck_count' => 'Number of Decks Created',
                    'win_count' => 'Number of Wins',
                    'event_count' => 'Events Attended',
                    'clown_count' => 'Number of Clown (Last Place)',
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
                'default_value' => '',
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
 * 
 * @param int $user_id
 * @return array ['deck_count', 'win_count', 'event_count', 'clown_count', 'days_registered', 'global_elo']
 */
function lpdh_get_user_stats($user_id)
{
    // 1. Days Registered
    $user_data = get_userdata($user_id);
    $registered = $user_data ? strtotime($user_data->user_registered) : time();
    $days_since_reg = floor((time() - $registered) / (60 * 60 * 24));

    // 2. Decks Count
    $deck_count = count_user_posts($user_id, 'deck', true);

    // 3. Events, Wins & Clown Check (Heavy Query)
    $events_attended = 0;
    $win_count = 0;
    $clown_count = 0;

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
                $total_players = count($rankings);
                
                foreach ($rankings as $rank) {
                    $p_id = 0;
                    $p_id_field = isset($rank['player_id']) ? $rank['player_id'] : null;
                    if (is_array($p_id_field) && isset($p_id_field['ID'])) $p_id = $p_id_field['ID'];
                    elseif (is_object($p_id_field)) $p_id = $p_id_field->ID;
                    elseif (is_numeric($p_id_field)) $p_id = intval($p_id_field);

                    if ($p_id == $user_id) {
                        $events_attended++;
                        $pos = isset($rank['pos']) ? intval($rank['pos']) : 0;
                        
                        // Check Win
                        if ($pos === 1) {
                            $win_count++;
                        }
                        
                        // Check Clown (Last Place)
                        if ($total_players > 1 && $pos === $total_players) {
                            $clown_count++;
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
        'clown_count' => $clown_count,
        'days_registered' => $days_since_reg,
        'global_elo' => 0 // Future implementation
    ];
}

/**
 * Checks a single condition against a value.
 */
function lpdh_check_achievement_condition($user_val, $operator, $target_val)
{
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
 * Automatically migrates old list format to new ID=>Date associative array.
 *
 * @param int $user_id
 * @return array Array of formatted achievement objects including 'date_unlocked'.
 */
function lpdh_get_user_achievements($user_id)
{
    // Retrieve stored achievements
    // Expected Format: [ ID => 'timestamp_or_date_string', ... ]
    $unlocked_data = get_user_meta($user_id, 'lpdh_unlocked_achievements', true);

    // MIGRATION / INITIALIZATION
    $migrated = false;
    if (!is_array($unlocked_data)) {
        $unlocked_data = [];
    } else {
        // Check if simpler indexed array (old format)
        // If keys are sequential integers (0, 1, 2...), it's likely the old format.
        // Caveat: If user only has one ach [0 => 123], key is 0. 
        // We can check if the value is an ID (int) vs a Date? 
        // Achievement IDs are post IDs.
        // Let's iterate and check.
        $new_data = [];
        foreach ($unlocked_data as $key => $val) {
            if (is_int($key)) {
                // Key is index, Value is ID. Need to flip.
                // Since we don't have date, we use current time or null.
                // Let's use current time to ensure they show up.
                $new_data[$val] = time(); 
                $migrated = true;
            } else {
                // Already associative [ID => Date]
                $new_data[$key] = $val;
            }
        }
        if ($migrated) {
            $unlocked_data = $new_data;
        }
    }

    // Get All Achievement Posts
    $all_achievements = get_posts([
        'post_type' => 'achievement',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    $newly_unlocked = false;
    $final_list = [];

    // Identify which achievements are NOT yet unlocked
    $locked_achievements = [];
    foreach ($all_achievements as $post) {
        if (array_key_exists($post->ID, $unlocked_data)) {
            // Already unlocked
            $final_list[] = lpdh_format_achievement($post, $unlocked_data[$post->ID]);
        } else {
            $locked_achievements[] = $post;
        }
    }

    // If there are locked achievements, we need to calculate stats
    if (!empty($locked_achievements)) {
        $stats = null; // Deferred loading

        foreach ($locked_achievements as $post) {
            $cond_type = get_field('condition_type', $post->ID);
            
            // Skip Manual achievements
            if ($cond_type === 'manual') {
                continue;
            }

            // Lazy load stats only if needed
            if ($stats === null) {
                $stats = lpdh_get_user_stats($user_id);
            }

            $operator = get_field('operator', $post->ID);
            $target_val = get_field('value', $post->ID);
            $user_val = isset($stats[$cond_type]) ? $stats[$cond_type] : 0;

            if (lpdh_check_achievement_condition($user_val, $operator, $target_val)) {
                // Unlocked!
                $unlock_date = time();
                $unlocked_data[$post->ID] = $unlock_date;
                $final_list[] = lpdh_format_achievement($post, $unlock_date);
                $newly_unlocked = true;
            }
        }
    }

    // Update Cache if changed (or migrated)
    if ($newly_unlocked || $migrated) {
        update_user_meta($user_id, 'lpdh_unlocked_achievements', $unlocked_data);
    }
    
    // Sort final list by unlock date DESC (newest first)
    usort($final_list, function($a, $b) {
        return $b['date_unlocked_ts'] - $a['date_unlocked_ts'];
    });

    return $final_list;
}

/**
 * Format post object into array expected by frontend
 */
function lpdh_format_achievement($post, $date_timestamp = null)
{
    $icon = get_field('icon', $post->ID);

    // HTML Icon Sanitization
    if (is_string($icon) && strpos($icon, '<i') !== false) {
        preg_match('/class=["\']([^"\']+)["\']/', $icon, $matches);
        $icon = isset($matches[1]) ? $matches[1] : trim(strip_tags($icon));
    }

    return [
        'id' => $post->ID,
        'title' => $post->post_title,
        'description' => $post->post_content,
        'icon' => $icon,
        'color_hex' => get_field('color_hex', $post->ID),
        'color_class' => get_field('color_class', $post->ID),
        'color' => get_field('color_class', $post->ID) ?: 'gold',
        'date_unlocked' => $date_timestamp ? date('d/m/Y', $date_timestamp) : '-',
        'date_unlocked_ts' => $date_timestamp ?: 0
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

    // Format: [ID => Timestamp]
    $unlocked_data = get_user_meta($user->ID, 'lpdh_unlocked_achievements', true);
    // Determine if array handles simple IDs (migration needed visibility) or Assoc
    // We treat existence of ID key as unlocked.
    $unlocked_ids = [];
    if (is_array($unlocked_data)) {
        foreach ($unlocked_data as $key => $val) {
            // Support both old [0 => ID] and new [ID => Date] format for checkbox status
            if (is_int($key) && !is_numeric($val) && is_array($val)) continue; // Weird case safety
            if (is_int($key) && is_numeric($val) && $key < 1000) { 
                 // Likely old index format [0=>123]. Key is small index.
                 $unlocked_ids[] = $val;
            } else {
                 // Assoc format [123 => timestamp]
                 $unlocked_ids[] = $key;
            }
        }
    }

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
                                <span class="description" style="color:#666;">
                                    <?php 
                                        $type = get_field('condition_type', $ach->ID);
                                        echo esc_html('(' . $type . ')');
                                        if ($type !== 'manual') {
                                            echo ' ' . esc_html(get_field('operator', $ach->ID) . ' ' . get_field('value', $ach->ID));
                                        }
                                    ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="description">
                        Manually checking/unchecking manages the achievement status. <br>
                        <strong>Note:</strong> Auto-achievements may re-lock/re-unlock based on stats upon profile visit. <br>
                        <em>Manual</em> type achievements persist unless removed here.
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

    // Existing Data
    $current_data = get_user_meta($user_id, 'lpdh_unlocked_achievements', true);
    if (!is_array($current_data)) $current_data = [];
    
    // Normalize current data to [ID => Date] if old format
    // (This is same logic as lpdh_get_user_achievements migration, localized here for saving safety)
    $normalized_current = [];
    foreach ($current_data as $k => $v) {
        if (is_int($k) && is_numeric($v) && $k < 100000) { // assumption index vs ID
             $normalized_current[$v] = time(); // assign date if missing
        } else {
             $normalized_current[$k] = $v;
        }
    }

    $new_ids = [];
    if (isset($_POST['lpdh_manual_achievements']) && is_array($_POST['lpdh_manual_achievements'])) {
        $new_ids = array_map('intval', $_POST['lpdh_manual_achievements']);
    } elseif (isset($_POST['action']) && ($_POST['action'] == 'update' || $_POST['action'] == 'profile')) {
        $new_ids = []; // All unchecked
    } else {
        return; // Not saving profile
    }

    // Construct new data array, preserving dates for existing ones
    $final_data = [];
    foreach ($new_ids as $id) {
        if (isset($normalized_current[$id])) {
            $final_data[$id] = $normalized_current[$id]; // Keep original date
        } else {
            $final_data[$id] = time(); // New grant, set to Now
        }
    }

    update_user_meta($user_id, 'lpdh_unlocked_achievements', $final_data);
}
add_action('personal_options_update', 'lpdh_save_user_achievements_admin');
add_action('edit_user_profile_update', 'lpdh_save_user_achievements_admin');
