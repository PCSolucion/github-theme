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
define( 'GITHUB_THEME_VERSION', '1.0.5' ); // Versión estática para permitir caché en producción


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
    // 1. Estilos Globales (Siempre necesarios)
    // Inline de variables.css para evitar una petición HTTP minúscula
    $variables_css_path = get_template_directory() . '/assets/css/variables.css';
    if ( file_exists( $variables_css_path ) ) {
        // Caché nativa (sin plugins) para evitar leer el disco y procesar regex en cada visita
        $mtime = filemtime( $variables_css_path );
        $cache = get_transient( 'github_theme_vars_inline_cache' );
        
        if ( false === $cache || $cache['mtime'] !== $mtime ) {
            $variables_css_content = file_get_contents( $variables_css_path );
            // Reemplazamos las rutas relativas de las fuentes por URLs absolutas para el inline (soporta minificadores)
            $variables_css_content = preg_replace( 
                "/(url\(['\"]?)\.\.\/fonts\//", 
                "$1" . get_template_directory_uri() . "/assets/fonts/", 
                $variables_css_content 
            );
            set_transient( 'github_theme_vars_inline_cache', array( 'mtime' => $mtime, 'content' => $variables_css_content ), WEEK_IN_SECONDS );
        } else {
            $variables_css_content = $cache['content'];
        }

        // Registramos un estilo vacío para poder "colgarle" el inline
        wp_register_style( 'github-theme-variables-inline', false );
        wp_enqueue_style( 'github-theme-variables-inline' );
        wp_add_inline_style( 'github-theme-variables-inline', $variables_css_content );
    }

    wp_enqueue_style( 'github-theme-header', get_template_directory_uri() . '/assets/css/header.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    wp_enqueue_style( 'github-theme-footer', get_template_directory_uri() . '/assets/css/footer.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    wp_enqueue_style( 'github-live-search', get_template_directory_uri() . '/assets/css/live-search.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    
    // 2. Estilos de Listados (Home, Archivos, Búsqueda)
    if ( ! is_singular() ) {
        wp_enqueue_style( 'github-theme-post-list', get_template_directory_uri() . '/assets/css/post-list.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    }

    // 3. Paginación (Solo donde hay navegación de posts)
    if ( is_home() || is_archive() || is_search() ) {
        wp_enqueue_style( 'github-theme-pagination', get_template_directory_uri() . '/assets/css/pagination.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    }

    // 4. Contribuciones (Solo en la Home)
    if ( is_home() || is_front_page() ) {
        wp_enqueue_style( 'github-theme-contributions', get_template_directory_uri() . '/assets/css/contributions.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    }

    // 5. Widgets y Formularios (Solo si hay sidebar o es un post/página)
    if ( is_singular() || is_active_sidebar( 'sidebar-1' ) ) {
        wp_enqueue_style( 'github-theme-widgets', get_template_directory_uri() . '/assets/css/widgets.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
        wp_enqueue_style( 'github-theme-forms', get_template_directory_uri() . '/assets/css/forms.css', array( 'github-theme-variables-inline' ), GITHUB_THEME_VERSION );
    }

    // 6. Hoja de estilos principal y Layout Base (Carga después de los componentes)
    // Inline de style.css para evitar otra petición diminuta de 1.0 kB
    $style_css_path = get_stylesheet_directory() . '/style.css';
    if ( file_exists( $style_css_path ) ) {
        $mtime = filemtime( $style_css_path );
        $cache = get_transient( 'github_theme_style_inline_cache' );
        
        if ( false === $cache || $cache['mtime'] !== $mtime ) {
            $style_css_content = file_get_contents( $style_css_path );
            set_transient( 'github_theme_style_inline_cache', array( 'mtime' => $mtime, 'content' => $style_css_content ), WEEK_IN_SECONDS );
        } else {
            $style_css_content = $cache['content'];
        }

        // Registramos un tirador vacío para inyectar este CSS de forma nativa
        wp_register_style( 'github-theme-base-style-inline', false );
        wp_enqueue_style( 'github-theme-base-style-inline' );
        wp_add_inline_style( 'github-theme-base-style-inline', $style_css_content );
    }
    
    wp_enqueue_style( 'github-theme-main',  get_template_directory_uri() . '/assets/css/main.css', array( 
        'github-theme-variables-inline',
        'github-theme-base-style-inline'
    ), GITHUB_THEME_VERSION );

    // 7. Estilos exclusivos de lectura (Single)
    if ( is_singular() ) {
        wp_enqueue_style( 'github-theme-single', get_template_directory_uri() . '/assets/css/single.css', array( 
            'github-theme-variables-inline'
        ), GITHUB_THEME_VERSION );
    }

    wp_enqueue_script( 'github-theme-main', get_template_directory_uri() . '/assets/js/main.js', array(), GITHUB_THEME_VERSION, true );

    // Live Search — Búsqueda en tiempo real
    wp_enqueue_script( 'github-theme-live-search', get_template_directory_uri() . '/assets/js/live-search.js', array(), time(), true );
    wp_localize_script( 'github-theme-live-search', 'liveSearchData', array(
        'restUrl' => esc_url_raw( rest_url( 'wp/v2' ) ),
        'homeUrl' => esc_url_raw( home_url( '/' ) ),
    ) );


}
add_action( 'wp_enqueue_scripts', 'github_theme_scripts' );

/**
 * Preconectar a dominios externos para mejorar la carga de fuentes y estilos.
 * Esto reduce la latencia en las cadenas de peticiones críticas sin sobrepasar el límite de preconnects.
 */
function github_theme_resource_hints( $urls, $relation_type ) {
    // dns-prefetch es más ligero y no satura el límite de 4 preconnects de Lighthouse
    if ( 'dns-prefetch' === $relation_type ) {
        $urls[] = 'https://cdn.jsdelivr.net';
    }
    
    // Solo usamos preconnect para el origen real de las fuentes (archivos WOFF2 pesados)
    if ( 'preconnect' === $relation_type ) {
        // Si no usas emojis o avatares externos de WP, puedes descomentar esto para quitarlos:
        // foreach ( $urls as $key => $url ) { if ( strpos( $url, 's.w.org' ) !== false ) unset( $urls[$key] ); }
    }

    return $urls;
}
add_filter( 'wp_resource_hints', 'github_theme_resource_hints', 10, 2 );

/**
 * Agregar atributo defer a scripts no críticos para mejorar el rendimiento.
 */
function github_theme_defer_scripts( $tag, $handle, $src ) {
    $defer_scripts = array(
        'github-theme-main',
        'github-live-search',
        'thickbox',
        'jquery-migrate',
    );

    if ( in_array( $handle, $defer_scripts ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'github_theme_defer_scripts', 10, 3 );

/**
 * Optimizar la carga de estilos CSS para evitar el "Render-blocking" en Lighthouse.
 * Usa rel="preload" para cargar los estilos de forma asíncrona.
 */
function github_theme_defer_styles( $html, $handle, $href, $media ) {
    if ( is_admin() ) {
        return $html;
    }

    // Lista de estilos que queremos aplazar (defer)
    // Dejamos fuera los estilos estructurales (post-list, contributions, header, variables) para evitar el CLS/FOUC.
    // Metemos las fuentes externas para que no bloqueen el renderizado y quiten el warning de "Critical Request Chain".
    $defer_styles = array(
        'github-theme-footer',
        'github-live-search',
        'github-theme-pagination',
        'github-theme-widgets',
        'github-theme-forms',
    );

    if ( in_array( $handle, $defer_styles ) ) {
        $html = sprintf(
            "<link rel='preload' as='style' id='%s-css' href='%s' media='%s' onload=\"this.onload=null;this.rel='stylesheet'\" />\n",
            esc_attr( $handle ),
            esc_url( $href ),
            esc_attr( $media )
        );
        $html .= sprintf(
            "<noscript><link rel='stylesheet' id='%s-noscript-css' href='%s' media='%s' /></noscript>\n",
            esc_attr( $handle ),
            esc_url( $href ),
            esc_attr( $media )
        );
    }

    return $html;
}
add_filter( 'style_loader_tag', 'github_theme_defer_styles', 10, 4 );



/**
 * Desactivar Dashicons en el frontend.
 * Solo se necesita en el admin; ahorra ~34.9 KiB y ~290ms.
 */
function github_dequeue_dashicons() {
    if ( ! is_admin() && ! is_user_logged_in() ) {
        // Dashicons es necesario para Thickbox (lightbox).
        // Si se desactiva, WordPress arroja un aviso de dependencia faltante.
        // wp_dequeue_style( 'dashicons' );
        // wp_deregister_style( 'dashicons' );
    }
}
add_action( 'wp_enqueue_scripts', 'github_dequeue_dashicons', 999 );

/**
 * Activar Thickbox como lightbox para imágenes enlazadas.
 * Soporta: JPG, JPEG, PNG, GIF, WebP, AVIF, SVG.
 */
function github_theme_lightbox_init() {
    if ( ! is_singular() ) {
        return;
    }
    wp_enqueue_script( 'thickbox' );
    wp_enqueue_style( 'thickbox' );

    $inline_script = "
    jQuery(document).ready(function($) {
        $('a[href$=\".jpg\"], a[href$=\".jpeg\"], a[href$=\".png\"], a[href$=\".gif\"], a[href$=\".webp\"], a[href$=\".avif\"], a[href$=\".svg\"], a[href$=\".JPG\"], a[href$=\".JPEG\"], a[href$=\".PNG\"]').addClass('thickbox');
    });
    ";

    wp_add_inline_script( 'thickbox', $inline_script );
}
add_action( 'wp_enqueue_scripts', 'github_theme_lightbox_init' );



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
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
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