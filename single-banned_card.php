<?php
/**
 * Template for displaying single banned card posts
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <div class="container py-5">
            <?php while (have_posts()):
                the_post();
                $scryfall_link = get_field('scryfall_link');
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header text-center mb-5">
                        <?php the_title('<h1 class="entry-title text-danger">', '</h1>'); ?>
                    </header>

                    <div class="row justify-content-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <?php
                            $image_html = '';
                            if (has_post_thumbnail()) {
                                $image_html = get_the_post_thumbnail(null, 'large', array('class' => 'img-fluid rounded shadow-sm'));
                            } else {
                                $scryfall_image_url = function_exists('lpdh_get_scryfall_image_url') ? lpdh_get_scryfall_image_url(get_the_ID()) : '';
                                if (!empty($scryfall_image_url) && $scryfall_image_url !== 'error') {
                                    $image_html = '<img src="' . esc_url($scryfall_image_url) . '" class="img-fluid rounded shadow-sm" alt="' . esc_attr(get_the_title()) . '">';
                                }
                            }

                            if (!empty($image_html)):
                                echo $image_html;
                            else: ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center p-5"
                                    style="min-height: 300px;">
                                    <i class="fas fa-ban fa-5x text-danger opacity-25"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body p-4">
                                    <h3 class="h5 border-bottom pb-2 mb-3">Ban Reason</h3>
                                    <div class="entry-content !f-plantin">
                                        <?php the_content(); ?>
                                    </div>

                                    <?php if ($scryfall_link): ?>
                                        <div class="mt-4">
                                            <a href="<?php echo esc_url($scryfall_link); ?>" target="_blank" rel="noopener"
                                                class="btn btn-outline-primary">
                                                View on Scryfall <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    </main>
</div>

<?php get_footer();