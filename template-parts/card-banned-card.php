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
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail('medium', array('class' => 'img-fluid rounded shadow-sm', 'style' => 'max-height: 150px; width: auto; object-fit: contain;')); ?>
            <?php else : ?>
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
                <div class="card-text text-muted">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
