<?php
$containerclass = $args['acf_fc_layout'];
if ($args['visible']) {
    $title = $args['title'];
    $subtitle = $args['subtitle'];
    $text = $args['text'];
    
    // Prepare args for helper function which expects 'titolo'
    $args_for_hash = array_merge($args, ['titolo' => $title]);
    ?>

    <div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args_for_hash, $args['acf_index']); ?>"></a>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 text-center">
                    <?php if($title) { ?>
                        <h2 class="section-title mb-3"><?php echo esc_html($title); ?></h2>
                    <?php } ?>
                    <?php if($subtitle) { ?>
                        <h3 class="section-subtitle mb-4 text-muted h5"><?php echo esc_html($subtitle); ?></h3>
                    <?php } ?>
                    <?php if($text) { ?>
                        <div class="section-text"><?php echo wp_kses_post($text); ?></div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
