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
                    $bg_style = 'style="background-color: ' . esc_attr($color_hex) . ';"';
                    $bg_class = '';
                } elseif (!empty($color_class)) {
                    $bg_class = 'bg-' . esc_attr($color_class);
                }
            ?>

                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="card bg-dark border-secondary shadow-lg">
                        <div class="card-body text-center py-5">
                            
                            <!-- Icon -->
                            <div class="lpdh-achievement-icon icon-xl <?php echo $bg_class; ?> shadow-sm mb-4 mx-auto" 
                                 <?php echo $bg_style; ?>>
                                <i class="<?php echo esc_attr($icon); ?>"></i>
                            </div>

                            <!-- Title -->
                            <h1 class="entry-title text-warning mb-3"><?php the_title(); ?></h1>

                            <!-- Content -->
                            <div class="entry-content lead text-light" style="max-width: 600px; margin: 0 auto;">
                                <?php the_content(); ?>
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
                        <div class="card-footer bg-dark border-secondary text-center py-3">
                           <a href="<?php echo get_post_type_archive_link('achievement'); ?>" class="btn btn-outline-light btn-sm">
                               <i class="fas fa-arrow-left me-2"></i> All Achievements
                           </a>
                        </div>
                    </div>
                </article>

            <?php endwhile; // End of the loop. ?>

        </main>
    </div>
</div>

<?php
get_footer();
