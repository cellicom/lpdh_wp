<?php
/**
 * Template Name: Registration Page
 * Template for user registration - mimics standard WordPress registration form
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Redirect logged in users to profile
if (is_user_logged_in()) {
    wp_redirect(admin_url('profile.php'));
    exit;
}

/**
 * Send custom new user notification email
 * More reliable than default wp_new_user_notification
 */
function bootscore_send_new_user_email($user_id, $password) {
    $user = get_userdata($user_id);
    
    if ( !$user ) {
        return false;
    }
    
    $user_login = $user->user_login;
    $user_email = $user->user_email;
    $site_name = get_bloginfo('name');
    $site_url = home_url();
    
    // Subject
    $subject = sprintf(
        /* translators: %s: Site name */
        __('[%s] Credenziali del tuo account', 'bootscore'),
        $site_name
    );
    
    // Message for user
    $message = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #007bff, #0056b3); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 24px;">{$site_name}</h1>
    </div>
    
    <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;">
        <h2 style="color: #007bff; margin-top: 0;">Benvenuto su {$site_name}!</h2>
        
        <p>Grazie per esserti registrato. Ecco le tue credenziali di accesso:</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Nome utente:</strong> {$user_login}</p>
            <p style="margin: 5px 0;"><strong>Password:</strong> {$password}</p>
        </div>
        
        <p><strong>Per accedere al tuo account:</strong></p>
        <p>
            <a href="{$site_url}/wp-admin" style="display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 10px;">
                Accedi ora
            </a>
        </p>
        
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        
        <p style="font-size: 12px; color: #666;">
            Dopo il primo accesso, ti consigliamo di cambiare la password per una maggiore sicurezza.
        </p>
    </div>
    
    <div style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
        <p>Questa è un'email automatica, non rispondere a questo messaggio.</p>
    </div>
</body>
</html>
HTML;
    
    // Headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
    );
    
    // Send email
    $sent = wp_mail($user_email, $subject, $message, $headers);
    $current = current_time('mysql');
    
    // Also send admin notification
    $admin_email = get_option('admin_email');
    if ( $admin_email && $admin_email !== $user_email ) {
        $admin_subject = sprintf(
            /* translators: %s: New user username */
            __('[%s] Nuovo utente registrato: %s', 'bootscore'),
            $site_name,
            $user_login
        );
        
        $admin_message = <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$admin_subject}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #28a745; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0;">Nuovo utente registrato</h1>
    </div>
    
    <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 10px 10px;">
        <p>Un nuovo utente si è appena registrato su <strong>{$site_name}</strong>.</p>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>Nome utente:</strong> {$user_login}</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> {$user_email}</p>
            <p style="margin: 5px 0;"><strong>Data registrazione:</strong> {$current}</p>
        </div>
        
        <p>
            <a href="{$site_url}/wp-admin/user-edit.php?user_id={$user_id}" style="display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                Visualizza utente
            </a>
        </p>
    </div>
</body>
</html>
HTML;
        
        wp_mail($admin_email, $admin_subject, $admin_message, $headers);
    }
    
    return $sent;
}

