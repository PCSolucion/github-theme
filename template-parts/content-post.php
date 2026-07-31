<?php
/**
 * Template part para mostrar la tarjeta de post en listados (Home, Archivos, Búsquedas).
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-item' ); ?>>
    <header class="post-header">
        <?php github_theme_post_categories(); ?>
        <h2 class="post-title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>
    </header>

    <footer class="post-footer">
        <?php github_theme_post_meta(); ?>
    </footer>
</article>
