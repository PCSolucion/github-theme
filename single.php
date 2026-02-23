<?php
/**
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>



<?php while (have_posts()) : the_post(); ?>
    <!-- Hero Section -->
    <header class="single-hero">
        <div class="hero-wrap">
            <?php github_theme_post_categories(); ?>

            <h1 class="entry-title"><?php the_title(); ?></h1>

            <?php github_theme_post_meta(); ?>
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
                    <?php echo github_theme_generate_toc(get_the_content()); ?>
                </nav>
            </div>
            <?php dynamic_sidebar('sidebar-1'); ?>
        </aside>
    </div>
<?php endwhile; ?>

<?php
get_footer();
