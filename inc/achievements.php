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
        'view_items'         => 'View Achievements',
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
        'menu_position'       => 4,
        'menu_icon'           => 'dashicons-awards',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => ['title', 'editor'],
        'show_in_rest'        => true,
    ];

    register_post_type('achievement', $args);
}
add_action('init', 'lpdh_register_achievement_cpt');

/**
 * Force Order by Date for Achievement Archive
 */
function lpdh_achievement_archive_order($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('achievement')) {
        $query->set('orderby', 'date'); // Date Published
        $query->set('order', 'DESC');   // Newest First
    }
}
add_action('pre_get_posts', 'lpdh_achievement_archive_order');


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
                    'win_count' => 'Number of 1st Place',
                    'clown_count' => 'Number of Clown (Last Place)',
                    'event_count' => 'Events Attended',
                    'deck_count' => 'Number of Decks Created',
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
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_ach_condition_type',
                            'operator' => '!=',
                            'value' => 'manual',
                        ),
                    ),
                ),
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
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_ach_condition_type',
                            'operator' => '!=',
                            'value' => 'manual',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_ach_is_secret',
                'label' => 'Secret Achievement',
                'name' => 'is_secret',
                'type' => 'true_false',
                'instructions' => 'If checked, title and description will be hidden until unlocked.',
                'wrapper' => array(
                    'width' => '100',
                ),
                'default_value' => 0,
                'ui' => 1,
            ),

            // Row 2: Icon Settings
            array(
                'key' => 'field_ach_icon',
                'label' => 'Icon Class',
                'name' => 'icon',
                'type' => 'font-awesome',
                'instructions' => 'Pick an icon.',
                'wrapper' => array(
                    'width' => '50',
                ),
                'default_value' => 'fa-medal',
                'return_format' => 'class',
            ),
            array(
                'key' => 'field_ach_icon_color',
                'label' => 'Icon Color',
                'name' => 'icon_color',
                'type' => 'color_picker',
                'instructions' => 'Color of the Icon itself.',
                'wrapper' => array(
                    'width' => '50',
                ),
                'default_value' => '#FFFFFF',
            ),

            // Row 3: Appearance (Background)
            array(
                'key' => 'field_ach_color_hex',
                'label' => 'Color (Hex)',
                'name' => 'color_hex',
                'type' => 'color_picker',
                'instructions' => 'Background Color',
                'wrapper' => array(
                    'width' => '50',
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
                    'width' => '50',
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

    global $wpdb;
    
    // 3. Events, Wins & Clown Check (Optimized ACF Repeater Query)
    $events_attended = 0;
    $win_count = 0;
    $clown_count = 0;

    // Search for User ID in 'event_ranking_%_player_id'
    // Matches plain ID or Serialized Object (if ACF returns User Object)
    // IMPORTANT: ACF stores repeater data using Field Name ('event_ranking'), not Field Key.
    $sql = $wpdb->prepare(
        "SELECT DISTINCT post_id FROM $wpdb->postmeta 
         WHERE meta_key LIKE %s 
         AND (meta_value = %s OR meta_value LIKE %s)",
        'event_ranking_%_player_id',
        $user_id,
        '%"ID";i:' . $user_id . ';%' // Serialized look-ahead
    );

    $participated_event_ids = $wpdb->get_col($sql);

    if (!empty($participated_event_ids)) {
        // Ensure they are valid published events
        $valid_events = get_posts([
            'post_type' => 'event',
            'post_status' => 'publish',
            'include' => $participated_event_ids,
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);

        foreach ($valid_events as $e_id) {
            // Get the full repeater to check position
            // Uses 'field_event_ranking' (Key) to safely retrieve structured data via ACF
            $rankings = get_field('field_event_ranking', $e_id);
            
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

    // STRICT VALIDATION (New Plan)
    // We do not guess. We only accept [ID => Timestamp].
    // If we find garbage (old indexed arrays), we discard it to fix corruption.
    if (!is_array($unlocked_data)) {
        $unlocked_data = [];
    } else {
        $valid_data = [];
        $has_changes = false;
        
        foreach ($unlocked_data as $key => $val) {
            // Check if Value is a valid Timestamp (> 100000)
            if (is_numeric($val) && $val > 100000) {
                // Good data
                $valid_data[$key] = $val;
            } else {
                // Bad data (Old format, index, or corrupt)
                // Discard it.
                $has_changes = true; 
            }
        }
        
        if ($has_changes || count($valid_data) !== count($unlocked_data)) {
            $unlocked_data = $valid_data;
            $migrated = true; // Trigger save
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
        'icon_color' => get_field('icon_color', $post->ID),
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
// add_action('show_user_profile', 'lpdh_render_user_achievements_admin');
// add_action('edit_user_profile', 'lpdh_render_user_achievements_admin');

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


// -----------------------------------------------------------------------------
// 6. Admin Submenu Page: Manage Achievements
// -----------------------------------------------------------------------------

function lpdh_register_achievement_admin_page() {
    add_submenu_page(
        'edit.php?post_type=achievement',
        'Manage User Achievements',
        'Manage Achievements',
        'manage_options',
        'lpdh-manage-achievements',
        'lpdh_render_manage_achievements_page'
    );
}
add_action('admin_menu', 'lpdh_register_achievement_admin_page');

// 7. Enqueue Assets for Admin Page
function lpdh_achievements_admin_scripts($hook) {
    // Check if we are on the correct page (Robust check)
    if (!isset($_GET['page']) || $_GET['page'] !== 'lpdh-manage-achievements') {
        return;
    }

    // Font Awesome (using CDN for admin - v6 for better compatibility)
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');

    // Select2
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
}
add_action('admin_enqueue_scripts', 'lpdh_achievements_admin_scripts');


function lpdh_render_manage_achievements_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle User Selection
    $selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    
    // Get all users for dropdown
    $users = get_users(['orderby' => 'display_name']);

    ?>
    <style>
        /* Admin-specific styles for icons */
        .lpdh-achievement-icon {
            width: 45px; height: 45px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff; flex-shrink: 0;
        }
        /* Force FontAwesome Font Family to override WP Admin Dashicons/etc if conflict */
        .lpdh-achievement-icon i {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", sans-serif;
            font-weight: 900;
            font-style: normal;
        }
        
        .lpdh-achievement-icon.icon-lg {
            width: 60px; height: 60px; font-size: 1.8rem;
        }
        .bg-bronze { background: linear-gradient(135deg, #cd7f32, #8c5a2b); }
        .bg-silver { background: linear-gradient(135deg, #c0c0c0, #808080); }
        .bg-gold { background: linear-gradient(135deg, #ffd700, #b8860b); }
        
        /* Switch Styles */
        .switch { position: relative; display: inline-block; width: 40px; height: 20px; margin-right: 10px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
        input:checked + .slider { background-color: #2196F3; }
        input:focus + .slider { box-shadow: 0 0 1px #2196F3; }
        input:checked + .slider:before { transform: translateX(20px); }
        .slider.round { border-radius: 34px; }
        .slider.round:before { border-radius: 50%; }
        
        .select2-container { width: 300px !important; }
    </style>

    <div class="wrap">
        <h1 class="wp-heading-inline">Manage User Achievements</h1>
        
        <!-- User Selector -->
        <div class="tablenav top">
            <div class="alignleft actions">
                <form method="get" action="">
                    <input type="hidden" name="post_type" value="achievement" />
                    <input type="hidden" name="page" value="lpdh-manage-achievements" />
                    <select name="user_id" id="lpdh-user-select" class="lpdh-select2">
                        <option value="">Select a User...</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u->ID; ?>" <?php selected($selected_user_id, $u->ID); ?>>
                                <?php echo esc_html($u->display_name . ' (' . $u->user_login . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="submit" class="button action" value="Select User" style="margin-left: 10px;" />
                </form>
            </div>
            
            <?php if ($selected_user_id): 
                $sel_user = get_userdata($selected_user_id);
            ?>
                <div class="alignleft actions">
                    <input type="text" id="lpdh-ach-search" placeholder="Search achievements..." style="height: 30px; margin-left: 20px;">
                    <button type="button" id="lpdh-btn-delete-all" class="button button-primary" style="margin-left: 10px; background-color: #dc3232; border-color: #dc3232; color: #fff;">
                        Delete All for <?php echo esc_html($sel_user ? $sel_user->display_name : 'User'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="lpdh-delete-modal" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div style="background:#fff; width:400px; padding:20px; margin:15% auto; box-shadow:0 0 10px rgba(0,0,0,0.5); border-radius:5px; text-align:center;">
                <h2 style="margin-top:0; color:#dc3232;">⚠ Warning</h2>
                <p>Are you sure you want to delete <strong>ALL</strong> achievements for this user?</p>
                <p>This action cannot be undone.</p>
                <div style="margin-top:20px;">
                    <button id="lpdh-confirm-delete" class="button button-primary bg-danger" style="background:#dc3232; border-color:#dc3232;">Yes, Delete Everything</button>
                    <button id="lpdh-cancel-delete" class="button button-secondary">Cancel</button>
                </div>
            </div>
        </div>

        <?php if ($selected_user_id): 
            $all_achievements = get_posts(['post_type' => 'achievement', 'posts_per_page' => -1, 'post_status' => 'publish']);
            
            // USE THE CENTRALIZED FUNCTION to ensure we see exactly what the frontend sees
            // This handles ID vs Date format, AND performs auto-unlocked checks for stats
            $user_achievements_list = lpdh_get_user_achievements($selected_user_id);
            
            // Convert to simple ID-keyed map for fast lookup
            $normalized_unlocked = [];
            foreach ($user_achievements_list as $ach_obj) {
                // $ach_obj is like ['id' => 123, 'title' => ..., 'date_unlocked_ts' => ...]
                $normalized_unlocked[$ach_obj['id']] = $ach_obj['date_unlocked_ts'];
            }
        ?>
            <div id="lpdh-achievement-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <?php foreach ($all_achievements as $post): 
                    $is_unlocked = isset($normalized_unlocked[$post->ID]);
                    $unlock_ts = $is_unlocked ? $normalized_unlocked[$post->ID] : false;
                    $unlock_date = $unlock_ts ? date('Y-m-d H:i', $unlock_ts) : '';
                    
                    $icon = get_field('icon', $post->ID);
                     if (is_string($icon) && strpos($icon, '<i') !== false) {
                        preg_match('/class=["\']([^"\']+)["\']/', $icon, $matches);
                        $icon = isset($matches[1]) ? $matches[1] : trim(strip_tags($icon));
                    }
                    
                    $color_hex = get_field('color_hex', $post->ID);
                    $color_class = get_field('color_class', $post->ID);
                    $bg_style = '';
                    $bg_class = 'bg-primary';
                    if (!empty($color_hex)) {
                        $darker_hex = lpdh_adjust_brightness($color_hex, -40); // Darken by 40 steps
                        $bg_style = 'style="background: linear-gradient(135deg, ' . esc_attr($color_hex) . ', ' . esc_attr($darker_hex) . ');"';
                        $bg_class = '';
                    } elseif (!empty($color_class)) {
                        $bg_class = 'bg-' . esc_attr($color_class);
                    }
                ?>
                    <div class="card ach-card" data-title="<?php echo esc_attr(strtolower($post->post_title)); ?>" style="background: #fff; border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                        <div style="display: flex; align-items: start; gap: 15px;">
                            <?php $icon_color = get_field('icon_color', $post->ID) ?: '#ffffff'; ?>
                            <div class="lpdh-achievement-icon <?php echo $bg_class; ?>" <?php echo $bg_style; ?>>
                                <i class="<?php echo esc_attr($icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>;"></i>
                            </div>
                            <div style="flex-grow: 1;">
                                <h3 style="margin: 0 0 5px; font-size: 1.1em;"><?php echo esc_html($post->post_title); ?></h3>
                                <p style="margin: 0 0 10px; color: #666; font-size: 0.9em;"><?php echo wp_trim_words($post->post_content, 10); ?></p>
                                
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee;">
                                    <label class="switch">
                                        <input type="checkbox" class="lpdh-ach-toggle" 
                                               data-ach-id="<?php echo $post->ID; ?>" 
                                               data-user-id="<?php echo $selected_user_id; ?>"
                                               <?php checked($is_unlocked); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="status-text" style="font-weight: bold; color: <?php echo $is_unlocked ? '#46b450' : '#dc3232'; ?>">
                                        <?php echo $is_unlocked ? 'Unlocked' : 'Locked'; ?>
                                    </span>
                                </div>
                                <div class="date-display" style="font-size: 0.85em; color: #888; margin-top: 5px; text-align: right;">
                                    <?php echo $is_unlocked ? $unlock_date : '-'; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>


        <?php else: ?>
            <div class="notice notice-info inline"><p>Please select a user to manage achievements.</p></div>
        <?php endif; ?>
    </div>
    <script>
    jQuery(document).ready(function($) {
        // Init Select2
        $('#lpdh-user-select').select2({
            placeholder: "Select a User...",
            allowClear: true
        });

        // Search Filter
        $('#lpdh-ach-search').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#lpdh-achievement-grid .ach-card').filter(function() {
                $(this).toggle($(this).data('title').indexOf(value) > -1)
            });
        });

        // Toggle AJAX
        $('.lpdh-ach-toggle').on('change', function() {
            var $checkbox = $(this);
            var $card = $checkbox.closest('.ach-card');
            var achId = $checkbox.data('ach-id');
            var userId = $checkbox.data('user-id');
            var isChecked = $checkbox.is(':checked');
            var $statusText = $card.find('.status-text');
            var $dateDisplay = $card.find('.date-display');

            $card.css('opacity', 0.5);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lpdh_toggle_user_achievement',
                    user_id: userId,
                    achievement_id: achId,
                    status: isChecked ? 1 : 0,
                    security: '<?php echo wp_create_nonce("lpdh_ach_toggle"); ?>'
                },
                success: function(response) {
                    $card.css('opacity', 1);
                    if(response.success) {
                        $statusText.text(isChecked ? 'Unlocked' : 'Locked')
                                   .css('color', isChecked ? '#46b450' : '#dc3232');
                        $dateDisplay.text(response.data.date);
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                        $checkbox.prop('checked', !isChecked); // Revert
                    }
                },
                error: function() {
                    $card.css('opacity', 1);
                    alert('Request failed');
                    $checkbox.prop('checked', !isChecked); // Revert
                }
            });
        });

        // Delete All Handling
        $('#lpdh-btn-delete-all').on('click', function(e) {
            e.preventDefault();
            $('#lpdh-delete-modal').fadeIn(200);
        });

        $('#lpdh-cancel-delete').on('click', function() {
            $('#lpdh-delete-modal').fadeOut(200);
        });

        $('#lpdh-confirm-delete').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true).text('Deleting...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'lpdh_delete_all_user_achievements',
                    user_id: <?php echo isset($_GET['user_id']) ? intval($_GET['user_id']) : 0; ?>,
                    security: '<?php echo wp_create_nonce("lpdh_delete_all"); ?>'
                },
                success: function(response) {
                    if(response.success) {
                        alert('All achievements deleted.');
                        location.reload();
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error'));
                        $btn.prop('disabled', false).text('Yes, Delete Everything');
                    }
                },
                error: function() {
                    alert('Request failed');
                    $btn.prop('disabled', false).text('Yes, Delete Everything');
                }
            });
        });
    });
    </script>
    <?php
}


// AJAX Handler: Toggle Single
function lpdh_ajax_toggle_user_achievement() {
    check_ajax_referer('lpdh_ach_toggle', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    $user_id = intval($_POST['user_id']);
    $ach_id = intval($_POST['achievement_id']);
    $status = intval($_POST['status']); // 1 = unlock, 0 = lock

    if (!$user_id || !$ach_id) {
        wp_send_json_error('Invalid ID');
    }

    $current_data = get_user_meta($user_id, 'lpdh_unlocked_achievements', true);
    if (!is_array($current_data)) $current_data = [];

    // STRICT Normalization (Abandon old data if format matches old style)
    $normalized = [];
    foreach ($current_data as $k => $valid_ts) {
        // Enforce: Value > 100000 (Timestamp)
        if (is_numeric($valid_ts) && $valid_ts > 100000) {
            $normalized[$k] = $valid_ts;
        }
        // Else: Old format or garbage -> Discard
    }

    $date_string = '-';

    if ($status === 1) {
        // Add if not exists
        if (!isset($normalized[$ach_id])) {
            $normalized[$ach_id] = time();
        }
        $date_string = date('Y-m-d H:i', $normalized[$ach_id]);
    } else {
        // Remove
        if (isset($normalized[$ach_id])) {
            unset($normalized[$ach_id]);
        }
    }

    update_user_meta($user_id, 'lpdh_unlocked_achievements', $normalized);
    
    wp_send_json_success(['date' => $date_string]);
}
add_action('wp_ajax_lpdh_toggle_user_achievement', 'lpdh_ajax_toggle_user_achievement');

// AJAX Handler: Delete All
function lpdh_ajax_delete_all_user_achievements() {
    check_ajax_referer('lpdh_delete_all', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    $user_id = intval($_POST['user_id']);

    if (!$user_id) {
        wp_send_json_error('Invalid User ID');
    }

    // Delete the meta entirely to wipe all history (old and new)
    delete_user_meta($user_id, 'lpdh_unlocked_achievements');

    wp_send_json_success();
}
add_action('wp_ajax_lpdh_delete_all_user_achievements', 'lpdh_ajax_delete_all_user_achievements');

