<?php
/**
 * Template para resultados de búsqueda
 *
 * @package GitHubTheme
 */

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
                    '<span class="search-term">' . get_search_query() . '</span>'
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
                                <span class="post-type">
                                    <?php echo get_post_type_object(get_post_type())->labels->singular_name; ?>
                                </span>
                                
                                <span class="post-date">
                                    <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                                        <path fill-rule="evenodd" d="M1.75 2.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V2.75a.25.25 0 00-.25-.25H1.75zM0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75zm9.22 3.72a.75.75 0 000 1.06L10.69 8 9.22 9.47a.75.75 0 101.06 1.06l2-2a.75.75 0 000-1.06l-2-2a.75.75 0 00-1.06 0zm-3.44 0a.75.75 0 010 1.06L5.31 8l1.47 1.47a.75.75 0 11-1.06 1.06l-2-2a.75.75 0 010-1.06l2-2a.75.75 0 011.06 0z"></path>
                                    </svg>
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

