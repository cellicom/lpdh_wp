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
        <!-- First Place (Large) -->
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

    <!-- Middle Row (2nd and 3rd - Medium) -->
    <div class="bottom-three top8-medium-row">
        <?php 
        $med_positions = array(
            1 => array('class' => 'silver', 'label' => '2ND PLACE'),
            2 => array('class' => 'bronze', 'label' => '3RD PLACE')
        );
        
        for ($i = 1; $i <= 2; $i++):
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

    <!-- Small Footer Row (4th to 8th - Small) -->
    <div class="top8-footer-row">
        <?php 
        for ($i = 3; $i < 8; $i++):
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
.top8-podium {
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
}

.top8-podium .first-place {
    margin-bottom: 30px;
}

.top8-medium-row {
    margin-bottom: 35px !important;
    justify-content: center !important;
    gap: 50px !important;
}

.top8-footer-row {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 0 20px;
    margin-top: auto;
    margin-bottom: 15px;
}

.top8-small-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 170px;
}

/* Small Cards Scaling */
.top8-footer-row .place-card {
    width: 60px;
    height: 84px;
}

.top8-footer-row .place-cards-wrapper.dual .place-card {
    width: 35px;
    height: 49px;
}

/* Small frames consistent with larger ones but scaled */
.top8-footer-row .place-card::after {
    border-width: 3px; 
}

/* Info scaling for footer */
.top8-footer-row .place-player {
    font-size: 13px !important;
    margin-top: 4px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    white-space: nowrap;
}

.top8-footer-row .place-commanders {
    font-size: 10px !important;
    opacity: 0.9;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.top8-footer-row .place-position {
    font-size: 8px !important;
    font-weight: 800;
    letter-spacing: 0.05em;
    margin-top: 1px;
}

/* Adjust colors for 1st place in 3+5 row */
.gold.place-item .place-position { color: #ffd700; text-shadow: 0 0 15px rgba(255, 215, 0, 0.6); }
.silver.place-item .place-position { color: #c0c0c0; }
.bronze.place-item .place-position { color: #cd7f32; }

/* Theme fixes */
.instagram-vaporwave .top8-footer-row .place-player { color: #fff; }
</style>
