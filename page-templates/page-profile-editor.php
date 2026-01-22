<?php
/**
 * Template Name: User Profile Editor
 * Template for editing user profile from the frontend
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Access control: Only logged in users
if (!is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$success = false;
$errors = array();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_editor_nonce'])) {
    if (wp_verify_nonce($_POST['profile_editor_nonce'], 'update_user_profile')) {

        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $nickname = sanitize_text_field($_POST['nickname']);
        $display_name = sanitize_text_field($_POST['display_name']);
        $description = sanitize_textarea_field($_POST['description']);
        $user_url = esc_url_raw($_POST['user_url']);

        // Socials
        $social_fields = array('facebook', 'twitter', 'instagram', 'linkedin', 'github', 'youtube', 'discord');
        foreach ($social_fields as $field) {
            if (isset($_POST[$field])) {
                update_user_meta($user_id, $field, esc_url_raw($_POST[$field]));
            }
        }

        // Basic Info Update
        $userdata = array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'nickname' => $nickname,
            'display_name' => $display_name,
            'description' => $description,
            'user_url' => $user_url,
        );

        // Password Update
        if (!empty($_POST['new_password'])) {
            if ($_POST['new_password'] === $_POST['confirm_password']) {
                $userdata['user_pass'] = $_POST['new_password'];
            } else {
                $errors[] = "Passwords do not match.";
            }
        }

        if (empty($errors)) {
            $update_res = wp_update_user($userdata);
            if (is_wp_error($update_res)) {
                $errors[] = $update_res->get_error_message();
            } else {
                $success = true;
                // Refresh user data
                $current_user = wp_get_current_user();
            }
        }
    } else {
        $errors[] = "Security check failed.";
    }
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <header class="profile-editor-header mb-5 text-center">
                        <h1 class="display-6 fw-bold">
                            <i class="fas fa-user-edit me-2"></i>Edit My Profile
                        </h1>
                        <p class="">Keep your information up to date.</p>
                    </header>

                    <?php if ($success): ?>
                        <div class="alert alert-success shadow-sm mb-4">
                            <i class="fas fa-check-circle me-2"></i> Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger shadow-sm mb-4">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li>
                                        <?php echo esc_html($error); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="profile-editor-form text-white">
                        <?php wp_nonce_field('update_user_profile', 'profile_editor_nonce'); ?>

                        <!-- Avatar Section -->
                        <div class="mb-5 text-center">
                            <div class="mb-3">
                                <?php echo get_avatar($user_id, 120, '', '', array('class' => 'rounded-circle shadow border border-3 border-primary')); ?>
                            </div>
                            <h5>Profile Picture</h5>
                            <p class="small text-warning">
                                <i class="fas fa-info-circle me-1"></i>
                                Your avatar is managed via <a href="https://gravatar.com" target="_blank"
                                    class="text-info text-decoration-underline">Gravatar</a>.
                            </p>
                        </div>

                        <hr class="border-secondary mb-5">

                        <!-- General Info -->
                        <h4 class="mb-4 text-primary"><i class="fas fa-info-circle me-2"></i>General Information</h4>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="first_name" class="form-label fw-bold">First Name</label>
                                <input type="text" name="first_name" id="first_name" class="form-control"
                                    value="<?php echo esc_attr($current_user->first_name); ?>">
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="last_name" class="form-label fw-bold">Last Name</label>
                                <input type="text" name="last_name" id="last_name" class="form-control"
                                    value="<?php echo esc_attr($current_user->last_name); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="nickname" class="form-label fw-bold">Nickname</label>
                            <input type="text" name="nickname" id="nickname" class="form-control"
                                value="<?php echo esc_attr($current_user->nickname); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="display_name" class="form-label fw-bold">Display Name publicly as</label>
                            <select name="display_name" id="display_name" class="form-select form-control">
                                <!-- Options will be populated by JS -->
                                <option value="<?php echo esc_attr($current_user->display_name); ?>" selected>
                                    <?php echo esc_html($current_user->display_name); ?>
                                </option>
                            </select>
                            <div class="form-text text-info">
                                <i class="fas fa-info-circle me-1"></i>
                                This is how your name will be displayed to other users throughout the site.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Biography</label>
                            <textarea name="description" id="description" rows="4"
                                class="form-control"><?php echo esc_textarea($current_user->description); ?></textarea>
                            <div class="form-text text-info">A short bio about yourself.</div>
                        </div>

                        <div class="mb-4">
                            <label for="user_url" class="form-label fw-bold">Website</label>
                            <input type="url" name="user_url" id="user_url" class="form-control"
                                value="<?php echo esc_attr($current_user->user_url); ?>">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const firstName = document.getElementById('first_name');
                                const lastName = document.getElementById('last_name');
                                const nickname = document.getElementById('nickname');
                                const displaySelect = document.getElementById('display_name');
                                const username = "<?php echo esc_js($current_user->user_login); ?>";
                                const currentDisplay = "<?php echo esc_js($current_user->display_name); ?>";

                                function updateOptions() {
                                    const first = firstName.value.trim();
                                    const last = lastName.value.trim();
                                    const nick = nickname.value.trim();

                                    const options = new Set();
                                    options.add(username);
                                    if (nick) options.add(nick);
                                    if (first) options.add(first);
                                    if (last) options.add(last);
                                    if (first && last) {
                                        options.add(`${first} ${last}`);
                                        options.add(`${last} ${first}`);
                                    }

                                    const savedValue = displaySelect.value;
                                    displaySelect.innerHTML = '';

                                    options.forEach(opt => {
                                        const el = document.createElement('option');
                                        el.value = opt;
                                        el.textContent = opt;
                                        if (opt === savedValue || opt === currentDisplay) {
                                            el.selected = true;
                                        }
                                        displaySelect.appendChild(el);
                                    });
                                }

                                [firstName, lastName, nickname].forEach(el => {
                                    el.addEventListener('input', updateOptions);
                                });

                                // Initial load
                                updateOptions();
                            });
                        </script>

                        <hr class="border-secondary my-5">

                        <!-- Socials -->
                        <h4 class="mb-4 text-primary"><i class="fas fa-share-alt me-2"></i>Social Profiles</h4>
                        <div class="row">
                            <?php
                            $socials = array(
                                'facebook' => 'Facebook URL',
                                'twitter' => 'Twitter / X URL',
                                'instagram' => 'Instagram URL',
                                'linkedin' => 'LinkedIn URL',
                                'github' => 'GitHub URL',
                                'youtube' => 'YouTube URL',
                                'discord' => 'Discord username or URL'
                            );
                            foreach ($socials as $key => $label): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="<?php echo $key; ?>" class="form-label small">
                                        <?php echo $label; ?>
                                    </label>
                                    <input type="text" name="<?php echo $key; ?>" id="<?php echo $key; ?>"
                                        class="form-control form-control-sm"
                                        value="<?php echo esc_attr(get_user_meta($user_id, $key, true)); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <hr class="border-secondary my-5">

                        <!-- Password -->
                        <h4 class="mb-4 text-primary"><i class="fas fa-lock me-2"></i>Change Password</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label small">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label small">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password"
                                    class="form-control">
                            </div>
                        </div>
                        <div class="form-text text-info mb-4">Leave both blank if you don't want to change your
                            password.</div>

                        <hr class="border-secondary my-5">

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>"
                                class="btn btn-outline-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>