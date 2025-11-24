<?php
/**
 * GitHub Theme Functions
 *
 * @package GitHubTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Configuración del tema
 */
function github_theme_setup() {
    // Soporte para título automático
    add_theme_support('title-tag');
    
    // Soporte para imágenes destacadas
    add_theme_support('post-thumbnails');
    
    // Soporte para HTML5
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ));
    
    // Soporte para feeds RSS
    add_theme_support('automatic-feed-links');
    
    // Registrar menús de navegación
    register_nav_menus(array(
        'primary' => __('Menú Principal', 'github-theme'),
        'footer' => __('Menú Footer', 'github-theme'),
    ));
    
    // Soporte para editor de bloques (Gutenberg)
    add_theme_support('wp-block-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    
    // Tamaños de imagen personalizados
    add_image_size('github-thumbnail', 400, 250, true);
}
add_action('after_setup_theme', 'github_theme_setup');

/**
 * Estilos y scripts del tema
 */
function github_theme_scripts() {
    // Estilos principales
    wp_enqueue_style('github-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Estilos adicionales
    wp_enqueue_style('github-theme-main', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0.0');
    
    // Scripts principales
    wp_enqueue_script('github-theme-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0.0', true);
    
    // Comentarios (si es necesario)
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'github_theme_scripts');

/**
 * Registrar áreas de widgets
 */
function github_theme_widgets_init() {
    register_sidebar(array(
        'name' => __('Sidebar Principal', 'github-theme'),
        'id' => 'sidebar-1',
        'description' => __('Widgets que aparecen en la sidebar principal', 'github-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widgets', 'github-theme'),
        'id' => 'footer-widgets',
        'description' => __('Widgets que aparecen en el footer', 'github-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'github_theme_widgets_init');

/**
 * Personalizar excerpt length
 */
function github_theme_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'github_theme_excerpt_length');

/**
 * Personalizar excerpt more
 */
function github_theme_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'github_theme_excerpt_more');

/**
 * Agregar clases al body
 */
function github_theme_body_classes($classes) {
    if (is_singular()) {
        $classes[] = 'singular';
    }
    
    if (is_front_page()) {
        $classes[] = 'home';
    }
    
    return $classes;
}
add_filter('body_class', 'github_theme_body_classes');

/**
 * Personalizar logo del sitio
 */
function github_theme_custom_logo_setup() {
    add_theme_support('custom-logo', array(
        'height' => 32,
        'width' => 32,
        'flex-height' => true,
        'flex-width' => true,
    ));
}
add_action('after_setup_theme', 'github_theme_custom_logo_setup');

/**
 * Personalizar colores del editor
 */
function github_theme_editor_color_palette() {
    add_theme_support('editor-color-palette', array(
        array(
            'name' => __('Fondo Primario', 'github-theme'),
            'slug' => 'bg-primary',
            'color' => '#0d1117',
        ),
        array(
            'name' => __('Fondo Secundario', 'github-theme'),
            'slug' => 'bg-secondary',
            'color' => '#161b22',
        ),
        array(
            'name' => __('Texto Primario', 'github-theme'),
            'slug' => 'text-primary',
            'color' => '#c9d1d9',
        ),
        array(
            'name' => __('Acento', 'github-theme'),
            'slug' => 'accent',
            'color' => '#58a6ff',
        ),
    ));
}
add_action('after_setup_theme', 'github_theme_editor_color_palette');

/**
 * Limpiar el head
 */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

/**
 * Agregar soporte para SVG
 */
function github_theme_mime_types($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'github_theme_mime_types');

/**
 * Función helper para obtener la URL del logo
 */
function github_theme_get_logo_url() {
    if (has_custom_logo()) {
        $logo = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');
        return $logo[0];
    }
    return false;
}

/**
 * Obtener color para una categoría (similar a GitHub repo-language-color)
 */
function github_theme_get_category_color($category_id) {
    // Colores estilo GitHub para lenguajes/proyectos
    $colors = array(
        '#f34b7d', // JavaScript/TypeScript
        '#3178c6', // TypeScript
        '#3776ab', // Python
        '#e34c26', // HTML
        '#563d7c', // CSS
        '#f1e05a', // JavaScript
        '#701516', // Ruby
        '#b07219', // Java
        '#c72d0f', // PHP
        '#00add8', // C#
        '#178600', // Go
        '#f18e33', // Kotlin
        '#4F5D95', // PHP
        '#a97bff', // Vue
        '#61dafb', // React
        '#42b883', // Vue.js
        '#000000', // C
        '#00599c', // C++
        '#e38c00', // Rust
        '#4479a1', // Swift
    );
    
    // Usar el ID de la categoría para obtener un color consistente
    return $colors[$category_id % count($colors)];
}

/**
 * Incluir funciones de contribuciones
 */
require get_template_directory() . '/inc/contributions.php';

/**
 * Calcular tiempo de lectura estimado
 */
function github_theme_estimated_reading_time() {
    $post = get_post();
    $content = $post->post_content;
    $wpm = 200; // Palabras por minuto promedio
    $clean_content = strip_shortcodes($content);
    $clean_content = strip_tags($clean_content);
    $word_count = str_word_count($clean_content);
    $time = ceil($word_count / $wpm);
    
    return $time . ' min de lectura';
}

