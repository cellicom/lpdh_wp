<?php
/**
 * Template for displaying achievement archive
 */

get_header();

// Prepare User Unlocked Data if logged in (for styling)
// Prepare User Unlocked Data if logged in (for styling)
$user_unlocked_ids = [];
if (is_user_logged_in()) {
    $uid = get_current_user_id();
    // Use the robust function to get consistent data (and trigger auto-unlocks)
    $list = lpdh_get_user_achievements($uid);
    foreach ($list as $item) {
        $user_unlocked_ids[] = $item['id'];
    }
}
?>

<div id="content" class="site-content container py-5">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">

            <header class="page-header mb-5 text-center">
                <h1 class="page-title text-warning display-4">All Achievements</h1>
                <p class="lead text-light">Collect them all!</p>
            </header>

            <?php if (have_posts()) : ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php while (have_posts()) : the_post(); 
                        $id = get_the_ID();
                        $is_unlocked = in_array($id, $user_unlocked_ids);
                        $opacity_class = $is_unlocked ? '' : 'opacity-50 grayscale';
                        $border_class = $is_unlocked ? 'border-warning' : 'border-secondary';
                        
                        $icon = get_field('icon', $id);
                        if (is_string($icon) && strpos($icon, '<i') !== false) {
                            preg_match('/class=["\']([^"\']+)["\']/', $icon, $matches);
                            $icon = isset($matches[1]) ? $matches[1] : trim(strip_tags($icon));
                        }
                        
                        $color_hex = get_field('color_hex', $id);
                        $color_class = get_field('color_class', $id);
                        
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
                        <div class="col">
                            <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                <div class="card h-100 bg-dark text-light shadow-sm <?php echo $border_class; ?> achievement-card">
                                    <div class="card-body text-center py-4 <?php echo $opacity_class; ?>">
                                        
                                        <?php $icon_color = get_field('icon_color', $id) ?: '#ffffff'; ?>
                                        <div class="lpdh-achievement-icon icon-lg <?php echo $bg_class; ?> shadow-sm mb-3 mx-auto" 
                                             <?php echo $bg_style; ?>>
                                            <i class="<?php echo esc_attr($icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>;"></i>
                                        </div>
                                        
                                        <?php
                                        $title_text = get_the_title();
                                        $desc_text = get_the_content();
                                        $is_secret = get_field('is_secret', $id);

                                        if ($is_secret && !$is_unlocked) {
                                            $title_text = "Secret Achievement";
                                            $desc_text = "This achievement is secret. It can be unlocked in a specific mode.";
                                        }
                                        ?>
                                        
                                        <div class="d-flex justify-content-center align-items-center mb-2">
                                            <h5 class="card-title text-warning mb-0 me-2"><?php echo esc_html($title_text); ?></h5>
                                            <?php if (current_user_can('edit_post', $id)): ?>
                                                <a href="<?php echo get_edit_post_link($id); ?>" class="text-secondary small" title="Edit Achievement">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>

                                        <p class="card-text small text-white-50 mb-3"><?php echo wp_kses_post($desc_text); ?></p>

                                        <?php 
                                        // Condition Display logic
                                        // Only show if NOT (Secret AND Locked)
                                        if (!($is_secret && !$is_unlocked)) {
                                            $cond_type = get_field('condition_type', $id);
                                            
                                            // Readable Labels
                                            $labels = [
                                                'manual' => 'Manual',
                                                'win_count' => 'Wins',
                                                'clown_count' => 'Last Places',
                                                'event_count' => 'Events',
                                                'deck_count' => 'Decks',
                                                'days_registered' => 'Days Registered',
                                                'global_elo' => 'Elo',
                                            ];
                                            
                                            $label = isset($labels[$cond_type]) ? $labels[$cond_type] : $cond_type;
                                            
                                            if ($cond_type !== 'manual') {
                                                $operator = get_field('operator', $id);
                                                $value = get_field('value', $id);
                                                echo '<p class="card-text x-small text-muted mb-0">Condition: <strong>' . esc_html($label) . ' ' . esc_html($operator) . ' ' . esc_html($value) . '</strong></p>';
                                            } else {
                                                echo '<p class="card-text x-small text-muted mb-0">Condition: <strong>Manual Grant</strong></p>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <?php if ($is_unlocked): ?>
                                        <div class="card-footer bg-success text-white text-center py-1 small fw-bold">
                                            Unlocked!
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endwhile; ?>
                </div>
                


                <div class="mt-5">
                    <?php the_posts_pagination(); ?>
                </div>

            <?php else : ?>
                <p>No achievements found.</p>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php
get_footer();
