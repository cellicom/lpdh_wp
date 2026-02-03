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

    <!-- Remaining 7 Places (2nd to 8th) -->
    <div class="top8-grid">
        <?php 
        for ($i = 1; $i < 8; $i++):
            if (isset($players_data[$i])):
                $player = $players_data[$i];
                $has_partner = !empty($player['partner_img']);
                $pos_label = ($i + 1) . 'TH PLACE';
                if ($i == 1) $pos_label = '2ND PLACE';
                if ($i == 2) $pos_label = '3RD PLACE';
                
                $class = 'place-' . ($i + 1);
                if ($i == 1) $class .= ' silver';
                if ($i == 2) $class .= ' bronze';
        ?>
            <div class="top8-item <?php echo $class; ?>">
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
    margin-bottom: 20px;
}

.top8-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 10px;
    padding: 0 40px;
}

/* Make 5-8 slightly smaller or adjust grid */
.top8-grid .top8-item {
    background: rgba(0, 0, 0, 0.3);
    padding: 10px;
    border-radius: 8px;
    text-align: center;
}

.top8-grid .place-cards-wrapper {
    margin-bottom: 8px;
}

.top8-grid .place-card {
    width: 60px;
    height: 84px;
}

.top8-grid .place-cards-wrapper.dual .place-card {
    width: 35px;
    height: 49px;
}

.top8-grid .place-player {
    font-size: 13px !important;
}

.top8-grid .place-commanders {
    font-size: 10px !important;
}

.top8-grid .place-position {
    font-size: 10px !important;
}

/* Special handling for the grid to fit 7 items */
/* Row 1: 2nd, 3rd, 4th */
/* Row 2: 5th, 6th, 7th, 8th */
@supports (display: grid) {
    .top8-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
    }
    .top8-item.place-2, .top8-item.place-3, .top8-item.place-4 {
        grid-column: span 4;
    }
    .top8-item.place-5, .top8-item.place-6, .top8-item.place-7, .top8-item.place-8 {
        grid-column: span 3;
    }
}
</style>
