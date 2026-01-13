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
                <div class="col-md-8 col-lg-6 mt-5">
                    <div class="card shadow-sm border-danger overflow-visible hover-lift">
                        <?php if($icon) { ?>
                            <div class="position-absolute top-0 start-50 translate-middle z-1">
                                <div class="bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                    <img src="<?php echo esc_url($icon); ?>" alt="" style="width: 40px; height: 40px; object-fit: contain;">
                                </div>
                            </div>
                        <?php } ?>
                        
                        <div class="card-body p-0 d-flex flex-column">
                            <div class="text-center pt-5 pb-3 px-3 border-bottom">
                                <?php if($title) { ?>
                                    <h4 class="card-title h5 mb-0 mt-3 text-danger fw-bold"><?php echo esc_html($title); ?></h4>
                                <?php } ?>
                            </div>

                            <div class="p-4 text-center flex-grow-1">
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
    </div>
    <style>
    .hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .hover-lift:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
    <?php
}
?>
