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

                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="entry-featured-image">
                        <?php the_post_thumbnail( 'large', array( 'class' => 'img-fluid' ) ); ?>
                    </div>
                <?php endif; ?>

                <div class="entry-content">
                    <?php the_content(); ?>
                </div>

                <?php
                // Display custom fields
                $decklist = get_field('field_decklist');
                
                if ( $decklist ) : ?>
                    <div class="deck-custom-fields">
                        <h3>Informazioni Deck</h3>
                        
                        <?php if ( $decklist ) : ?>
                            <div class="decklist-field">
                                <strong>Decklist:</strong>
                                <a href="<?php echo esc_url( $decklist ); ?>" target="_blank" rel="noopener" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt"></i> Vedi Decklist
                                </a>
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
            // Post navigation
            the_post_navigation( array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Deck precedente:', 'bootscore' ) . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Deck successivo:', 'bootscore' ) . '</span> <span class="nav-title">%title</span>',
            ) );

            // If comments are open or we have at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
            ?>

        <?php endwhile; ?>

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

<?php get_sidebar(); ?>
<?php get_footer(); ?>