// Handle registration form submission
if ( isset($_POST['bootscore_register_nonce']) && wp_verify_nonce($_POST['bootscore_register_nonce'], 'bootscore_register_action') ) {
    
    $user_login = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
    $user_email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    
    $errors = new WP_Error();
    
    // Validate username
    if ( empty($user_login) ) {
        $errors->add('empty_username', __('Per favore inserisci un nome utente.', 'bootscore'));
    } elseif ( !validate_username($user_login) ) {
        $errors->add('invalid_username', __('Il nome utente non è valido.', 'bootscore'));
    } elseif ( username_exists($user_login) ) {
        $errors->add('username_exists', __('Questo nome utente è già in uso.', 'bootscore'));
    }
    
    // Validate email
    if ( empty($user_email) ) {
        $errors->add('empty_email', __('Per favore inserisci un indirizzo email.', 'bootscore'));
    } elseif ( !is_email($user_email) ) {
        $errors->add('invalid_email', __('L\'indirizzo email non è valido.', 'bootscore'));
    } elseif ( email_exists($user_email) ) {
        $errors->add('email_exists', __('Questo indirizzo email è già registrato.', 'bootscore'));
    }
    
    // If no errors, create user
    if ( empty($errors->errors) ) {
        
        // Generate random password
        $password = wp_generate_password(12, true);
        
        // Create user data array
        $user_data = array(
            'user_login' => $user_login,
            'user_email' => $user_email,
            'user_pass'  => $password,
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => 'player', // Use the custom 'player' role defined in functions.php
        );
        
        // Insert user
        $user_id = wp_insert_user($user_data);
        
        if ( is_wp_error($user_id) ) {
            $error_message = $user_id->get_error_message();
            $register_error = sprintf(
                /* translators: %s: Error message */
                __('Si è verificato un errore durante la registrazione: %s', 'bootscore'),
                $error_message
            );
        } else {
            // Send custom registration notification email
            bootscore_send_new_user_email($user_id, $password);
            
            // Set success message
            $register_success = true;
            
            // Log the user in automatically (optional - remove if not desired)
            // wp_set_auth_cookie($user_id, true, false);
            // wp_set_current_user($user_id);
        }
    } else {
        // Collect error messages
        $error_messages = array();
        foreach ( $errors->errors as $error_code => $error_messages_list ) {
            foreach ( $error_messages_list as $error_message ) {
                $error_messages[] = $error_message;
            }
        }
    }
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-8">
                    
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h1 class="h4 mb-0">
                                <i class="fas fa-user-plus me-2"></i>
                                <?php esc_html_e('Registrazione', 'bootscore'); ?>
                            </h1>
                        </div>
                        
                        <div class="card-body">
                            
                            <?php if ( current_user_can('administrator') || current_user_can('editor') || current_user_can('author') || current_user_can('player') ) : ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php 
                                    $current_user = wp_get_current_user();
                                    printf(
                                        /* translators: %s: User display name */
                                        __('Sei già registrato come %s.', 'bootscore'),
                                        '<strong>' . esc_html($current_user->display_name) . '</strong>'
                                    ); 
                                    ?>
                                    <div class="mt-2">
                                        <a href="<?php echo esc_url(admin_url('profile.php')); ?>" class="alert-link me-3">
                                            <?php esc_html_e('Vai al tuo profilo', 'bootscore'); ?>
                                        </a>
                                        <br>
                                        <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="alert-link">
                                            <?php esc_html_e('Effettua il logout per registrarti con un altro account.', 'bootscore'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php elseif ( $register_success ?? false ) : ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong><?php esc_html_e('Registrazione completata!', 'bootscore'); ?></strong>
                                    <p class="mb-0 mt-2">
                                        <?php esc_html_e('Congratulazioni! La tua registrazione è stata completata con successo. Riceverai un\'email con le istruzioni per accedere al tuo account.', 'bootscore'); ?>
                                    </p>
                                    <hr>
                                    <p class="mb-0">
                                        <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-success">
                                            <i class="fas fa-sign-in-alt me-1"></i>
                                            <?php esc_html_e('Accedi ora', 'bootscore'); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php else : ?>
                                
                                <?php if ( !empty($error_messages) ) : ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong><?php esc_html_e('Si sono verificati i seguenti errori:', 'bootscore'); ?></strong>
                                        <ul class="mb-0 mt-2">
                                            <?php foreach ( $error_messages as $error_message ) : ?>
                                                <li><?php echo esc_html($error_message); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ( !empty($register_error) ) : ?>
                                    <div class="alert alert-danger">
                                        <i class="fas fa-exclamation-circle me-2"></i>
                                        <?php echo esc_html($register_error); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <form id="registerform" method="post" action="">
                                    <?php wp_nonce_field('bootscore_register_action', 'bootscore_register_nonce'); ?>
                                    
                                    <!-- Username -->
                                    <div class="mb-3">
                                        <label for="user_login" class="form-label">
                                            <?php esc_html_e('Nome utente', 'bootscore'); ?>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" 
                                                   name="user_login" 
                                                   id="user_login" 
                                                   class="form-control form-control-lg" 
                                                   value="<?php echo isset($_POST['user_login']) ? esc_attr(sanitize_user($_POST['user_login'])) : ''; ?>" 
                                                   required 
                                                   autocomplete="username"
                                                   placeholder="<?php esc_attr_e('Inserisci il tuo nome utente', 'bootscore'); ?>"
                                            >
                                        </div>
                                        <div class="form-text">
                                            <?php esc_html_e('Il nome utente deve essere unico e non può essere modificato.', 'bootscore'); ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div class="mb-3">
                                        <label for="user_email" class="form-label">
                                            <?php esc_html_e('Email', 'bootscore'); ?>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                            <input type="email" 
                                                   name="user_email" 
                                                   id="user_email" 
                                                   class="form-control form-control-lg" 
                                                   value="<?php echo isset($_POST['user_email']) ? esc_attr(sanitize_email($_POST['user_email'])) : ''; ?>" 
                                                   required 
                                                   autocomplete="email"
                                                   placeholder="<?php esc_attr_e('Inserisci il tuo indirizzo email', 'bootscore'); ?>"
                                            >
                                        </div>
                                        <div class="form-text">
                                            <?php esc_html_e('Utilizza un indirizzo email valido. Ti invieremo le istruzioni di accesso.', 'bootscore'); ?>
                                        </div>
                                    </div>
                                    
                                    <!-- First Name -->
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">
                                                <?php esc_html_e('Nome', 'bootscore'); ?>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-id-card"></i>
                                                </span>
                                                <input type="text" 
                                                       name="first_name" 
                                                       id="first_name" 
                                                       class="form-control" 
                                                       value="<?php echo isset($_POST['first_name']) ? esc_attr(sanitize_text_field($_POST['first_name'])) : ''; ?>" 
                                                       autocomplete="given-name"
                                                       placeholder="<?php esc_attr_e('Inserisci il tuo nome', 'bootscore'); ?>"
                                                >
                                            </div>
                                        </div>
                                        
                                        <!-- Last Name -->
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">
                                                <?php esc_html_e('Cognome', 'bootscore'); ?>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-id-card"></i>
                                                </span>
                                                <input type="text" 
                                                       name="last_name" 
                                                       id="last_name" 
                                                       class="form-control" 
                                                       value="<?php echo isset($_POST['last_name']) ? esc_attr(sanitize_text_field($_POST['last_name'])) : ''; ?>" 
                                                       autocomplete="family-name"
                                                       placeholder="<?php esc_attr_e('Inserisci il tuo cognome', 'bootscore'); ?>"
                                                >
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Submit Button -->
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="wp-submit" id="wp-submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-user-plus me-2"></i>
                                            <?php esc_html_e('Registrati', 'bootscore'); ?>
                                        </button>
                                    </div>
                                    
                                </form>
                                
                                <!-- Additional Links -->
                                <hr class="my-4">
                                
                                <div class="text-center">
                                    <p class="mb-2">
                                        <?php esc_html_e('Hai già un account?', 'bootscore'); ?>
                                    </p>
                                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        <?php esc_html_e('Accedi', 'bootscore'); ?>
                                    </a>
                                </div>
                                
                            <?php endif; ?>
                            
                        </div>
                    </div>
                    
                    <!-- Privacy Notice -->
                    <div class="card mt-4">
                        <div class="card-body bg-light">
                            <h5 class="card-title">
                                <i class="fas fa-shield-alt text-primary me-2"></i>
                                <?php esc_html_e('Informativa sulla privacy', 'bootscore'); ?>
                            </h5>
                            <p class="card-text small text-muted mb-0">
                                <?php 
                                printf(
                                    /* translators: %s: Site name */
                                    __('Registrandoti su %s, accetti di fornire i tuoi dati personali per la creazione e gestione del tuo account. I tuoi dati saranno trattati nel rispetto della normativa sulla protezione dei dati personali.', 'bootscore'),
                                    get_bloginfo('name')
                                ); 
                                ?>
                            </p>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
    </main>
</div>

<style>
/* Registration Form Styling */
.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    padding: 1.25rem;
}

.card-body {
    padding: 2rem;
}

.form-control,
.form-control:focus {
    border-radius: 8px;
}

.input-group-text {
    border-radius: 8px 0 0 8px;
    background-color: #f8f9fa;
    border-right: none;
}

.form-control:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
}

