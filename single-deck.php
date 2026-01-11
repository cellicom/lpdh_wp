<?php
/**
 * Template for displaying single deck posts
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

                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                    
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

                <div class="deck-visuals d-flex justify-content-center flex-wrap gap-4 mb-5">
                    <?php 
                    $commander = get_field('commander');
                    if ( has_post_thumbnail() || $commander ) : ?>
                        <div class="deck-image-wrapper text-center">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'large', array( 'class' => 'img-fluid rounded shadow-sm' ) ); ?>
                            <?php endif; ?>
                            
                            <?php if ( $commander ) : ?>
                                <div class="mt-2 fw-bold text-muted small text-uppercase"><?php esc_html_e('Commander', 'bootscore'); ?></div>
                                <div class="fw-bold"><?php echo esc_html( $commander ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php 
                    $partner_image = get_field('featured_image_partner');
                    $partner = get_field('partner');
                    if ( $partner_image || $partner ) : ?>
                        <div class="deck-image-wrapper text-center">
                            <?php if ( $partner_image ) : ?>
                                <?php echo wp_get_attachment_image( $partner_image['ID'], 'large', false, array( 'class' => 'img-fluid rounded shadow-sm' ) ); ?>
                            <?php endif; ?>
                            
                            <?php if ( $partner ) : ?>
                                <div class="mt-2 fw-bold text-muted small text-uppercase"><?php esc_html_e('Partner / Background', 'bootscore'); ?></div>
                                <div class="fw-bold"><?php echo esc_html( $partner ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                // Display custom fields
                $decklist = get_field('decklist');
                $decklist_text = get_field('decklist_text');
                
                if ( $decklist || $decklist_text ) : ?>
                    <div class="deck-custom-fields">
                        <h3>Informazioni Deck</h3>
                        
                        <?php if ( $decklist ) : ?>
                            <div class="decklist-field mb-4">
                                <strong>Decklist:</strong>
                                <a href="<?php echo esc_url( $decklist ); ?>" target="_blank" rel="noopener" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt"></i> <?php esc_html_e('Vedi Decklist', 'bootscore'); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ( $decklist_text ) : ?>
                            <div class="decklist-text-field">
                                <h4><?php esc_html_e('Lista Carte', 'bootscore'); ?></h4>
                                <div class="decklist-content bg-light p-3 rounded border">
                                    <?php echo nl2br( esc_html( $decklist_text ) ); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <footer class="entry-footer">
                    <?php
                    // Display categories and tags if any
                    $categories = get_the_category();
                    if ( ! empty( $categories ) ) {
                        echo '<div class="cat-links"><i class="fas fa-folder"></i> Categorie: ';
                        foreach ( $categories as $category ) {
                            echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a> ';
                        }
                        echo '</div>';
                    }
                    
                    $tags = get_the_tags();
                    if ( ! empty( $tags ) ) {
                        echo '<div class="tag-links"><i class="fas fa-tags"></i> Tag: ';
                        foreach ( $tags as $tag ) {
                            echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '">' . esc_html( $tag->name ) . '</a> ';
                        }
                        echo '</div>';
                    }
                    ?>
                </footer>

            </article>

            <?php
            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        <?php endwhile; ?>

                </div>
            </div>
        </div>

    </main>
</div>

<style>
.deck-custom-fields {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin: 30px 0;
}

.deck-image-wrapper {
    max-width: 300px;
    width: 100%;
}

.deck-custom-fields h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #495057;
}

.decklist-field,
.author-field {
    margin-bottom: 15px;
}

.decklist-field strong,
.author-field strong {
    display: inline-block;
    width: 100px;
    color: #6c757d;
}

.decklist-field a {
    text-decoration: none;
}

.decklist-field a:hover {
    text-decoration: underline;
}

.entry-meta {
    margin-bottom: 20px;
    color: #6c757d;
}

.entry-meta span {
    margin-right: 20px;
}

.entry-meta i {
    margin-right: 5px;
}

@media (max-width: 768px) {
    .deck-custom-fields {
        padding: 15px;
    }
    
    .decklist-field strong,
    .author-field strong {
        display: block;
        width: 100%;
        margin-bottom: 5px;
    }
}
</style>

<?php get_footer(); ?>
