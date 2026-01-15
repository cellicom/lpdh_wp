<?php
/**
 * Template for displaying event archive
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header container my-4 text-center">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
        </header>

        <div class="container pb-5">

            <!-- Filters -->
            <form method="get" class="mb-5">
                <div class="row justify-content-center g-3">
                    <div class="col-md-4 col-lg-3">
                        <select name="event_year" class="form-select" onchange="this.form.submit()">
                            <option value="">All Years</option>
                            <?php
                            global $wpdb;
                            $years = $wpdb->get_col("SELECT DISTINCT YEAR(meta_value) FROM $wpdb->postmeta WHERE meta_key = 'event_date' ORDER BY meta_value DESC");
                            foreach ($years as $year) {
                                $selected = (isset($_GET['event_year']) && $_GET['event_year'] == $year) ? 'selected' : '';
                                echo '<option value="' . esc_attr($year) . '" ' . $selected . '>' . esc_html($year) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-lg-3">
                        <select name="event_place_id" class="form-select" onchange="this.form.submit()">
                            <option value="">All Places</option>
                            <?php
                            $places = get_posts(['post_type' => 'place', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC']);
                            foreach ($places as $place) {
                                $selected = (isset($_GET['event_place_id']) && $_GET['event_place_id'] == $place->ID) ? 'selected' : '';
                                echo '<option value="' . esc_attr($place->ID) . '" ' . $selected . '>' . esc_html($place->post_title) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </form>

            <?php if (have_posts()): ?>
                <div class="event-archive-grid">
                    <?php while (have_posts()):
                        the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
                    <?php endwhile; ?>
                </div>

                <div class="mt-5">
                    <?php
                    the_posts_pagination(array(
                        'mid_size' => 2,
                        'prev_text' => __('Previous', 'bootscore'),
                        'next_text' => __('Next', 'bootscore'),
                    ));
                    ?>
                </div>

            <?php else: ?>
                <div class="alert alert-info">
                    <?php esc_html_e('No events found.', 'bootscore'); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

</main>
</div>

<?php get_footer(); ?>