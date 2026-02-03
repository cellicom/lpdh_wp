<?php
/**
 * Template Part: Top 8 Instagram Layout
 * 
 * Expected variables:
 * - $players_data: Array of top 8 players data
 * - $event_title, $event_date, $formatted_date, $place_name
 */

if (!isset($players_data) || empty($players_data)) {
    return;
}
?>

<!-- Podium Top 8 -->
<div class="podium top8-podium">
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

    <!-- Medium Row (2nd to 4th) -->
    <div class="bottom-three top8-medium-row">
        <?php 
        $med_positions = array(
            1 => array('class' => 'silver', 'label' => '2ND PLACE'),
            2 => array('class' => 'bronze', 'label' => '3RD PLACE'),
            3 => array('class' => 'fourth', 'label' => '4TH PLACE')
        );
        
        for ($i = 1; $i <= 3; $i++):
            if (isset($players_data[$i])):
                $player = $players_data[$i];
                $has_partner = !empty($player['partner_img']);
        ?>
            <div class="place-item <?php echo $med_positions[$i]['class']; ?>">
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
                    <div class="place-position"><?php echo $med_positions[$i]['label']; ?></div>
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

    <!-- Small Footer Row (5th to 8th) -->
    <div class="top8-footer-row">
        <?php 
        for ($i = 4; $i < 8; $i++):
            if (isset($players_data[$i])):
                $player = $players_data[$i];
                $has_partner = !empty($player['partner_img']);
                $pos_label = ($i + 1) . 'TH PLACE';
        ?>
            <div class="top8-small-item">
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
                    <div class="place-position"><?php echo $pos_label; ?></div>
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

<style>
.top8-podium .first-place {
    margin-bottom: 30px;
}

.top8-medium-row {
    margin-bottom: 40px !important;
}

.top8-footer-row {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 0 40px;
}

.top8-small-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 200px;
}

/* Small Cards Scaling */
.top8-footer-row .place-card {
    width: 65px;
    height: 91px;
}

.top8-footer-row .place-cards-wrapper.dual .place-card {
    width: 40px;
    height: 56px;
}

/* Base styles for ornate frames in small items */
.top8-footer-row .place-card::after {
    border-width: 4px; /* Slightly thinner frames for small items */
}

/* Info scaling for footer */
.top8-footer-row .place-player {
    font-size: 14px !important;
    margin-top: 5px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.top8-footer-row .place-commanders {
    font-size: 11px !important;
    opacity: 0.9;
}

.top8-footer-row .place-position {
    font-size: 10px !important;
    font-weight: 800;
    letter-spacing: 0.05em;
    margin-top: 2px;
}

/* Lost Wood and Vaporwave specific adjustments for footer */
.instagram-lostwood .top8-footer-row .place-player,
.instagram-lostwood .top8-footer-row .place-commanders {
    color: #f8f1e5;
}

.instagram-vaporwave .top8-footer-row .place-player,
.instagram-vaporwave .top8-footer-row .place-commanders {
    color: #fff;
    text-shadow: 0 0 5px rgba(255, 0, 255, 0.5);
}
</style>
