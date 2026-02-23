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
    <?php if (function_exists('github_theme_social_meta_tags')) : ?>
    <?php github_theme_social_meta_tags(); ?>
    <?php endif; ?>
    <link rel="icon" type="image/png" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAACXBIWXMAAA7EAAAOxAGVKw4bAAACAElEQVRYhe3Wv2sUQRjG8c+GQ0FFBEE0RbAQYRpLDQF/YGVhYSOi1v4DpjeFiIhwFpaxsbOwELESGxOJNiKoZASDCAZygkUIEkOIuxa3krvzluxdbiOCTzPsu++873d3Zt55+a+/rKT1IYRQw33sy01pwbiKJTTwGjOYjzEOBOAjDvYYZxlPMYG3vYAM9ZioSDtwDi9xKYSw5QCtIJMYGzTAIh7iQT6+0vztRRC3QgilYtdKAnzChRhjCnnwEdzF2S7+oziMDxsF7msJYoxpjPEzLmKui0tNyWXY1B6IMX7H44LXw5UD5PpSYN+5VQAHCuwLlQOEELbhTJdXKd6XiVH2FHQmhl24gSNdXBqaR3VgAMOYCCH8xHbNI3giH7vppuI60RfAflwr6fsE98reB4MsxSke4XKMcaXspEEApJoV7wrOxxiXeplcdglWrFe8Nc1e4Bve4AVmYoyrvSTuFWAWx7Q0Jv00H5sB+J003dgtV30aWS3PkcqyNeMn/5g/6H4gTz41RHYV7/AVC5JkWn3qtPrzNte+ClEJjeK29g8cwyRJ0OwpUdUf4GhB7BEdt2RVAHsL7EPYvRUAPwrsqUxbkaoKYLbAvigx32ro3IQp7mBPh71hvQZsrCx7Jkme4VRLjmVcl7VfUomqVJ+q4ZDmpluVmSNpGD9eWcp/U78AAOmLkXNgG2UAAAAASUVORK5CYII=">
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
                        <svg class="logo-dot" width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="#008ec2"/></svg><span class="logo-path">/<?php echo esc_html(get_bloginfo('name')); ?></span>
                    </a>
                </h1>
            <?php else : ?>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home" aria-label="<?php bloginfo('name'); ?>">
                    <svg class="logo-dot" width="6" height="6" viewBox="0 0 6 6"><circle cx="3" cy="3" r="3" fill="#008ec2"/></svg><span class="logo-path">/<?php echo esc_html(get_bloginfo('name')); ?></span>
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
                    role="button"
                    aria-haspopup="dialog"
                    aria-expanded="false"
                />
                <kbd class="AppHeader-search-kbd">Ctrl+K</kbd>
            </form>
        </div>
        
        <nav class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Menú Principal', 'github-theme'); ?>">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_id'        => 'github-main-menu',
                'menu_class'     => 'nav-menu',
                'container'      => false,
                'fallback_cb'    => 'github_theme_default_menu',
            ));
            ?>
        </nav>
    </div>
</header>
