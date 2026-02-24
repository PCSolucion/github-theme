<?php
/**
 * Funciones de Seguridad y Limpieza
 *
 * @package GitHubTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Seguridad y Limpieza: Eliminar información innecesaria del head
 * Oculta la versión de WordPress, enlaces a servicios externos no usados y feeds
 */
function github_remove_version_info() {
    // 1. Eliminar meta tags innecesarias del header
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    
    // 2. Eliminar versión de los feeds RSS
    add_filter('the_generator', '__return_empty_string');

    // 3. Eliminar enlaces de descubrimiento de la REST API y oEmbed
    remove_action('wp_head', 'rest_output_link_wp_head', 10);
    remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
    remove_action('wp_head', 'wp_oembed_add_host_js');
    remove_action('template_redirect', 'rest_output_link_header', 11);
}
add_action('init', 'github_remove_version_info');

/**
 * Seguridad: Desactivar XML-RPC y pingbacks
 * Evita ataques de fuerza bruta y DDoS amplification.
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('wp_headers', function($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/**
 * Seguridad: Ocultar usuarios en REST API y sitemap
 */

add_filter( 'rest_endpoints', function( $endpoints ) {
    // Solo ocultar endpoints sensibles para usuarios NO logueados
    // Los usuarios logueados necesitan la REST API para el editor de WordPress
    if ( !is_user_logged_in() ) {
        // Ocultar usuarios (Enumeración)
        if ( isset( $endpoints['/wp/v2/users'] ) ) unset( $endpoints['/wp/v2/users'] );
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        
        // Ocultar posts (Anti-scraping)
        if ( isset( $endpoints['/wp/v2/posts'] ) ) unset( $endpoints['/wp/v2/posts'] );
        if ( isset( $endpoints['/wp/v2/posts/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/posts/(?P<id>[\d]+)'] );
    }

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
 * Limpiar query de búsqueda para prevenir inyecciones y caracteres no deseados
 */
function github_theme_sanitize_search_query($query) {
    if ($query->is_search && !is_admin() && $query->is_main_query()) {
        // Obtener el término directamente de la URL y sanitizarlo
        $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        
        // 1. Limpieza profunda y eliminación de etiquetas/caracteres especiales
        $search_term = strip_tags($s);
        $search_term = preg_replace('/[^\p{L}\p{N}\s\-_\.]/u', '', $search_term);
        $search_term = trim(preg_replace('/\s+/', ' ', $search_term));
        $search_term = mb_substr($search_term, 0, 100);
        
        // 2. Validación de búsqueda vacía o demasiado corta
        if (mb_strlen($search_term) < 2) {
            $query->set('s', ''); 
            $query->set('post__in', array(0)); 
            return;
        }
        
        // 3. Aplicar término limpio
        $query->set('s', $search_term);
    }
    return $query;
}
add_filter('pre_get_posts', 'github_theme_sanitize_search_query');

/**
 * Ofuscar correos electrónicos en menús para prevenir spam
 */
function ofuscar_email_menu( $atts, $item, $args, $depth ) {
    if ( isset( $atts['href'] ) && preg_match( '/^mailto:/i', $atts['href'] ) ) {
        $email = preg_replace( '/^mailto:(.*)/i', '$1', $atts['href'] );
        $atts['href'] = 'mailto:' . antispambot( $email, 1 );
    }
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'ofuscar_email_menu', 10, 4 );

/**
 * Mensajes de error de login genéricos para seguridad
 */
function github_no_wordpress_errors(){
    return 'Algo salió mal. Por favor, inténtalo de nuevo.';
}
// add_filter( 'login_errors', 'github_no_wordpress_errors' );

/**
 * Bloquear enumeración de autores
 */
function github_block_user_enumeration() {
    if (is_admin()) return;

    // 1. Bloquear enumeración por query string (?author=1)
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
add_action('template_redirect', 'github_block_user_enumeration', 1);

/**
 * Seguridad: Validar parámetros de consulta para prevenir anomalías en rutas
 * Asegura que el parámetro 'cat' (categoría) sea siempre numérico.
 * Evita que inyecciones o valores inesperados causen fugas de información o errores 404 extraños.
 */
function github_theme_validate_query_params($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    // 1. Validar parámetro de categoría (cat)
    // WordPress suele convertirlo a entero, pero forzamos la validación para mayor seguridad
    if ($query->is_category() || isset($_GET['cat'])) {
        $cat = $query->get('cat');
        if (!empty($cat) && !is_numeric($cat) && !is_array($cat)) {
            // Si no es numérico, lo invalidamos para evitar consultas erróneas a la BD
            $query->set('cat', 0);
            $query->set('category_name', '');
        }
    }

    // 2. Validar otros parámetros comunes que podrían ser sondeados
    $numeric_params = array('p', 'page_id', 'author', 'm');
    foreach ($numeric_params as $param) {
        $val = $query->get($param);
        if (!empty($val) && !is_numeric($val) && !is_array($val)) {
            $query->set($param, 0);
        }
    }
}
add_action('pre_get_posts', 'github_theme_validate_query_params');

/**
 * Redirigir todos los errores 404 al inicio (Home)
 * Útil para evitar errores 404 penalizables por SEO y mejorar la privacidad del sitio.
 */
function github_theme_redirect_404_to_home() {
    if (is_404()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}
add_action('template_redirect', 'github_theme_redirect_404_to_home');

// 3. Eliminar info de autor en oEmbed API
add_filter('oembed_response_data', function($data) {
    if (isset($data['author_name'])) unset($data['author_name']);
    if (isset($data['author_url'])) unset($data['author_url']);
    return $data;
});

/**
 * CONFIGURACIÓN DE CONTENT SECURITY POLICY (CSP)
 * Agrega los dominios necesarios para que el tema funcione correctamente (Geist Fonts y Twitch API)
 */
function github_theme_security_headers($headers) {
    // Definimos los dominios permitidos
    $style_src = "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net";
    $font_src = "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net";
    $connect_src = "connect-src 'self' https://decapi.me https://cdn.jsdelivr.net"; // Para la API de Twitch y Source Maps
    
    if (isset($headers['Content-Security-Policy'])) {
        // Si ya existe una política, intentamos ampliarla de forma inteligente
        $csp = $headers['Content-Security-Policy'];
        
        // Ampliar style-src
        if (strpos($csp, 'style-src') !== false) {
            $csp = preg_replace('/style-src\s+([^;]+)/i', "style-src $1 https://cdn.jsdelivr.net", $csp);
        } else {
            $csp .= "; " . $style_src;
        }
        
        // Ampliar font-src
        if (strpos($csp, 'font-src') !== false) {
            $csp = preg_replace('/font-src\s+([^;]+)/i', "font-src $1 https://cdn.jsdelivr.net", $csp);
        } else {
            $csp .= "; " . $font_src;
        }

        // Ampliar connect-src
        if (strpos($csp, 'connect-src') !== false) {
            $csp = preg_replace('/connect-src\s+([^;]+)/i', "connect-src $1 https://decapi.me https://cdn.jsdelivr.net", $csp);
        } else {
            $csp .= "; " . $connect_src;
        }
        
        $headers['Content-Security-Policy'] = $csp;
    } else {
        // Si no existe, creamos una básica pero permisiva para el tema
        $headers['Content-Security-Policy'] = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " . $style_src . "; " . $font_src . "; img-src 'self' data: https:; " . $connect_src . ";";
    }

    // 2. Cabeceras de protección básicas
    $headers['X-Frame-Options']         = 'SAMEORIGIN';
    $headers['X-Content-Type-Options']  = 'nosniff';
    $headers['X-XSS-Protection']        = '1; mode=block';
    $headers['Referrer-Policy']         = 'strict-origin-when-cross-origin';

    // 3. HSTS (Fuerza HTTPS) - Solo aplicar si el sitio está en HTTPS
    if ( is_ssl() ) {
        $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
    }
    
    return $headers;
}
add_filter('wp_headers', 'github_theme_security_headers', 999);

/**
 * Seguridad y Rendimiento: Deshabilitar Comentarios por Completo
 * Dado que el tema no utiliza comentarios, cerramos todas las vías de entrada y gestión.
 */
function github_theme_disable_comments_logic() {
    // 1. Cerrar comentarios y trackbacks en nuevos posts
    add_filter( 'comments_open', '__return_false', 20, 2 );
    add_filter( 'pings_open', '__return_false', 20, 2 );

    // 2. Cerrar comentarios y trackbacks existentes
    add_filter( 'comments_array', '__return_empty_array', 10, 2 );

    // 3. Eliminar soporte para comentarios en types de post
    $post_types = get_post_types();
    foreach ( $post_types as $post_type ) {
        if ( post_type_supports( $post_type, 'comments' ) ) {
            remove_post_type_support( $post_type, 'comments' );
            remove_post_type_support( $post_type, 'trackbacks' );
        }
    }

    // 4. Eliminar menús de comentarios del panel de administración
    if ( is_admin() ) {
        remove_menu_page( 'edit-comments.php' );
    }
}
add_action( 'init', 'github_theme_disable_comments_logic' );

// 5. Eliminar el menú de comentarios de la barra superior
add_action( 'wp_before_admin_bar_render', function() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu( 'comments' );
});

// 6. Eliminar widgets de comentarios recientes si existieran
add_action( 'widgets_init', function() {
    unregister_widget( 'WP_Widget_Recent_Comments' );
});
