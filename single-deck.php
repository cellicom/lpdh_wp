<?php
/**
 * Template for displaying single deck posts
 * 
 * @package Bootscore Child
 * @version 6.0.0
 */

// Handle deletion before any output
if (isset($_POST['action']) && $_POST['action'] === 'delete_deck' && isset($_POST['deck_id'])) {
    $deck_id = intval($_POST['deck_id']);
    if (wp_verify_nonce($_POST['_wpnonce'], 'delete_deck_' . $deck_id)) {
        $deck = get_post($deck_id);
        if ($deck && $deck->post_type === 'deck' && (intval($deck->post_author) === get_current_user_id() || current_user_can('administrator'))) {
            wp_delete_post($deck_id, true);
            wp_redirect(get_author_posts_url($deck->post_author));
            exit;
        }
    }
}

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <?php while (have_posts()):
                        the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <?php the_title('<h1 class="entry-title mb-0">', '</h1>'); ?>
                                        <?php if (!lpdh_is_deck_legal(get_the_ID())): ?>
                                            <span class="badge bg-danger" style="font-size: 0.7em;">NOT LEGAL</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    if (is_user_logged_in() && (get_the_author_meta('ID') == get_current_user_id() || current_user_can('administrator'))):
                                        $deck_editor_url = lpdh_get_deck_editor_url();
                                        ?>
                                        <div class="deck-actions d-flex gap-2">
                                            <a href="<?php echo esc_url(add_query_arg('edit', get_the_ID(), $deck_editor_url)); ?>"
                                                class="btn btn-primary">
                                                <i class="fas fa-edit me-2"></i> Edit Deck
                                            </a>
                                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteDeckModal">
                                                <i class="fas fa-trash-alt me-2"></i> Delete Deck
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="entry-meta">
                                    <span class="posted-on">
                                        <i class="fas fa-calendar"></i>
                                        <time class="entry-date published"
                                            datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                            <?php echo esc_html(get_the_date()); ?>
                                        </time>
                                    </span>

                                    <span class="posted-by">
                                        <i class="fas fa-user"></i>
                                        <span class="author vcard"><a class="url fn n"
                                                href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php echo esc_html(get_the_author()); ?></a></span>
                                    </span>
                                </div>
                            </header>

                            <div class="deck-visuals d-flex justify-content-center flex-wrap gap-4 mb-5">
                                <?php
                                $commander = get_field('commander');
                                $commander_img = get_commander_image(get_the_ID());

                                if (has_post_thumbnail() || $commander): ?>
                                    <div class="deck-image-wrapper text-center">
                                        <img src="<?php echo esc_url($commander_img); ?>" class="img-fluid rounded shadow-sm"
                                            alt="<?php echo esc_attr($commander ? $commander : get_the_title()); ?>">

                                        <?php if ($commander): ?>
                                            <div class="mt-2 fw-bold  small text-uppercase">
                                                <?php esc_html_e('Commander', 'bootscore'); ?>
                                            </div>
                                            <div class="fw-bold"><?php echo esc_html($commander); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <?php
                                $partner = get_field('partner');
                                $partner_img = get_partner_image(get_the_ID());

                                if ($partner_img): ?>
                                    <div class="deck-image-wrapper text-center">
                                        <img src="<?php echo esc_url($partner_img); ?>" class="img-fluid rounded shadow-sm"
                                            alt="<?php echo esc_attr($partner ? $partner : 'Partner'); ?>">

                                        <?php if ($partner): ?>
                                            <div class="mt-2 fw-bold small text-uppercase">
                                                <?php esc_html_e('Partner / Background', 'bootscore'); ?>
                                            </div>
                                            <div class="fw-bold"><?php echo esc_html($partner); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php
                            // Display custom fields
                            $decklist = get_field('decklist');
                            $decklist_text = get_field('decklist_text');
                            $private_deck = get_field('private_deck');

                            if (!$private_deck && ($decklist || $decklist_text)): ?>
                                <div class="deck-custom-fields">
                                    <h3>Deck Information</h3>

                                    <?php if ($decklist): ?>
                                        <div class="decklist-field mb-4">
                                            <strong>Decklist:</strong>
                                            <a href="<?php echo esc_url($decklist); ?>" target="_blank" rel="noopener"
                                                class="btn btn-primary">
                                                <i class="fas fa-external-link-alt"></i>
                                                <?php esc_html_e('View Decklist', 'bootscore'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($decklist_text): ?>
                                        <div class="decklist-text-field">
                                            <h4><?php esc_html_e('Card List', 'bootscore'); ?></h4>
                                            <div class="decklist-content bg-light p-3 rounded border">
                                                <?php echo nl2br(esc_html($decklist_text)); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <footer class="entry-footer">
                                <?php
                                // Display categories and tags if any
                                $categories = get_the_category();
                                if (!empty($categories)) {
                                    echo '<div class="cat-links"><i class="fas fa-folder"></i> Categories: ';
                                    foreach ($categories as $category) {
                                        echo '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a> ';
                                    }
                                    echo '</div>';
                                }

                                $tags = get_the_tags();
                                if (!empty($tags)) {
                                    echo '<div class="tag-links"><i class="fas fa-tags"></i> Tags: ';
                                    foreach ($tags as $tag) {
                                        echo '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a> ';
                                    }
                                    echo '</div>';
                                }
                                ?>
                            </footer>

                        </article>

                        <?php
                        // If comments are open or we have at least one comment, load up the comment template.
                        if (comments_open() || get_comments_number()):
                            comments_template();
                        endif;
                        ?>

                        <?php if (is_user_logged_in() && (get_the_author_meta('ID') == get_current_user_id() || current_user_can('administrator'))): ?>
                            <!-- Delete Confirmation Modal -->
                            <div class="modal fade" id="deleteDeckModal" tabindex="-1" aria-labelledby="deleteDeckModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content shadow bg-dark text-white border-secondary">
                                        <div class="modal-header border-secondary">
                                            <h5 class="modal-title" id="deleteDeckModalLabel">Delete Deck</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body py-4">
                                            <div class="text-center mb-3">
                                                <i class="fas fa-exclamation-triangle text-danger fa-3x"></i>
                                            </div>
                                            <p class="text-center mb-0">Are you sure you want to delete
                                                <strong><?php the_title(); ?></strong>?<br>This action cannot be undone.
                                            </p>
                                        </div>
                                        <div class="modal-footer border-secondary">
                                            <button type="button" class="btn btn-outline-light"
                                                data-bs-dismiss="modal">Cancel</button>
                                            <form method="post">
                                                <?php wp_nonce_field('delete_deck_' . get_the_ID()); ?>
                                                <input type="hidden" name="action" value="delete_deck">
                                                <input type="hidden" name="deck_id" value="<?php echo get_the_ID(); ?>">
                                                <button type="submit" class="btn btn-danger">Confirm Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php endwhile; ?>

                </div>
            </div>
        </div>

    </main>
</div>



<?php get_footer(); ?>