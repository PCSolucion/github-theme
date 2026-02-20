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
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('post-item'); ?>>
                        <header class="post-header">
                            <h2 class="post-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <div class="post-meta">
                                <?php $commit_hash = substr(md5(get_the_ID()), 0, 7); ?>
                                <span class="commit-hash" title="<?php esc_attr_e('ID del commit (ficticio)', 'github-theme'); ?>">
                                    <?php echo $commit_hash; ?>
                                </span>
                                <span class="file-size" title="<?php esc_attr_e('Peso total estimado (HTML + Imágenes)', 'github-theme'); ?>">
                                    <?php echo github_theme_get_total_download_size(); ?>
                                </span>
                                <span class="post-date">
                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo get_the_date(); ?>
                                    </time>
                                </span>
                            </div>
                            <?php github_theme_post_categories(); ?>
                        </header>
                        

                        
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
                'screen_reader_text' => ' ',
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

