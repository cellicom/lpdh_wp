<?php
$acf = get_field('sections');
if(!empty($acf)) {
     foreach ($acf as $sectionkey => $section) {
        ?>
        <div class="acf-section-box acf-section-box-<?php echo $sectionkey; ?>">
        <?php
            if (!empty($section['box'])) {
                foreach ($section['box'] as $key => $box) {
                    $box['acf_index'] = $key;
                    ?>
                    <div class="acf-box" data-acf-box="<?php echo $box['acf_fc_layout']; ?>">
                    <?php
                        $template = 'template-parts/acfboxes/' . $box['acf_fc_layout'];
                        $exist = get_template_part($template, $box['acf_fc_layout'], $box);
                        if ($exist === false) {
                            echo $box['acf_fc_layout'] . " not exist<br><hr><br>";
                        }
                    ?>
                    </div>
                    <?php
                }
            }
        ?>
        </div>
        <?php
    }
}
