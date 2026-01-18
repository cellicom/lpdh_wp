<?php
if ($args['visible']) {
    // Get ACF fields for this layout
    $containerclass = $args['acf_fc_layout'];
    $icon = $args['icon'];
    $title = $args['title'];

    // Prepare args for hashtag function
    $args_for_hash = array_merge($args, ['titolo' => $title]);

    // WP_Query for 'banned_card' CPT
    $banned_cards_query = new WP_Query([
        'post_type' => 'banned_card',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    ?>

    <div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args_for_hash, $args['acf_index']); ?>"></a>
        <div class="container">
            <?php if ($title) { ?>
                <div class="row justify-content-center mb-5">
                    <div class="col-12 text-center">
                        <?php if ($icon) { ?>
                            <img src="<?php echo esc_url($icon); ?>" alt="" style="width: 60px; height: 60px; object-fit: contain;"
                                class="mb-3">
                        <?php } ?>
                        <h2 class="section-title text-danger fw-bold"><?php echo esc_html($title); ?></h2>
                    </div>
                </div>
            <?php } ?>

            <?php if ($banned_cards_query->have_posts()): ?>
                <div class="banned-cards-list mx-auto" style="max-width: 900px;">
                    <?php while ($banned_cards_query->have_posts()):
                        $banned_cards_query->the_post(); ?>
                        <?php get_template_part('template-parts/card', 'banned-card'); ?>
                    <?php endwhile; ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else: ?>
                <div class="text-center">
                    <p class="text-muted mb-0"><?php esc_html_e('Nessuna carta in questa lista al momento.', 'bootscore'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php
}
?>