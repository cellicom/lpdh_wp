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

// Handle Account Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_account') {
    if (wp_verify_nonce($_POST['delete_account_nonce'], 'delete_account_action')) {
        require_once(ABSPATH . 'wp-admin/includes/user.php');

        // Permanently delete user's decks
        $decks = get_posts(array(
            'post_type' => 'deck',
            'author' => $user_id,
            'posts_per_page' => -1,
            'post_status' => 'any'
        ));

        foreach ($decks as $deck) {
            wp_delete_post($deck->ID, true);
        }

        // Delete user (without reassignment for other posts to keep events unchanged)
        wp_delete_user($user_id);
        wp_redirect(home_url());
        exit;
    } else {
        $errors[] = "Security check failed for account deletion.";
    }
}

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

        // Private Profile
        update_user_meta($user_id, 'private_profile', isset($_POST['private_profile']) ? '1' : '0');

        // Preferred City
        if (isset($_POST['preferred_city'])) {
            update_user_meta($user_id, 'preferred_city', sanitize_text_field($_POST['preferred_city']));
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

                        <!-- Privacy Settings -->
                        <h4 class="mb-4 text-primary"><i class="fas fa-shield-alt me-2"></i>Privacy Settings</h4>
                        <div class="mb-4">
                            <div class="form-check ps-0 d-flex align-items-center gap-3">
                                <input type="checkbox" name="private_profile" id="private_profile"
                                    class="form-check-input ms-0" value="1" <?php checked('1', get_user_meta($user_id, 'private_profile', true)); ?>>
                                <label for="private_profile" class="form-label fw-bold mb-0">Private Profile</label>
                            </div>
                            <small class="text-warning d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Your profile will become private hiding the user detail page.
                            </small>
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

                        <?php
                        $cities = array();
                        $places_query = new WP_Query(array(
                            'post_type' => 'place',
                            'posts_per_page' => -1,
                            'post_status' => 'publish',
                        ));
                        if ($places_query->have_posts()) {
                            while ($places_query->have_posts()) {
                                $places_query->the_post();
                                $city = get_field('place_city');
                                if (!empty($city)) {
                                    $cities[$city] = $city;
                                }
                            }
                            wp_reset_postdata();
                        }
                        sort($cities);
                        $user_preferred_city = get_user_meta($user_id, 'preferred_city', true);
                        ?>
                        <?php if (!empty($cities)): ?>
                            <div class="mb-4">
                                <label for="preferred_city" class="form-label fw-bold">Preferred City</label>
                                <select name="preferred_city" id="preferred_city" class="form-select form-control">
                                    <option value="">Select your preferred city...</option>
                                    <?php foreach ($cities as $city): ?>
                                        <option value="<?php echo esc_attr($city); ?>" <?php selected($user_preferred_city, $city); ?>>
                                            <?php echo esc_html($city); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-info">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    Your preferred city.
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <label for="user_url" class="form-label fw-bold">Website</label>
                            <input type="url" name="user_url" id="user_url" class="form-control"
                                value="<?php echo esc_attr($current_user->user_url); ?>">
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
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

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center mb-5">
                            <a href="<?php echo esc_url(get_author_posts_url($user_id)); ?>"
                                class="btn btn-outline-light">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>

                    <!-- Danger Zone -->
                    <div class="card border-danger bg-dark shadow-lg mt-5">
                        <div class="card-body p-4">
                            <h4 class="text-danger mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </h4>
                            <p class="text-light mb-4">
                                Once you delete your account, there is no going back. Please be certain.
                                <strong class="text-danger">All your assigned decks will also be permanently
                                    deleted.</strong>
                            </p>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#deleteAccountModal">
                                <i class="fas fa-user-slash me-2"></i>Delete My Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Delete Account Confirmation Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow bg-dark text-white border-danger">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-danger" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="mb-4">
                    <i class="fas fa-exclamation-circle text-danger fa-4x mb-3"></i>
                </div>
                <h4 class="fw-bold mb-3">Are you absolutely sure?</h4>
                <p class="text-light mb-0">
                    This action is <strong class="text-danger uppercase">irreversible</strong>.
                    Your profile, personal settings, and <strong class="text-danger">all your assigned decks</strong>
                    will be permanently removed.
                </p>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                <form method="post">
                    <?php wp_nonce_field('delete_account_action', 'delete_account_nonce'); ?>
                    <input type="hidden" name="action" value="delete_account">
                    <button type="submit" class="btn btn-danger px-4">Yes, Delete My Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>