<?php
/**
 * Email Template Helper Functions
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

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
                '<img src="%s" alt="%s" style="max-width: 80%; height: auto;">',
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