.input-group .form-control {
    border-radius: 0 8px 8px 0;
}

.btn-primary {
    border-radius: 8px;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.alert {
    border-radius: 8px;
}

/* Validation states */
.was-validated .form-control:invalid,
.form-control.is-invalid {
    border-color: #dc3545;
}

.was-validated .input-group-text:invalid,
.input-group-text.is-invalid {
    border-color: #dc3545;
}

.form-text {
    font-size: 0.8rem;
    color: #6c757d;
}

/* Real-time validation feedback */
.username-feedback,
.email-feedback {
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.username-feedback.valid,
.email-feedback.valid {
    color: #198754;
}

.username-feedback.invalid,
.email-feedback.invalid {
    color: #dc3545;
}

.username-feedback.loading,
.email-feedback.loading {
    color: #6c757d;
}

/* Loading spinner */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 2px;
}

/* Validation icon sizing */
.validation-icon {
    font-size: 0.9rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-body {
        padding: 1.5rem;
    }
    
    .input-group-text {
        padding: 0.375rem 0.75rem;
    }
}

/* Focus states for better accessibility */
.form-control:focus,
.btn:focus {
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Card hover effects */
.card {
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Input group validation feedback */
.input-group-append .validation-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0 8px 8px 0;
}

.input-group-append .validation-badge.valid {
    background-color: #d1e7dd;
    color: #0a3622;
    border: 1px solid #badbcc;
    border-left: none;
}

.input-group-append .validation-badge.invalid {
    background-color: #f8d7da;
    color: #58151c;
    border: 1px solid #f5c2c7;
    border-left: none;
}

.input-group-append .validation-badge.loading {
    background-color: #e2e3e5;
    color: #41464b;
    border: 1px solid #d3d6d8;
    border-left: none;
}
</style>

<script type="text/javascript">
(function($) {
    'use strict';
    
    $(document).ready(function() {
        var usernameTimer = null;
        var emailTimer = null;
        var usernameNonce = '<?php echo wp_create_nonce('bootscore_register_nonce'); ?>';
        var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        
        // Username validation
        $('#user_login').on('input', function() {
            var $input = $(this);
            var username = $input.val().trim();
            
            // Clear previous timer
            if (usernameTimer) {
                clearTimeout(usernameTimer);
            }
            
            // Remove previous feedback
            $input.closest('.input-group').find('.validation-badge').remove();
            $input.closest('.mb-3').find('.username-feedback').remove();
            
            if (username.length < 2) {
                return; // Don't check if too short
            }
            
            // Show loading state
            $input.closest('.input-group').after(
                '<div class="username-feedback loading">' +
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' +
                    '<span>Controllo in corso...</span>' +
                '</div>'
            );
            
            // Debounce the AJAX call (wait 500ms after last input)
            usernameTimer = setTimeout(function() {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'bootscore_check_username',
                        user_login: username,
                        nonce: usernameNonce
                    },
                    success: function(response) {
                        // Remove previous feedback
                        $input.closest('.mb-3').find('.username-feedback').remove();
                        $input.closest('.input-group').find('.validation-badge').remove();
                        
                        if (response.success) {
                            // Username is available
                            $input.removeClass('is-invalid').addClass('is-valid');
                            $input.closest('.input-group').after(
                                '<div class="username-feedback valid">' +
                                    '<i class="fas fa-check-circle validation-icon"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                '</div>'
                            );
                            $input.closest('.input-group').find('.input-group-text').addClass('border-success');
                        } else {
                            // Username is taken or invalid
                            $input.removeClass('is-valid').addClass('is-invalid');
                            $input.closest('.mb-3').find('.username-feedback').remove();
                            $input.closest('.mb-3').append(
                                '<div class="username-feedback invalid">' +
                                    '<i class="fas fa-times-circle validation-icon"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                '</div>'
                            );
                            $input.closest('.input-group').find('.input-group-text').addClass('border-danger');
                        }
                    },
                    error: function() {
                        // Remove loading feedback
                        $input.closest('.mb-3').find('.username-feedback').remove();
                        $input.closest('.mb-3').append(
                            '<div class="username-feedback invalid">' +
                                '<i class="fas fa-exclamation-circle validation-icon"></i>' +
                                '<span>Errore durante il controllo. Riprova.</span>' +
                            '</div>'
                        );
                    }
                });
            }, 500);
        });
        
        // Email validation
        $('#user_email').on('input', function() {
            var $input = $(this);
            var email = $input.val().trim();
            
            // Clear previous timer
            if (emailTimer) {
                clearTimeout(emailTimer);
            }
            
            // Remove previous feedback
            $input.closest('.input-group').find('.validation-badge').remove();
            $input.closest('.mb-3').find('.email-feedback').remove();
            
            if (email.length < 5 || !email.includes('@')) {
                return; // Don't check if too short or invalid format
            }
            
            // Show loading state
            $input.closest('.input-group').after(
                '<div class="email-feedback loading">' +
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' +
                    '<span>Controllo in corso...</span>' +
                '</div>'
            );
            
            // Debounce the AJAX call (wait 500ms after last input)
            emailTimer = setTimeout(function() {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'bootscore_check_email',
                        user_email: email,
                        nonce: usernameNonce
                    },
                    success: function(response) {
                        // Remove previous feedback
                        $input.closest('.mb-3').find('.email-feedback').remove();
                        $input.closest('.input-group').find('.validation-badge').remove();
                        
                        if (response.success) {
                            // Email is available
                            $input.removeClass('is-invalid').addClass('is-valid');
                            $input.closest('.input-group').after(
                                '<div class="email-feedback valid">' +
                                    '<i class="fas fa-check-circle validation-icon"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                '</div>'
                            );
                            $input.closest('.input-group').find('.input-group-text').addClass('border-success');
                        } else {
                            // Email is taken or invalid
                            $input.removeClass('is-valid').addClass('is-invalid');
                            $input.closest('.mb-3').find('.email-feedback').remove();
                            $input.closest('.mb-3').append(
                                '<div class="email-feedback invalid">' +
                                    '<i class="fas fa-times-circle validation-icon"></i>' +
                                    '<span>' + response.data.message + '</span>' +
                                '</div>'
                            );
                            $input.closest('.input-group').find('.input-group-text').addClass('border-danger');
                        }
                    },
                    error: function() {
                        // Remove loading feedback
                        $input.closest('.mb-3').find('.email-feedback').remove();
                        $input.closest('.mb-3').append(
                            '<div class="email-feedback invalid">' +
                                '<i class="fas fa-exclamation-circle validation-icon"></i>' +
                                '<span>Errore durante il controllo. Riprova.</span>' +
                            '</div>'
                        );
                    }
                });
            }, 500);
        });
        
        // Clear validation feedback when user starts typing again
        $('#user_login, #user_email').on('input', function() {
            $(this).removeClass('is-valid is-invalid');
            $(this).closest('.input-group').find('.input-group-text').removeClass('border-success border-danger');
        });
        
        // Form submission validation
        $('#registerform').on('submit', function(e) {
            var isValid = true;
            var $usernameFeedback = $('.username-feedback.invalid');
            var $emailFeedback = $('.email-feedback.invalid');
            
            // Check if there are any validation errors
            if ($usernameFeedback.length > 0 || $emailFeedback.length > 0) {
                e.preventDefault();
                alert('Correggi gli errori evidenziati prima di procedere.');
                return false;
            }
            
            // Check if fields are empty
            if (!$('#user_login').val().trim()) {
                e.preventDefault();
                alert('Il nome utente è richiesto.');
                $('#user_login').focus();
                return false;
            }
            
            if (!$('#user_email').val().trim()) {
                e.preventDefault();
                alert('L\'indirizzo email è richiesto.');
                $('#user_email').focus();
                return false;
            }
        });
    });
})(jQuery);
</script>

<?php get_footer(); ?>
