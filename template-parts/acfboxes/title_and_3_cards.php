<?php
$containerclass = $args['acf_fc_layout'];
if ($args['visible']) {
    $title = $args['title'];
    $cards = $args['cards'];

    // Prepare args for helper function which expects 'titolo'
    $args_for_hash = array_merge($args, ['titolo' => $title]);
    ?>

    <div class="container-fluid py-5 bg-light <?php echo esc_attr($containerclass); ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args_for_hash, $args['acf_index']); ?>"></a>
        <div class="container">
            <?php if ($title) { ?>
                <div class="row justify-content-center mb-5">
                    <div class="col-12 text-center">
                        <h2 class="section-title"><?php echo esc_html($title); ?></h2>
                    </div>
                </div>
            <?php } ?>

            <?php if ($cards) { ?>
                <div class="row justify-content-center g-4">
                    <?php foreach ($cards as $card) { ?>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0 bg-primary text-white">
                                <div class="card-body text-center p-4">
                                    <?php if (!empty($card['icons'])) { ?>
                                        <div class="mb-3">
                                            <img src="<?php echo esc_url($card['icons']); ?>" class="img-fluid icon-circle"
                                                alt="<?php echo esc_attr($card['title']); ?>"
                                                style="max-height: 64px; max-width: 64px; object-fit: contain;">
                                        </div>
                                    <?php } ?>
                                    <?php if (!empty($card['title'])) { ?>
                                        <h4 class="card-title mb-3 h5 text-dark"><?php echo esc_html($card['title']); ?></h4>
                                    <?php } ?>
                                    <?php if (!empty($card['text'])) { ?>
                                        <div class="card-text small !f-plantin"><?php echo wp_kses_post($card['text']); ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
?>