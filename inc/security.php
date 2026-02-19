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
}
add_action('init', 'github_remove_version_info');

/**
 * Seguridad: Ocultar usuarios en REST API y sitemap
 */
add_filter('xmlrpc_enabled', '__return_false');

add_filter( 'rest_endpoints', function( $endpoints ) {
    // Solo ocultar endpoints sensibles para usuarios NO logueados
    // Los usuarios logueados necesitan la REST API para el editor de WordPress
    if ( !is_user_logged_in() ) {
        // Ocultar usuarios (Enumeración)
        if ( isset( $endpoints['/wp/v2/users'] ) ) unset( $endpoints['/wp/v2/users'] );
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
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
 * PROTECCIÓN CONTRA INYECCIÓN LDAP
 */
function github_theme_block_ldap_injection() {
    // NO ejecutar en el área de administración
    if (is_admin()) {
        return;
    }
    
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
                // Redirigir a búsqueda limpia
                $clean_search = str_replace($forbidden_chars, '', $search);
                $clean_search = trim($clean_search);
                
                if (!empty($clean_search)) {
                    wp_safe_redirect(add_query_arg('s', urlencode($clean_search), home_url('/')));
                    exit;
                } else {
                    wp_safe_redirect(home_url('/'));
                    exit;
                }
            }
        }
    }
}
add_action('template_redirect', 'github_theme_block_ldap_injection', 0);

/**
 * Limpiar query de búsqueda para prevenir inyección SQL y LDAP
 */
function github_theme_sanitize_search_query($query) {
    if ($query->is_search && !is_admin()) {
        // Limpiar el término de búsqueda
        $search_term = get_search_query();
        
        // Eliminar caracteres peligrosos - ser más restrictivo
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
add_filter( 'login_errors', 'github_no_wordpress_errors' );

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
    
    return $headers;
}
add_filter('wp_headers', 'github_theme_security_headers', 999);
