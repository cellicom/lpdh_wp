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
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    ?>

    <div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args_for_hash, $args['acf_index']); ?>"></a>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-danger">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-4">
                                <?php if($icon) { ?>
                                    <img src="<?php echo esc_url($icon); ?>" class="me-3" alt="" style="width: 32px; height: 32px; object-fit: contain;">
                                <?php } ?>
                                <?php if($title) { ?>
                                    <h4 class="card-title h5 mb-0 text-danger fw-bold"><?php echo esc_html($title); ?></h4>
                                <?php } ?>
                            </div>

                            <?php if ($banned_cards_query->have_posts()) : ?>
                                <ul class="list-unstyled mb-0">
                                    <?php while($banned_cards_query->have_posts()) : $banned_cards_query->the_post(); ?>
                                        <?php $scryfall_link = get_field('scryfall_link'); ?>
                                        <li class="mb-2">
                                            <a href="<?php echo esc_url($scryfall_link); ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-body fw-medium">
                                                <?php the_title(); ?>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                                <?php wp_reset_postdata(); ?>
                            <?php else : ?>
                                <p class="text-muted mb-0"><?php esc_html_e('Nessuna carta in questa lista al momento.', 'bootscore'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
