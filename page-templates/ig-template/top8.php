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
    <!-- Top Row (1st, 2nd, 3rd) -->
    <div class="bottom-three top8-top-row">
        <?php 
        $top_three_pos = array(
            0 => array('class' => 'gold first-place-fix', 'label' => '1ST PLACE'),
            1 => array('class' => 'silver', 'label' => '2ND PLACE'),
            2 => array('class' => 'bronze', 'label' => '3RD PLACE')
        );
        
        for ($i = 0; $i <= 2; $i++):
            if (isset($players_data[$i])):
                $player = $players_data[$i];
                $has_partner = !empty($player['partner_img']);
                $is_first = ($i === 0);
        ?>
            <div class="place-item <?php echo $top_three_pos[$i]['class']; ?>">
                <div class="place-cards-wrapper <?php echo $has_partner ? 'dual' : ''; ?>">
                    <?php if ($player['commander_img']): ?>
                        <div class="place-card <?php echo $is_first ? 'first-place-card-style' : ''; ?>">
                            <img src="<?php echo esc_attr($player['commander_img']); ?>" alt="Commander">
                        </div>
                        <?php if ($has_partner && $player['partner_img']): ?>
                            <div class="place-card <?php echo $is_first ? 'first-place-card-style' : ''; ?>">
                                <img src="<?php echo esc_attr($player['partner_img']); ?>" alt="Partner">
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="place-info">
                    <div class="place-position"><?php echo $top_three_pos[$i]['label']; ?></div>
                    <div class="place-player <?php echo $is_first ? 'first-place-player-fix' : ''; ?>"><?php echo esc_html($player['player_name']); ?></div>
                    <div class="place-commanders <?php echo $is_first ? 'first-place-commanders-fix' : ''; ?>">
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

    <!-- Small Footer Row (4th to 8th) -->
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

.top8-top-row {
    margin-bottom: 60px !important;
    justify-content: center !important;
    gap: 40px !important;
    margin-top: 0 !important;
}

/* Adjust 1st place in top row to be more prominent */
.first-place-fix .place-card {
    width: 180px;
    height: 250px;
}
.first-place-fix .place-cards-wrapper.dual .place-card {
    width: 110px;
    height: 154px;
}

/* Adjust 2nd/3rd to standard size if needed but they use default */

.top8-footer-row {
    display: flex;
    justify-content: center;
    gap: 15px;
    padding: 0 40px;
    margin-top: auto;
    margin-bottom: 20px;
}

.top8-small-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 180px;
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
    border-width: 3px; 
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

/* Adjust colors for 1st place in 3+5 row */
.gold.place-item .place-position { color: #ffd700; text-shadow: 0 0 15px rgba(255, 215, 0, 0.6); }
.silver.place-item .place-position { color: #c0c0c0; }
.bronze.place-item .place-position { color: #cd7f32; }

/* Theme fixes */
.instagram-vaporwave .top8-footer-row .place-player { color: #fff; }
</style>

