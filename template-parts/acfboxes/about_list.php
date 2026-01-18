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

                $icon = $person['icon'] ?? '';
                $title = $person['title'] ?? '';
                $nickname = $person['nickname'] ?? '';
                $subtitle = $person['subtitle'] ?? '';
                $text = $person['text'] ?? '';
                $url_data = $person['url'] ?? '';
                $profile = $person['profile'] ?? '';

                // Fallback to profile data if fields are empty
                if ($profile) {
                    if (!$title)
                        $title = $profile['display_name'];
                    if (!$nickname)
                        $nickname = $profile['user_nicename'];
                    if (!$icon)
                        $icon = get_avatar_url($profile['ID']);
                }

                if (is_array($url_data)) {
                    $href = $url_data['url'] ?? '#';
                    $target = $url_data['target'] ?? '_self';
                } else {
                    $href = $url_data ?: '#';
                    $target = '_self';
                }
                $has_link = !empty($url_data);
                ?>
                <div class="col-12">
                    <?php if ($has_link): ?>
                        <a href="<?php echo esc_url($href); ?>" target="<?php echo esc_attr($target); ?>"
                            class="text-decoration-none">
                        <?php endif; ?>
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
                                            <h3 class="h5 mb-0 text-dark fw-bold"><?php echo esc_html($title); ?></h3>
                                        <?php endif; ?>
                                        <?php if ($nickname): ?>
                                            <span
                                                class="badge bg-light text-dark border !f-plantin">@<?php echo esc_html($nickname); ?></span>
                                        <?php endif; ?>
                                        </div>

                                        <?php if ($subtitle): ?>
                                            <div class="item-subtitle">
                                                <p class="text-primary mb-0 fw-medium"><?php echo esc_html($subtitle); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($text): ?>
                                            <div class="item-text">
                                                <p class="text-muted mb-0 !f-plantin"><?php echo nl2br(esc_html($text)); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($has_link): ?>
                                            <div class="item-arrow text-primary">
                                                <i class="fas fa-chevron-right fa-lg"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if ($has_link): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .about-card-grid {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 0.5rem 1.5rem;
        align-items: center;
    }

    .item-image {
        grid-area: img;
    }

    .item-header {
        grid-area: hdr;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .item-subtitle {
        grid-area: sub;
    }

    .item-text {
        grid-area: txt;
    }

    .item-arrow {
        grid-area: arr;
    }

    /* Desktop */
    @media (min-width: 992px) {
        .about-card-grid {
            grid-template-areas:
                "img hdr arr"
                "img sub arr"
                "img txt arr";
            align-items: start;
            gap: 0.25rem 1.5rem;
        }

        .item-arrow {
            align-self: center;
        }
    }

    /* Mobile & Tablet */
    @media (max-width: 991.98px) {
        .about-card-grid {
            grid-template-areas:
                "img hdr arr"
                "sub sub sub"
                "txt txt txt";
            gap: 0.75rem 1rem;
        }

        .item-header {
            flex-direction: column;
            align-items: start;
            gap: 0.25rem;
        }

        .item-image img {
            height: 60px !important;
            width: 60px !important;
        }
    }

    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }
</style>