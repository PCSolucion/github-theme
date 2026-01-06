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
    // Estilos principales con versión basada en tiempo de modificación del archivo
    $style_file = get_template_directory() . '/style.css';
    $style_version = file_exists($style_file) ? filemtime($style_file) : '1.0.0';
    wp_enqueue_style('github-theme-style', get_stylesheet_uri(), array(), $style_version);
    
    // Estilos adicionales con versión basada en tiempo de modificación
    $main_css_file = get_template_directory() . '/assets/css/main.css';
    $main_css_version = file_exists($main_css_file) ? filemtime($main_css_file) : '1.0.0';
    wp_enqueue_style('github-theme-main', get_template_directory_uri() . '/assets/css/main.css', array(), $main_css_version);
    
    // Scripts principales con defer para mejorar rendimiento
    $main_js_file = get_template_directory() . '/assets/js/main.js';
    $main_js_version = file_exists($main_js_file) ? filemtime($main_js_file) : '1.0.0';
    wp_enqueue_script('github-theme-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), $main_js_version, true);

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
 * Cargar CSS no crítico de forma asíncrona
 * Evita que CSS como Thickbox bloquee la renderización inicial
 */
function github_theme_async_styles($html, $handle, $href, $media) {
    // Lista de estilos que pueden cargarse de forma asíncrona
    $async_styles = array(
        'thickbox'
    );
    
    if (in_array($handle, $async_styles)) {
        // Cargar con media="print" y luego cambiar a "all" con JavaScript
        // Esto evita que bloquee la renderización inicial
        $html = str_replace("media='all'", "media='print' onload='this.media=\"all\"'", $html);
        $html = str_replace('media="all"', 'media="print" onload="this.media=\'all\'"', $html);
        
        // Agregar noscript fallback para usuarios sin JavaScript
        $noscript = '<noscript><link rel="stylesheet" href="' . esc_url($href) . '"></noscript>';
        $html .= $noscript;
    }
    
    return $html;
}
// Desactivado temporalmente - causaba error de JavaScript con onload
// add_filter('style_loader_tag', 'github_theme_async_styles', 10, 4);

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
            'tecnologia'   => '#0366d6', // Tech Blue
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
 * Generar meta description dinámica para SEO
 */
function github_theme_meta_description() {
    $description = '';
    
    // Página de inicio
    if (is_front_page() || is_home()) {
        $description = 'Blog de tecnología especializado en tutoriales y guías sobre Windows, Linux y WordPress. Soluciones prácticas, tips y trucos para optimizar tu experiencia tecnológica.';
    }
    // Post individual
    elseif (is_single()) {
        global $post;
        
        // Intentar usar el excerpt si existe
        if (!empty($post->post_excerpt)) {
            $description = wp_strip_all_tags($post->post_excerpt);
        } 
        // Si no hay excerpt, usar las primeras palabras del contenido
        else {
            $content = wp_strip_all_tags($post->post_content);
            $content = preg_replace('/\s+/', ' ', $content); // Normalizar espacios
            $words = explode(' ', $content);
            $description = implode(' ', array_slice($words, 0, 25));
        }
    }
    // Página de categoría
    elseif (is_category()) {
        $category = single_cat_title('', false);
        $cat_description = category_description();
        
        if (!empty($cat_description)) {
            $description = wp_strip_all_tags($cat_description);
        } else {
            $description = "Artículos y tutoriales sobre {$category}. Guías prácticas, tips y soluciones tecnológicas en nuestro blog especializado.";
        }
    }
    // Página de etiqueta
    elseif (is_tag()) {
        $tag = single_tag_title('', false);
        $tag_description = tag_description();
        
        if (!empty($tag_description)) {
            $description = wp_strip_all_tags($tag_description);
        } else {
            $description = "Contenido etiquetado como {$tag}. Encuentra artículos relacionados sobre tecnología, Windows, Linux y WordPress.";
        }
    }
    // Página de autor
    elseif (is_author()) {
        $author = get_the_author();
        $description = "Artículos escritos por {$author}. Tutoriales y guías sobre tecnología, Windows, Linux y WordPress.";
    }
    // Página de archivo
    elseif (is_archive()) {
        if (is_day()) {
            $description = 'Artículos publicados el ' . get_the_date();
        } elseif (is_month()) {
            $description = 'Artículos publicados en ' . get_the_date('F Y');
        } elseif (is_year()) {
            $description = 'Artículos publicados en ' . get_the_date('Y');
        } else {
            $description = 'Archivo de artículos sobre tecnología, Windows, Linux y WordPress.';
        }
    }
    // Página de búsqueda
    elseif (is_search()) {
        $search_query = get_search_query();
        $description = "Resultados de búsqueda para: {$search_query}. Encuentra artículos y tutoriales sobre tecnología.";
    }
    // Página 404
    elseif (is_404()) {
        $description = 'Página no encontrada. Explora nuestro blog de tecnología para encontrar tutoriales sobre Windows, Linux y WordPress.';
    }
    // Página genérica
    elseif (is_page()) {
        global $post;
        
        if (!empty($post->post_excerpt)) {
            $description = wp_strip_all_tags($post->post_excerpt);
        } else {
            $content = wp_strip_all_tags($post->post_content);
            $content = preg_replace('/\s+/', ' ', $content);
            $words = explode(' ', $content);
            $description = implode(' ', array_slice($words, 0, 25));
        }
    }
    // Fallback por defecto
    else {
        $description = get_bloginfo('description');
        if (empty($description)) {
            $description = 'Blog de tecnología con tutoriales sobre Windows, Linux y WordPress. Guías prácticas y soluciones tecnológicas.';
        }
    }
    
    // Limpiar y limitar la longitud (máximo 160 caracteres para SEO óptimo)
    $description = wp_strip_all_tags($description);
    $description = preg_replace('/\s+/', ' ', $description);
    $description = trim($description);
    
    if (strlen($description) > 160) {
        $description = substr($description, 0, 157) . '...';
    }
    
    return $description;
}

/**
 * Generar etiquetas OpenGraph y Twitter Card para redes sociales
 */
function github_theme_social_meta_tags() {
    // Variables comunes
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    $site_url = home_url('/');
    
    // Obtener imagen por defecto (logo del sitio o primera imagen del tema)
    $default_image = get_template_directory_uri() . '/assets/img/logo.svg';
    if (has_custom_logo()) {
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($logo) {
            $default_image = $logo[0];
        }
    }
    
    // Inicializar variables
    $og_title = '';
    $og_description = '';
    $og_image = $default_image;
    $og_url = '';
    $og_type = 'website';
    $twitter_card = 'summary_large_image';
    
    // Página de inicio
    if (is_front_page() || is_home()) {
        $og_title = $site_name;
        $og_description = $site_description ?: 'Blog de tecnología especializado en tutoriales y guías sobre Windows, Linux y WordPress.';
        $og_url = $site_url;
        $og_type = 'website';
    }
    // Post individual
    elseif (is_single()) {
        global $post;
        
        $og_title = get_the_title();
        $og_description = github_theme_meta_description();
        $og_url = get_permalink();
        $og_type = 'article';
        
        // Obtener imagen destacada si existe
        if (has_post_thumbnail()) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            if ($thumbnail) {
                $og_image = $thumbnail[0];
            }
        }
    }
    // Página
    elseif (is_page()) {
        $og_title = get_the_title();
        $og_description = github_theme_meta_description();
        $og_url = get_permalink();
        $og_type = 'website';
        
        // Obtener imagen destacada si existe
        if (has_post_thumbnail()) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            if ($thumbnail) {
                $og_image = $thumbnail[0];
            }
        }
    }
    // Categoría
    elseif (is_category()) {
        $category = get_queried_object();
        $og_title = single_cat_title('', false) . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_category_link($category->term_id);
        $og_type = 'website';
    }
    // Etiqueta
    elseif (is_tag()) {
        $tag = get_queried_object();
        $og_title = single_tag_title('', false) . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_tag_link($tag->term_id);
        $og_type = 'website';
    }
    // Autor
    elseif (is_author()) {
        $author = get_queried_object();
        $og_title = 'Artículos de ' . $author->display_name . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_author_posts_url($author->ID);
        $og_type = 'profile';
    }
    // Búsqueda
    elseif (is_search()) {
        $og_title = 'Resultados de búsqueda: ' . get_search_query() . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_search_link();
        $og_type = 'website';
    }
    // Fallback
    else {
        $og_title = $site_name;
        $og_description = $site_description ?: github_theme_meta_description();
        $og_url = $site_url;
        $og_type = 'website';
    }
    
    // Limpiar y escapar valores
    $og_title = wp_strip_all_tags($og_title);
    $og_description = wp_strip_all_tags($og_description);
    
    // Generar las etiquetas
    ?>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo esc_attr($og_type); ?>">
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>">
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <meta property="og:locale" content="es_ES">
    
    <?php if ($og_type === 'article' && is_single()) : ?>
    <meta property="article:published_time" content="<?php echo get_the_date('c'); ?>">
    <meta property="article:modified_time" content="<?php echo get_the_modified_date('c'); ?>">
    <meta property="article:author" content="<?php echo esc_attr(get_the_author()); ?>">
    <?php
    $categories = get_the_category();
    if ($categories) {
        foreach ($categories as $category) {
            echo '<meta property="article:section" content="' . esc_attr($category->name) . '">' . "\n    ";
        }
    }
    $tags = get_the_tags();
    if ($tags) {
        foreach ($tags as $tag) {
            echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n    ";
        }
    }
    ?>
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?php echo esc_attr($twitter_card); ?>">
    <meta name="twitter:url" content="<?php echo esc_url($og_url); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($og_title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($og_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">
    <?php
    // Si tienes cuenta de Twitter, descomenta y añade tu @usuario
    // echo '<meta name="twitter:site" content="@tu_usuario">' . "\n    ";
    // echo '<meta name="twitter:creator" content="@tu_usuario">' . "\n    ";
    ?>
    
    <!-- URL Canónica -->
    <link rel="canonical" href="<?php echo esc_url($og_url); ?>">
    <?php
}

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
 * Seguridad: Evitar Fingerprinting (Huella Digital)
 * Oculta la versión de WordPress del código fuente, feeds RSS, scripts y estilos.
 */
