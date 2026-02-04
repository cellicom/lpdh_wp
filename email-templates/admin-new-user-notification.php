<?php
/**
 * Email Template: Admin New User Notification
 * 
 * @package Bootscore Child
 * @version 1.0.0
 * 
 * Available variables:
 * @var string $user_login Username
 * @var string $user_email User email
 * @var int $user_id User ID
 * @var string $registration_time Registration timestamp
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Initialize template
$template = new LPDH_Email_Template();

// Prepare content
$content = sprintf(
    '<h2 style="color: %s; margin-top: 0;">New User Registered</h2>',
    'inherit'
);

$content .= sprintf(
    '<p>A new user has just registered on <strong>%s</strong>.</p>',
    get_bloginfo('name')
);

// User details box
$content .= $template->get_info_box(array(
    'Username' => $user_login,
    'Email' => $user_email,
    'User ID' => $user_id,
    'Registration Date' => $registration_time,
));

$content .= '<p style="text-align: center;">';
$content .= $template->get_button(
    admin_url('user-edit.php?user_id=' . $user_id),
    'View User Profile'
);
$content .= '</p>';

// Wrap in email template
echo $template->wrap('New User Registered: ' . $user_login, $content);
