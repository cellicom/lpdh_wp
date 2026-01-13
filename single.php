<?php
/**
 * Template for displaying all single posts
 *
 * @package Bootscore Child
 */

get_header(); ?>

<div id="content" class="site-content <?= esc_attr(apply_filters('bootscore/class/container', 'container', 'single')); ?> <?= esc_attr(apply_filters('bootscore/class/content/spacer', 'pt-3 pb-5', 'single')); ?>">
    <div id="primary" class="content-area">

        <?php do_action( 'bootscore_after_primary_open', 'single' ); ?>

        <?php the_breadcrumb(); ?>

        <div class="row">
            <div class="<?= esc_attr(apply_filters('bootscore/class/main/col', 'col')); ?>">

                <main id="main" class="site-main">

                    <?php while ( have_posts() ) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php bootscore_category_badge(); ?>
                                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                                
                                <div class="entry-meta text-muted">
                                    <span class="posted-on">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        <time class="entry-date published" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                            <?php echo esc_html( get_the_date() ); ?>
                                        </time>
                                    </span>
                                    <span class="posted-by ms-3">
                                        <i class="fas fa-user me-1"></i>
                                        <a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
                                            <?php echo esc_html( get_the_author() ); ?>
                                        </a>
                                    </span>
                                </div>
                            </header>

                            <div class="entry-content clearfix">
                                <?php if ( has_post_thumbnail() ) : ?>
                                    <div class="single-post-featured-image mb-3">
                                        <?php the_post_thumbnail( 'large', array( 'class' => 'img-fluid rounded shadow-sm', 'style' => 'max-height: 300px; object-fit: contain; width: 100%;' ) ); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php the_content(); ?>
                            </div>

                            <footer class="entry-footer mt-5 border-top pt-3">
                                <?php
                                bootscore_tags();
                                ?>
                                
                                <nav aria-label="bs page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item">
                                            <?php previous_post_link('%link'); ?>
                                        </li>
                                        <li class="page-item">
                                            <?php next_post_link('%link'); ?>
                                        </li>
                                    </ul>
                                </nav>
                                
                                <?php
                                // Articoli correlati
                                $categories = get_the_category();
                                if ($categories) {
                                    $category_ids = array();
                                    foreach ($categories as $individual_category) {
                                        $category_ids[] = $individual_category->term_id;
                                    }
                                    $args = array(
                                        'category__in' => $category_ids,
                                        'post__not_in' => array(get_the_ID()),
                                        'posts_per_page' => 3,
                                        'ignore_sticky_posts' => 1
                                    );
                                    $related_query = new WP_Query($args);
                                    if ($related_query->have_posts()) {
                                        ?>
                                        <div class="related-posts mt-5 pt-4 border-top">
                                            <h3 class="h4 mb-4"><?php esc_html_e('Articoli correlati', 'bootscore'); ?></h3>
                                            <div class="row g-4">
                                                <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                                    <div class="col-md-4">
                                                        <div class="card h-100 shadow-sm border-0">
                                                            <?php if (has_post_thumbnail()) : ?>
                                                                <a href="<?php the_permalink(); ?>">
                                                                    <?php the_post_thumbnail('medium_large', array('class' => 'card-img-top object-fit-cover')); ?>
                                                                </a>
                                                            <?php endif; ?>
                                                            <div class="card-body">
                                                                <?php bootscore_category_badge(); ?>
                                                                <h5 class="card-title h6 my-2">
                                                                    <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark stretched-link">
                                                                        <?php the_title(); ?>
                                                                    </a>
                                                                </h5>
                                                                <small class="text-muted"><?php echo get_the_date(); ?></small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    wp_reset_postdata();
                                }
                                ?>
                            </footer>

                        </article>

                        <?php comments_template(); ?>

                    <?php endwhile; ?>

                </main>

            </div>
            <?php get_sidebar(); ?>
        </div>

    </div>
</div>

<?php get_footer(); ?>
