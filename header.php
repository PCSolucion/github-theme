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
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="header-container">
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
                <img src="https://res.cloudinary.com/pcsolucion/image/upload/v1616730181/Pcsolucion-LiukinTheme/logo.svg" alt="<?php bloginfo('name'); ?>" class="site-logo-image" />
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

<?php
/**
 * Menú por defecto si no hay menú configurado
 */
function github_theme_default_menu() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Inicio</a></li>';
    if (get_option('show_on_front') == 'page') {
        $page_for_posts = get_option('page_for_posts');
        if ($page_for_posts) {
            echo '<li><a href="' . esc_url(get_permalink($page_for_posts)) . '">Blog</a></li>';
        }
    }
    wp_list_pages(array(
        'title_li' => '',
        'exclude' => get_option('page_on_front'),
    ));
    echo '</ul>';
}
?>

