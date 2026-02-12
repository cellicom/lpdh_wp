<?php
/**
 * LPDH Email System
 *
 * Contains email template includes, AJAX handlers for preview/send,
 * and the Email Test admin page.
 *
 * @package Bootscore Child
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Include Email Template System
require_once get_stylesheet_directory() . '/inc/class-email-template.php';

/**
 * Get email template HTML
 * 
 * @param string $type Template type (new-user-welcome, admin-new-user-notification, etc.)
 * @param array $data Data to pass to template
 * @return string|false Email HTML or false on failure
 */
function lpdh_get_email_template($type, $data = array())
{
    $template_path = get_stylesheet_directory() . '/email-templates/' . $type . '.php';

    if (!file_exists($template_path)) {
        error_log('LPDH Email: Template not found - ' . $type);
        return false;
    }

    // Start output buffering
    ob_start();

    // Extract data for template
    extract($data);

    // Include template
    include $template_path;

    // Get content
    $content = ob_get_clean();

    return $content;
}

/**
 * Get email theme colors
 * 
 * @return array Theme color configuration
 */
function lpdh_get_email_theme_colors()
{
    $template = new LPDH_Email_Template();
    return $template->get_theme_name();
}

/**
 * Get email logo HTML
 * 
 * @return string Logo HTML
 */
function lpdh_get_email_logo_html()
{
    $custom_logo_id = get_theme_mod('custom_logo');

    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
        if ($logo_url) {
            return sprintf(
                '<img src="%s" alt="%s" style="max-width: 80%%; height: auto;">',
                esc_url($logo_url),
                esc_attr(get_bloginfo('name'))
            );
        }
    }

    return '<h1>' . esc_html(get_bloginfo('name')) . '</h1>';
}

/**
 * Send email using template
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $template_type Template type
 * @param array $template_data Data for template
 * @return bool Whether email was sent successfully
 */
function lpdh_send_templated_email($to, $subject, $template_type, $template_data = array())
{
    $content = lpdh_get_email_template($template_type, $template_data);

    if (!$content) {
        return false;
    }

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
    );

    return wp_mail($to, $subject, $content, $headers);
}

/**
 * Render email preview (for testing)
 * 
 * @param string $template_type Template type
 * @param array $template_data Data for template
 * @param bool $echo Whether to echo or return
 * @return string|void Email HTML
 */
function lpdh_render_email_preview($template_type, $template_data = array(), $echo = true)
{
    $content = lpdh_get_email_template($template_type, $template_data);

    if ($echo) {
        echo $content;
    } else {
        return $content;
    }
}

/**
 * Get sample user data for email testing
 * 
 * @param int $user_id User ID (default: 1)
 * @return array User data array
 */
function lpdh_get_sample_email_data($user_id = 1)
{
    $user = get_userdata($user_id);

    if (!$user) {
        return array();
    }

    return array(
        'user_id' => $user->ID,
        'user_login' => $user->user_login,
        'user_email' => $user->user_email,
        'first_name' => $user->first_name ?: 'John',
        'last_name' => $user->last_name ?: 'Doe',
        'display_name' => $user->display_name,
        'password' => 'SamplePass123!',
        'login_url' => lpdh_get_login_register_url(),
        'registration_time' => current_time('mysql'),
    );
}

/**
 * AJAX handler for email preview
 */
function lpdh_ajax_preview_email()
{
    check_ajax_referer('lpdh_email_preview', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have permission to access this feature.'));
    }

    $template_type = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'new-user-welcome';
    $theme_override = isset($_GET['theme']) ? sanitize_text_field($_GET['theme']) : '';

    // Temporarily override theme if requested
    if ($theme_override) {
        add_filter('option_lpdh_active_theme', function () use ($theme_override) {
            return $theme_override;
        });
    }

    // Get sample data
    $sample_data = lpdh_get_sample_email_data(1);

    // Render template
    lpdh_render_email_preview($template_type, $sample_data, true);
    exit;
}
add_action('wp_ajax_lpdh_preview_email', 'lpdh_ajax_preview_email');

/**
 * AJAX handler for sending test email
 */
function lpdh_ajax_send_test_email()
{
    check_ajax_referer('lpdh_send_test_email', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have permission to send test emails.'));
    }

    $recipient = isset($_POST['test_email']) ? sanitize_email($_POST['test_email']) : '';
    $template_type = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : 'new-user-welcome';
    $theme_override = isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : '';

    if (empty($recipient) || !is_email($recipient)) {
        wp_send_json_error(array('message' => 'Please provide a valid email address.'));
    }

    // Temporarily override theme if requested
    if ($theme_override) {
        add_filter('option_lpdh_active_theme', function () use ($theme_override) {
            return $theme_override;
        });
    }

    // Get sample data
    $sample_data = lpdh_get_sample_email_data(1);

    // Define subject based on template type
    $subjects = array(
        'new-user-welcome' => '[TEST] Your Account Credentials',
        'admin-new-user-notification' => '[TEST] New User Registered',
    );

    $subject = isset($subjects[$template_type]) ? $subjects[$template_type] : '[TEST] Email Template';

    // Send email
    $sent = lpdh_send_templated_email($recipient, $subject, $template_type, $sample_data);

    if ($sent) {
        wp_send_json_success(array(
            'message' => 'Test email sent successfully to ' . $recipient . '!'
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Failed to send test email. Please check your email configuration.'
        ));
    }
}
add_action('wp_ajax_lpdh_send_test_email', 'lpdh_ajax_send_test_email');

