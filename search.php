<?php
/**
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="site-wrapper">
    <main class="content-area">
        <header class="search-header">
            <h1 class="search-title">
                <?php
                printf(
                    /* translators: %s: término de búsqueda */
                    esc_html__('Resultados de búsqueda para: %s', 'github-theme'),
                    '<span class="search-term">' . esc_html(get_search_query()) . '</span>'
                );
                ?>
            </h1>
        </header>
        
        <?php if (have_posts()) : ?>
            <div class="post-list">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        <header class="post-header">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <?php github_theme_post_meta(); ?>
                        </header>
                        
                        <div class="post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php
            // Paginación
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => '← Anterior',
                'next_text' => 'Siguiente →',
                'screen_reader_text' => ' ',
            ));
            ?>
            
        <?php else : ?>
            <div class="no-posts">
                <p><?php esc_html_e('No se encontraron resultados para su búsqueda.', 'github-theme'); ?></p>
                <p><?php esc_html_e('Intente con otros términos de búsqueda.', 'github-theme'); ?></p>
            </div>
        <?php endif; ?>
    </main>
    
    <?php get_sidebar(); ?>
</div>

<?php
get_footer();

