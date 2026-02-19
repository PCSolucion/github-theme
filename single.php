<?php
/**
 * Template para entradas individuales
 * @package GitHubTheme
 */
get_header();
?>



<?php while (have_posts()) : the_post(); ?>
    <!-- Hero Section -->
    <header class="single-hero">
        <div class="hero-wrap">
            <?php github_theme_post_categories(); ?>

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <div class="post-meta">
                <span>
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.5 5a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0zM3.761 15.002a.75.75 0 01-.75-.75 10 10 0 0110 0 .75.75 0 01-.75.75h-8.5z"></path>
                    </svg>
                    <?php the_author(); ?>
                </span>

                <span>
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor">
                        <path fill-rule="evenodd" d="M1.75 2.5a.25.25 0 00-.25.25v10.5c0 .138.112.25.25.25h12.5a.25.25 0 00.25-.25V2.75a.25.25 0 00-.25-.25H1.75zM0 2.75C0 1.784.784 1 1.75 1h12.5c.966 0 1.75.784 1.75 1.75v10.5A1.75 1.75 0 0114.25 15H1.75A1.75 1.75 0 010 13.25V2.75z"></path>
                    </svg>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo get_the_date(); ?>
                    </time>
                </span>
                
                </span>
            </div>
            </div>
        </div>
    </header>

    <div class="single-layout">
        <main class="single-main">
            <article id="post-<?php the_ID(); ?>" <?php post_class('post-article'); ?>>
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
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>">
                                    #<?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </footer>
                <?php endif; ?>
            </article>
            

        </main>
        
        <aside class="single-aside">
            <div class="toc-box">
                <h3>Contenido</h3>
                <nav id="table-of-contents">
                    <!-- JS will populate this -->
                </nav>
            </div>
            <?php dynamic_sidebar('sidebar-1'); ?>
        </aside>
    </div>
<?php endwhile; ?>

<?php
get_footer();
