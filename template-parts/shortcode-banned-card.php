<?php
/**
 * Template part for displaying banned card in shortcode
 */
$align = isset($args['align']) ? $args['align'] : 'left';
$row_class = 'row g-0';
if ($align === 'right') {
    $row_class .= ' flex-row-reverse';
}
?>
<div class="card mb-3 shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-white border-0 pb-0">
        <h5 class="card-title text-danger mb-0">
            <a href="<?php the_permalink(); ?>" class="text-danger text-decoration-none hover-underline">
                <strong><?php the_title(); ?></strong>
            </a>
        </h5>
    </div>
    <div class="card-body">
        <div class="<?php echo esc_attr($row_class); ?> align-items-center">
            <div class="col-4 col-sm-3 d-flex align-items-center justify-content-center">
                <a href="<?php the_permalink(); ?>" class="hover-lift d-block">
                    <?php
                    $image_html = '';
                    if (has_post_thumbnail()) {
                        $image_html = get_the_post_thumbnail(null, 'medium', array('class' => 'img-fluid rounded', 'style' => 'max-height: 200px; width: auto; object-fit: contain;'));
                    } else {
                        $scryfall_image_url = function_exists('lpdh_get_scryfall_image_url') ? lpdh_get_scryfall_image_url(get_the_ID()) : '';
                        if (!empty($scryfall_image_url) && $scryfall_image_url !== 'error') {
                            $image_html = '<img src="' . esc_url($scryfall_image_url) . '" class="img-fluid rounded p-2" alt="' . esc_attr(get_the_title()) . '" style="max-height: 200px; width: auto; object-fit: contain;">';
                        }
                    }

                    if (!empty($image_html)):
                        echo $image_html;
                    else: ?>
                        <div class="py-4 text-danger opacity-25">
                            <i class="fas fa-ban fa-3x"></i>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
            <div class="col-8 col-sm-9 <?php echo ($align === 'right' ? 'pe-3' : 'ps-3'); ?>">
                <div class="card-text small card-text-ellipsis !f-plantin">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
</div>