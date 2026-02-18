<?php
/**
 * Template para entradas individuales
 * @package GitHubTheme
 */
get_header();
?>

<style>
/* ESTILOS INLINE — Estilo npmx.dev */
.single-hero {
    background: var(--github-bg-secondary);
    border-bottom: 1px solid var(--github-border);
    padding: 48px 0;
    margin-bottom: 32px;
}

.hero-wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

.single-hero .entry-title {
    font-size: 36px;
    font-weight: 700;
    color: var(--github-text-primary);
    margin: 16px 0;
    font-family: var(--github-font-sans);
}

.single-hero .post-meta {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    color: var(--github-text-secondary);
    font-size: 14px;
    font-family: var(--github-font-mono);
}

.single-hero .post-meta svg {
    width: 16px;
    height: 16px;
    vertical-align: middle;
    margin-right: 4px;
}

.single-layout {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 48px;
    align-items: start;
}

.single-main {
    min-width: 0;
    grid-column: 2;
}

.single-aside {
    position: sticky;
    top: 120px;
    grid-column: 1;
    grid-row: 1;
}

.post-article {
    background: var(--github-bg-secondary);
    border: 1px solid var(--github-border);
    border-radius: 8px;
    padding: 40px;
}

.post-article .entry-content {
    font-size: 16px;
    line-height: 1.8;
    color: var(--github-text-primary);
    text-align: justify;
}

.post-article .entry-content h2 {
    font-size: 24px;
    margin-top: 48px;
    margin-bottom: 16px;
    border-bottom: 1px solid var(--github-border);
    padding-bottom: 10px;
    font-weight: 600;
    color: var(--github-text-primary);
    position: relative;
}

.post-article .entry-content h3 {
    font-size: 20px;
    margin-top: 32px;
    margin-bottom: 12px;
    font-weight: 600;
    color: var(--github-text-primary);
    position: relative;
}

/* Anchor link icon on hover */
.post-article .entry-content h2:hover::before,
.post-article .entry-content h3:hover::before {
    content: "🔗";
    position: absolute;
    left: -28px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0.5;
    transition: opacity 0.2s;
    cursor: pointer;
}

.post-article .entry-content h2:hover::before,
.post-article .entry-content h3:hover::before {
    opacity: 1;
}

.post-article .entry-content p {
    margin-bottom: 16px;
}

.post-article .entry-content a {
    color: var(--github-text-primary);
    text-decoration: underline;
    text-decoration-color: var(--github-text-tertiary);
    text-underline-offset: 3px;
    transition: text-decoration-color 0.2s ease;
}

.post-article .entry-content a:hover {
    text-decoration-color: var(--github-text-primary);
}

.toc-box {
    background: var(--github-bg-secondary);
    border: 1px solid var(--github-border);
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
    transition: border-color 0.2s ease;
}

.toc-box:hover {
    border-color: #444444;
}

.toc-box h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--github-text-primary);
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--github-border);
    font-family: var(--github-font-mono);
}

.toc-box h3::before {
    content: none;
}

#table-of-contents ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

#table-of-contents li {
    margin-bottom: 4px;
}

#table-of-contents a {
    display: block;
    padding: 8px 12px;
    padding-left: 24px;
    color: var(--github-text-secondary);
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-family: var(--github-font-mono);
    position: relative;
    transition: color 0.2s ease;
}

#table-of-contents a::before {
    content: "▸";
    position: absolute;
    left: 8px;
    color: var(--github-text-tertiary);
}

#table-of-contents a:hover {
    color: var(--github-text-primary);
    background: transparent;
}

#table-of-contents a.active {
    color: var(--github-text-primary);
    background: transparent;
    border-left: 2px solid var(--github-text-primary);
    font-weight: 600;
}

/* TOC Hierarchy */
#table-of-contents li ul {
    margin-left: 12px;
}

#table-of-contents .toc-h3 a {
    padding-left: 36px;
    font-size: 13px;
    color: var(--github-text-tertiary);
}

.post-tags {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid var(--github-border);
}

.post-tags a {
    padding: 4px 10px;
    background: var(--github-accent-subtle);
    border: 1px solid var(--github-border);
    border-radius: 16px;
    color: var(--github-text-secondary);
    text-decoration: none;
    font-size: 12px;
    font-family: var(--github-font-mono);
    transition: all 0.2s ease;
}

.post-tags a:hover {
    color: var(--github-text-primary);
    border-color: #444444;
}

@media (max-width: 1024px) {
    .single-layout {
        grid-template-columns: 1fr;
    }
    .single-main {
        grid-column: 1;
    }
    .single-aside {
        grid-column: 1;
        grid-row: 2;
        position: static;
    }
}

@media (max-width: 768px) {
    .post-article {
        padding: 24px;
    }
    .single-hero .entry-title {
        font-size: 28px;
    }
}
</style>

<?php while (have_posts()) : the_post(); ?>
    <!-- Hero Section -->
    <header class="single-hero">
        <div class="hero-wrap">
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
                
                <span>
                    <svg aria-hidden="true" viewBox="0 0 16 16" fill="currentColor">
                        <path fill-rule="evenodd" d="M1.5 8a6.5 6.5 0 1113 0 6.5 6.5 0 01-13 0zM8 0a8 8 0 100 16A8 8 0 008 0zm.5 4.75a.75.75 0 00-1.5 0v3.5a.75.75 0 00.471.696l2.5 1a.75.75 0 00.557-1.392L8.5 7.742V4.75z"></path>
                    </svg>
                    <?php echo github_theme_estimated_reading_time(); ?>
                </span>
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
            
            <?php
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>
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
