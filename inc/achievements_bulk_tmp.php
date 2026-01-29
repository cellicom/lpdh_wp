<?php
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
        
        if (!$original_post) continue;

        // Calculate New Title (Increment Year)
        // Regex to find 4-digit year 20XX
        $title = $original_post->post_title;
        $year_found = false;
        
        $new_title = preg_replace_callback('/\b(20[2-9][0-9])\b/', function($matches) use (&$year_found) {
            $year_found = true;
            return intval($matches[1]) + 1;
        }, $title);

        if (!$year_found) {
            // Append " (Next Year)" if no year found to avoid confusion
            $new_title .= ' (Next Year)';
        }

        // Create New Post
        $new_post_args = array(
            'post_title'    => $new_title,
            'post_content'  => $original_post->post_content,
            'post_status'   => 'draft', // Draft for safety
            'post_type'     => 'achievement',
            'post_author'   => get_current_user_id(),
        );

        $new_post_id = wp_insert_post($new_post_args);

        if ($new_post_id) {
            $duplicated_count++;

            // Duplicate ACF Fields
            // We get all meta and filter for what we need, or just rely on ACFs get_fields
            // Better to use get_post_meta for everything to ensure we catch all custom fields
            $meta = get_post_meta($post_id);

            foreach ($meta as $key => $values) {
                // Skip WP internal meta
                if (strpos($key, '_') === 0 && strpos($key, '_acf') !== 0 && $key !== '_thumbnail_id') {
                   // Actually ACF fields often define definition keys starting with _
                   // So we should be careful. 
                   // Safest is to duplicate everything that is NOT standard WP lock/edit stuff
                   if (in_array($key, ['_edit_lock', '_edit_last'])) continue;
                }
                
                foreach ($values as $value) {
                     // ACF Unserialization handled by add_post_meta automatically if we pass raw?
                     // get_post_meta returns unserialized by default for single=false? No, returns array of values.
                     // IMPORTANT: If we use add_post_meta, we should pass the raw value.
                     // get_post_meta($id) returns [ key => [val1, val2] ]
                     
                     // Use simpler approach: Loop specific ACF fields we know
                     // 'condition_type', 'operator', 'value', 'is_secret', 'icon', 'icon_color', 'color_hex', 'color_class'
                     // But we want to be generic. 
                     
                     // Let's use get_post_meta duplication which is standard for clones
                     add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
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
