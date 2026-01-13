<?php
/**
 * Template part for displaying banned card in a list
 *
 * @package Bootscore Child
 */

$scryfall_link = get_field('scryfall_link');
?>
<div class="card mb-3 border-0 shadow-sm overflow-hidden hover-lift">
    <div class="row g-0">
        <div class="col-md-3 col-lg-2 bg-light d-flex align-items-center justify-content-center p-3">
            <?php
            $image_html = '';
            if ( has_post_thumbnail() ) {
                $image_html = get_the_post_thumbnail(null, 'medium', array('class' => 'img-fluid rounded shadow-sm', 'style' => 'max-height: 150px; width: auto; object-fit: contain;'));
            } else {
                $scryfall_image_url = function_exists('lpdh_get_scryfall_image_url') ? lpdh_get_scryfall_image_url(get_the_ID()) : '';
                if ( !empty($scryfall_image_url) && $scryfall_image_url !== 'error' ) {
                    $image_html = '<img src="' . esc_url($scryfall_image_url) . '" class="img-fluid rounded shadow-sm" style="max-height: 150px; width: auto; object-fit: contain;" alt="' . esc_attr(get_the_title()) . '">';
                }
            }

            if ( !empty($image_html) ) :
                echo $image_html;
            else : ?>
                <i class="fas fa-ban fa-3x text-danger opacity-25"></i>
            <?php endif; ?>
        </div>
        <div class="col-md-9 col-lg-10">
            <div class="card-body h-100 d-flex flex-column justify-content-center">
                <h3 class="card-title h5 mb-2 text-danger fw-bold">
                    <?php if($scryfall_link): ?>
                        <a href="<?php echo esc_url($scryfall_link); ?>" target="_blank" rel="noopener" class="text-decoration-none text-danger">
                            <?php the_title(); ?> <i class="fas fa-external-link-alt fa-xs ms-1 opacity-50"></i>
                        </a>
                    <?php else: ?>
                        <?php the_title(); ?>
                    <?php endif; ?>
                </h3>
                <div class="card-text text-muted card-text-ellipsis">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