function github_remove_version_info() {
    // 1. Eliminar meta tag generator del header
    remove_action('wp_head', 'wp_generator');
    // 2. Eliminar versión de los feeds RSS
    add_filter('the_generator', '__return_empty_string');
}
add_action('init', 'github_remove_version_info');

/**
 * Eliminar parámetro de versión (?ver=x.x) de scripts y estilos
 * Dificulta saber qué versión de WP o plugins se está usando
 */
function github_remove_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'github_remove_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'github_remove_ver_css_js', 9999 );

// Seguridad: Ocultar usuarios en REST API y sitemap
add_filter('xmlrpc_enabled', '__return_false');
add_filter( 'rest_endpoints', function( $endpoints ) {
    // Ocultar usuarios (Enumeración)
    if ( isset( $endpoints['/wp/v2/users'] ) ) unset( $endpoints['/wp/v2/users'] );
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    
    // Ocultar listado de Posts y Páginas (Information Disclosure)
    if ( isset( $endpoints['/wp/v2/posts'] ) ) unset( $endpoints['/wp/v2/posts'] );
    if ( isset( $endpoints['/wp/v2/posts/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/posts/(?P<id>[\d]+)'] );
    if ( isset( $endpoints['/wp/v2/pages'] ) ) unset( $endpoints['/wp/v2/pages'] );
    if ( isset( $endpoints['/wp/v2/pages/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/pages/(?P<id>[\d]+)'] );

    return $endpoints;
});
add_filter( 'wp_sitemaps_users_enabled', '__return_false' );

/**
 * Sanitizar parámetros de la REST API para prevenir inyecciones
 * Protege contra inyección SQL y XSS en query strings
 */
add_filter('rest_request_before_callbacks', function($response, $handler, $request) {
    // Obtener todos los parámetros de la petición
    $params = $request->get_params();
    // Lista de parámetros permitidos para la REST API
    $allowed_params = array('id', 'page', 'per_page', 'search', 'slug', 'status', 'context');
    
    foreach ($params as $key => $value) {
        // Sanitizar claves (nombres de parámetros)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $key)) {
            return new WP_Error(
                'invalid_param',
                'Parámetro no válido detectado.',
                array('status' => 400)
            );
        }
        
        // Sanitizar valores - detectar patrones de inyección SQL
        if (is_string($value)) {
            // Patrones sospechosos de SQL injection
            $sql_patterns = array(
                '/(\bunion\b.*\bselect\b)/i',
                '/(\bselect\b.*\bfrom\b)/i',
                '/(\binsert\b.*\binto\b)/i',
                '/(\bdelete\b.*\bfrom\b)/i',
                '/(\bdrop\b.*\btable\b)/i',
                '/(\bupdate\b.*\bset\b)/i',
                '/(\/\*.*\*\/)/i',
                '/(--)/i',
                '/(;)/i',
                '/(\bexec\b)/i',
                '/(\bxp_\w+)/i',
            );
            
            foreach ($sql_patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return new WP_Error(
                        'security_blocked',
                        'Petición bloqueada por seguridad.',
                        array('status' => 403)
                    );
                }
            }
            
            // Patrones de XSS
            $xss_patterns = array(
                '/(<script)/i',
                '/(javascript:)/i',
                '/(onclick)/i',
                '/(onerror)/i',
                '/(onload)/i',
            );
            
            foreach ($xss_patterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return new WP_Error(
                        'security_blocked',
                        'Petición bloqueada por seguridad.',
                        array('status' => 403)
                    );
                }
            }
        }
    }
    
    return $response;
}, 10, 3);

