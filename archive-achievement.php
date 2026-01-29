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
                    ?>
                        <div class="col">
                            <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                <div class="card h-100 bg-dark text-light shadow-sm <?php echo $border_class; ?> achievement-card">
                                    <div class="card-body text-center py-4 <?php echo $opacity_class; ?>">
                                        
                                        <?php 
                                        $badge_data = lpdh_format_achievement(get_post($id), $is_unlocked ? time() : null);
                                        echo lpdh_render_achievement_icon($badge_data, 'icon-lg mb-3 mx-auto'); 
                                        ?>
                                        
                                        <?php
                                        $title_text = get_the_title();
                                        $desc_text = get_the_content();
                                        $is_secret = get_field('is_secret', $id);

                                        if ($is_secret && !$is_unlocked) {
                                            $title_text = "Secret Achievement";
                                            $desc_text = "This achievement is secret. It can be unlocked in a specific mode.";
                                        }
                                        ?>
                                        
                                        <h5 class="card-title text-warning mb-2"><?php echo esc_html($title_text); ?></h5>
                                        <p class="card-text small text-white-50"><?php echo wp_trim_words($desc_text, 10); ?></p>
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
                    <!-- Pagination Removed -->
                </div>

            <?php else : ?>
                <p>No achievements found.</p>
            <?php endif; ?>

        </main>
    </div>
</div>

<?php
get_footer();
