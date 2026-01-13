<?php
/**
 * Template for displaying FAQ archive
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header();

// Prepare data for box_faq.php
$faqs_data = array();

if ( have_posts() ) {
    while ( have_posts() ) {
        the_post();
        $faqs_data[] = array(
            'domanda'  => get_the_title(),
            'risposta' => apply_filters( 'the_content', get_the_content() ),
        );
    }
}

// Arguments for the template part
$args = array(
    'acf_fc_layout' => 'faq-archive-section',
    'visibile'      => true,
    'titolo'        => '',
    'faq'           => $faqs_data,
    'acf_index'     => 0,
);
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4 text-center">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <div class="container pb-4">
            <form method="get" action="<?php echo esc_url( get_post_type_archive_link( 'faq' ) ); ?>">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-lg-6">
                        <div class="input-group">
                            <input type="text" name="s" class="form-control" placeholder="<?php esc_attr_e( 'Search FAQs...', 'bootscore' ); ?>" value="<?php echo get_search_query(); ?>">
                            <input type="hidden" name="post_type" value="faq">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> <?php esc_html_e( 'Search', 'bootscore' ); ?>
                            </button>
                            <?php if ( get_search_query() ) : ?>
                                <a href="<?php echo esc_url( get_post_type_archive_link( 'faq' ) ); ?>" class="btn btn-outline-secondary" title="<?php esc_attr_e( 'Clear', 'bootscore' ); ?>"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <?php
        if ( ! empty( $faqs_data ) ) {
            get_template_part( 'template-parts/acfboxes/box_faq', null, $args );
        } else {
            ?>
            <div class="container py-5">
                <div class="alert alert-info text-center">
                    <?php esc_html_e( 'No FAQs found.', 'bootscore' ); ?>
                </div>
            </div>
            <?php
        }
        ?>

    </main>
</div>

<style>
.accordion {
    max-width: 800px;
    margin: 0 auto;
}
</style>

<?php get_footer(); ?>