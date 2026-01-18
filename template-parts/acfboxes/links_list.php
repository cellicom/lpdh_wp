<?php
$containerclass = $args['acf_fc_layout'];
$links = $args['links'];
?>

<div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
    <a name="<?php echo getUrlHashtagFromAcfBox($args, $args['acf_index']); ?>"></a>
    <div class="container">
        <?php if ($links) { ?>
            <div class="row justify-content-center g-4">
                <?php foreach ($links as $link) {
                    $icon = $link['icon'];
                    $title = $link['title'];
                    $subtitle = $link['subtitle'];
                    $url = $link['url'];
                    ?>
                    <div class="col-12 col-lg-6">
                        <?php if ($url) { ?><a href="<?php echo esc_url($url); ?>" class="text-decoration-none text-reset"
                                target="_blank" rel="noopener noreferrer"><?php } ?>
                            <div class="card h-100 shadow-sm border-0 bg-light transition-hover">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start">
                                        <?php if ($icon) { ?>
                                            <div class="flex-shrink-0 me-3">
                                                <img src="<?php echo esc_url($icon); ?>" alt=""
                                                    style="width: 48px; height: 48px; object-fit: contain;">
                                            </div>
                                        <?php } ?>
                                        <div>
                                            <?php if ($title) { ?>
                                                <h5 class="card-title fw-bold mb-2"><?php echo esc_html($title); ?></h5>
                                            <?php } ?>
                                            <?php if ($subtitle) { ?>
                                                <div class="card-text text-muted small !f-plantin">
                                                    <?php echo nl2br(esc_html($subtitle)); ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($url) { ?>
                            </a><?php } ?>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>