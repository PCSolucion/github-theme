<?php
/**
 * Template para archivos (categoría, etiquetas, fechas, etc.)
 *
 * @package GitHubThem
 */

get_header();
?>

<div class="site-wrapper">
    <main class="content-area">
        <header class="archive-header">
            <?php
            the_archive_title('<h1 class="archive-title">', '</h1>');
            the_archive_description('<div class="archive-description">', '</div>');
            ?>
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
                                <span class="post-date">
                                    <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                                        <path fill-rule="evenodd" d="M1.75 2.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V2.75a.25.25 0 00-.25-.25H1.75zM0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75zm9.22 3.72a.75.75 0 000 1.06L10.69 8 9.22 9.47a.75.75 0 101.06 1.06l2-2a.75.75 0 000-1.06l-2-2a.75.75 0 00-1.06 0zm-3.44 0a.75.75 0 010 1.06L5.31 8l1.47 1.47a.75.75 0 11-1.06 1.06l-2-2a.75.75 0 010-1.06l2-2a.75.75 0 011.06 0z"></path>
                                    </svg>
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo get_the_date(); ?>
                                    </time>
                                </span>
                                
                                <span class="post-author">
                                    <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                                        <path fill-rule="evenodd" d="M10.5 5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zm.061 3.073a4 4 0 10-5.122 0 6.004 6.004 0 00-3.431 2.84C.896 12.217 1.826 13 3.221 13h1.214a4.474 4.474 0 011.715-.564 4.504 4.504 0 012.314 0c.305.132.626.264.957.39a4.497 4.497 0 001.43-.564 6.01 6.01 0 00-1.295-2.853zM3.221 14c-2.005 0-3.268-1.145-3.268-2.7 0-1.554 1.263-2.7 3.268-2.7 2.005 0 3.268 1.146 3.268 2.7 0 1.555-1.263 2.7-3.268 2.7zm9.558 0c-2.005 0-3.268-1.145-3.268-2.7 0-1.554 1.263-2.7 3.268-2.7 2.005 0 3.268 1.146 3.268 2.7 0 1.555-1.263 2.7-3.268 2.7z"></path>
                                    </svg>
                                    <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                                        <?php the_author(); ?>
                                    </a>
                                </span>
                            </div>
                        </header>
                        
                        <div class="post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        
                        <?php if (has_tag()) : ?>
                            <div class="post-tags">
                                <?php
                                $tags = get_the_tags();
                                if ($tags) {
                                    foreach ($tags as $tag) :
                                ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php 
                                    endforeach;
                                }
                                ?>
                            </div>
                        <?php endif; ?>
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
                <p><?php esc_html_e('No se encontraron entradas en este archivo.', 'github-theme'); ?></p>
            </div>
        <?php endif; ?>
    </main>
    
    <?php get_sidebar(); ?>
</div>

<?php
get_footer();

