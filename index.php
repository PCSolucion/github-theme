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
        <?php if (is_home() || is_front_page()) : ?>
            <h1 class="screen-reader-text"><?php bloginfo('name'); ?></h1>
        <?php endif; ?>


        <?php 
        // Mostrar tabla de contribuciones antes de los posts
        if (is_home() || is_front_page()) {
            echo '<div id="contributions">';
            github_theme_render_contributions_table();
            echo '</div>';
        }
        ?>
        
        <?php if (have_posts()) : ?>
            <div id="latest-posts" class="post-list">
                <?php 
                while (have_posts()) : the_post();
                    get_template_part( 'template-parts/content', 'post' );
                endwhile; 
                ?>
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
                <p><?php esc_html_e('No se encontraron entradas.', 'github-theme'); ?></p>
            </div>
        <?php endif; ?>
    </main>
    
    <?php get_sidebar(); ?>
</div>

<?php
get_footer();

