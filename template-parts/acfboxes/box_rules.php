<?php
$containerclass = $args['acf_fc_layout'];
$cards = $args['cards'];
?>

<div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
    <a name="<?php echo getUrlHashtagFromAcfBox($args, $args['acf_index']); ?>"></a>
    <div class="container">
        <?php if($cards) { ?>
            <div class="row justify-content-center g-4">
                <?php foreach($cards as $card) { 
                    $type = $card['type']; // 'rules' or 'ban'
                    $icon = $card['icon'];
                    $title = $card['title'];
                    $rows = $card['rows'];
                    
                    // Stili condizionali basati sul tipo
                    $card_border = ($type === 'ban') ? 'border-danger' : 'border-0';
                    $card_bg = ($type === 'ban') ? 'bg-danger-subtle' : 'bg-white';
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm <?php echo esc_attr($card_border . ' ' . $card_bg); ?>">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if($icon) { ?>
                                        <img src="<?php echo esc_url($icon); ?>" class="me-3" alt="" style="width: 32px; height: 32px; object-fit: contain;">
                                    <?php } ?>
                                    <?php if($title) { ?>
                                        <h4 class="card-title h5 mb-0"><?php echo esc_html($title); ?></h4>
                                    <?php } ?>
                                </div>
                                <?php if($rows) { ?>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach($rows as $row) { 
                                            $text = $row['text'];
                                            $url = $row['url'];
                                            $link_inline = $row['link_inline'];
                                            ?>
                                            <li class="mb-2">
                                                <?php if($url && $link_inline) { ?>
                                                    <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($url); ?>" class="text-decoration-none text-body fw-medium"><?php echo wp_kses_post($text); ?></a>
                                                <?php } else { ?>
                                                    <?php echo wp_kses_post($text); ?>
                                                    <?php if($url) { ?>
                                                        <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($url); ?>" class="ms-1 text-decoration-none">&rarr;</a>
                                                    <?php } ?>
                                                <?php } ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
