<?php
/**
 * Template Name: Email Test Page
 * 
 * Admin-only page for testing email templates
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Security check - admin only
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have permission to access this page.'));
}

// Get sample data
$sample_data = lpdh_get_sample_email_data(1);

// Get available templates
$templates = array(
    'new-user-welcome' => 'New User Welcome Email',
    'admin-new-user-notification' => 'Admin New User Notification',
);

// Get available themes
$themes = array(
    'default' => 'Bootscore (Default)',
    'vaporwave' => 'Vaporwave',
    'vaporwave-green' => 'Vaporwave Green',
    'lost-wood' => 'Lost Wood',
);

// Current selections
$selected_template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'new-user-welcome';
$selected_theme = isset($_GET['theme_override']) ? sanitize_text_field($_GET['theme_override']) : get_option('lpdh_active_theme', 'default');

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="fas fa-envelope me-2"></i>
                        Email Template Testing
                    </h1>

                    <!-- Test Controls -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Template Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" id="email-test-form">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="template" class="form-label">Email Template</label>
                                        <select name="template" id="template" class="form-select">
                                            <?php foreach ($templates as $key => $label): ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_template, $key); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="theme_override" class="form-label">Theme Override (for testing)</label>
                                        <select name="theme_override" id="theme_override" class="form-select">
                                            <?php foreach ($themes as $key => $label): ?>
                                                <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_theme, $key); ?>>
                                                    <?php echo esc_html($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            Current active theme: <strong><?php echo esc_html(ucwords(str_replace('-', ' ', get_option('lpdh_active_theme', 'default')))); ?></strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-2"></i>Update Preview
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="reset-btn">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Email Preview</h5>
                        </div>
                        <div class="card-body p-0">
                            <iframe 
                                id="email-preview-iframe" 
                                style="width: 100%; min-height: 600px; border: none;"
                                src="<?php echo esc_url(add_query_arg(array(
                                    'action' => 'lpdh_preview_email',
                                    'template' => $selected_template,
                                    'theme' => $selected_theme,
                                    'nonce' => wp_create_nonce('lpdh_email_preview')
                                ), admin_url('admin-ajax.php'))); ?>">
                            </iframe>
                        </div>
                    </div>

                    <!-- Send Test Email Section -->
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-paper-plane me-2"></i>Send Test Email</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Send the currently previewed email template to a test email address. 
                                This will use the current theme override setting.
                            </p>

                            <div id="email-send-result"></div>

                            <form id="send-test-email-form">
                                <div class="row align-items-end">
                                    <div class="col-md-8 mb-3 mb-md-0">
                                        <label for="test_email" class="form-label">Recipient Email Address</label>
                                        <input 
                                            type="email" 
                                            class="form-control" 
                                            id="test_email" 
                                            name="test_email" 
                                            value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" 
                                            required
                                            placeholder="your@email.com">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success w-100" id="send-test-btn">
                                            <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                        </button>
                                    </div>
                                </div>

                                <input type="hidden" name="template" value="<?php echo esc_attr($selected_template); ?>">
                                <input type="hidden" name="theme" value="<?php echo esc_attr($selected_theme); ?>">
                                <input type="hidden" name="action" value="lpdh_send_test_email">
                                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('lpdh_send_test_email'); ?>">
                            </form>

                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Note:</strong> Using sample data from user ID: 1 (<?php echo esc_html($sample_data['user_login']); ?>)
                                </small>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>
</div>

<script>
jQuery(document).ready(function($) {
    // Reset button
    $('#reset-btn').on('click', function() {
        window.location.href = window.location.pathname;
    });

    // Update iframe when form changes
    $('#email-test-form').on('submit', function() {
        const template = $('#template').val();
        const theme = $('#theme_override').val();
        const iframeSrc = '<?php echo admin_url('admin-ajax.php'); ?>?' + 
            'action=lpdh_preview_email&' +
            'template=' + template + '&' +
            'theme=' + theme + '&' +
            'nonce=<?php echo wp_create_nonce('lpdh_email_preview'); ?>';
        
        $('#email-preview-iframe').attr('src', iframeSrc);
    });

    // Send test email
    $('#send-test-email-form').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#send-test-btn');
        const $result = $('#email-send-result');
        const formData = $(this).serialize();

        // Disable button
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending...');
        $result.empty();

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $result.html(
                        '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-check-circle me-2"></i>' + response.data.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>'
                    );
                } else {
                    $result.html(
                        '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-exclamation-circle me-2"></i>' + response.data.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>'
                    );
                }
            },
            error: function() {
                $result.html(
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fas fa-exclamation-circle me-2"></i>An error occurred while sending the email.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>'
                );
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Send Test Email');
            }
        });
    });
});
</script>

<?php get_footer(); ?>
