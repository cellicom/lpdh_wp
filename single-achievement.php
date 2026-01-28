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
                            <div class="d-inline-flex align-items-center justify-content-center mb-4 rounded-circle shadow <?php echo $bg_class; ?>" 
                                 <?php echo $bg_style; ?> 
                                 style="width: 100px; height: 100px; font-size: 3rem; color: #fff;">
                                <i class="<?php echo esc_attr($icon); ?>"></i>
                            </div>

                            <!-- Title -->
                            <h1 class="entry-title text-warning mb-3"><?php the_title(); ?></h1>

                            <!-- Content -->
                            <div class="entry-content lead text-light" style="max-width: 600px; margin: 0 auto;">
                                <?php the_content(); ?>
                            </div>

                            <!-- Logic/Condition Info -->
                            <div class="mt-5 pt-4 border-top border-secondary d-inline-block text-start">
                                <h6 class="text-uppercase text-muted small mb-3">Unlock Condition</h6>
                                <p class="mb-0 text-info font-monospace">
                                    <?php 
                                    $type = get_field('condition_type', $id);
                                    if ($type === 'manual') {
                                        echo 'Manual Unlock (Admin Awarded)';
                                    } else {
                                        echo esc_html(strtoupper($type)); 
                                        echo ' <span class="text-white">' . esc_html(get_field('operator', $id)) . '</span> ';
                                        echo esc_html(get_field('value', $id));
                                    }
                                    ?>
                                </p>
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
