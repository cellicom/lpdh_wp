<?php
/**
 * Template for displaying single event posts
 *
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <div class="container pb-5">
            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <?php while ( have_posts() ) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <header class="entry-header text-center mb-4 mt-4">
                                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                                
                                <?php
                                $event_date = get_field('field_event_date');
                                $place_obj = get_field('field_event_place');
                                ?>
                                
                                <div class="event-details text-muted d-flex justify-content-center gap-4 mt-2">
                                    <?php if ($event_date) : ?>
                                        <div class="event-date">
                                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                                            <?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($event_date))); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($place_obj) : ?>
                                        <div class="event-place">
                                            <i class="fas fa-map-marker-alt text-primary me-1"></i>
                                            <?php echo esc_html($place_obj->post_title); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </header>

                            <?php if ( has_post_thumbnail() ) : ?>
                                <div class="entry-featured-image mb-5 text-center">
                                    <?php the_post_thumbnail( 'medium_large', array( 'class' => 'img-fluid rounded shadow-sm', 'style' => 'max-height: 400px; width: auto;' ) ); ?>
                                </div>
                            <?php endif; ?>

                            <div class="entry-content mb-5">
                                <?php the_content(); ?>
                                
                                <?php 
                                $fb_link = get_field('field_event_fb_link');
                                if ($fb_link) : ?>
                                    <div class="mt-4 text-center">
                                        <a href="<?php echo esc_url($fb_link); ?>" target="_blank" class="btn btn-outline-primary">
                                            <i class="fab fa-facebook me-2"></i> Evento Facebook
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php
                            // Rankings Table
                            $rankings = get_field('field_event_ranking');
                            if ( is_array($rankings) && !empty($rankings) ) : ?>
                                <div class="event-rankings mb-5">
                                    <h3 class="mb-3 border-bottom pb-2">Classifica Giocatori</h3>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 60px;">#</th>
                                                    <th scope="col">Giocatore</th>
                                                    <th scope="col">Deck</th>
                                                    <th scope="col" class="text-center">Punti</th>
                                                    <th scope="col" class="text-center d-none d-sm-table-cell">W-D-L</th>
                                                    <th scope="col" class="text-center d-none d-md-table-cell">Via %</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $total_rankings = count($rankings);
                                                foreach ( $rankings as $index => $rank ) : 
                                                    $pos = isset($rank['pos']) ? $rank['pos'] : '';
                                                    $name = isset($rank['name']) ? $rank['name'] : '';
                                                    $deck = isset($rank['deck']) ? $rank['deck'] : '';
                                                    $points = isset($rank['points']) ? $rank['points'] : '0';
                                                    $win = isset($rank['win']) ? $rank['win'] : '0';
                                                    $draw = isset($rank['draw']) ? $rank['draw'] : '0';
                                                    $lose = isset($rank['lose']) ? $rank['lose'] : '0';
                                                    $via = isset($rank['via']) ? $rank['via'] : '-';
                                                    
                                                    // Colors for top 3
                                                    $row_class = '';
                                                    $pos_int = intval($pos);
                                                    
                                                    if ( $pos_int === 1 ) {
                                                        $row_class = 'rank-gold';
                                                    } elseif ( $pos_int === 2 ) {
                                                        $row_class = 'rank-silver';
                                                    } elseif ( $pos_int === 3 ) {
                                                        $row_class = 'rank-bronze';
                                                    }
                                                    
                                                    // Last player clown emoji
                                                    $display_pos = ($index === $total_rankings - 1) ? '🤡' : esc_html($pos);
                                                ?>
                                                    <tr class="<?php echo esc_attr($row_class); ?>">
                                                        <td class="text-center fw-bold"><?php echo $display_pos; ?></td>
                                                        <td>
                                                            <?php echo esc_html($name); ?>
                                                        </td>
                                                        <td class="fst-italic text-muted"><?php echo esc_html($deck); ?></td>
                                                        <td class="text-center fw-bold"><?php echo esc_html($points); ?></td>
                                                        <td class="text-center d-none d-sm-table-cell">
                                                            <span class="badge bg-success bg-opacity-10 text-success"><?php echo esc_html($win); ?></span>
                                                            <span class="text-muted">-</span>
                                                            <span class="badge bg-secondary bg-opacity-10 text-secondary"><?php echo esc_html($draw); ?></span>
                                                            <span class="text-muted">-</span>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger"><?php echo esc_html($lose); ?></span>
                                                        </td>
                                                        <td class="text-center d-none d-md-table-cell small"><?php echo esc_html($via); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </article>

                    <?php endwhile; ?>

                </div>
            </div>
        </div>

    </main>
</div>

<style>
.rank-gold td { color: #d4af37; font-weight: bold; }
.rank-silver td { color: #8a8a8a; font-weight: bold; }
.rank-bronze td { color: #cd7f32; font-weight: bold; }
</style>

<?php get_footer(); ?>