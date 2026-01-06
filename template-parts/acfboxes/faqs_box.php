<?php
$containerclass = $args['acf_fc_layout'];
if ($args['visible']) {
    $title = $args['title'];
    $container_id = uniqid($containerclass . '_');
    
    // Prepare args for helper function which expects 'titolo'
    $args_for_hash = array_merge($args, ['titolo' => $title]);

    // Query FAQ CPT since the JSON layout does not have a repeater
    $faqs_query = new WP_Query(array(
        'post_type' => 'faq',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'menu_order',
        'order' => 'ASC'
    ));
    ?>

    <div class="container-fluid py-5 <?php echo esc_attr($containerclass); ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args_for_hash, $args['acf_index']); ?>"></a>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if(!empty($title)) { ?>
                        <div class="text-center mb-5">
                            <h2 class="section-title"><?php echo esc_html($title); ?></h2>
                        </div>
                    <?php } ?>
                    
                    <div class="accordion" id="<?php echo esc_attr($container_id); ?>">
                        <?php 
                        if ($faqs_query->have_posts()) {
                            $i = 0;
                            while($faqs_query->have_posts()) {
                                $faqs_query->the_post();
                                $faq_id = $container_id . '_faq_domanda_' . $i;
                                $faq_risposta_id = $container_id . '_faq_risposta_' . $i;
                                $domanda = get_the_title();
                                $risposta = get_the_content();
                                ?>
                                <div class="accordion-item mb-3 border rounded overflow-hidden">
                                    <h2 class="accordion-header" id="<?php echo esc_attr($faq_id); ?>">
                                        <button class="accordion-button collapsed fw-bold text-eblue" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr($faq_risposta_id); ?>" aria-expanded="false" aria-controls="<?php echo esc_attr($faq_risposta_id); ?>">
                                            <?php echo esc_html($domanda); ?>
                                        </button>
                                    </h2>
                                    <div id="<?php echo esc_attr($faq_risposta_id); ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo esc_attr($faq_id); ?>" data-bs-parent="#<?php echo esc_attr($container_id); ?>">
                                        <div class="accordion-body">
                                            <?php echo apply_filters('the_content', $risposta); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                $i++;
                            }
                            wp_reset_postdata();
                        } else {
                            ?>
                            <div class="alert alert-info text-center">
                                <?php esc_html_e('Nessuna FAQ disponibile al momento.', 'bootscore'); ?>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>