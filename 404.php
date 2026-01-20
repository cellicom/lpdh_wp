<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Bootscore Child
 */

get_header(); ?>

<div id="content" class="site-content container py-2">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <section class="error-404 not-found">

                        <header class="page-header mb-0 pb-0" style="padding-bottom: 0 !important;">
                            <h1 class="page-title home-title-hero fw-bold" style="font-size: 12rem; line-height: 0.8;">
                                404</h1>
                        </header>

                        <div class="page-content mb-4">
                            <div class="mb-3 mt-n4">
                                <img src="<?= get_stylesheet_directory_uri(); ?>/assets/img/404_hand_img.png"
                                    alt="404 Hand" class="img-fluid"
                                    style="max-height: 350px; width: auto; position: relative; z-index: -1;">
                            </div>

                            <p class="h3 mb-4">Seems like you drew another land...</p>

                            <a href="<?= esc_url(home_url('/')); ?>" class="btn btn-primary btn-lg px-5">Mulligan</a>
                        </div>

                    </section>
                </div>
            </div>
        </main>
    </div>
</div>

<?php get_footer(); ?>