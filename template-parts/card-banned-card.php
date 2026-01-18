<?php
/**
 * Template part for displaying banned card
 *
 * @package Bootscore Child
 */
?>
<div class="card mb-3 shadow-sm border-0 hover-lift overflow-hidden">
    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-reset">
        <div class="row g-0">
            <div class="col-4 col-sm-3 bg-light d-flex align-items-center justify-content-center">
                <?php
                $image_html = '';
                if (has_post_thumbnail()) {
                    $image_html = get_the_post_thumbnail(null, 'medium', array('class' => 'img-fluid rounded-start', 'style' => 'max-height: 140px; width: auto; object-fit: contain;'));
                } else {
                    $scryfall_image_url = function_exists('lpdh_get_scryfall_image_url') ? lpdh_get_scryfall_image_url(get_the_ID()) : '';
                    if (!empty($scryfall_image_url) && $scryfall_image_url !== 'error') {
                        $image_html = '<img src="' . esc_url($scryfall_image_url) . '" class="img-fluid rounded-start p-2" alt="' . esc_attr(get_the_title()) . '" style="max-height: 140px; width: auto; object-fit: contain;">';
                    }
                }

                if (!empty($image_html)):
                    echo $image_html;
                else: ?>
                    <div class="py-4 text-danger opacity-25">
                        <i class="fas fa-ban fa-3x"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-8 col-sm-9">
                <div class="card-body h-100 d-flex flex-column justify-content-center">
                    <h5 class="card-title text-danger mb-1"><strong><?php the_title(); ?></strong></h5>
                    <div class="card-text small text-muted card-text-ellipsis !f-plantin">
                        <?php echo wp_trim_words(get_the_content(), 20, '...'); ?>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>