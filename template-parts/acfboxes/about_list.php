<?php
$visible = $args['visible'] ?? true;
if (!$visible)
    return;

$people = $args['people'] ?? [];
?>
<div class="container py-4">
    <?php if ($people): ?>
        <div class="row g-4">
            <?php foreach ($people as $person):
                if (!($person['visible'] ?? true))
                    continue;

                $profile_id = $person['profile'] ?? 0;
                if (!$profile_id) continue;

                $user = get_userdata($profile_id);
                if (!$user) continue;

                $first_name = get_user_meta($profile_id, 'first_name', true);
                $last_name = get_user_meta($profile_id, 'last_name', true);
                $full_name = trim($first_name . ' ' . $last_name);
                if (!$full_name) {
                    $full_name = $user->display_name;
                }

                $icon = get_avatar_url($profile_id, ['size' => 160]);
                $title = $full_name;
                $nickname = get_user_meta($profile_id, 'nickname', true) ?: $user->user_login;
                $subtitle = $person['subtitle'] ?? '';
                $text = $person['text'] ?? '';
                $href = lpdh_get_user_profile_url($profile_id);
                
                ?>
                <div class="col-12">
                    <a href="<?php echo esc_url($href); ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm hover-lift transition-base">
                            <div class="card-body p-4">
                                <div class="about-card-grid">
                                    <?php if ($icon): ?>
                                        <div class="item-image">
                                            <img src="<?php echo esc_url($icon); ?>" alt="" class="rounded-circle"
                                                style="height: 80px; width: 80px; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>

                                    <div class="item-header">
                                        <?php if ($title): ?>
                                            <h3 class="h5 mb-0 text-white fw-bold"><?php echo esc_html($title); ?></h3>
                                        <?php endif; ?>
                                        <?php if ($nickname): ?>
                                            <span
                                                class="badge bg-dark text-white border !f-plantin">@<?php echo esc_html($nickname); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($subtitle): ?>
                                        <div class="item-subtitle">
                                            <p class="text-primary mb-0 fw-medium"><?php echo esc_html($subtitle); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($text): ?>
                                        <div class="item-text">
                                            <p class="mb-0 !f-plantin"><?php echo nl2br(esc_html($text)); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="item-arrow text-primary">
                                        <i class="fas fa-chevron-right fa-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>