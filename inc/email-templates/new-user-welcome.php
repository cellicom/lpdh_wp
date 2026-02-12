<?php
/**
 * Email Template: New User Welcome
 * 
 * @package Bootscore Child
 * @version 1.0.0
 * 
 * Available variables:
 * @var string $user_login Username
 * @var string $user_email User email
 * @var string $password Generated password
 * @var string $login_url Login page URL
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Initialize template
$template = new LPDH_Email_Template();

// Prepare content
$content = sprintf(
    '<h2 style="color: %s; margin-top: 0;">Welcome to %s!</h2>',
    'inherit',
    get_bloginfo('name')
);

$content .= '<p>Thank you for registering. Your account has been created successfully. Here are your access credentials:</p>';

// Credentials box
$content .= $template->get_info_box(array(
    'Username' => $user_login,
    'Password' => $password,
));

$content .= '<p><strong>To access your account:</strong></p>';
$content .= '<p style="text-align: center;">';
$content .= $template->get_button($login_url, 'Log In Now');
$content .= '</p>';

$content .= '<hr style="border: none; border-top: 1px solid #ddd; margin: 25px 0;">';

$content .= '<p style="font-size: 13px; color: #666;">
    <strong>Security Tip:</strong> After your first login, we recommend changing your password for better security. 
    You can do this from your profile settings.
</p>';

// Wrap in email template
echo $template->wrap('Your Account Credentials', $content);
