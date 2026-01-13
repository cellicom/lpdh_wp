<?php
/**
 * Template for displaying deck archive
 * 
 * @package Bootscore Child
 * @version 6.0.0
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <header class="page-header">
            <h1 class="page-title"><?php post_type_archive_title(); ?></h1>
            <?php
            the_archive_description( '<div class="archive-description">', '</div>' );
            ?>
        </header>

        <?php if ( have_posts() ) : ?>

            <div class="deck-archive-grid">
                <?php while ( have_posts() ) : the_post(); ?>

                    <article id="post-<?php the_ID(); ?>" <?php post_class('deck-card'); ?>>

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="deck-thumbnail">
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'medium', array( 'class' => 'img-fluid' ) ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="deck-content">
                            <header class="entry-header">
                                <?php the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' ); ?>
                                
                                <div class="entry-meta">
                                    <span class="posted-on">
                                        <i class="fas fa-calendar"></i>
                                        <time class="entry-date published" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                                            <?php echo esc_html( get_the_date() ); ?>
                                        </time>
                                    </span>
                                    
                                    <span class="posted-by">
                                        <i class="fas fa-user"></i>
                                        <span class="author vcard"><a class="url fn n" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php echo esc_html( get_the_author() ); ?></a></span>
                                    </span>
                                </div>
                            </header>

                            <div class="entry-summary">
                                <?php the_excerpt(); ?>
                            </div>

                            <?php
                            // Display custom fields
                            $decklist = get_field('field_decklist');
                            
                            if ( $decklist ) : ?>
                                <div class="decklist-preview">
                                    <a href="<?php echo esc_url( $decklist ); ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> View Decklist
                                    </a>
                                </div>
                            <?php endif; ?>

                            <footer class="entry-footer">
                                <a href="<?php the_permalink(); ?>" class="btn btn-primary">
                                    <?php esc_html_e( 'Read more', 'bootscore' ); ?>
                                </a>
                            </footer>
                        </div>

                    </article>

                <?php endwhile; ?>
            </div>

            <?php
            // Pagination
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __( 'Previous', 'bootscore' ),
                'next_text' => __( 'Next', 'bootscore' ),
            ) );
            ?>

        <?php else : ?>

            <section class="no-results not-found">
                <header class="page-header">
                    <h1 class="page-title"><?php esc_html_e( 'No decks found', 'bootscore' ); ?></h1>
                </header>

                <div class="page-content">
                    <p><?php esc_html_e( 'It seems there are no decks to display.', 'bootscore' ); ?></p>
                </div>
            </section>

        <?php endif; ?>

    </main>
</div>

<style>
.deck-archive-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.deck-card {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: box-shadow 0.3s ease;
}

.deck-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.deck-thumbnail {
    position: relative;
    overflow: hidden;
}

.deck-thumbnail img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.deck-card:hover .deck-thumbnail img {
    transform: scale(1.05);
}

.deck-content {
    padding: 20px;
}

.entry-title {
    margin-top: 0;
    margin-bottom: 10px;
}

.entry-title a {
    color: #495057;
    text-decoration: none;
}

.entry-title a:hover {
    color: #007bff;
}

.entry-meta {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 15px;
}

.entry-meta span {
    margin-right: 15px;
}

.entry-meta i {
    margin-right: 5px;
}

.entry-summary {
    margin-bottom: 15px;
    line-height: 1.6;
}

.decklist-preview {
    margin-bottom: 15px;
}

.entry-footer {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
    margin-top: 15px;
}

.entry-footer .btn {
    text-decoration: none;
}

.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e9ecef;
}

.page-title {
    margin-bottom: 10px;
    color: #495057;
}

.archive-description {
    color: #6c757d;
    font-size: 1.1rem;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

/* Responsive */
@media (max-width: 768px) {
    .deck-archive-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .deck-content {
        padding: 15px;
    }
    
    .deck-thumbnail img {
        height: 180px;
    }
}

@media (max-width: 576px) {
    .deck-archive-grid {
        grid-template-columns: 1fr;
    }
    
    .entry-meta span {
        display: block;
        margin-right: 0;
        margin-bottom: 5px;
    }
}
</style>

<?php get_footer(); ?>