/**
 * Rate limiting para búsquedas (prevenir spam)
 */
function github_theme_search_rate_limit() {
    // Solo aplicar en búsquedas
    if (!is_search()) {
        return;
    }
    
    // Obtener IP del usuario
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // Sanitizar IP para usar como clave de transient
    $transient_key = 'search_limit_' . md5($user_ip);
    
    // Obtener número de búsquedas realizadas
    $search_count = get_transient($transient_key);
    
    // Límite: 10 búsquedas por minuto
    $max_searches = 10;
    $time_window = 60; // segundos
    
    if ($search_count === false) {
        // Primera búsqueda, iniciar contador
        set_transient($transient_key, 1, $time_window);
    } elseif ($search_count >= $max_searches) {
        // Límite excedido, mostrar error
        wp_die(
            '<h1>Demasiadas búsquedas</h1>' .
            '<p>Has excedido el límite de búsquedas. Por favor, espera un momento antes de intentarlo de nuevo.</p>' .
            '<p><a href="' . esc_url(home_url('/')) . '">Volver al inicio</a></p>',
            'Límite de búsquedas excedido',
            array(
                'response' => 429,
                'back_link' => true
            )
        );
    } else {
        // Incrementar contador
        set_transient($transient_key, $search_count + 1, $time_window);
    }
}
add_action('template_redirect', 'github_theme_search_rate_limit', 1);

