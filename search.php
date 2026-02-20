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
            
            <?php get_search_form(); ?>
        </header>
        
        <?php if (have_posts()) : ?>
            <div class="post-list">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        <header class="post-header">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="post-meta">
                                <span class="commit-hash" title="<?php esc_attr_e('ID del commit (ficticio)', 'github-theme'); ?>">
                                    <?php echo github_theme_get_post_commit_hash(); ?>
                                </span>
                                <span class="file-size" title="<?php esc_attr_e('Peso total estimado (HTML + Imágenes)', 'github-theme'); ?>">
                                    <?php echo github_theme_get_total_download_size(); ?>
                                </span>
                                <span class="post-date">
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo get_the_date(); ?>
                                    </time>
                                </span>
                            </div>
                        </header>
                        
                        <div class="post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <div class="post-footer">
                            <a href="<?php the_permalink(); ?>" class="button">
                                Ver más →
                            </a>
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

