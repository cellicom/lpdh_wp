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
        'name' => 'Achievements',
        'singular_name' => 'Achievement',
        'menu_name' => 'Achievements',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Achievement',
        'edit_item' => 'Edit Achievement',
        'new_item' => 'New Achievement',
        'view_item' => 'View Achievement',
        'view_items' => 'View Achievements',
        'search_items' => 'Search Achievements',
        'not_found' => 'No achievements found',
        'not_found_in_trash' => 'No achievements found in Trash',
    ];

    $args = [
        'labels' => $labels,
        'public' => true, // Queryable for single/archive templates
        'has_archive' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 4,
        'menu_icon' => 'dashicons-awards',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'editor'],
        'show_in_rest' => true,
    ];

    register_post_type('achievement', $args);
}
add_action('init', 'lpdh_register_achievement_cpt');

/**
 * Force Order for Achievement Archive
 * Note: Sorting is handled via lpdh_achievement_archive_sort filter for full visibility
 */
function lpdh_achievement_archive_order($query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('achievement')) {
        $query->set('posts_per_page', -1);
    }
}
add_action('pre_get_posts', 'lpdh_achievement_archive_order');

/**
 * Sort Achievements: Year DESC (populated first), Condition ASC, Value ASC
 * Using posts_results filter to ensure visibility of posts with missing meta
 */
function lpdh_achievement_archive_sort($posts, $query)
{
    if (!is_admin() && $query->is_main_query() && $query->is_post_type_archive('achievement')) {
        usort($posts, function ($a, $b) {
            // 1. Year (DESC)
            $year_a = get_field('year', $a->ID);
            $year_b = get_field('year', $b->ID);

            // Treat empty/false as 0
            $val_year_a = $year_a ? intval($year_a) : 0;
            $val_year_b = $year_b ? intval($year_b) : 0;

            if ($val_year_a !== $val_year_b) {
                return $val_year_b - $val_year_a; // DESC
            }

            // 2. Condition Type (ASC)
            $type_a = get_field('condition_type', $a->ID);
            $type_b = get_field('condition_type', $b->ID);
            $type_a = $type_a ?: '';
            $type_b = $type_b ?: '';

            if ($type_a !== $type_b) {
                return strcmp($type_a, $type_b);
            }

            // 3. Value (ASC)
            $val_a = get_field('value', $a->ID);
            $val_b = get_field('value', $b->ID);

            return intval($val_a) - intval($val_b); // ASC
        });
    }
    return $posts;
}
add_filter('posts_results', 'lpdh_achievement_archive_sort', 10, 2);

/**
 * Define custom columns for Achievement List
 */
function lpdh_achievement_posts_columns($columns)
{
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['ach_condition'] = 'Condition';
            $new_columns['ach_secret'] = 'Secret';
            $new_columns['ach_year'] = 'Year';
        }
    }
    return $new_columns;
}
add_filter('manage_achievement_posts_columns', 'lpdh_achievement_posts_columns');

/**
 * Display content for custom columns
 */
function lpdh_achievement_posts_custom_column($column, $post_id)
{
    switch ($column) {
        case 'ach_condition':
            $type = get_field('condition_type', $post_id);
            $oper = get_field('operator', $post_id);
            $val = get_field('value', $post_id);

            $labels = [
                'manual' => 'Manual',
                'win_count' => 'Wins',
                'clown_count' => 'Clowns',
                'event_count' => 'Attendance',
                'deck_count' => 'Decks',
                'days_registered' => 'Registration',
                'elo' => 'Elo',
                'deck_with_banned' => 'Banned',
                'deck_commander_partner' => 'Cmdr/Part',
                'spinned_wheel_count' => 'Spins',
            ];

            if ($type === 'manual') {
                echo 'Manual';
            } else {
                $type_label = isset($labels[$type]) ? $labels[$type] : $type;
                echo '<strong>' . esc_html($type_label) . '</strong> ' . esc_html($oper) . ' ' . esc_html($val);
            }
            break;

        case 'ach_secret':
            $is_secret = get_field('is_secret', $post_id);
            if ($is_secret) {
                echo '<span class="dashicons dashicons-hidden" style="color:#d63638;" title="Secret"></span> Yes';
            } else {
                echo '<span class="dashicons dashicons-visibility" style="color:#999;" title="Not Secret"></span> No';
            }
            break;

        case 'ach_year':
            $is_yearly = get_field('yearly', $post_id);
            $year = get_field('year', $post_id);
            if ($is_yearly && $year) {
                echo '<strong>' . esc_html($year) . '</strong>';
            } else {
                echo '<span style="color:#999;">-</span>';
            }
            break;
    }
}
add_action('manage_achievement_posts_custom_column', 'lpdh_achievement_posts_custom_column', 10, 2);

