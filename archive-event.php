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

            <?php if ( have_posts() ) : ?>
                <div class="event-archive-grid">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part('template-parts/card', 'event'); ?>
                    <?php endwhile; ?>
                </div>

                <div class="mt-5">
                    <?php
                    the_posts_pagination( array(
                        'mid_size'  => 2,
                        'prev_text' => __( 'Previous', 'bootscore' ),
                        'next_text' => __( 'Next', 'bootscore' ),
                    ) );
                    ?>
                </div>

            <?php else : ?>
                <div class="alert alert-info">
                    <?php esc_html_e( 'No events found.', 'bootscore' ); ?>
                </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<style>
.event-archive-grid {
    display: grid;
    grid-template-columns: 1fr; /* Mobile: 1 card per riga */
    gap: 20px;
    justify-content: center;
}

.event-card-inner {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    height: 100%;
    position: relative;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.event-card-inner:hover {
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    transform: translateY(-5px);
}

.event-thumbnail {
    height: 300px;
    overflow: hidden;
    background-color: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.event-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.placeholder-image {
    padding: 20px;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.placeholder-image img {
    object-fit: contain;
    max-height: 100px;
    width: auto;
}

.event-title {
    font-weight: 700;
    line-height: 1.3;
    min-height: 2.6em; /* Circa 2 righe */
}

.event-divider {
    margin: 10px 0 15px;
    opacity: 0.1;
}

/* Icons Colors */
.event-place i, .fa-map-marker-alt { color: #dc3545 !important; } /* Red */
.event-date i, .fa-calendar-alt { color: #0d6efd !important; } /* Dark Blue */
.winner-label, .winner-icon { color: #FFD700 !important; } /* Gold */

/* Tablet: 2 card per riga */
@media (min-width: 768px) {
    .event-archive-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Desktop: 4 card per riga */
@media (min-width: 1200px) {
    .event-archive-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Pagination Styling */
.navigation.pagination .nav-links {
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.navigation.pagination .page-numbers {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 50%;
    background-color: #fff;
    border: 1px solid #dee2e6;
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    line-height: 1;
}

.navigation.pagination .page-numbers:hover {
    background-color: #e9ecef;
    color: #0a58ca;
    border-color: #dee2e6;
}

.navigation.pagination .page-numbers.current {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
    pointer-events: none;
}

.navigation.pagination .page-numbers.dots {
    border: none;
    background: transparent;
    color: #6c757d;
}
</style>

<?php get_footer(); ?>