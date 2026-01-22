<?php
$containerclass = $args['acf_fc_layout'];
$cards = $args['cards'];
?>

<div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
    <a name="<?php echo getUrlHashtagFromAcfBox($args, $args['acf_index']); ?>"></a>
    <div class="container">
        <?php if ($cards) { ?>
            <div class="row justify-content-center g-4">
                <?php foreach ($cards as $card) {
                    $type = $card['type']; // 'rules' or 'ban'
                    $icon = $card['icon'];
                    $title = $card['title'];
                    $rows = $card['rows'];

                    // Stili condizionali basati sul tipo
                    $card_border = ($type === 'ban') ? 'border-danger' : 'border-0';
                    $card_bg = ($type === 'ban') ? 'bg-danger-subtle' : 'bg-white';
                    ?>
                    <div class="col-md-6 col-lg-4 mt-5">
                        <div
                            class="card h-100 shadow-sm <?php echo esc_attr($card_border . ' ' . $card_bg); ?> overflow-visible hover-lift">
                            <?php if ($icon) { ?>
                                <div class="position-absolute top-0 start-50 translate-middle z-1">
                                    <div class="bg-secondary rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                                        style="width: 80px; height: 80px;">
                                        <img src="<?php echo esc_url($icon); ?>" alt=""
                                            style="width: 40px; height: 40px; object-fit: contain;">
                                    </div>
                                </div>
                            <?php } ?>

                            <div class="card-body p-0 d-flex flex-column">
                                <div class="text-center pt-5 pb-3 px-3 border-bottom">
                                    <?php if ($title) { ?>
                                        <h4 class="card-title h5 mb-0 mt-3"><?php echo esc_html($title); ?></h4>
                                    <?php } ?>
                                </div>

                                <div class="p-4 text-center flex-grow-1">
                                    <?php if ($rows) { ?>
                                        <ul class="list-unstyled mb-0">
                                            <?php foreach ($rows as $row) {
                                                $text = $row['text'];
                                                $url = $row['url'];
                                                $link_inline = $row['link_inline'];
                                                ?>
                                                <li class="mb-2 !f-plantin">
                                                    <?php if ($url && $link_inline) { ?>
                                                        <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($url); ?>"
                                                            class="text-decoration-none text-body fw-medium !f-plantin"><?php echo wp_kses_post($text); ?></a>
                                                    <?php } else { ?>
                                                        <?php echo wp_kses_post($text); ?>
                                                        <?php if ($url) { ?>
                                                            <a target="_blank" rel="noopener noreferrer" href="<?php echo esc_url($url); ?>"
                                                                class="ms-1 text-decoration-none">&rarr;</a>
                                                        <?php } ?>
                                                    <?php } ?>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>