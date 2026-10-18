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
                $scryfall_link   = get_field('scryfall_link');
                $combined_with   = get_field('combined_with'); // array of WP_Post objects (multiple)
                $has_combined    = !empty($combined_with);
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header text-center mb-5">
                        <?php
                        if ($has_combined) {
                            $combined_names = array_map(function($p) { return get_the_title($p->ID); }, $combined_with);
                            $combined_label = implode(' + ', $combined_names);
                            echo '<h1 class="entry-title text-danger">' . esc_html(get_the_title()) . ' + ' . esc_html($combined_label) . '</h1>';
                        } else {
                            the_title('<h1 class="entry-title text-danger">', '</h1>');
                        }
                        ?>
                    </header>

                    <div class="row justify-content-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="d-flex align-items-start justify-content-center gap-2 flex-wrap">
                            <?php
                            // Main card image
                            $main_img = lpdh_banned_card_image_html(get_the_ID());
                            if ($main_img) {
                                echo $main_img;
                            } else { ?>
                                <div class="bg-light rounded d-flex align-items-center justify-content-center p-5" style="min-height: 300px;">
                                    <i class="fas fa-ban fa-5x text-danger opacity-25"></i>
                                </div>
                            <?php }

                            // Combined-with card images
                            if ($has_combined) {
                                foreach ($combined_with as $cw_post) {
                                    $cw_img = lpdh_banned_card_image_html($cw_post->ID);
                                    if ($cw_img) {
                                        echo $cw_img;
                                    }
                                }
                            }
                            ?>
                        </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body p-4">
                                    <h3 class="h5 border-bottom pb-2 mb-3">Ban Reason</h3>
                                    <div class="entry-content !f-plantin">
                                        <?php the_content(); ?>
                                    </div>

                                    <?php if ($scryfall_link):
                                        $scryfall_host = parse_url($scryfall_link, PHP_URL_HOST);
                                        // Remove 'www.'
                                        $scryfall_name = preg_replace('/^www\./', '', $scryfall_host);
                                        // Remove extension (everything after the last dot)
                                        $scryfall_name = preg_replace('/\.[^.]+$/', '', $scryfall_name);
                                        // Split by dots to handle subdomains (like sub.domain -> Sub Domain)
                                        $parts = explode('.', $scryfall_name);
                                        $parts = array_map('ucfirst', $parts);
                                        $scryfall_display_name = implode(' ', $parts);
                                        ?>
                                        <div class="mt-4">
                                            <a href="<?php echo esc_url($scryfall_link); ?>" target="_blank" rel="noopener"
                                                class="btn btn-primary">
                                                View on <?php echo esc_html($scryfall_display_name); ?> <i
                                                    class="fas fa-external-link-alt fa-xs ms-1"></i>
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