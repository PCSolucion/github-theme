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
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        <header class="post-header">
                            <?php github_theme_post_categories(); ?>
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                        </header>

                        <footer class="post-footer">
                            <div class="post-meta">
                                <span class="commit-hash" title="<?php esc_attr_e('ID del commit (ficticio)', 'github-theme'); ?>"><?php echo github_theme_get_post_commit_hash(); ?></span>
                                <?php 
                                $file_size = github_theme_get_total_download_size();
                                if ( ! empty( $file_size ) ) : 
                                ?>
                                <span class="file-size" title="<?php esc_attr_e('Peso total estimado (HTML + Imágenes)', 'github-theme'); ?>"><?php echo $file_size; ?></span>
                                <?php endif; ?>
                                <span class="post-date">
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo get_the_date(); ?></time>
                                </span>
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

