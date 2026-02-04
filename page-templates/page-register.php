<?php
/**
 * Template Name: Registration Page
 * Template for user registration, login, and password recovery
 * 
 * @package Bootscore Child
 * @version 1.2.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Redirect logged in users to profile detail page
if (is_user_logged_in()) {
    wp_redirect(lpdh_get_user_profile_url(get_current_user_id()));
    exit;
}

/**
 * Send custom new user notification email
 */
function bootscore_send_new_user_email($user_id, $password)
{
    $user = get_userdata($user_id);

    if (!$user) {
        return false;
    }

    $site_name = get_bloginfo('name');

    // Prepare data for user welcome email
    $user_data = array(
        'user_login' => $user->user_login,
        'user_email' => $user->user_email,
        'password' => $password,
        'login_url' => lpdh_get_login_register_url(),
    );

    // Send welcome email to user
    $subject = sprintf(
        /* translators: %s: Site name */
        __('[%s] Your account credentials', 'bootscore'),
        $site_name
    );

    $sent = lpdh_send_templated_email(
        $user->user_email,
        $subject,
        'new-user-welcome',
        $user_data
    );

    // Send admin notification
    $admin_email = get_option('admin_email');
    if ($admin_email && $admin_email !== $user->user_email) {
        $admin_data = array(
            'user_login' => $user->user_login,
            'user_email' => $user->user_email,
            'user_id' => $user_id,
            'registration_time' => current_time('mysql'),
        );

        $admin_subject = sprintf(
            /* translators: %s: New user username */
            __('[%s] New user registered: %s', 'bootscore'),
            $site_name,
            $user->user_login
        );

        lpdh_send_templated_email(
            $admin_email,
            $admin_subject,
            'admin-new-user-notification',
            $admin_data
        );
    }

    return $sent;
}

$errors = new WP_Error();
$login_error = '';
$register_error = '';
$lost_password_error = '';
$lost_password_success = false;
$register_success = false;

// Handle registration form submission
if (isset($_POST['bootscore_register_nonce']) && wp_verify_nonce($_POST['bootscore_register_nonce'], 'bootscore_register_action')) {

    $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';

    // Validate username
    if (empty($user_login)) {
        $errors->add('empty_username', __('Please enter a username.', 'bootscore'));
    } elseif (!validate_username($user_login)) {
        $errors->add('invalid_username', __('The username is invalid.', 'bootscore'));
    } elseif (username_exists($user_login)) {
        $errors->add('username_exists', __('This username is already in use.', 'bootscore'));
    }

    // Validate email
    if (empty($user_email)) {
        $errors->add('empty_email', __('Please enter an email address.', 'bootscore'));
    } elseif (!is_email($user_email)) {
        $errors->add('invalid_email', __('The email address is invalid.', 'bootscore'));
    } elseif (email_exists($user_email)) {
        $errors->add('email_exists', __('This email address is already registered.', 'bootscore'));
    }

    // If no errors, create user
    if (empty($errors->errors)) {

        // Generate random password
        $password = wp_generate_password(12, true);

        // Create user data array
        $user_data = array(
            'user_login' => $user_login,
            'user_email' => $user_email,
            'user_pass' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'player',
        );

        // Insert user
        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            $register_error = sprintf(
                /* translators: %s: Error message */
                __('An error occurred during registration: %s', 'bootscore'),
                $user_id->get_error_message()
            );
        } else {
            bootscore_send_new_user_email($user_id, $password);
            $register_success = true;
        }
    }
}

// Handle login form submission
if (isset($_POST['lpdh_login_nonce']) && wp_verify_nonce($_POST['lpdh_login_nonce'], 'lpdh_login_action')) {
    $creds = array(
        'user_login' => isset($_POST['log']) ? sanitize_user($_POST['log']) : '',
        'user_password' => isset($_POST['pwd']) ? $_POST['pwd'] : '',
        'remember' => isset($_POST['rememberme']),
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        wp_redirect(lpdh_get_user_profile_url($user->ID));
        exit;
    }
}