/**
 * Make custom columns sortable
 */
function lpdh_achievement_sortable_columns($columns)
{
    $columns['ach_year'] = 'ach_year';
    return $columns;
}
add_filter('manage_edit-achievement_sortable_columns', 'lpdh_achievement_sortable_columns');


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
                    'width' => '100',
                ),
                'choices' => array(
                    'manual' => 'Manual Achievement',
                    'win_count' => 'Number of 1st Place',
                    'clown_count' => 'Number of Clown (Last Place)',
                    'event_count' => 'Events Attended',
                    'deck_count' => 'Number of Decks Created',
                    'days_registered' => 'Days Since Registration',
                    'elo' => 'Elo',
                    'deck_with_banned' => 'Deck with Banned Card',
                    'deck_commander_partner' => 'Deck with Commander/Partner',
                    'spinned_wheel_count' => 'Spinned the Wheel (Count)',
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
                    'width' => '50',
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
                    'width' => '50',
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
                'instructions' => 'Hidden from the main list unless unlocked.',
                'wrapper' => array(
                    'width' => '33',
                ),
                'default_value' => 0,
                'ui' => 1,
            ),
            array(
                'key' => 'field_ach_yearly',
                'label' => 'Yearly Achievement',
                'name' => 'yearly',
                'type' => 'true_false',
                'instructions' => 'Check if this is an annual achievement.',
                'wrapper' => array(
                    'width' => '33',
                ),
                'default_value' => 0,
                'ui' => 1,
            ),
            array(
                'key' => 'field_ach_year',
                'label' => 'Reference Year',
                'name' => 'year',
                'type' => 'select',
                'instructions' => 'Select the year for this achievement.',
                'required' => 0,
                'wrapper' => array(
                    'width' => '34',
                ),
                'choices' => (function () {
                    $ach_years = array();
                    global $wpdb;
                    $min_date = $wpdb->get_var("SELECT MIN(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'event_date'");
                    $max_date = $wpdb->get_var("SELECT MAX(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'event_date'");

                    $start_year = $min_date ? intval(date('Y', strtotime($min_date))) : intval(date('Y')) - 1;
                    $end_year = $max_date ? intval(date('Y', strtotime($max_date))) + 1 : intval(date('Y')) + 1;

                    if ($start_year > intval(date('Y')))
                        $start_year = intval(date('Y')) - 1;

                    for ($y = $end_year; $y >= $start_year; $y--) {
                        $ach_years[$y] = $y;
                    }
                    return $ach_years;
                })(),
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_ach_yearly',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
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
 * @param string $year 'global' or 'YYYY'
 * @return array ['deck_count', 'win_count', 'event_count', 'clown_count', 'days_registered', 'elo', ...]
 */



/**
 * Checks if user has a deck with a specific Commander or Partner.
 * 
 * @param int    $user_id
 * @param string $target_name
 * @param string $operator ('CONTAINS', 'EQUALS', etc)
 * @return bool
 */
/**
 * Checks if user has a deck with a specific Commander or Partner.
 * 
 * @param int    $user_id
 * @param string $target_name
 * @param string $operator ('CONTAINS', 'EQUALS', etc)
 * @param string $year 'global' or 'YYYY'
 * @return bool
 */
function lpdh_check_deck_commander($user_id, $target_name, $operator = 'CONTAINS', $year = 'global')
{
    if (empty($target_name))
        return false;

    $args = [
        'post_type' => 'deck',
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'fields' => 'ids'
    ];
    if ($year !== 'global') {
        $args['date_query'] = [['year' => $year]];
    }
    $user_decks = get_posts($args);

    foreach ($user_decks as $d_id) {
        // ACF Fields for Commander/Partner
        // Assuming they are text fields or post objects? usually text or relation.
        // Based on previous context, they might be text names or IDs. 
        // Let's assume text for names as per request "nome preciso".
        $commander = get_field('commander', $d_id); // Returns string or object
        $partner = get_field('partner', $d_id);

        if (is_object($commander))
            $commander = $commander->post_title;
        if (is_object($partner))
            $partner = $partner->post_title;

        $commander = is_string($commander) ? $commander : '';
        $partner = is_string($partner) ? $partner : '';

        // Check Logic
        if (
            lpdh_compare_string($commander, $target_name, $operator) ||
            lpdh_compare_string($partner, $target_name, $operator)
        ) {
            return true;
        }
    }
    return false;
}

/**
 * String comparison helper
 */
function lpdh_compare_string($haystack, $needle, $operator)
{
    if (empty($haystack) || empty($needle))
        return false;

    $haystack = strtolower(trim($haystack));
    $needle = strtolower(trim($needle));

    if ($operator === 'EQUALS' || $operator === '=') {
        return $haystack === $needle;
    } else {
        // Default to CONTAINS
        return strpos($haystack, $needle) !== false;
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
    $migrated = false;

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
        foreach ($locked_achievements as $post) {
            $cond_type = get_field('condition_type', $post->ID);

            // Skip Manual achievements
            if ($cond_type === 'manual') {
                continue;
            }

            $is_yearly = get_field('yearly', $post->ID);
            $year = ($is_yearly && $cond_type !== 'spinned_wheel_count') ? get_field('year', $post->ID) : 'global';

            // Special Check: Deck with Commander/Partner
            if ($cond_type === 'deck_commander_partner') {
                $target_name = get_field('value', $post->ID);
                $operator = get_field('operator', $post->ID);

                if (lpdh_check_deck_commander($user_id, $target_name, $operator, $year)) {
                    // Unlocked!
                    $unlock_date = time();
                    $unlocked_data[$post->ID] = $unlock_date;
                    $final_list[] = lpdh_format_achievement($post, $unlock_date);
                    $newly_unlocked = true;
                }
                continue; // Skip standard stat check
            }

            // Fetch stats for this achievement's year context
            $stats = lpdh_get_player_stats($user_id, $year);

            $operator = get_field('operator', $post->ID);
            $target_val = get_field('value', $post->ID);
            $user_val = isset($stats[$cond_type]) ? $stats[$cond_type] : 0;

            if (lpdh_check_stat_condition($user_val, $operator, $target_val)) {
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
    usort($final_list, function ($a, $b) {
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
        'date_unlocked_ts' => $date_timestamp ?: 0,
        'yearly' => get_field('yearly', $post->ID),
        'year' => get_field('year', $post->ID)
    ];
}

/**
 * Renders an achievement icon with optional yearly badge.
 *
 * @param array $badge_data Array from lpdh_format_achievement
 * @param string $size_class CSS class for size (icon-compact, icon-lg, icon-xl)
 * @return string HTML output
 */
function lpdh_render_achievement_icon($badge_data, $size_class = '')
{
    $bg_style = '';
    $bg_class = 'bg-primary';

    if (!empty($badge_data['color_hex'])) {
        $darker = lpdh_adjust_brightness($badge_data['color_hex'], -40);
        $bg_style = 'style="background: linear-gradient(135deg, ' . esc_attr($badge_data['color_hex']) . ', ' . esc_attr($darker) . ');"';
        $bg_class = '';
    } elseif (!empty($badge_data['color_class'])) {
        $bg_class = 'bg-' . esc_attr($badge_data['color_class']);
    } elseif (!empty($badge_data['color'])) {
        $bg_class = 'bg-' . esc_attr($badge_data['color']);
    }

    $icon_col = isset($badge_data['icon_color']) && $badge_data['icon_color'] ? $badge_data['icon_color'] : '#ffffff';
    $size_class = $size_class ? ' ' . $size_class : '';

    ob_start();
    ?>
    <div class="lpdh-achievement-icon<?php echo esc_attr($size_class); ?> <?php echo esc_attr($bg_class); ?> shadow-sm position-relative"
        <?php echo $bg_style; ?>>
        <i class="<?php echo esc_attr($badge_data['icon']); ?>" style="color: <?php echo esc_attr($icon_col); ?>;"></i>
        <?php if (!empty($badge_data['yearly']) && !empty($badge_data['year'])): ?>
            <span class="lpdh-achievement-year-badge"><?php echo esc_html($badge_data['year']); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
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
            if (is_int($key) && !is_numeric($val) && is_array($val))
                continue; // Weird case safety
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
                                <input type="checkbox" name="lpdh_manual_achievements[]" id="ach_<?php echo $ach->ID; ?>"
                                    value="<?php echo $ach->ID; ?>" <?php checked(in_array($ach->ID, $unlocked_ids)); ?> />
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
    if (!is_array($current_data))
        $current_data = [];

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

function lpdh_register_achievement_admin_page()
{
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

// 7. Enqueue Assets for Achievements
function lpdh_enqueue_achievements_assets()
{
    // Correctly get the Font Awesome URL from the ACF Font Awesome plugin configuration
    $fa_url = apply_filters('ACFFA_get_fa_url', '');
    if ($fa_url) {
        wp_enqueue_style('acffa_font-awesome', $fa_url);
    } else {
        // Fallback to CDN
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    }
}
add_action('wp_enqueue_scripts', 'lpdh_enqueue_achievements_assets');

function lpdh_achievements_admin_scripts($hook)
{
    $screen = get_current_screen();

    // Load Font Awesome on achievement-related screens
    if (
        (isset($screen->post_type) && $screen->post_type === 'achievement') ||
        (isset($_GET['page']) && $_GET['page'] === 'lpdh-manage-achievements')
    ) {
        lpdh_enqueue_achievements_assets();
    }

    // Select2 only for management page
    if (isset($_GET['page']) && $_GET['page'] === 'lpdh-manage-achievements') {
        wp_enqueue_style('select2', get_stylesheet_directory_uri() . '/assets/css/select2.min.css');
        wp_enqueue_script('select2', get_stylesheet_directory_uri() . '/assets/js/select2.min.js', ['jquery'], '4.1.0', true);
    }
}
add_action('admin_enqueue_scripts', 'lpdh_achievements_admin_scripts');

/**
 * Common Achievement Icon Styles for Admin
 */
function lpdh_achievements_admin_head_styles()
{
    $screen = get_current_screen();
    if (
        (isset($screen->post_type) && $screen->post_type === 'achievement') ||
        (isset($_GET['page']) && $_GET['page'] === 'lpdh-manage-achievements')
    ) {
        ?>
        <style>
            /* Centralized Achievement Icon Styles for Admin */
            .lpdh-achievement-icon {
                width: 45px;
                height: 45px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                color: #fff;
                flex-shrink: 0;
                position: relative !important;
                /* Ensure badges stay within the icon */
            }

            .lpdh-achievement-icon i {
                font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome", sans-serif !important;
                font-weight: 900 !important;
                font-style: normal;
                display: inline-block;
            }

            .lpdh-achievement-icon.icon-lg {
                width: 60px;
                height: 60px;
                font-size: 1.8rem;
            }

            .lpdh-achievement-icon.icon-compact {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .lpdh-achievement-year-badge {
                position: absolute;
                bottom: -12%;
                left: 50%;
                transform: translateX(-50%);
                background: #ffd700;
                color: #000;
                font-size: 0.50rem;
                font-weight: bold;
                padding: 1px 3px;
                border-radius: 4px;
                line-height: 1;
                border: 1px solid #000;
                z-index: 2;
                white-space: nowrap;
            }

            .icon-compact .lpdh-achievement-year-badge {
                font-size: 8px;
                padding: 0 2px;
            }

            .bg-bronze {
                background: linear-gradient(135deg, #cd7f32, #8c5a2b);
            }

            .bg-silver {
                background: linear-gradient(135deg, #c0c0c0, #808080);
            }

            .bg-gold {
                background: linear-gradient(135deg, #ffd700, #b8860b);
            }
        </style>
        <?php
    }
}
add_action('admin_head', 'lpdh_achievements_admin_head_styles');


function lpdh_render_manage_achievements_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    // Handle User Selection
    $selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

    // Get all users for dropdown
    $users = get_users(['orderby' => 'display_name']);

    ?>
    <style>
        /* Extra styles specific only to the Management Page */

        /* Switch Styles */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 20px;
            margin-right: 10px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            transform: translateX(20px);
        }

        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .select2-container {
            width: 300px !important;
            max-width: 100%;
        }

        @media screen and (max-width: 782px) {
            .select2-container {
                width: 100% !important;
                margin-bottom: 10px;
            }

            .tablenav.top {
                height: auto;
            }

            .tablenav .actions {
                display: block;
                float: none;
                margin-bottom: 10px;
            }
        }
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
                    <input type="text" id="lpdh-ach-search" placeholder="Search achievements..."
                        style="height: 30px; margin-left: 20px;">
                    <button type="button" id="lpdh-btn-delete-all" class="button button-primary"
                        style="margin-left: 10px; background-color: #dc3232; border-color: #dc3232; color: #fff;">
                        Delete All for <?php echo esc_html($sel_user ? $sel_user->display_name : 'User'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="lpdh-delete-modal"
            style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
            <div
                style="width:400px; padding:20px; margin:15% auto; box-shadow:0 0 10px rgba(0,0,0,0.5); border-radius:5px; text-align:center;">
                <h2 style="margin-top:0; color:#dc3232;">⚠ Warning</h2>
                <p>Are you sure you want to delete <strong>ALL</strong> achievements for this user?</p>
                <p>This action cannot be undone.</p>
                <div style="margin-top:20px;">
                    <button id="lpdh-confirm-delete" class="button button-primary bg-danger"
                        style="background:#dc3232; border-color:#dc3232;">Yes, Delete Everything</button>
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
            <div id="lpdh-achievement-grid"
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px;">
                <?php foreach ($all_achievements as $post):
                    $is_unlocked = isset($normalized_unlocked[$post->ID]);
                    $unlock_ts = $is_unlocked ? $normalized_unlocked[$post->ID] : false;
                    $unlock_date = $unlock_ts ? date('Y-m-d H:i', $unlock_ts) : '';

                    $badge_data = lpdh_format_achievement($post, $unlock_ts);
                    ?>
                    <div class="card ach-card" data-title="<?php echo esc_attr(strtolower($post->post_title)); ?>"
                        style="border: 1px solid #ccd0d4; padding: 15px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04); display: flex; flex-direction: column;">
                        <div style="display: flex; align-items: start; gap: 15px; flex-grow: 1;">
                            <?php echo lpdh_render_achievement_icon($badge_data); ?>
                            <div style="flex-grow: 1; display: flex; flex-direction: column; height: 100%;">
                                <div style="display: flex; align-items: center; margin-bottom: 5px;">
                                    <h3 style="margin: 0; font-size: 1.1em;">
                                        <?php echo esc_html($post->post_title); ?>
                                    </h3>
                                    <?php if (current_user_can('edit_post', $post->ID)): ?>
                                        <a href="<?php echo get_edit_post_link($post->ID); ?>" target="_blank"
                                            style="margin-left: 10px; color: #666; font-size: 0.9em;" title="Edit Achievement">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <div style="margin: 0 0 10px; color: #666; font-size: 0.9em; flex-grow: 1;">
                                    <?php echo wp_kses_post($post->post_content); ?>
                                </div>

                                <?php
                                // Condition Display
                                $cond_type = get_field('condition_type', $post->ID);
                                $labels = [
                                    'manual' => 'Manual',
                                    'win_count' => 'Wins',
                                    'clown_count' => 'Last Places',
                                    'event_count' => 'Events',
                                    'deck_count' => 'Decks',
                                    'days_registered' => 'Days Registered',
                                    'elo' => 'Elo',
                                    'deck_with_banned' => 'Banned Decks',
                                ];
                                $label = isset($labels[$cond_type]) ? $labels[$cond_type] : $cond_type;

                                if ($cond_type !== 'manual') {
                                    $operator = get_field('operator', $post->ID);
                                    $value = get_field('value', $post->ID);
                                    echo '<p style="margin: 0 0 5px; font-size: 0.85em; color: #888;">Condition: <strong>' . esc_html($label) . ' ' . esc_html($operator) . ' ' . esc_html($value) . '</strong></p>';
                                } else {
                                    echo '<p style="margin: 0 0 5px; font-size: 0.85em; color: #888;">Condition: <strong>Manual Grant</strong></p>';
                                }
                                ?>

                                <div
                                    style="display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 10px; border-top: 1px solid #eee;">
                                    <label class="switch">
                                        <input type="checkbox" class="lpdh-ach-toggle" data-ach-id="<?php echo $post->ID; ?>"
                                            data-user-id="<?php echo $selected_user_id; ?>" <?php checked($is_unlocked); ?>>
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="status-text"
                                        style="font-weight: bold; color: <?php echo $is_unlocked ? '#46b450' : '#dc3232'; ?>">
                                        <?php echo $is_unlocked ? 'Unlocked' : 'Locked'; ?>
                                    </span>
                                </div>
                                <div class="date-display"
                                    style="font-size: 0.85em; color: #888; margin-top: 5px; text-align: right;">
                                    <?php echo $is_unlocked ? $unlock_date : '-'; ?>
                                </div>
                                <?php if (!$is_unlocked && $cond_type !== 'manual'): ?>
                                    <div style="margin-top: 10px; border-top: 1px dashed #ddd; padding-top: 10px; text-align: right;">
                                        <button type="button" class="button button-small lpdh-ach-check"
                                            data-ach-id="<?php echo $post->ID; ?>" data-user-id="<?php echo $selected_user_id; ?>">
                                            <i class="fas fa-microscope"></i> Check
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>


        <?php else: ?>
            <div class="notice notice-info inline">
                <p>Please select a user to manage achievements.</p>
            </div>
        <?php endif; ?>
    </div>
    <script>
        jQuery(document).ready(function ($) {
            // Init Select2
            $('#lpdh-user-select').select2({
                placeholder: "Select a User...",
                allowClear: true
            });

            // Search Filter
            $('#lpdh-ach-search').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('#lpdh-achievement-grid .ach-card').filter(function () {
                    $(this).toggle($(this).data('title').indexOf(value) > -1)
                });
            });

            // Toggle AJAX
            $('.lpdh-ach-toggle').on('change', function () {
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
                    success: function (response) {
                        $card.css('opacity', 1);
                        if (response.success) {
                            $statusText.text(isChecked ? 'Unlocked' : 'Locked')
                                .css('color', isChecked ? '#46b450' : '#dc3232');
                            $dateDisplay.text(response.data.date);
                        } else {
                            alert('Error: ' + (response.data || 'Unknown error'));
                            $checkbox.prop('checked', !isChecked); // Revert
                        }
                    },
                    error: function () {
                        $card.css('opacity', 1);
                        alert('Request failed');
                        $checkbox.prop('checked', !isChecked); // Revert
                    }
                });
            });

            // Check Condition AJAX
            $('.lpdh-ach-check').on('click', function () {
                var $btn = $(this);
                var achId = $btn.data('ach-id');
                var userId = $btn.data('user-id');
                var originalText = $btn.html();

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'lpdh_check_achievement_condition',
                        ach_id: achId,
                        user_id: userId,
                        security: '<?php echo wp_create_nonce("lpdh_ach_nonce"); ?>'
                    },
                    success: function (response) {
                        $btn.prop('disabled', false).html(originalText);
                        if (response.success) {
                            var data = response.data;
                            console.log("--- ACHIEVEMENT CHECK ---");
                            console.log("Post ID: " + achId);
                            console.log("Condition: " + data.condition_label);
                            console.log("Context: " + data.year_context);
                            console.log("User Value: " + data.user_value);
                            console.log("Operator: " + data.operator);
                            console.log("Target Value: " + data.target_value);
                            console.log("Is Met: " + (data.is_met ? "TRUE \u2705" : "FALSE \u274C"));
                            console.log("--------------------------");

                            if (data.is_met) {
                                alert("Result: TRUE \u2705\nThe user meets the requirements for this achievement!");
                            } else {
                                alert("Result: FALSE \u274C\nThe user DOES NOT meet the requirements for this achievement yet.\n\n" +
                                    "Current: " + data.user_value + " | Target: " + data.target_value);
                            }
                        } else {
                            alert('Error: ' + response.data);
                        }
                    },
                    error: function () {
                        $btn.prop('disabled', false).html(originalText);
                        alert('Request failed.');
                    }
                });
            });

            // Delete All Handling
            $('#lpdh-btn-delete-all').on('click', function (e) {
                e.preventDefault();
                $('#lpdh-delete-modal').fadeIn(200);
            });

            $('#lpdh-cancel-delete').on('click', function () {
                $('#lpdh-delete-modal').fadeOut(200);
            });

            $('#lpdh-confirm-delete').on('click', function () {
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
                    success: function (response) {
                        if (response.success) {
                            alert('All achievements deleted.');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data || 'Unknown error'));
                            $btn.prop('disabled', false).text('Yes, Delete Everything');
                        }
                    },
                    error: function () {
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
function lpdh_ajax_toggle_user_achievement()
{
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
    if (!is_array($current_data))
        $current_data = [];

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
function lpdh_ajax_delete_all_user_achievements()
{
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

/**
 * AJAX Handler: Check Achievement Condition for a specific user
 */
function lpdh_ajax_check_achievement_condition()
{
    check_ajax_referer('lpdh_ach_nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    $ach_id = intval($_POST['ach_id']);
    $user_id = intval($_POST['user_id']);

    if (!$ach_id || !$user_id) {
        wp_send_json_error('Invalid ID');
    }

    $post = get_post($ach_id);
    if (!$post)
        wp_send_json_error('Achievement not found');

    $cond_type = get_field('condition_type', $ach_id);
    $is_yearly = get_field('yearly', $ach_id);
    $year = ($is_yearly && $cond_type !== 'spinned_wheel_count') ? get_field('year', $ach_id) : 'global';

    $stats = lpdh_get_player_stats($user_id, $year);

    $operator = get_field('operator', $ach_id);
    $target_val = get_field('value', $ach_id);
    $user_val = 0;

    // Special Check: Deck with Commander/Partner
    if ($cond_type === 'deck_commander_partner') {
        $target_name = get_field('value', $ach_id);
        $operator = get_field('operator', $ach_id);
        $is_met = lpdh_check_deck_commander($user_id, $target_name, $operator, $year);
        $user_val = $is_met ? 1 : 0;
        $target_val = 1;
    } else {
        $user_val = isset($stats[$cond_type]) ? $stats[$cond_type] : 0;
        $is_met = lpdh_check_stat_condition($user_val, $operator, $target_val);
    }

    $labels = [
        'manual' => 'Manual',
        'win_count' => 'Wins',
        'clown_count' => 'Last Places',
        'event_count' => 'Events',
        'deck_count' => 'Decks',
        'days_registered' => 'Registration',
        'elo' => 'Elo',
        'deck_with_banned' => 'Banned',
        'deck_commander_partner' => 'Cmdr/Part',
        'spinned_wheel_count' => 'Spins'
    ];

    wp_send_json_success([
        'user_value' => $user_val,
        'target_value' => $target_val,
        'operator' => $operator,
        'is_met' => $is_met,
        'condition_label' => isset($labels[$cond_type]) ? $labels[$cond_type] : $cond_type,
        'year_context' => $year
    ]);
}
add_action('wp_ajax_lpdh_check_achievement_condition', 'lpdh_ajax_check_achievement_condition');

/**
 * Add "Duplicate for next year" to Bulk Actions for Achievements
 */
function lpdh_achievement_custom_bulk_actions($actions)
{
    $actions['duplicate_year'] = 'Duplicate for next year';
    return $actions;
}
add_filter('bulk_actions-edit-achievement', 'lpdh_achievement_custom_bulk_actions');

/**
 * Handle "Duplicate for next year" Bulk Action
 */
function lpdh_achievement_handle_bulk_actions($redirect_to, $doaction, $post_ids)
{
    if ($doaction !== 'duplicate_year') {
        return $redirect_to;
    }

    $duplicated_count = 0;

    foreach ($post_ids as $post_id) {
        // Get Original Post
        $original_post = get_post($post_id);

        if (!$original_post)
            continue;

        // Calculate New Title and Year (Incrementing ACF field if present)
        $title = $original_post->post_title;
        $old_year = get_post_meta($post_id, 'year', true);
        $next_year = false;

        if ($old_year) {
            $next_year = intval($old_year) + 1;
            // Precise replacement: replace the old year in the title with the new one
            if (strpos($title, (string) $old_year) !== false) {
                $new_title = str_replace((string) $old_year, (string) $next_year, $title);
            } else {
                $new_title = $title . ' ' . $next_year;
            }
        } else {
            // No year field found, fall back to " (Next Year)" suffix pattern
            $new_title = $title . ' (Next Year)';
        }

        // Create New Post
        $new_post_args = array(
            'post_title' => $new_title,
            'post_content' => $original_post->post_content,
            'post_status' => 'draft', // Draft for safety
            'post_type' => 'achievement',
            'post_author' => get_current_user_id(),
        );

        $new_post_id = wp_insert_post($new_post_args);

        if ($new_post_id) {
            $duplicated_count++;

            // Duplicate ACF Fields
            $meta = get_post_meta($post_id);

            foreach ($meta as $key => $values) {
                // Skip WP internal meta
                if (strpos($key, '_') === 0 && strpos($key, '_acf') !== 0 && $key !== '_thumbnail_id') {
                    if (in_array($key, ['_edit_lock', '_edit_last']))
                        continue;
                }

                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }

            // --- Update Year Field ---
            // If we calculated a next_year based on the ACF field, apply it
            if ($next_year) {
                update_post_meta($new_post_id, 'year', $next_year);
            }
        }
    }

    // Build Redirect URL
    return add_query_arg('lpdh_duplicated_count', $duplicated_count, $redirect_to);
}
add_filter('handle_bulk_actions-edit-achievement', 'lpdh_achievement_handle_bulk_actions', 10, 3);

/**
 * Show Admin Notice after Duplication
 */
function lpdh_achievement_bulk_action_admin_notice()
{
    if (!empty($_REQUEST['lpdh_duplicated_count'])) {
        $count = intval($_REQUEST['lpdh_duplicated_count']);
        printf(
            '<div id="message" class="updated notice is-dismissible"><p>%s</p></div>',
            sprintf(_n('%s achievement duplicated for next year.', '%s achievements duplicated for next year.', $count, 'text-domain'), $count)
        );
    }
}
add_action('admin_notices', 'lpdh_achievement_bulk_action_admin_notice');
