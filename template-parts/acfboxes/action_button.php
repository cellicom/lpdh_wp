<?php
$containerclass = $args['acf_fc_layout'];
if ($args['visible']) {
    $icon = $args['icon'];
    $label = $args['label'];
    $link = $args['link'];
    
    // No title field in this layout, so hashtag might be generic based on index
    ?>

    <div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args, $args['acf_index']); ?>"></a>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center">
                    <?php if($link) { ?>
                        <a href="<?php echo esc_url($link); ?>" class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-sm">
                            <?php if($icon) { ?>
                                <img src="<?php echo esc_url($icon); ?>" alt="" class="me-2" style="height:24px; width:auto; vertical-align: middle;"> 
                            <?php } ?>
                            <span style="vertical-align: middle;"><?php echo esc_html($label); ?></span>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>