// Handle lost password submission
if (isset($_POST['lpdh_lost_password_nonce']) && wp_verify_nonce($_POST['lpdh_lost_password_nonce'], 'lpdh_lost_password_action')) {
    $user_login_recovery = isset($_POST['user_login_recovery']) ? sanitize_user($_POST['user_login_recovery']) : '';

    if (empty($user_login_recovery)) {
        $lost_password_error = __('Please enter your username or email address.', 'bootscore');
    } else {
        $result = retrieve_password($user_login_recovery);
        if (is_wp_error($result)) {
            $lost_password_error = $result->get_error_message();
        } else {
            $lost_password_success = true;
        }
    }
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">

                    <!-- Login Form Section -->
                    <div id="lpdh-login-section" <?php echo (isset($_GET['login']) && $_GET['login'] === 'true') || !empty($login_error) ? '' : 'style="display:none;"'; ?>>
                        <h1 class="h2 mb-4 text-center">
                            <i class="fas fa-sign-in-alt me-2"></i>
                            <?php esc_html_e('Log In', 'bootscore'); ?>
                        </h1>

                        <?php if (!empty($login_error)): ?>
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $login_error; ?>
                            </div>
                        <?php endif; ?>

                        <form name="loginform" id="loginform" action="" method="post">
                            <?php wp_nonce_field('lpdh_login_action', 'lpdh_login_nonce'); ?>

                            <div class="mb-3">
                                <label for="user_login_login"
                                    class="form-label"><?php esc_html_e('Username or Email Address', 'bootscore'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" name="log" id="user_login_login"
                                        class="form-control form-control-lg"
                                        value="<?php echo isset($_POST['log']) ? esc_attr($_POST['log']) : ''; ?>"
                                        required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="user_pass_login"
                                    class="form-label"><?php esc_html_e('Password', 'bootscore'); ?></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="pwd" id="user_pass_login"
                                        class="form-control form-control-lg" required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input name="rememberme" type="checkbox" class="form-check-input" id="rememberme"
                                    value="forever">
                                <label class="form-check-label"
                                    for="rememberme"><?php esc_html_e('Remember Me', 'bootscore'); ?></label>
                            </div>

                            <div class="d-grid gap-2 mb-4">
                                <button type="submit" name="wp-submit" id="wp-submit-login"
                                    class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <?php esc_html_e('Log In', 'bootscore'); ?>
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <p><?php esc_html_e("Don't have an account?", 'bootscore'); ?>
                                    <a href="#" class="lpdh-toggle-form"
                                        data-target="register"><?php esc_html_e('Register here', 'bootscore'); ?></a>
                                </p>
                                <p class="small">
                                    <a href="#" class="lpdh-toggle-form"
                                        data-target="lostpassword"><?php esc_html_e('Lost your password?', 'bootscore'); ?></a>
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Register Form Section -->
                    <div id="lpdh-register-section" <?php echo (!isset($_GET['login']) || $_GET['login'] !== 'true') && empty($login_error) && empty($lost_password_error) && !$lost_password_success ? '' : 'style="display:none;"'; ?>>

                        <?php if ($register_success): ?>
                            <div class="text-center py-4">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle text-success fa-5x"></i>
                                </div>
                                <h1 class="h2 mb-3"><?php esc_html_e('Registration Complete!', 'bootscore'); ?></h1>
                                <p class="lead mb-4">
                                    <?php esc_html_e('Congratulations! Your registration was completed successfully. You will receive an email with instructions to access your account.', 'bootscore'); ?>
                                </p>
                                <a href="#" class="btn btn-primary btn-lg lpdh-toggle-form" data-target="login">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    <?php esc_html_e('Log In Now', 'bootscore'); ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <h1 class="h2 mb-4 text-center">
                                <i class="fas fa-user-plus me-2"></i>
                                <?php esc_html_e('Register', 'bootscore'); ?>
                            </h1>

                            <?php if ($errors->has_errors()): ?>
                                <div class="alert alert-danger mb-4">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong><?php esc_html_e('The following errors occurred:', 'bootscore'); ?></strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($errors->get_error_messages() as $message): ?>
                                            <li><?php echo esc_html($message); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($register_error)): ?>
                                <div class="alert alert-danger mb-4">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo esc_html($register_error); ?>
                                </div>
                            <?php endif; ?>

                            <form id="registerform" method="post" action="">
                                <?php wp_nonce_field('bootscore_register_action', 'bootscore_register_nonce'); ?>

                                <div class="mb-3">
                                    <label for="user_login" class="form-label">
                                        <?php esc_html_e('Username', 'bootscore'); ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="user_login" id="user_login"
                                            class="form-control form-control-lg"
                                            value="<?php echo isset($_POST['user_login']) ? esc_attr(sanitize_user($_POST['user_login'])) : ''; ?>"
                                            required autocomplete="username"
                                            placeholder="<?php esc_attr_e('Enter your username', 'bootscore'); ?>">
                                    </div>
                                    <div class="form-text text-info">
                                        <?php esc_html_e('The username must be unique and cannot be changed.', 'bootscore'); ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="user_email" class="form-label">
                                        <?php esc_html_e('Email Address', 'bootscore'); ?>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" name="user_email" id="user_email"
                                            class="form-control form-control-lg"
                                            value="<?php echo isset($_POST['user_email']) ? esc_attr(sanitize_email($_POST['user_email'])) : ''; ?>"
                                            required autocomplete="email"
                                            placeholder="<?php esc_attr_e('Enter your email address', 'bootscore'); ?>">
                                    </div>
                                    <div class="form-text text-info">
                                        <?php esc_html_e('Use a valid email address. We will send you access instructions.', 'bootscore'); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name"
                                            class="form-label"><?php esc_html_e('First Name', 'bootscore'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            <input type="text" name="first_name" id="first_name"
                                                class="form-control form-control-lg"
                                                value="<?php echo isset($_POST['first_name']) ? esc_attr(sanitize_text_field($_POST['first_name'])) : ''; ?>"
                                                autocomplete="given-name"
                                                placeholder="<?php esc_attr_e('Your first name', 'bootscore'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name"
                                            class="form-label"><?php esc_html_e('Last Name', 'bootscore'); ?></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                            <input type="text" name="last_name" id="last_name"
                                                class="form-control form-control-lg"
                                                value="<?php echo isset($_POST['last_name']) ? esc_attr(sanitize_text_field($_POST['last_name'])) : ''; ?>"
                                                autocomplete="family-name"
                                                placeholder="<?php esc_attr_e('Your last name', 'bootscore'); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mb-4">
                                    <button type="submit" name="wp-submit" id="wp-submit-register"
                                        class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus me-2"></i>
                                        <?php esc_html_e('Register', 'bootscore'); ?>
                                    </button>
                                </div>

                                <div class="text-center mt-3">
                                    <p><?php esc_html_e('Already have an account?', 'bootscore'); ?>
                                        <a href="#" class="lpdh-toggle-form"
                                            data-target="login"><?php esc_html_e('Log in here', 'bootscore'); ?></a>
                                    </p>
                                </div>

                                <div class="mt-5 pt-4 border-top border-white-50">
                                    <h5 class="h6 mb-2">
                                        <i class="fas fa-shield-alt text-primary me-2"></i>
                                        <?php esc_html_e('Privacy Policy', 'bootscore'); ?>
                                    </h5>
                                    <p class="small mb-0">
                                        <?php
                                        printf(
                                            /* translators: %s: Site name */
                                            __('By registering on %s, you agree to provide your personal data for the creation and management of your account. Your data will be processed in compliance with personal data protection regulations.', 'bootscore'),
                                            get_bloginfo('name')
                                        );
                                        ?>
                                        <?php if (get_privacy_policy_url()) : ?>
                                            <a href="<?php echo esc_url(get_privacy_policy_url()); ?>" target="_blank" class="text-info ms-1"><?php esc_html_e('Read our Privacy Policy', 'bootscore'); ?></a>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Lost Password Section -->
                    <div id="lpdh-lostpassword-section" <?php echo !empty($lost_password_error) || $lost_password_success ? '' : 'style="display:none;"'; ?>>
                        <h1 class="h2 mb-4 text-center">
                            <i class="fas fa-key me-2"></i>
                            <?php esc_html_e('Lost Password', 'bootscore'); ?>
                        </h1>

                        <?php if ($lost_password_success): ?>
                            <div class="alert alert-success mb-4">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php esc_html_e('Check your email for the confirmation link, then visit the login page.', 'bootscore'); ?>
                            </div>
                            <div class="text-center">
                                <a href="#" class="btn btn-primary lpdh-toggle-form" data-target="login">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    <?php esc_html_e('Back to Login', 'bootscore'); ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($lost_password_error)): ?>
                                <div class="alert alert-danger mb-4">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo $lost_password_error; ?>
                                </div>
                            <?php endif; ?>

                            <p class="text-center mb-4">
                                <?php esc_html_e('Please enter your username or email address. You will receive a link to create a new password via email.', 'bootscore'); ?>
                            </p>

                            <form name="lostpasswordform" id="lostpasswordform" action="" method="post">
                                <?php wp_nonce_field('lpdh_lost_password_action', 'lpdh_lost_password_nonce'); ?>

                                <div class="mb-3">
                                    <label for="user_login_recovery"
                                        class="form-label"><?php esc_html_e('Username or Email Address', 'bootscore'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" name="user_login_recovery" id="user_login_recovery"
                                            class="form-control form-control-lg" required>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 mb-4">
                                    <button type="submit" name="wp-submit" id="wp-submit-recovery"
                                        class="btn btn-primary btn-lg">
                                        <?php esc_html_e('Get New Password', 'bootscore'); ?>
                                    </button>
                                </div>

                                <div class="text-center mt-3">
                                    <a href="#" class="lpdh-toggle-form" data-target="login"><i
                                            class="fas fa-arrow-left me-1"></i>
                                        <?php esc_html_e('Back to Login', 'bootscore'); ?></a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>

    </main>
</div>

<script type="text/javascript">
    (function ($) {
        'use strict';

        $(document).ready(function () {
            var usernameTimer = null;
            var emailTimer = null;
            var nonce = '<?php echo wp_create_nonce('bootscore_register_nonce'); ?>';
            var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            // Toggle functionality
            $('.lpdh-toggle-form').on('click', function (e) {
                e.preventDefault();
                var target = $(this).data('target');

                $('#lpdh-login-section, #lpdh-register-section, #lpdh-lostpassword-section').fadeOut(200, function () {
                    if (target === 'login') {
                        $('#lpdh-login-section').fadeIn(200);
                        var newUrl = window.location.pathname + '?login=true';
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    } else if (target === 'register') {
                        $('#lpdh-register-section').fadeIn(200);
                        var newUrl = window.location.pathname;
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    } else if (target === 'lostpassword') {
                        $('#lpdh-lostpassword-section').fadeIn(200);
                        var newUrl = window.location.pathname + '?action=lostpassword';
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    }
                });
            });

            // Username validation
            $('#user_login').on('input', function () {
                var $input = $(this);
                var username = $input.val().trim();

                if (usernameTimer) clearTimeout(usernameTimer);
                $input.closest('.mb-3').find('.username-feedback').remove();

                if (username.length < 2) return;

                $input.closest('.input-group').after(
                    '<div class="username-feedback mt-1 small text-info">' +
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span>Checking...</span>' +
                    '</div>'
                );

                usernameTimer = setTimeout(function () {
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'bootscore_check_username',
                            user_login: username,
                            nonce: nonce
                        },
                        success: function (response) {
                            $input.closest('.mb-3').find('.username-feedback').remove();
                            if (response.success) {
                                $input.removeClass('is-invalid').addClass('is-valid');
                                $input.closest('.input-group').after(
                                    '<div class="username-feedback mt-1 small text-success">' +
                                    '<i class="fas fa-check-circle me-1"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                    '</div>'
                                );
                            } else {
                                $input.removeClass('is-valid').addClass('is-invalid');
                                $input.closest('.input-group').after(
                                    '<div class="username-feedback mt-1 small text-danger">' +
                                    '<i class="fas fa-times-circle me-1"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                    '</div>'
                                );
                            }
                        }
                    });
                }, 500);
            });

            // Email validation
            $('#user_email').on('input', function () {
                var $input = $(this);
                var email = $input.val().trim();

                if (emailTimer) clearTimeout(emailTimer);
                $input.closest('.mb-3').find('.email-feedback').remove();

                if (email.length < 5 || !email.includes('@')) return;

                $input.closest('.input-group').after(
                    '<div class="email-feedback mt-1 small text-info">' +
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' +
                    '<span>Checking...</span>' +
                    '</div>'
                );

                emailTimer = setTimeout(function () {
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'bootscore_check_email',
                            user_email: email,
                            nonce: nonce
                        },
                        success: function (response) {
                            $input.closest('.mb-3').find('.email-feedback').remove();
                            if (response.success) {
                                $input.removeClass('is-invalid').addClass('is-valid');
                                $input.closest('.input-group').after(
                                    '<div class="email-feedback mt-1 small text-success">' +
                                    '<i class="fas fa-check-circle me-1"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                    '</div>'
                                );
                            } else {
                                $input.removeClass('is-valid').addClass('is-invalid');
                                $input.closest('.input-group').after(
                                    '<div class="email-feedback mt-1 small text-danger">' +
                                    '<i class="fas fa-times-circle me-1"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                    '</div>'
                                );
                            }
                        }
                    });
                }, 500);
            });

            // Form submission validation
            $('#registerform').on('submit', function (e) {
                if ($('.username-feedback.text-danger').length > 0 || $('.email-feedback.text-danger').length > 0) {
                    e.preventDefault();
                    alert('Please fix the highlighted errors before proceeding.');
                    return false;
                }
            });
        });
    })(jQuery);
</script>

<?php get_footer(); ?>