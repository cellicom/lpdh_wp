<?php
/**
 * Template part for displaying banned card in shortcode
 */
$align = isset($args['align']) ? $args['align'] : 'left';
$row_class = 'row g-0';
if ($align === 'right') {
    $row_class .= ' flex-row-reverse';
}

$combined_with = get_field('combined_with'); // array of WP_Post objects or false
$has_combined  = !empty($combined_with);
?>
<div class="card mb-3 shadow-sm border-0 overflow-hidden">
    <div class="card-header bg-white border-0 pb-0">
        <h5 class="card-title text-danger mb-0">
            <a href="<?php the_permalink(); ?>" class="text-danger text-decoration-none hover-underline">
                <strong><?php the_title(); ?></strong>
            </a>
            <?php if ($has_combined):
                $cw_names = array_map(function($p) { return get_the_title($p->ID); }, $combined_with);
                echo '<span class="text-muted fw-normal" style="font-size:.8em;"> + ' . esc_html(implode(' + ', $cw_names)) . '</span>';
            endif; ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="<?php echo esc_attr($row_class); ?> align-items-center">
            <div class="col-4 col-sm-3 d-flex align-items-start justify-content-center gap-2 flex-wrap">
                <a href="<?php the_permalink(); ?>" class="hover-lift d-block">
                    <?php
                    // Helper reuse (defined in card-banned-card.php or single-banned_card.php)
                    $main_img = lpdh_banned_card_image_html(get_the_ID(), 'medium', 'img-fluid rounded');
                    if ($main_img) {
                        echo $main_img;
                    } else { ?>
                        <div class="py-4 text-danger opacity-25">
                            <i class="fas fa-ban fa-3x"></i>
                        </div>
                    <?php }
                    ?>
                </a>
                <?php if ($has_combined):
                    foreach ($combined_with as $cw_post):
                        $cw_img = lpdh_banned_card_image_html($cw_post->ID);
                        if ($cw_img) {
                            echo '<a href="' . esc_url(get_permalink($cw_post->ID)) . '" class="hover-lift d-block">' . $cw_img . '</a>';
                        }
                    endforeach;
                endif; ?>
            </div>
            <div class="col-8 col-sm-9 <?php echo ($align === 'right' ? 'pe-3' : 'ps-3'); ?>">
                <div class="card-text small card-text-ellipsis !f-plantin">
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </div>
</div>