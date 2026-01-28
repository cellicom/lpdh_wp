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

            <?php
            $is_private = get_user_meta($author_id, 'private_profile', true);
            $can_view = !$is_private || (is_user_logged_in() && (get_current_user_id() == $author_id || current_user_can('administrator')));

            if (!$can_view): ?>
                <div class="row justify-content-center my-5">
                    <div class="col-md-8 text-center">
                        <div class="card bg-dark border-warning shadow-lg py-5 px-4 mb-5">
                            <div class="card-body">
                                <div class="mb-4">
                                    <i class="fas fa-user-shield fa-5x text-warning mb-3"></i>
                                </div>
                                <h1 class="display-4 text-warning mb-4">Profile is Private</h1>
                                <p class="lead text-light mb-0">This user has chosen to keep their profile private.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>








                <!-- User Header -->
                <div class="row justify-content-center mb-5">
                    <div class="col-md-8 text-center">
                        <div class="author-avatar mb-3 d-inline-block">
                            <?php echo get_avatar($author_id, 150, '', '', array('class' => 'rounded-circle shadow border')); ?>
                        </div>

                        <?php if ($is_private && is_user_logged_in() && get_current_user_id() == $author_id): ?>
                            <div class="mb-3">
                                <span class="text-info small">
                                    <i class="fas fa-eye-slash me-1"></i> Your profile is currently set to private.
                                </span>
                            </div>
                        <?php endif; ?>




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
                                'user_url' => ['icon' => 'fas fa-globe', 'label' => 'Website', 'class' => 'website'],
                                'facebook' => ['icon' => 'fab fa-facebook', 'label' => 'Facebook', 'class' => 'facebook'],
                                'twitter' => ['icon' => 'fab fa-x-twitter', 'label' => 'X (Twitter)', 'class' => 'twitter'],
                                'instagram' => ['icon' => 'fab fa-instagram', 'label' => 'Instagram', 'class' => 'instagram'],
                                'linkedin' => ['icon' => 'fab fa-linkedin', 'label' => 'LinkedIn', 'class' => 'linkedin'],
                                'github' => ['icon' => 'fab fa-github', 'label' => 'GitHub', 'class' => 'github'],
                                'youtube' => ['icon' => 'fab fa-youtube', 'label' => 'YouTube', 'class' => 'youtube'],
                                'discord' => ['icon' => 'fab fa-discord', 'label' => 'Discord', 'class' => 'discord'],
                            );

                            foreach ($socials as $key => $data) {
                                $url = get_the_author_meta($key, $author_id);
                                if ($url) {
                                    printf(
                                        '<a href="%s" class="author-social-link %s mx-2" target="_blank" rel="noopener" aria-label="%s"><i class="%s fa-lg"></i></a>',
                                        esc_url($url),
                                        esc_attr($data['class']),
                                        esc_attr($data['label']),
                                        esc_attr($data['icon'])
                                    );
                                }
                            }
                            ?>
                        </div>

                        <!-- Achievements -->
                        <?php
                        $user_achievements = lpdh_get_user_achievements($author_id);
                        if (!empty($user_achievements)): 
                            $total_ach = count($user_achievements);
                            $visible_ach = array_slice($user_achievements, 0, 5);
                            $hidden_count = $total_ach - 5;
                        ?>
                            <div class="author-achievements mt-4">
                                <h5 class="text-uppercase text-warning small mb-3">Achievements</h5>
                                <div class="d-flex justify-content-center flex-wrap gap-3 align-items-center">
                                    <?php foreach ($visible_ach as $badge): 
                                        $bg_style = '';
                                        $bg_class = 'bg-primary'; 
                                        
                                        if (!empty($badge['color_hex'])) {
                                            $bg_style = 'style="background-color: ' . esc_attr($badge['color_hex']) . ';"';
                                            $bg_class = ''; 
                                        } elseif (!empty($badge['color_class'])) {
                                            $bg_class = 'bg-' . esc_attr($badge['color_class']);
                                        } elseif (!empty($badge['color'])) {
                                             $bg_class = 'bg-' . esc_attr($badge['color']);
                                        }
                                    ?>
                                        <div class="achievement-badge" data-bs-toggle="tooltip"
                                            title="<?php echo esc_attr($badge['title']); ?>">
                                            <div class="lpdh-achievement-icon <?php echo $bg_class; ?> shadow-sm" <?php echo $bg_style; ?>>
                                                <i class="<?php echo esc_attr($badge['icon']); ?>"></i>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if ($hidden_count > 0): ?>
                                        <button type="button" class="btn btn-outline-secondary rounded-circle lpdh-achievement-icon" 
                                                data-bs-toggle="modal" data-bs-target="#achievementsModal">
                                            <small>+<?php echo $hidden_count; ?></small>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Achievements Modal -->
                            <div class="modal fade" id="achievementsModal" tabindex="-1" aria-labelledby="achievementsModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content bg-dark text-light border-secondary">
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title" id="achievementsModalLabel">Unlocked Achievements (<?php echo $total_ach; ?>)</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($user_achievements as $badge):
                                                    $bg_style = '';
                                                    $bg_class = 'bg-primary';
                                                    if (!empty($badge['color_hex'])) {
                                                        $bg_style = 'style="background-color: ' . esc_attr($badge['color_hex']) . ';"';
                                                        $bg_class = '';
                                                    } elseif (!empty($badge['color_class'])) {
                                                        $bg_class = 'bg-' . esc_attr($badge['color_class']);
                                                    }
                                                ?>
                                                <div class="list-group-item bg-dark text-light border-secondary d-flex align-items-center p-3">
                                                    <div class="me-4">
                                                        <div class="lpdh-achievement-icon icon-lg <?php echo $bg_class; ?> shadow-sm" <?php echo $bg_style; ?>>
                                                            <i class="<?php echo esc_attr($badge['icon']); ?>"></i>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                                            <h5 class="mb-0 text-warning"><?php echo esc_html($badge['title']); ?></h5>
                                                            <small class="text-info"><?php echo esc_html($badge['date_unlocked']); ?></small>
                                                        </div>
                                                        <p class="mb-0 text-white-50 small"><?php echo esc_html($badge['description']); ?></p>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
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
                    <div class="row justify-content-center my-4 g-2">
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

            <?php endif; ?>

        </div>
    </main>
</div>



<?php get_footer(); ?>