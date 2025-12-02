<?php
/**
 * Header del tema
 *
 * @package GitHubTheme
 */
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

<!-- Barra de notificación de Twitch Stream -->
<div class="twitch-stream-bar" data-channel="liiukiin" style="display: none;">
    <a href="https://www.twitch.tv/liiukiin" target="_blank" rel="noopener noreferrer" class="twitch-stream-link">
        <svg class="twitch-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/>
        </svg>
        <span class="twitch-text">🔴 En directo en Twitch</span>
        <span class="twitch-cta">Ver stream →</span>
    </a>
</div>

<header class="site-header">
    <div class="header-container">
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo.svg" alt="<?php bloginfo('name'); ?>" class="site-logo-image" />
            </a>
        </div>
        
        <div class="header-search-wrapper">
            <form role="search" method="get" class="AppHeader-search" action="<?php echo esc_url(home_url('/')); ?>">
                <input 
                    type="search" 
                    class="AppHeader-search-input" 
                    placeholder="Buscar o saltar a..."
                    value="<?php echo get_search_query(); ?>" 
                    name="s"
                />
                <svg class="AppHeader-search-icon" aria-hidden="true" viewBox="0 0 16 16" version="1.1">
                    <path fill-rule="evenodd" d="M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z"></path>
                </svg>
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
