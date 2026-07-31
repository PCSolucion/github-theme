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
        <header class="archive-header">
            <?php
            the_archive_title('<h1 class="archive-title">', '</h1>');
            the_archive_description('<div class="archive-description">', '</div>');
            ?>
        </header>
        
        <?php if (have_posts()) : ?>
            <div class="post-list">
                <?php 
                while (have_posts()) : the_post();
                    get_template_part( 'template-parts/content', 'post' );
                endwhile; 
                ?>
            </div>
            
            <?php github_theme_pagination(); ?>
            
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

