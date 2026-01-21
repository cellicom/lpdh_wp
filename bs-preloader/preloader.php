<?php
/**
 * Custom Template for Bootscore Preloader
 */
?>
<?php
$active_theme = get_option('lpdh_active_theme', 'default');

if ($active_theme === 'vaporwave' || $active_theme === 'vaporwave-green'): ?>
    <!-- Preloader Windows 95 -->
    <div id="preloader" class="w95-preloader-wrapper">
        <div class="w95-window">
            <div class="w95-title-bar">
                <div class="w95-title-text">
                    <img src="<?= get_stylesheet_directory_uri(); ?>/assets/img/logo/logo-lpdh-transparent.png" alt="Icon"
                        width="16" height="16" style="margin-right: 5px;">
                    System Loading...
                </div>
                <div class="w95-title-controls">
                    <div class="w95-btn-control">_</div>
                    <div class="w95-btn-control">□</div>
                    <div class="w95-btn-control">X</div>
                </div>
            </div>
            <div class="w95-window-body">
                <p class="mb-2 text-center">Loading
                    <?php echo esc_html(get_bloginfo('name')); ?>...
                </p>
                <div class="w95-progress-track">
                    <div class="w95-progress-fill"></div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Standard Preloader -->
    <div id="preloader"
        class="<?= esc_attr(apply_filters('bootscore/class/preloader/bg', 'bg-body align-items-center justify-content-center position-fixed top-0 end-0 bottom-0 start-0 zi-1070')); ?>">
        <div id="status"
            class="<?= esc_attr(apply_filters('bootscore/class/preloader/spinner', 'spinner-border text-primary')); ?>"
            role="status">
            <?php do_action('bootscore_preloader_status'); ?>
            <span class="visually-hidden"><?php _e('Loading...', 'bootscore'); ?></span>
        </div>
    </div>
<?php endif; ?>