<?php
/**
 * Template part for displaying a deck card.
 *
 * @package Bootscore Child
 * @version 6.0.0
 *
 * @param array $args {
 *     @type bool $show_author Whether to show the author in the card footer. Default true.
 * }
 */

$show_author = $args['show_author'] ?? true;
?>
<div class="col-md-6 col-lg-4">
    <div class="card h-100 shadow-sm border-0 deck-card">
        <?php
        $commander_img = get_commander_image(get_the_ID());
        $partner_img = get_partner_image(get_the_ID());

        if ($partner_img): ?>
            <a href="<?php the_permalink(); ?>" class="d-flex overflow-hidden rounded-top deck-card-img-container">
                <img src="<?php echo esc_url($commander_img); ?>"
                    class="w-50 object-fit-cover transition-transform deck-card-img" alt="Commander">
                <img src="<?php echo esc_url($partner_img); ?>"
                    class="w-50 object-fit-cover transition-transform deck-card-img" alt="Partner">
            </a>
        <?php else: ?>
            <a href="<?php the_permalink(); ?>" class="d-block overflow-hidden rounded-top">
                <img src="<?php echo esc_url($commander_img); ?>"
                    class="card-img-top object-fit-cover transition-transform deck-card-single-img" alt="Commander">
            </a>
        <?php endif; ?>
        <div class="card-body">
            <h5 class="card-title mb-2">
                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark stretched-link">
                    <?php the_title(); ?>
                </a>
            </h5>
            <?php
            $commander = get_field('commander');
            $partner = get_field('partner');
            if ($commander): ?>
                <p class="card-text small text-muted mb-0">
                    <i class="fas fa-user-shield me-1"></i> <?php echo esc_html($commander); ?>
                    <?php if ($partner): ?>
                        <span class="mx-1">+</span> <?php echo esc_html($partner); ?>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
        </div>
        <?php if ($show_author): ?>
            <div class="card-footer bg-white border-top-0">
                <small class="text-muted">By <?php the_author_posts_link(); ?></small>
            </div>
        <?php endif; ?>
    </div>
</div>