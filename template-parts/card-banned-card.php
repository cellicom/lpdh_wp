<?php
/**
 * Template part for displaying banned card
 *
 * @package Bootscore Child
 */

$combined_with = get_field('combined_with'); // array of WP_Post or false
$has_combined  = !empty($combined_with);

// Column widths: wider image col when combined cards are present
$col_img  = $has_combined ? 'col-6 col-sm-5' : 'col-4 col-sm-3';
$col_body = $has_combined ? 'col-6 col-sm-7' : 'col-8 col-sm-9';

// Max height for card images
$img_h = $has_combined ? '240px' : '140px';
?>
<div class="card mb-3 shadow-sm border-0 hover-lift overflow-hidden">
    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-reset">
        <div class="row g-0">
            <div class="<?php echo esc_attr($col_img); ?> bg-light d-flex align-items-center justify-content-center p-1" style="overflow:hidden; min-width:0;">
                <?php
                $img_style = 'max-height:' . $img_h . '; width:auto; object-fit:contain; flex-shrink:1; min-width:0;';
                $main_img  = lpdh_banned_card_image_html(get_the_ID(), 'medium', 'img-fluid rounded-start');

                if ($has_combined) {
                    echo '<div class="d-flex align-items-center justify-content-center w-100" style="gap:4px; min-width:0; overflow:hidden;">';

                    // Main image
                    if ($main_img) {
                        echo preg_replace('/<img/', '<img style="' . esc_attr($img_style) . '"', $main_img, 1);
                    } else {
                        echo '<div class="py-2 text-danger opacity-25"><i class="fas fa-ban fa-2x"></i></div>';
                    }

                    // Combined images with "+" separator
                    foreach ($combined_with as $cw_post) {
                        $cw_img = lpdh_banned_card_image_html($cw_post->ID, 'medium', 'img-fluid');
                        if ($cw_img) {
                            echo '<span class="text-danger fw-bold" style="font-size:1.2rem; line-height:1; flex-shrink:0;">+</span>';
                            echo preg_replace('/<img/', '<img style="' . esc_attr($img_style) . '"', $cw_img, 1);
                        }
                    }

                    echo '</div>';
                } else {
                    if ($main_img) {
                        echo $main_img;
                    } else { ?>
                        <div class="py-4 text-danger opacity-25">
                            <i class="fas fa-ban fa-3x"></i>
                        </div>
                    <?php }
                }
                ?>
            </div>
            <div class="<?php echo esc_attr($col_body); ?>">
                <div class="card-body h-100 d-flex flex-column justify-content-center">
                    <h5 class="card-title text-danger mb-1">
                        <strong><?php the_title(); ?></strong>
                        <?php if ($has_combined):
                            $cw_names = array_map(function($p) { return get_the_title($p->ID); }, $combined_with);
                            echo '<span class="text-danger fw-normal" style="font-size:.8em;"> + ' . esc_html(implode(' + ', $cw_names)) . '</span>';
                        endif; ?>
                    </h5>
                    <div class="card-text small card-text-ellipsis !f-plantin">
                        <?php echo wp_trim_words(get_the_content(), 20, '...'); ?>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>