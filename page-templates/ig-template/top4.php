<?php
/**
 * Template Part: Top 4 Instagram Layout
 * 
 * Expected variables:
 * - $players_data: Array of top 4 players data
 * - $event_title, $event_date, $formatted_date, $place_name
 */

if (!isset($players_data) || empty($players_data)) {
    return;
}
?>

<!-- Podium -->
<div class="podium">
    <?php if (isset($players_data[0])): 
        $first = $players_data[0];
        $has_partner = !empty($first['partner_img']);
    ?>
        <!-- First Place -->
        <div class="first-place">
            <div class="first-place-cards-wrapper <?php echo $has_partner ? 'dual' : ''; ?>">
                <?php if ($first['commander_img']): ?>
                    <div class="first-place-card">
                        <img src="<?php echo esc_attr($first['commander_img']); ?>" alt="Commander">
                    </div>
                    <?php if ($has_partner && $first['partner_img']): ?>
                        <div class="first-place-card">
                            <img src="<?php echo esc_attr($first['partner_img']); ?>" alt="Partner">
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="first-place-info">
                <div class="first-place-position">1ST PLACE</div>
                <div class="first-place-player"><?php echo esc_html($first['player_name']); ?></div>
                <div class="first-place-commanders">
                    <?php 
                    echo esc_html($first['commander_name']);
                    if ($first['partner_name']) {
                        echo ' / ' . esc_html($first['partner_name']);
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bottom Three Places -->
    <div class="bottom-three">
        <?php 
        $positions = array(
            1 => array('class' => 'silver', 'label' => '2ND PLACE'),
            2 => array('class' => 'bronze', 'label' => '3RD PLACE'),
            3 => array('class' => 'fourth', 'label' => '4TH PLACE')
        );
        
        for ($i = 1; $i <= 3; $i++):
            if (isset($players_data[$i])):
                $player = $players_data[$i];
                $has_partner = !empty($player['partner_img']);
        ?>
            <div class="place-item <?php echo $positions[$i]['class']; ?>">
                <div class="place-cards-wrapper <?php echo $has_partner ? 'dual' : ''; ?>">
                    <?php if ($player['commander_img']): ?>
                        <div class="place-card">
                            <img src="<?php echo esc_attr($player['commander_img']); ?>" alt="Commander">
                        </div>
                        <?php if ($has_partner && $player['partner_img']): ?>
                            <div class="place-card">
                                <img src="<?php echo esc_attr($player['partner_img']); ?>" alt="Partner">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="place-info">
                    <div class="place-position"><?php echo $positions[$i]['label']; ?></div>
                    <div class="place-player"><?php echo esc_html($player['player_name']); ?></div>
                    <div class="place-commanders">
                        <?php 
                        echo esc_html($player['commander_name']);
                        if ($player['partner_name']) {
                            echo ' / ' . esc_html($player['partner_name']);
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php 
            endif;
        endfor; 
        ?>
    </div>
</div>
