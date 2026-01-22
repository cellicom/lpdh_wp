<?php
/**
 * Template for displaying author archive (User Profile)
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header();

$author = get_queried_object();
$author_id = $author->ID;

// Get URLs from settings
$deck_editor_url = lpdh_get_deck_editor_url();
$profile_editor_url = lpdh_get_profile_editor_url();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">

            <!-- User Header -->
            <div class="row justify-content-center mb-5">
                <div class="col-md-8 text-center">
                    <div class="author-avatar mb-3 d-inline-block">
                        <?php echo get_avatar($author_id, 150, '', '', array('class' => 'rounded-circle shadow border')); ?>
                    </div>
                    <h1 class="author-title mb-1"><?php echo esc_html($author->display_name); ?></h1>
                    <p class="mb-3">@<?php echo esc_html($author->user_login); ?></p>

                    <?php if (get_the_author_meta('description', $author_id)): ?>
                        <div class="author-description mx-auto" style="max-width: 600px;">
                            <?php echo wp_kses_post(get_the_author_meta('description', $author_id)); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Social Links -->
                    <div class="author-social mt-3">
                        <?php
                        $socials = array(
                            'user_url' => 'fas fa-globe',
                            'facebook' => 'fab fa-facebook',
                            'twitter' => 'fab fa-twitter',
                            'instagram' => 'fab fa-instagram',
                            'linkedin' => 'fab fa-linkedin',
                            'github' => 'fab fa-github',
                            'youtube' => 'fab fa-youtube',
                            'discord' => 'fab fa-discord',
                        );

                        foreach ($socials as $key => $icon) {
                            $url = get_the_author_meta($key, $author_id);
                            if ($url) {
                                echo '<a href="' . esc_url($url) . '" class="text-decoration-none text-primary mx-2" target="_blank" rel="noopener"><i class="' . esc_attr($icon) . ' fa-lg"></i></a>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php
            // Query Decks
            $decks_args = array(
                'post_type' => 'deck',
                'posts_per_page' => -1,
                'author' => $author_id,
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
            );
            $decks_query = new WP_Query($decks_args);
            ?>

            <?php if (is_user_logged_in() && get_current_user_id() == $author_id): ?>
                <div class="row justify-content-center mb-5 g-2">
                    <div class="col-auto">
                        <a href="<?php echo esc_url(lpdh_get_stats_url($author_id)); ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-chart-bar me-2"></i> View my Stats
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?php echo esc_url($profile_editor_url); ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-edit me-2"></i> Edit my Profile
                        </a>
                    </div>
                    <div class="col-auto">
                        <a href="<?php echo esc_url($deck_editor_url); ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i> Add Deck
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Decks List -->
            <?php if ($decks_query->have_posts()): ?>
                <div class="decks-section pt-4 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0">Decks</h2>
                        <?php if (is_user_logged_in() && (current_user_can('player') || current_user_can('administrator'))): ?>
                            <a href="<?php echo esc_url($deck_editor_url); ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Add Deck
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="row g-4">
                        <?php while ($decks_query->have_posts()):
                            $decks_query->the_post(); ?>
                            <?php get_template_part('template-parts/card', 'deck', ['show_author' => false]); ?>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif;
            wp_reset_postdata(); ?>

        </div>
    </main>
</div>



<?php get_footer(); ?>