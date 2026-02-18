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
define( 'GITHUB_THEME_VERSION', wp_get_theme()->get( 'Version' ) ?: '1.0.0' );


// =============================================================================
// CONFIGURACIÓN DEL TEMA
// =============================================================================

/**
 * Configuración principal del tema.
 * Registra soporte de características, menús y tamaños de imagen.
 */
function github_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
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

    add_image_size( 'github-thumbnail', 400, 250, true );
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
    // Google Fonts: Geist (UI principal) + Inter (fallback) + JetBrains Mono (código)
    wp_enqueue_style(
        'github-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap',
        array(),
        null
    );

    // Geist Font (via CDN)
    wp_enqueue_style(
        'geist-font',
        'https://cdn.jsdelivr.net/npm/geist@1.3.1/dist/fonts/geist-sans/style.min.css',
        array(),
        null
    );

    wp_enqueue_style(
        'geist-mono-font',
        'https://cdn.jsdelivr.net/npm/geist@1.3.1/dist/fonts/geist-mono/style.min.css',
        array(),
        null
    );

    wp_enqueue_style( 'github-theme-style', get_stylesheet_uri(), array(), GITHUB_THEME_VERSION );
    wp_enqueue_style( 'github-theme-main',  get_template_directory_uri() . '/assets/css/main.css', array(), GITHUB_THEME_VERSION );

    wp_enqueue_script( 'github-theme-main', get_template_directory_uri() . '/assets/js/main.js', array( 'jquery' ), GITHUB_THEME_VERSION, true );

    // Live Search — Búsqueda en tiempo real
    wp_enqueue_style( 'github-live-search', get_template_directory_uri() . '/assets/css/live-search.css', array(), GITHUB_THEME_VERSION );
    wp_enqueue_script( 'github-theme-live-search', get_template_directory_uri() . '/assets/js/live-search.js', array(), time(), true );
    wp_localize_script( 'github-theme-live-search', 'liveSearchData', array(
        'restUrl' => esc_url_raw( rest_url( 'wp/v2' ) ),
        'homeUrl' => esc_url_raw( home_url( '/' ) ),
    ) );

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'github_theme_scripts' );

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
 * Live Search: endpoint REST personalizado.
 *
 * Ruta: GET /wp-json/github-theme/v1/search?q=TERMINO&per_page=8
 * Busca SOLO en post_title directamente en la base de datos.
 * Esto es infalible — no depende de hooks ni de la versión de WordPress.
 */
function github_theme_register_live_search_endpoint() {
    register_rest_route( 'github-theme/v1', '/search', array(
        'methods'             => 'GET',
        'callback'            => 'github_theme_live_search_handler',
        'permission_callback' => '__return_true',
        'args'                => array(
            'q' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function( $value ) {
                    return is_string( $value ) && strlen( $value ) >= 1;
                },
            ),
            'per_page' => array(
                'default'           => 8,
                'sanitize_callback' => 'absint',
            ),
        ),
    ) );
}
add_action( 'rest_api_init', 'github_theme_register_live_search_endpoint' );

function github_theme_live_search_handler( WP_REST_Request $request ) {
    global $wpdb;

    $term     = $request->get_param( 'q' );
    $per_page = min( (int) $request->get_param( 'per_page' ), 20 );
    $like     = '%' . $wpdb->esc_like( $term ) . '%';

    // Búsqueda directa en post_title — rápida y exacta
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID, post_title, post_excerpt, post_date, post_name
             FROM {$wpdb->posts}
             WHERE post_status = 'publish'
               AND post_type  = 'post'
               AND post_title LIKE %s
             ORDER BY post_date DESC
             LIMIT %d",
            $like,
            $per_page
        )
    );

    if ( empty( $results ) ) {
        return rest_ensure_response( array() );
    }

    $data = array();
    foreach ( $results as $post ) {
        $post_id   = (int) $post->ID;
        $thumbnail = '';
        $thumb_id  = get_post_thumbnail_id( $post_id );
        if ( $thumb_id ) {
            $img = wp_get_attachment_image_src( $thumb_id, 'thumbnail' );
            if ( $img ) {
                $thumbnail = $img[0];
            }
        }

        // Categorías
        $cats       = get_the_category( $post_id );
        $categories = ! empty( $cats ) ? implode( ', ', wp_list_pluck( $cats, 'name' ) ) : '';

        $data[] = array(
            'id'         => $post_id,
            'title'      => $post->post_title,
            'excerpt'    => wp_trim_words( strip_tags( $post->post_excerpt ), 20, '…' ),
            'date'       => $post->post_date,
            'link'       => get_permalink( $post_id ),
            'thumbnail'  => $thumbnail,
            'categories' => $categories,
        );
    }

    return rest_ensure_response( $data );
}


/**
 * Desactivar Dashicons en el frontend.
 * Solo se necesita en el admin; ahorra ~34.9 KiB y ~290ms.
 */
function github_dequeue_dashicons() {
    if ( ! is_admin() && ! is_user_logged_in() ) {
        wp_dequeue_style( 'dashicons' );
        wp_deregister_style( 'dashicons' );
    }
}
add_action( 'wp_enqueue_scripts', 'github_dequeue_dashicons', 999 );

/**
 * Activar Thickbox como lightbox para imágenes enlazadas.
 * Soporta: JPG, JPEG, PNG, GIF, WebP, AVIF, SVG.
 */
function github_theme_lightbox_init() {
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

/**
 * Permitir subida de archivos SVG.
 */
function github_theme_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'github_theme_mime_types' );


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

/**
 * Menú de navegación por defecto cuando no hay menú configurado en el admin.
 */
function github_theme_default_menu() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . __( 'Inicio', 'github-theme' ) . '</a></li>';

    if ( get_option( 'show_on_front' ) === 'page' ) {
        $page_for_posts = get_option( 'page_for_posts' );
        if ( $page_for_posts ) {
            echo '<li><a href="' . esc_url( get_permalink( $page_for_posts ) ) . '">' . __( 'Blog', 'github-theme' ) . '</a></li>';
        }
    }

    wp_list_pages( array(
        'title_li' => '',
        'exclude'  => get_option( 'page_on_front' ),
    ) );

    echo '</ul>';
}

/**
 * Obtener la URL del logo personalizado del sitio.
 *
 * @return string|false URL del logo o false si no hay logo configurado.
 */
function github_theme_get_logo_url() {
    if ( has_custom_logo() ) {
        $logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
        return ( $logo && isset( $logo[0] ) ) ? $logo[0] : false;
    }
    return false;
}

/**
 * Calcular el tiempo de lectura estimado del post actual.
 *
 * @return string Tiempo estimado en formato "X min de lectura".
 */
function github_theme_estimated_reading_time() {
    $post = get_post();
    if ( ! $post ) {
        return __( '0 min de lectura', 'github-theme' );
    }
    $content       = $post->post_content;
    $wpm           = 200; // Palabras por minuto promedio
    $clean_content = strip_tags( strip_shortcodes( $content ) );
    $word_count    = str_word_count( $clean_content );
    $time          = ceil( $word_count / $wpm );

    return sprintf( __( '%d min de lectura', 'github-theme' ), $time );
}


// =============================================================================
// INCLUDES
// =============================================================================

require get_template_directory() . '/inc/template-functions.php';
require get_template_directory() . '/inc/contributions.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/security.php';
require get_template_directory() . '/inc/optimization.php';