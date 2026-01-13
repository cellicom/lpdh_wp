<?php
// var_dump($args);
$containerclass = $args['acf_fc_layout'];
if ($args['visibile'] === true) {
    $boxtitolo = $args['titolo'];
    $faqs = $args['faq'];
    $container_id = uniqid($containerclass . '_');
    ?>

    <div class="container-fluid <?php echo $containerclass; ?>">
        <a name="<?php echo getUrlHashtagFromAcfBox($args, $args['acf_index']); ?>"></a>
        <div class="row justify-content-center">
            <div class="container justify-content-center">
                <div class="row justify-content-center">
                    <?php if(!empty($boxtitolo)) { ?>
                        <h3 class="box-title mb-4"><?php echo $boxtitolo; ?></h3>
                    <?php } ?>
                </div>
                <div class="row justify-content-center">
                    <div class="accordion" id="<?php echo $container_id; ?>">
                        <?php foreach($faqs as $key => $faq) {
                            $faq_id = $container_id . '_faq_domanda_' . $key;
                            $faq_risposta_id = $container_id . '_faq_risposta_' . $key;
                            $domanda = $faq['domanda'];
                            $risposta = $faq['risposta'];
                        ?>

                        <div class="accordion-item">
                            <div class="accordion-header" id="<?php echo $faq_id; ?>">
                              <h2>
                                <button class="accordion-button collapsed text-18px text-eblue" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $faq_risposta_id; ?>" aria-expanded="false" aria-controls="<?php echo $faq_risposta_id; ?>">
                                    <?php echo $domanda; ?>
                                </button>
                              </h2>
                                <div id="<?php echo $faq_risposta_id; ?>" class="accordion-collapse collapse"
                                     role="region" aria-labelledby="<?php echo $faq_id; ?>">
                                    <div class="accordion-body text-18px">
                                        <?php echo $risposta; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php
}
?>
