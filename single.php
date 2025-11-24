<?php
/**
 * Template para entradas individuales
 *
 * @package GitHubTheme
 */

get_header();
?>

<?php while (have_posts()) : the_post(); ?>
    <!-- Hero Section -->
    <header class="single-post-hero">
        <div class="hero-container">
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

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="post-meta">
                <span class="post-author">
                    <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                        <path fill-rule="evenodd" d="M10.5 5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM3.761 15.002a.75.75 0 01-.75-.75 10 10 0 0110 0 .75.75 0 01-.75.75h-8.5z"></path>
                    </svg>
                    <?php the_author(); ?>
                </span>

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
            </div>
        </div>
    </header>

    <div class="site-wrapper">
        <div class="single-post-grid">
            <main class="single-post-content">
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-post-article'); ?>>
                    <div class="entry-content">
                        <?php
                        the_content();
                        
                        wp_link_pages(array(
                            'before' => '<div class="page-links">' . esc_html__('Páginas:', 'github-theme'),
                            'after' => '</div>',
                        ));
                        ?>
                    </div>
                    
                    <?php if (has_tag()) : ?>
                        <footer class="entry-footer">
                            <div class="post-tags">
                                <?php
                                $tags = get_the_tags();
                                foreach ($tags as $tag) :
                                ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag">
                                        #<?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </footer>
                    <?php endif; ?>
                </article>
                
                <?php
                // Comentarios
                if (comments_open() || get_comments_number()) :
                    comments_template();
                endif;
                ?>
            </main>
            
            <aside class="single-post-sidebar">
                <div class="toc-container">
                    <h3 class="toc-title">Contenido</h3>
                    <nav id="table-of-contents" class="toc-nav">
                        <!-- JS will populate this -->
                    </nav>
                </div>
                <?php dynamic_sidebar('sidebar-1'); ?>
            </aside>
        </div>
    </div>
<?php endwhile; ?>

<?php
get_footer();
