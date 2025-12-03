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
 * Activar Thickbox en WordPress
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


// Seguridad: Ocultar usuarios en REST API y sitemap
add_filter('xmlrpc_enabled', '__return_false');
add_filter( 'rest_endpoints', function( $endpoints ) {
    if ( isset( $endpoints['/wp/v2/users'] ) ) {
        unset( $endpoints['/wp/v2/users'] );
    }
    if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
        unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
    }
    return $endpoints;
});
add_filter( 'wp_sitemaps_users_enabled', '__return_false' );

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
 * Limpiar query de búsqueda para prevenir inyección SQL
 */
function github_theme_sanitize_search_query($query) {
    if ($query->is_search && !is_admin()) {
        // Limpiar el término de búsqueda
        $search_term = get_search_query();
        
        // Eliminar caracteres peligrosos
        $search_term = strip_tags($search_term);
        $search_term = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $search_term);
        
        // Limitar longitud (máximo 100 caracteres)
        $search_term = substr($search_term, 0, 100);
        
        // Actualizar query
        if (!empty($search_term)) {
            $query->set('s', $search_term);
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