/**
 * ==========================================
 * PROTECCIÓN CONTRA INYECCIÓN LDAP
 * ==========================================
 * Bloquea caracteres típicos de inyección LDAP:
 * - Paréntesis: ( )
 * - Asteriscos: *
 * - Caracteres NUL: \x00 o %00
 * - Backslash: \
 * - Pipe: |
 * - Ampersand: &
 */
function github_theme_block_ldap_injection() {
    // Solo procesar si hay parámetros GET
    if (empty($_GET)) {
        return;
    }
    
    // Obtener la query string completa
    $query_string = isset($_SERVER['QUERY_STRING']) ? urldecode($_SERVER['QUERY_STRING']) : '';
    $request_uri = isset($_SERVER['REQUEST_URI']) ? urldecode($_SERVER['REQUEST_URI']) : '';
    
    // Patrones de inyección LDAP a bloquear
    $ldap_patterns = array(
        '/\x00/',                    // Caracteres NUL
        '/%00/',                     // NUL codificado
        '/\*\)/',                    // *) - patrón típico de LDAP injection
        '/\(\|/',                    // (| - patrón OR en LDAP
        '/\(\&/',                    // (& - patrón AND en LDAP
        '/\)\)/',                    // )) - cierre de múltiples filtros
        '/\(\*/',                    // (* - wildcard en filtro
        '/\\\\[0-9a-fA-F]{2}/',    // Escape hex de LDAP
        '/uid=\*/',                  // Enumeración de usuarios LDAP
        '/cn=\*/',                   // Common name wildcard
        '/objectclass=\*/',          // Object class enumeration
    );
    
    // Verificar query string y URI
    foreach ($ldap_patterns as $pattern) {
        if (preg_match($pattern, $query_string) || preg_match($pattern, $request_uri)) {
            // Log del intento (opcional, descomentar si quieres registrar)
            // error_log('LDAP Injection attempt blocked from IP: ' . $_SERVER['REMOTE_ADDR'] . ' - Pattern: ' . $pattern);
            
            // Responder con error 400 Bad Request
            status_header(400);
            wp_die(
                '<h1>Solicitud no válida</h1>' .
                '<p>La solicitud contiene caracteres no permitidos.</p>' .
                '<p><a href="' . esc_url(home_url('/')) . '">Volver al inicio</a></p>',
                'Solicitud no válida',
                array(
                    'response' => 400,
                    'back_link' => true
                )
            );
        }
    }
    
    // Verificar específicamente el parámetro de búsqueda 's'
    if (isset($_GET['s'])) {
        $search = $_GET['s'];
        
        // Caracteres prohibidos en búsqueda LDAP
        $forbidden_chars = array('*', '(', ')', '\\', chr(0), '|', '&', '!');
        
        foreach ($forbidden_chars as $char) {
            if (strpos($search, $char) !== false) {
                // Redirigir a búsqueda limpia o mostrar error
                $clean_search = str_replace($forbidden_chars, '', $search);
                $clean_search = trim($clean_search);
                
                if (!empty($clean_search)) {
                    // Redirigir a búsqueda limpia
                    wp_safe_redirect(add_query_arg('s', urlencode($clean_search), home_url('/')));
                    exit;
                } else {
                    // Si no queda nada después de limpiar, redirigir al inicio
                    wp_safe_redirect(home_url('/'));
                    exit;
                }
            }
        }
    }
}
// Ejecutar muy temprano, antes que cualquier otra cosa
add_action('init', 'github_theme_block_ldap_injection', 0);

