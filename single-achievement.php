<?php
/**
 * Template for displaying single achievement
 */

get_header();
?>

<div id="content" class="site-content container py-5">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <?php
            while (have_posts()) :
                the_post();
                
                // data
                $id = get_the_ID();
                $icon = get_field('icon', $id);
                $color_hex = get_field('color_hex', $id);
                $color_class = get_field('color_class', $id);
                
                // Icon Sanitization (shared logic, ideally helper function but inline ok for template)
                if (is_string($icon) && strpos($icon, '<i') !== false) {
                    preg_match('/class=["\']([^"\']+)["\']/', $icon, $matches);
                    $icon = isset($matches[1]) ? $matches[1] : trim(strip_tags($icon));
                }

                $bg_style = '';
                $bg_class = 'bg-primary';
                if (!empty($color_hex)) {
                    $darker = lpdh_adjust_brightness($color_hex, -40);
                    $bg_style = 'style="background: linear-gradient(135deg, ' . esc_attr($color_hex) . ', ' . esc_attr($darker) . ');"';
                    $bg_class = '';
                } elseif (!empty($color_class)) {
                    $bg_class = 'bg-' . esc_attr($color_class);
                }
            ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="text-center py-5">
                        <div class="container" style="max-width: 800px;">
                            
                            <!-- Icon -->
                            <?php $icon_color = get_field('icon_color', get_the_ID()) ?: '#ffffff'; ?>
                            <div class="lpdh-achievement-icon icon-xl <?php echo $bg_class; ?> shadow-sm mb-4 mx-auto" 
                                 <?php echo $bg_style; ?>>
                                <i class="<?php echo esc_attr($icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>;"></i>
                            </div>

                            <?php
                            // Secret Check
                            $is_secret = get_field('is_secret', $id);
                            
                            // Check Status (Done early for display logic)
                            // Note: Logic duplicated from below slightly, but necessary for title masking
                            $is_unlocked_check = false;
                            $unlock_date_val = '';
                            if (is_user_logged_in()) {
                                $uid_check = get_current_user_id();
                                $list_check = lpdh_get_user_achievements($uid_check);
                                foreach ($list_check as $item_check) {
                                    if ($item_check['id'] == $id) {
                                        $is_unlocked_check = true;
                                        $unlock_date_val = $item_check['date_unlocked'];
                                        break;
                                    }
                                }
                            }

                            $title_text = get_the_title();
                            $content_text = get_the_content();

                            if ($is_secret && !$is_unlocked_check) {
                                $title_text = "Secret Achievement";
                                $content_text = "This achievement is secret. It can be unlocked in a specific mode.";
                            } else {
                                $is_yearly = get_field('yearly', $id);
                                $year = get_field('year', $id);
                                if ($is_yearly && $year) {
                                    $title_text .= ' (' . $year . ')';
                                }
                            }
                            ?>

                            <!-- Title -->
                            <h1 class="entry-title text-warning mb-3"><?php echo esc_html($title_text); ?></h1>

                            <!-- Content -->
                            <div class="entry-content lead text-light" style="max-width: 600px; margin: 0 auto;">
                                <?php echo wp_kses_post($content_text); ?>
                            </div>

                            <?php
                            // Check Status
                            $is_unlocked = false;
                            $unlock_date = '';
                            if (is_user_logged_in()) {
                                $uid = get_current_user_id();
                                $list = lpdh_get_user_achievements($uid);
                                foreach ($list as $item) {
                                    if ($item['id'] == $id) {
                                        $is_unlocked = true;
                                        $unlock_date = $item['date_unlocked'];
                                        break;
                                    }
                                }
                            }
                            ?>

                            <?php if ($is_unlocked): ?>
                                <div class="mt-4 pt-4 border-top border-secondary">
                                    <span class="badge bg-success text-dark fs-5 p-3">
                                        <i class="fas fa-check-circle me-2"></i> Unlocked on <?php echo $unlock_date; ?>
                                    </span>
                                </div>
                            <?php elseif (is_user_logged_in()): ?>
                                <div class="mt-4 pt-4 border-top border-secondary">
                                    <span class="badge bg-secondary fs-6 p-3 opacity-75">
                                        <i class="fas fa-lock me-2"></i> Locked
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                        <div class="text-center py-3">
                           <a href="<?php echo get_post_type_archive_link('achievement'); ?>" class="btn btn-outline-light btn-sm">
                               <i class="fas fa-arrow-left me-2"></i> All Achievements
                           </a>
                           <div class="mt-5 pt-4 border-top border-secondary">
                                <h4 class="text-white mb-4"><?php esc_html_e('These users have unlocked this achievement:', 'lpdh'); ?></h4>
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <?php
                                    // Query users who have this achievement unlocked
                                    // We search for the post ID in the serialized array string
                                    // Using key search "i:123;" (older) or just the ID if stored as simple array?
                                    // Based on get_user_achievements, it stores [ID => time]. 
                                    // So serialized string contains "i:POSTID;" as a KEY.
                                    
                                    global $wpdb;
                                    $ach_id = get_the_ID();
                                    $meta_key = 'lpdh_unlocked_achievements';
                                    
                                    // Like query for serialized integer key: i:123;
                                    $like_key = 'i:' . $ach_id . ';';

                                    // Use WP_User_Query for better performance than get_users() handling
                                    $user_query = new WP_User_Query(array(
                                        'meta_query' => array(
                                            array(
                                                'key'     => $meta_key,
                                                'value'   => $like_key,
                                                'compare' => 'LIKE'
                                            )
                                        ),
                                        'fields' => 'all_with_meta', // lighter return? actually need ID
                                    ));

                                    $unlocked_users = $user_query->get_results();

                                    if (!empty($unlocked_users)) {
                                        foreach ($unlocked_users as $user) {
                                            $profile_url = get_author_posts_url($user->ID);
                                            $avatar = get_avatar($user->ID, 64, '', $user->display_name, ['class' => 'rounded-circle shadow-sm border border-secondary']);
                                            
                                            echo '<a href="' . esc_url($profile_url) . '" class="text-decoration-none" data-bs-toggle="tooltip" title="' . esc_attr($user->display_name) . '">';
                                            echo $avatar;
                                            echo '</a>';
                                        }
                                    } else {
                                        echo '<p class="text-white-50 fst-italic">' . esc_html__('No one has unlocked this yet. Be the first!', 'lpdh') . '</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

            <?php endwhile; // End of the loop. ?>

        </main>
    </div>
</div>

<?php
get_footer();
