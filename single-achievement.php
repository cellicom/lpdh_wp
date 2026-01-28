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
                            <div class="lpdh-achievement-icon icon-xl <?php echo $bg_class; ?> shadow-sm mb-4" 
                                 <?php echo $bg_style; ?>>
                                <i class="<?php echo esc_attr($icon); ?>"></i>
                            </div>

                            <!-- Title -->
                            <h1 class="entry-title text-warning mb-3"><?php the_title(); ?></h1>

                            <!-- Content -->
                            <div class="entry-content lead text-light" style="max-width: 600px; margin: 0 auto;">
                                <?php the_content(); ?>
                            </div>


                            
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
