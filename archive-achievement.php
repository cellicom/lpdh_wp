<?php
/**
 * Template for displaying achievement archive
 */

get_header();

// Prepare User Unlocked Data if logged in (for styling)
$user_unlocked_ids = [];
if (is_user_logged_in()) {
    $meta = get_user_meta(get_current_user_id(), 'lpdh_unlocked_achievements', true);
    if (is_array($meta)) {
        // Handle migration format safely: keys vs values
        foreach ($meta as $k => $v) {
            // New format: ID => TS. Old: Index => ID
            if (is_int($k) && is_numeric($v) && $k < 1000) $user_unlocked_ids[] = $v;
            else $user_unlocked_ids[] = $k;
        }
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
                            $bg_style = 'style="background-color: ' . esc_attr($color_hex) . ';"';
                            $bg_class = '';
                        } elseif (!empty($color_class)) {
                            $bg_class = 'bg-' . esc_attr($color_class);
                        }
                    ?>
                        <div class="col">
                            <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                <div class="card h-100 bg-dark text-light shadow-sm <?php echo $border_class; ?> achievement-card">
                                    <div class="card-body text-center py-4 <?php echo $opacity_class; ?>">
                                        
                                        <div class="d-inline-flex align-items-center justify-content-center mb-3 rounded-circle shadow <?php echo $bg_class; ?>" 
                                             <?php echo $bg_style; ?> 
                                             style="width: 70px; height: 70px; font-size: 2rem; color: #fff;">
                                            <i class="<?php echo esc_attr($icon); ?>"></i>
                                        </div>
                                        
                                        <h5 class="card-title text-warning mb-2"><?php the_title(); ?></h5>
                                        <p class="card-text small text-white-50"><?php echo wp_trim_words(get_the_content(), 10); ?></p>
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
                
                <style>
                    .grayscale { filter: grayscale(100%); }
                    .achievement-card { transition: transform 0.2s; }
                    .achievement-card:hover { transform: translateY(-5px); }
                </style>

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
