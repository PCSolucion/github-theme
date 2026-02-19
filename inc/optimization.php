<?php
/**
 * Funciones de Optimización y Rendimiento
 *
 * @package GitHubTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

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
 * Optimizar Heartbeat API
 * Reducir frecuencia en lugar de deshabilitarlo para evitar problemas con el editor
 */
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

/**
 * Agregar sugerencias de recursos (preload para CSS crítico)
 */
function github_preload_critical_assets() {
    $version = GITHUB_THEME_VERSION;
    // Preload del CSS principal con su versión correspondiente para evitar duplicados
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '?ver=' . $version . '" as="style">' . "\n";
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/main.css?ver=' . $version . '" as="style">' . "\n";
}
add_action('wp_head', 'github_preload_critical_assets', 1);

/**
 * Deshabilitar jQuery Migrate
 * Elimina el script de compatibilidad y el mensaje de consola asociado.
 * Solo se aplica en el frontend para no romper plugins en el admin.
 */
function github_remove_jquery_migrate($scripts) {
    if (!is_admin() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        
        if ($script->deps) { // Comprobar dependencias
            $script->deps = array_diff($script->deps, array('jquery-migrate'));
        }
    }
}
add_action('wp_default_scripts', 'github_remove_jquery_migrate');
