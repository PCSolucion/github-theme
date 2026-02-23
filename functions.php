<?php
/**
 * GitHub Theme Functions
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// CONSTANTES
// =============================================================================

/** Versión del tema para control de caché (Cache Busting) */
define( 'GITHUB_THEME_VERSION', '1.0.6' ); // Versión estática para permitir caché en producción


// =============================================================================
// CONFIGURACIÓN DEL TEMA
// =============================================================================

/**
 * Configuración principal del tema.
 * Registra soporte de características, menús y tamaños de imagen.
 */
function github_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'align-wide' );

    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    register_nav_menus( array(
        'primary' => __( 'Menú Principal', 'github-theme' ),
        'footer'  => __( 'Menú Footer', 'github-theme' ),
    ) );
}
add_action( 'after_setup_theme', 'github_theme_setup' );

/**
 * Soporte para logo personalizado.
 */
function github_theme_custom_logo_setup() {
    add_theme_support( 'custom-logo', array(
        'height'      => 32,
        'width'       => 32,
        'flex-height' => true,
        'flex-width'  => true,
    ) );
}
add_action( 'after_setup_theme', 'github_theme_custom_logo_setup' );

/**
 * Paleta de colores para el editor de bloques.
 */
function github_theme_editor_color_palette() {
    add_theme_support( 'editor-color-palette', array(
        array(
            'name'  => __( 'Fondo Primario', 'github-theme' ),
            'slug'  => 'bg-primary',
            'color' => '#0a0a0a',
        ),
        array(
            'name'  => __( 'Fondo Secundario', 'github-theme' ),
            'slug'  => 'bg-secondary',
            'color' => '#111111',
        ),
        array(
            'name'  => __( 'Texto Primario', 'github-theme' ),
            'slug'  => 'text-primary',
            'color' => '#ededed',
        ),
        array(
            'name'  => __( 'Acento', 'github-theme' ),
            'slug'  => 'accent',
            'color' => '#ededed',
        ),
    ) );
}
add_action( 'after_setup_theme', 'github_theme_editor_color_palette' );

/**
 * Desactivar el editor Gutenberg (bloque clásico).
 */
add_filter( 'use_block_editor_for_post',      '__return_false', 10 );
add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );


// =============================================================================
// ASSETS: ESTILOS Y SCRIPTS
// =============================================================================

/**
 * Encolar estilos y scripts del tema.
 */
function github_theme_scripts() {
    // 1. Estilos Globales (Ahora incluyen variables, base y header)
    wp_enqueue_style( 'github-theme-main',  get_template_directory_uri() . '/assets/css/main.css', array(), GITHUB_THEME_VERSION );
    // 2. Estilos de Listados (Home, Archivos, Búsqueda)
    if ( ! is_singular() ) {
        wp_enqueue_style( 'github-theme-post-list', get_template_directory_uri() . '/assets/css/post-list.css', array( 'github-theme-main' ), GITHUB_THEME_VERSION );
    }

    // 4. Contribuciones (Solo en la Home)
    if ( is_home() || is_front_page() ) {
        wp_enqueue_style( 'github-theme-contributions', get_template_directory_uri() . '/assets/css/contributions.css', array( 'github-theme-main' ), GITHUB_THEME_VERSION );
    }



    // 6. Estilos exclusivos de lectura (Single)
    if ( is_singular() ) {
        wp_enqueue_style( 'github-theme-single', get_template_directory_uri() . '/assets/css/single.css', array( 
            'github-theme-main'
        ), GITHUB_THEME_VERSION );
    }

    wp_enqueue_script( 'github-theme-main', get_template_directory_uri() . '/assets/js/main.js', array(), GITHUB_THEME_VERSION, true );
    
    // Configuración para el buscador (Ahora integrado en main.js)
    wp_localize_script( 'github-theme-main', 'liveSearchData', array(
        'restUrl' => esc_url_raw( rest_url( 'wp/v2' ) ),
        'homeUrl' => esc_url_raw( home_url( '/' ) ),
    ) );


}
add_action( 'wp_enqueue_scripts', 'github_theme_scripts' );

// (Filtros de optimización movidos a inc/optimization.php)




// =============================================================================
// WIDGETS
// =============================================================================

/**
 * Registrar áreas de widgets del tema.
 */
function github_theme_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar Principal', 'github-theme' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Widgets que aparecen en la sidebar principal', 'github-theme' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer Widgets', 'github-theme' ),
        'id'            => 'footer-widgets',
        'description'   => __( 'Widgets que aparecen en el footer', 'github-theme' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'github_theme_widgets_init' );


// =============================================================================
// CONTENIDO Y FILTROS
// =============================================================================

/**
 * Limitar la longitud del excerpt a 30 palabras.
 */
function github_theme_excerpt_length( $length ) {
    return 30;
}
add_filter( 'excerpt_length', 'github_theme_excerpt_length' );

/**
 * Cambiar el texto "leer más" del excerpt.
 */
function github_theme_excerpt_more( $more ) {
    return '...';
}
add_filter( 'excerpt_more', 'github_theme_excerpt_more' );

/**
 * Agregar clases CSS personalizadas al <body>.
 */
function github_theme_body_classes( $classes ) {
    if ( is_singular() ) {
        $classes[] = 'singular';
    }

    if ( is_front_page() ) {
        $classes[] = 'home';
    }

    return $classes;
}
add_filter( 'body_class', 'github_theme_body_classes' );

/**
 * Limpiar etiquetas <p> y <br> dentro de bloques <pre>.
 * WordPress a veces envuelve el contenido de <pre> en <p>, rompiendo el formato de código.
 * Prioridad 9 para ejecutarse antes que wpautop.
 *
 * @param string $content El contenido del post.
 * @return string Contenido con las etiquetas <pre> saneadas.
 */
function github_theme_fix_pre_tags( $content ) {
    $content = preg_replace_callback( '/<pre([^>]*)>(.*?)<\/pre>/is', function( $matches ) {
        $pre_attrs    = $matches[1];
        $inner_content = $matches[2];

        $inner_content = str_replace( array( '<p>', '</p>' ), '', $inner_content );
        $inner_content = str_replace( array( '<br>', '<br/>', '<br />' ), "\n", $inner_content );

        return '<pre' . $pre_attrs . '>' . $inner_content . '</pre>';
    }, $content );

    return $content;
}
add_filter( 'the_content', 'github_theme_fix_pre_tags', 9 );


// =============================================================================
// FUNCIONES HELPER
// =============================================================================

// (Funciones de utilidad movidas a inc/template-functions.php)

// =============================================================================
// INCLUDES
// =============================================================================

require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/contributions.php';
require get_template_directory() . '/inc/live-search.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/security.php';
require get_template_directory() . '/inc/optimization.php';