/**
 * Limpiar query de búsqueda para prevenir inyección SQL y LDAP
 */
function github_theme_sanitize_search_query($query) {
    if ($query->is_search && !is_admin()) {
        // Limpiar el término de búsqueda
        $search_term = get_search_query();
        
        // Eliminar caracteres peligrosos - ser más restrictivo
        // Solo permitir letras, números, espacios, guiones y guiones bajos
        $search_term = strip_tags($search_term);
        
        // Eliminar caracteres de inyección LDAP específicamente
        $search_term = str_replace(array('*', '(', ')', '\\', '|', '&', '!', chr(0)), '', $search_term);
        
        // Eliminar cualquier otro carácter especial
        $search_term = preg_replace('/[^\p{L}\p{N}\s\-_\.]/u', '', $search_term);
        
        // Limitar longitud (máximo 100 caracteres)
        $search_term = substr($search_term, 0, 100);
        
        // Normalizar espacios múltiples
        $search_term = preg_replace('/\s+/', ' ', $search_term);
        $search_term = trim($search_term);
        
        // Actualizar query
        if (!empty($search_term)) {
            $query->set('s', $search_term);
        } else {
            // Si la búsqueda queda vacía después de sanitizar, cancelar la búsqueda
            $query->set('s', '');
            $query->is_search = false;
        }
    }
    return $query;
}
add_filter('pre_get_posts', 'github_theme_sanitize_search_query');

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

