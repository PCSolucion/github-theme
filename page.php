<?php
/**
 * Template para páginas estáticas
 *
 * @package GitHubTheme
 */

get_header();
?>

<div class="site-wrapper">
    <main class="content-area">
        <?php while (have_posts()) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
                <header class="entry-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>
                
                <div class="entry-content">
                    <?php
                    the_content();
                    
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Páginas:', 'github-theme'),
                        'after' => '</div>',
                    ));
                    ?>
                </div>
                
                <?php
                // Si los comentarios están habilitados o hay comentarios
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>
            </article>
        <?php endwhile; ?>
    </main>
    
    <?php get_sidebar(); ?>
</div>

<?php
get_footer();




















