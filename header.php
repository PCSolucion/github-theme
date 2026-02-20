<?php
/**
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (function_exists('github_theme_meta_description')) : ?>
    <meta name="description" content="<?php echo esc_attr(github_theme_meta_description()); ?>">
    <?php endif; ?>
    <?php if (function_exists('github_theme_social_meta_tags')) : ?>
    <?php github_theme_social_meta_tags(); ?>
    <?php endif; ?>
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>


<header class="site-header">
    <div class="header-container">
        <div class="site-branding">
            <?php if ( is_front_page() || is_home() ) : ?>
                <h1 class="site-title">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home" aria-label="<?php bloginfo('name'); ?>">
                        <svg class="logo-dot" width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="#008ec2"/></svg><span class="logo-path">/pcsolucion</span>
                    </a>
                </h1>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home" aria-label="<?php bloginfo('name'); ?>">
                    <svg class="logo-dot" width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="#008ec2"/></svg><span class="logo-path">/pcsolucion</span>
                </a>
            <?php endif; ?>
        </div>
        
        <div class="header-search-wrapper">
            <form role="search" method="get" class="AppHeader-search" action="<?php echo esc_url(home_url('/')); ?>">
                <span class="search-slash">/</span>
                <input 
                    type="search" 
                    id="header-search-input"
                    class="AppHeader-search-input" 
                    placeholder="buscar contenido..."
                    value="" 
                    name="s"
                    aria-label="Buscar contenido"
                    readonly
                />
                <kbd class="AppHeader-search-kbd">Ctrl+K</kbd>
            </form>
        </div>
        
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Menú Principal', 'github-theme'); ?>">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class' => 'nav-menu',
                'container' => false,
                'fallback_cb' => 'github_theme_default_menu',
            ));
            ?>
        </nav>
    </div>
</header>