function ofuscar_email_menu( $atts, $item, $args, $depth ) {
    if ( isset( $atts['href'] ) && preg_match( '/^mailto:/i', $atts['href'] ) ) {
        $email = preg_replace( '/^mailto:(.*)/i', '$1', $atts['href'] );
        $atts['href'] = 'mailto:' . antispambot( $email, 1 );
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'ofuscar_email_menu', 10, 4 );

function github_no_wordpress_errors(){
    return 'Algo salió mal. Por favor, inténtalo de nuevo.';
}
add_filter( 'login_errors', 'github_no_wordpress_errors' );

/**
 * Bloquear enumeración de autores
 * 1. Bloquea ?author=N (Query String)
 * 2. Bloquea /author/username/ (Archivos de autor)
 * 3. Elimina información de autor en oEmbed
 */
function github_block_user_enumeration() {
    if (is_admin()) return;

    // 1. Bloquear enumeración por query string (?author=1)
    // IMPORTANTE: La prioridad 1 en el hook asegura que esto corra ANTES que redirect_canonical
    if (isset($_GET['author']) || isset($_GET['author_name'])) {
        wp_redirect(home_url());
        exit;
    }

    // 2. Bloquear acceso directo a archivos de autor (/author/nombre-usuario)
    if (is_author()) {
        wp_redirect(home_url());
        exit;
    }
}
// Prioridad 1 es CRÍTICA para ganar a redirect_canonical de WordPress (que tiene prioridad 10)
add_action('template_redirect', 'github_block_user_enumeration', 1);

// 3. Eliminar info de autor en oEmbed API (otro vector de enumeración)
add_filter('oembed_response_data', function($data) {
    if (isset($data['author_name'])) unset($data['author_name']);
    if (isset($data['author_url'])) unset($data['author_url']);
    return $data;
});

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

/**
 * Limitar revisiones de posts para reducir tamaño de BD
 */
if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 3);
}

/**
 * Aumentar tiempo de autosave para reducir consultas
 */
if (!defined('AUTOSAVE_INTERVAL')) {
    define('AUTOSAVE_INTERVAL', 300); // 5 minutos
}

/**
 * Deshabilitar Heartbeat API en el frontend
 * Reduce solicitudes AJAX innecesarias
 */
function github_disable_heartbeat() {
    // Deshabilitar completamente en el frontend
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
    // Modificar intervalo en el admin (de 15s a 60s)
    else {
        add_filter('heartbeat_settings', function($settings) {
            $settings['interval'] = 60;
            return $settings;
        });
    }
}
add_action('init', 'github_disable_heartbeat', 1);

/**
 * Optimizar carga de jQuery
 * Mover jQuery al footer cuando sea posible
 */
// function github_optimize_jquery() {
//     if (!is_admin()) {
//         // Mover jQuery al footer
//         wp_scripts()->add_data('jquery', 'group', 1);
//         wp_scripts()->add_data('jquery-core', 'group', 1);
//         wp_scripts()->add_data('jquery-migrate', 'group', 1);
//     }
// }
// add_action('wp_enqueue_scripts', 'github_optimize_jquery', 100);

/**
 * Agregar sugerencias de recursos (preload para CSS crítico)
 */
function github_preload_critical_assets() {
    // Preload del CSS principal
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">' . "\n";
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/main.css" as="style">' . "\n";
}
add_action('wp_head', 'github_preload_critical_assets', 1);

/**
 * Aumentar límites de subida para evitar error "enlace caducado"
 */
@ini_set('upload_max_size', '128M');
@ini_set('post_max_size', '128M');
@ini_set('max_execution_time', '300');
@ini_set('max_input_time', '300');

// Aumentar límite de memoria si es necesario
if (!defined('WP_MEMORY_LIMIT')) {
    define('WP_MEMORY_LIMIT', '256M');
}
if (!defined('WP_MAX_MEMORY_LIMIT')) {
    define('WP_MAX_MEMORY_LIMIT', '512M');
}