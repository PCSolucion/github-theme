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
 * Definir la versión del tema para control de caché (Cache Busting)
 */
define('GITHUB_THEME_VERSION', wp_get_theme()->get('Version') ?: '1.0.0');

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


/**
 * Estilos y scripts del tema
 */
function github_theme_scripts() {
    // Fuentes de Google Fonts: Inter para la interfaz y JetBrains Mono para código/metadatos
    wp_enqueue_style('github-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap', array(), null);

    // Estilos principales
    wp_enqueue_style('github-theme-style', get_stylesheet_uri(), array(), GITHUB_THEME_VERSION);
    
    // Estilos adicionales
    wp_enqueue_style('github-theme-main', get_template_directory_uri() . '/assets/css/main.css', array(), GITHUB_THEME_VERSION);
    
    // Scripts principales con defer para mejorar rendimiento
    wp_enqueue_script('github-theme-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), GITHUB_THEME_VERSION, true);

    // Comentarios (si es necesario)
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'github_theme_scripts');

/**
 * Agregar atributo defer a scripts no críticos
 * Mejora el rendimiento al no bloquear el renderizado
 */
function github_theme_defer_scripts($tag, $handle, $src) {
    // Lista de scripts que pueden cargarse con defer
    $defer_scripts = array(
        'github-theme-main',
        'thickbox',
        'jquery-migrate'
    );
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}
add_filter('script_loader_tag', 'github_theme_defer_scripts', 10, 3);


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
    // Obtener el objeto de categoría para verificar el slug
    $category = get_category($category_id);
    
    if ($category && !is_wp_error($category)) {
        $slug = $category->slug;
        
        // Mapa de colores personalizados por categoría
        $category_colors = array(
            'css'          => '#563d7c', // Purple CSS
            'gameplays'    => '#db6b00', // Orange Gameplay
            'linux'        => '#f1e05a', // Yellow Linux
            'noticias'     => '#24292e', // Dark Grey News
            'programacion' => '#f34b7d', // Pink Code
            'seguridad'    => '#d73a49', // Red Security
            'streaming'    => '#9146ff', // Twitch Purple
            'tecnologia'   => '#00bcd4', // Tech Cyan
            'videojuegos'  => '#2ea44f', // Green Gaming
            'windows'      => '#0078d7', // Windows Blue
            'wordpress'    => '#21759b', // WordPress Blue
        );

        if (array_key_exists($slug, $category_colors)) {
            return $category_colors[$slug];
        }
    }

    // Colores estilo GitHub para lenguajes/proyectos (Fallback)
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

/**
 * Incluir funciones de SEO y Meta Tags
 */
require get_template_directory() . '/inc/seo.php';


// Desactivar el editor Gutenberg
add_filter('use_block_editor_for_post', '__return_false', 10);

// Desactivar para tipos de contenido personalizados
add_filter('use_block_editor_for_post_type', '__return_false', 10);

/**
 * Desactivar Dashicons en el frontend
 * Dashicons solo se necesita en el admin, esto ahorra ~34.9 KiB y 290ms
 */
function github_dequeue_dashicons() {
    if (!is_admin() && !is_user_logged_in()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons');
    }
}
add_action('wp_enqueue_scripts', 'github_dequeue_dashicons', 999);

/**
 * Activar Thickbox en WordPress con carga optimizada
 * Se carga de forma asíncrona para no bloquear la renderización inicial
 */
function activar_lightbox_thickbox() {
    // Cargar los scripts y estilos de Thickbox
    wp_enqueue_script('thickbox');
    wp_enqueue_style('thickbox');
    
    // Agregar clase thickbox a imágenes enlazadas usando wp_add_inline_script
    // Soporta formatos: JPG, JPEG, PNG, GIF, WebP, AVIF, SVG
    $inline_script = "
    jQuery(document).ready(function($) {
        $('a[href$=\".jpg\"], a[href$=\".jpeg\"], a[href$=\".png\"], a[href$=\".gif\"], a[href$=\".webp\"], a[href$=\".avif\"], a[href$=\".svg\"], a[href$=\".JPG\"], a[href$=\".JPEG\"], a[href$=\".PNG\"]').addClass('thickbox');
    });
    ";
    
    wp_add_inline_script('thickbox', $inline_script);
}
add_action('wp_enqueue_scripts', 'activar_lightbox_thickbox');

/**
 * Incluir funciones de Seguridad y Limpieza
 */
require get_template_directory() . '/inc/security.php';

/**
 * Limpiar etiquetas p y br dentro de bloques pre
 * WordPress a veces envuelve el contenido de <pre> en <p>, lo que rompe el formato de código
 */
function github_theme_fix_pre_tags($content) {
    // Buscar todos los bloques <pre>...</pre> y limpiar su interior
    $content = preg_replace_callback('/<pre([^>]*)>(.*?)<\/pre>/is', function($matches) {
        $pre_attrs = $matches[1]; // Atributos del pre (class, etc.)
        $inner_content = $matches[2]; // Contenido dentro del pre
        
        // Eliminar <p> y </p>
        $inner_content = str_replace(['<p>', '</p>'], '', $inner_content);
        
        // Reemplazar <br> y <br/> con saltos de línea
        $inner_content = str_replace(['<br>', '<br/>', '<br />'], "\n", $inner_content);
        
        // Reconstruir el pre limpio
        return '<pre' . $pre_attrs . '>' . $inner_content . '</pre>';
    }, $content);
    
    return $content;
}
add_filter('the_content', 'github_theme_fix_pre_tags', 9); // Prioridad 9 para que se ejecute antes que wpautop



/**
 * Habilitar lazy loading nativo para imágenes
 * Mejora el rendimiento al cargar imágenes solo cuando son visibles
 */
function github_add_lazy_loading($content) {
    // Solo en el contenido del post
    if (is_singular() && in_the_loop() && is_main_query()) {
        // Agregar loading="lazy" a todas las imágenes que no lo tengan
        $content = preg_replace('/<img((?![^>]*loading=)[^>]*)>/i', '<img$1 loading="lazy">', $content);
    }
    return $content;
}
add_filter('the_content', 'github_add_lazy_loading', 20);

/**
 * Agregar loading="lazy" a imágenes destacadas
 */
function github_lazy_load_featured_images($attr) {
    $attr['loading'] = 'lazy';
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'github_lazy_load_featured_images');

/**
 * Optimizar consultas de WordPress
 * Reduce el número de consultas a la base de datos
 */
function github_optimize_queries() {
    // Deshabilitar emojis (ahorra 2 solicitudes HTTP)
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // Deshabilitar embeds de WordPress (ahorra 1 solicitud)
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
    
    // Deshabilitar REST API links en el head
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('template_redirect', 'rest_output_link_header', 11);
}
add_action('init', 'github_optimize_queries');


function github_disable_heartbeat() {
    // Modificar intervalo del heartbeat en lugar de deshabilitarlo
    // Deshabilitarlo causa pantalla blanca al publicar/programar posts
    add_filter('heartbeat_settings', function($settings) {
        // Reducir frecuencia a 60 segundos (por defecto es 15-60s dependiendo de la actividad)
        $settings['interval'] = 60;
        return $settings;
    });
}
add_action('init', 'github_disable_heartbeat', 1);


function github_preload_critical_assets() {
    // Preload del CSS principal
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">' . "\n";
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/main.css" as="style">' . "\n";
}
add_action('wp_head', 'github_preload_critical_assets', 1);