/**
 * Register Email Test Admin Page as submenu under LPDH
 */
function lpdh_register_email_test_page()
{
    add_submenu_page(
        'lpdh-main',
        'Email Test',
        'Email Test',
        'manage_options',
        'lpdh-email-test',
        'lpdh_render_email_test_page'
    );
}
add_action('admin_menu', 'lpdh_register_email_test_page');

/**
 * Render Email Test Admin Page
 */
function lpdh_render_email_test_page()
{
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
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-email"></span> Email Template Testing</h1>

        <!-- Test Controls -->
        <div class="card" style="max-width: none;">
            <h2>Template Settings</h2>
            <form method="get" id="email-test-form">
                <input type="hidden" name="page" value="lpdh-email-test">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="template">Email Template</label></th>
                        <td>
                            <select name="template" id="template" class="regular-text">
                                <?php foreach ($templates as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_template, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="theme_override">Theme Override</label></th>
                        <td>
                            <select name="theme_override" id="theme_override" class="regular-text">
                                <?php foreach ($themes as $key => $label): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_theme, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Current active theme:
                                <strong>
                                    <?php echo esc_html(ucwords(str_replace('-', ' ', get_option('lpdh_active_theme', 'default')))); ?>
                                </strong>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Update Preview', 'primary', 'submit', false); ?>
                <button type="button" class="button" id="reset-btn">Reset</button>
            </form>
        </div>

        <!-- Preview Section -->
        <div class="card" style="max-width: none; margin-top: 20px;">
            <h2>Email Preview</h2>
            <div style="border: 1px solid #ddd; background: #f9f9f9; padding: 10px;">
                <iframe id="email-preview-iframe" style="width: 100%; min-height: 600px; border: none; background: white;"
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
        <div class="card" style="max-width: none; margin-top: 20px;">
            <h2>Send Test Email</h2>
            <p>Send the currently previewed email template to a test email address using the current theme override setting.
            </p>
            <div id="email-send-result"></div>
            <form id="send-test-email-form">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="test_email">Recipient Email Address</label></th>
                        <td>
                            <input type="email" id="test_email" name="test_email"
                                value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" class="regular-text"
                                required>
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="template" value="<?php echo esc_attr($selected_template); ?>">
                <input type="hidden" name="theme" value="<?php echo esc_attr($selected_theme); ?>">
                <input type="hidden" name="action" value="lpdh_send_test_email">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('lpdh_send_test_email'); ?>">
                <?php submit_button('Send Test Email', 'primary', 'submit', false, array('id' => 'send-test-btn')); ?>
            </form>
            <p class="description">
                <span class="dashicons dashicons-info"></span>
                <strong>Note:</strong> Using sample data from user ID: 1
                (
                <?php echo esc_html($sample_data['user_login']); ?>)
            </p>
        </div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            // Reset button
            $('#reset-btn').on('click', function () {
                window.location.href = '?page=lpdh-email-test';
            });

            // Update iframe when form changes
            $('#email-test-form').on('submit', function (e) {
                e.preventDefault();
                const template = $('#template').val();
                const theme = $('#theme_override').val();
                const iframeSrc = '<?php echo admin_url('admin-ajax.php'); ?>?' +
                    'action=lpdh_preview_email&' +
                    'template=' + template + '&' +
                    'theme=' + theme + '&' +
                    'nonce=<?php echo wp_create_nonce('lpdh_email_preview'); ?>';

                $('#email-preview-iframe').attr('src', iframeSrc);

                // Update form action values
                $('input[name="template"]').val(template);
                $('input[name="theme"]').val(theme);

                // Update URL
                window.history.pushState({}, '', '?page=lpdh-email-test&template=' + template + '&theme_override=' + theme);
            });

            // Send test email
            $('#send-test-email-form').on('submit', function (e) {
                e.preventDefault();

                const $btn = $('#send-test-btn');
                const $result = $('#email-send-result');
                const formData = $(this).serialize();

                // Disable button
                $btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none;margin:0 5px 0 0;"></span>Sending...');
                $result.empty();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            $result.html(
                                '<div class="notice notice-success"><p><span class="dashicons dashicons-yes"></span> ' + response.data.message + '</p></div>'
                            );
                        } else {
                            $result.html(
                                '<div class="notice notice-error"><p><span class="dashicons dashicons-warning"></span> ' + response.data.message + '</p></div>'
                            );
                        }
                    },
                    error: function () {
                        $result.html(
                            '<div class="notice notice-error"><p><span class="dashicons dashicons-warning"></span> An error occurred while sending the email.</p></div>'
                        );
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('Send Test Email');
                    }
                        });
        });
                });
    </script>

    <style>
        .card {
            background: white;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            padding: 20px;
            margin-bottom: 20px;
        }

        .card h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        #email-send-result .notice {
            margin: 10px 0;
        }

        #email-send-result .dashicons {
            vertical-align: middle;
        }
    </style>
    <?php
}
