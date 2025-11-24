<?php
/**
 * Template principal del blog
 *
 * @package GitHubTheme
 */

get_header();
?>

<div class="site-wrapper">
    <main class="content-area">
        <?php 
        // Mostrar tabla de contribuciones antes de los posts
        if (is_home() || is_front_page()) {
            github_theme_render_contributions_table();
        }
        ?>
        
        <?php if (have_posts()) : ?>
            <div class="post-list">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        <header class="post-header">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                        </header>
                        
                        <div class="post-excerpt">
                            <?php the_excerpt(); ?>
                        </div>

                        <footer class="post-footer">
                            <?php if (has_category()) : ?>
                                <div class="post-categories">
                                    <?php
                                    $categories = get_the_category();
                                    foreach ($categories as $category) :
                                        $category_color = github_theme_get_category_color($category->term_id);
                                    ?>
                                        <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="post-category" style="--category-color: <?php echo esc_attr($category_color); ?>">
                                            <span class="repo-language-color" style="background-color: <?php echo esc_attr($category_color); ?>"></span>
                                            <span class="category-name"><?php echo esc_html($category->name); ?></span>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="post-meta">
                                <span class="post-date">
                                    <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                                        <path fill-rule="evenodd" d="M1.75 2.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V2.75a.25.25 0 00-.25-.25H1.75zM0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75zm9.22 3.72a.75.75 0 000 1.06L10.69 8 9.22 9.47a.75.75 0 101.06 1.06l2-2a.75.75 0 000-1.06l-2-2a.75.75 0 00-1.06 0zm-3.44 0a.75.75 0 010 1.06L5.31 8l1.47 1.47a.75.75 0 11-1.06 1.06l-2-2a.75.75 0 010-1.06l2-2a.75.75 0 011.06 0z"></path>
                                    </svg>
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo get_the_date(); ?>
                                    </time>
                                </span>
                                
                                <span class="post-reading-time">
                                    <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                                        <path fill-rule="evenodd" d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zm.5 4.75a.75.75 0 00-1.5 0v3.5a.75.75 0 00.471.696l2.5 1a.75.75 0 00.557-1.392L8.5 7.742V4.75z"></path>
                                    </svg>
                                    <?php echo github_theme_estimated_reading_time(); ?>
                                </span>
                                
                                <?php if (comments_open() || get_comments_number()) : ?>
                                    <span class="post-comments">
                                        <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                                            <path fill-rule="evenodd" d="M2.75 2.5a.25.25 0 00-.25.25v7.5c0 .138.112.25.25.25h2a.75.75 0 01.75.75v2.19l2.72-2.72a.75.75 0 01.53-.22h4.5a.25.25 0 00.25-.25v-7.5a.25.25 0 00-.25-.25H2.75zM1 2.75C1 1.784 1.784 1 2.75 1h10.5c.966 0 1.75.784 1.75 1.75v7.5A1.75 1.75 0 0113.25 12H9.06l-2.573 2.573A1.457 1.457 0 014 13.543V12H2.75A1.75 1.75 0 011 10.25v-7.5z"></path>
                                        </svg>
                                        <?php comments_number('0', '1', '%'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </footer>
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
                <p><?php esc_html_e('No se encontraron entradas.', 'github-theme'); ?></p>
            </div>
        <?php endif; ?>
    </main>
    
    <?php get_sidebar(); ?>
</div>

<?php
get_footer();

