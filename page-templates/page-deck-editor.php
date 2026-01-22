<?php
/**
 * Template Name: Deck Editor
 * Template for creating and editing decks from the frontend
 * 
 * @package Bootscore Child
 * @version 1.0.0
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

// Access control: Only logged in players or admins
if (!is_user_logged_in() || (!current_user_can('player') && !current_user_can('administrator'))) {
    wp_redirect(home_url());
    exit;
}

$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$is_edit = false;

if ($edit_id) {
    $deck_to_edit = get_post($edit_id);
    if ($deck_to_edit && $deck_to_edit->post_type === 'deck' && (intval($deck_to_edit->post_author) === get_current_user_id() || current_user_can('administrator'))) {
        $is_edit = true;

        // Update browser tab title
        add_filter('pre_get_document_title', function ($title) use ($deck_to_edit) {
            return 'Edit Deck: ' . $deck_to_edit->post_title . ' - ' . get_bloginfo('name');
        }, 999);

    } else {
        // Not authorized to edit this post
        wp_redirect(home_url());
        exit;
    }
}

$success = false;
$errors = array();

// Handle Form Submission
if (isset($_POST['deck_editor_nonce']) && wp_verify_nonce($_POST['deck_editor_nonce'], 'deck_editor_action')) {

    $deck_name = isset($_POST['deck_name']) ? sanitize_text_field($_POST['deck_name']) : '';
    $commander = isset($_POST['commander']) ? sanitize_text_field($_POST['commander']) : '';
    $partner = isset($_POST['partner']) ? sanitize_text_field($_POST['partner']) : '';
    $decklist_url = isset($_POST['decklist_url']) ? esc_url_raw($_POST['decklist_url']) : '';
    $decklist_text = isset($_POST['decklist_text']) ? sanitize_textarea_field($_POST['decklist_text']) : '';

    if (empty($deck_name)) {
        $errors[] = "Deck name is required.";
    }

    if (empty($errors)) {
        $post_data = array(
            'post_title' => $deck_name,
            'post_status' => 'publish',
            'post_type' => 'deck',
            'post_author' => get_current_user_id()
        );

        if ($is_edit) {
            $post_data['ID'] = $edit_id;
            $deck_id = wp_update_post($post_data);
        } else {
            $deck_id = wp_insert_post($post_data);
        }

        if (is_wp_error($deck_id)) {
            $errors[] = "Error while saving: " . $deck_id->get_error_message();
        } else {
            // Update ACF Fields
            if (function_exists('update_field')) {
                update_field('field_commander', $commander, $deck_id);
                update_field('field_partner', $partner, $deck_id);
                update_field('field_decklist', $decklist_url, $deck_id);
                update_field('field_decklist_text', $decklist_text, $deck_id);
            }

            // Handle Image Uploads
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            // Commander Image
            if (!empty($_FILES['commander_image']['name'])) {
                $attachment_id = media_handle_upload('commander_image', $deck_id);
                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($deck_id, $attachment_id);
                }
            }

            // Partner Image
            if (!empty($_FILES['partner_image']['name'])) {
                $attachment_id = media_handle_upload('partner_image', $deck_id);
                if (!is_wp_error($attachment_id) && function_exists('update_field')) {
                    update_field('field_featured_image_partner', $attachment_id, $deck_id);
                }
            }

            // Redirect to deck detail
            wp_redirect(get_permalink($deck_id));
            exit;
        }
    }
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <header class="deck-editor-header mb-5 text-center">
                        <h1 class="display-6 fw-bold">
                            <i class="fas fa-layer-group me-2"></i>
                            <?php echo $is_edit ? 'Edit: ' . esc_html($deck_to_edit->post_title) : 'Add New Deck'; ?>
                        </h1>
                        <p class="">Fill in the details below to
                            <?php echo $is_edit ? 'update your' : 'create a new'; ?> deck.
                        </p>
                    </header>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger shadow-sm mb-4">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo esc_html($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="" method="post" enctype="multipart/form-data" class="deck-editor-form">
                        <?php wp_nonce_field('deck_editor_action', 'deck_editor_nonce'); ?>

                        <div class="mb-4">
                            <label for="deck_name" class="form-label fw-bold">Name</label>
                            <input type="text" name="deck_name" id="deck_name" class="form-control form-control-lg"
                                value="<?php echo $is_edit ? esc_attr($deck_to_edit->post_title) : (isset($_POST['deck_name']) ? esc_attr($_POST['deck_name']) : ''); ?>"
                                required>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="commander" class="form-label fw-bold">Commander</label>
                                <input type="text" name="commander" id="commander" class="form-control"
                                    value="<?php echo $is_edit ? esc_attr(get_field('commander', $edit_id)) : (isset($_POST['commander']) ? esc_attr($_POST['commander']) : ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="commander_image" class="form-label fw-bold">Commander Image</label>
                                <input type="file" name="commander_image" id="commander_image" class="form-control"
                                    accept="image/*">
                                <small class="text-warning d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    If not uploaded, it will be automatically fetched from Scryfall.
                                </small>
                            </div>
                        </div>

                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <label for="partner" class="form-label fw-bold">Partner / Background</label>
                                <input type="text" name="partner" id="partner" class="form-control"
                                    value="<?php echo $is_edit ? esc_attr(get_field('partner', $edit_id)) : (isset($_POST['partner']) ? esc_attr($_POST['partner']) : ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="partner_image" class="form-label fw-bold">Partner Image</label>
                                <input type="file" name="partner_image" id="partner_image" class="form-control"
                                    accept="image/*">
                                <small class="text-warning d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>
                                    If not uploaded, it will be automatically fetched from Scryfall.
                                </small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="decklist_url" class="form-label fw-bold">Decklist URL</label>
                            <input type="url" name="decklist_url" id="decklist_url" class="form-control"
                                placeholder="https://www.moxfield.com/decks/..."
                                value="<?php echo $is_edit ? esc_url(get_field('decklist', $edit_id)) : (isset($_POST['decklist_url']) ? esc_url($_POST['decklist_url']) : ''); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="decklist_text" class="form-label fw-bold">Decklist (Text)</label>
                            <textarea name="decklist_text" id="decklist_text" class="form-control"
                                rows="10"><?php echo $is_edit ? esc_textarea(get_field('decklist_text', $edit_id)) : (isset($_POST['decklist_text']) ? esc_textarea($_POST['decklist_text']) : ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-3 mt-5">
                            <button type="submit" class="btn btn-primary btn-lg py-3 fw-bold shadow-sm">
                                <i class="fas fa-save me-2"></i>
                                <?php echo $is_edit ? 'Save Changes' : 'Create Deck'; ?>
                            </button>
                            <a href="<?php echo $is_edit ? get_permalink($edit_id) : get_author_posts_url(get_current_user_id()); ?>"
                                class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>