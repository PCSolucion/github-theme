<?php
/**
 * Template para página 404 (No encontrada)
 *
 * @package GitHubTheme
 */

get_header();
?>

<div class="site-wrapper">
    <main class="content-area">
        <div class="error-404">
            <h1>404</h1>
            <p><?php esc_html_e('Página no encontrada', 'github-theme'); ?></p>
            <p>
                <?php esc_html_e('Lo sentimos, la página que está buscando no existe o ha sido movida.', 'github-theme'); ?>
            </p>
            
            <div class="error-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="button">
                    <?php esc_html_e('Volver al inicio', 'github-theme'); ?>
                </a>
            </div>
            
            <div class="search-form-container" style="max-width: 500px; margin: 32px auto 0;">
                <?php get_search_form(); ?>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();




















