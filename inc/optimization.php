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
 * Agregar sugerencias de recursos (preload y preconnect) para mejorar el rendimiento
 */
function github_preload_critical_assets() {
    $version = GITHUB_THEME_VERSION;

    // 1. Preconnect a dominios externos críticos
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . "\n";

    // 2. Preload de fuentes críticas (Geist Sans y Mono)
    // Estas son las fuentes principales de la UI y código
    echo '<link rel="preload" href="https://cdn.jsdelivr.net/npm/@fontsource/geist-sans@5.0.3/files/geist-sans-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
    echo '<link rel="preload" href="https://cdn.jsdelivr.net/npm/@fontsource/geist-mono@5.0.3/files/geist-mono-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>' . "\n";

    // 3. Preload del CSS principal (Sin versión para concordar con la optimización de cacheo)
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">' . "\n";
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/assets/css/main.css" as="style">' . "\n";
}
add_action('wp_head', 'github_preload_critical_assets', 1);

/**
 * Generar automáticamente IDs para encabezados h2 y h3
 * Esto permite el funcionamiento de enlaces de ancla y mejora el SEO.
 */
function github_theme_auto_heading_ids($content) {
    if (is_singular() && in_the_loop() && is_main_query()) {
        $content = preg_replace_callback('/<(h[2-3])([^>]*)>(.*?)<\/h[2-3]>/i', function($matches) {
            $tag = $matches[1];
            $attributes = $matches[2];
            $title = $matches[3];
            
            // Si ya tiene un ID, no lo tocamos
            if (strpos($attributes, 'id=') !== false) {
                return $matches[0];
            }
            
            // Generar ID a partir del texto (slugify)
            $id = sanitize_title(wp_strip_all_tags($title));
            
            return "<{$tag}{$attributes} id=\"{$id}\">{$title}</{$tag}>";
        }, $content);
    }
    return $content;
}
add_filter('the_content', 'github_theme_auto_heading_ids', 10);

/**
 * Eliminar query strings (?ver=) de assets estáticos (CSS y JS)
 * Mejora el rendimiento al permitir que CDNs y proxies cacheen mejor los archivos.
 */
function github_theme_remove_script_version($src) {
    if (strpos($src, '?ver=') || strpos($src, '&ver=')) {
        $src = remove_query_arg('ver', $src);
    }
    return $src;
}
add_filter('style_loader_src', 'github_theme_remove_script_version', 9999);
add_filter('script_loader_src', 'github_theme_remove_script_version', 9999);

/**
 * Eliminar bloques de CSS y SVG innecesarios del core (Gutenberg Bloat)
 */
function github_theme_remove_wp_bloat() {
    // Eliminar estilos globales de bloques (inline CSS)
    wp_dequeue_style('global-styles');
    
    // Eliminar los filtros SVG de duotono de los bloques
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    remove_action('render_block', 'wp_render_duotone_support', 10);
}
add_action('wp_enqueue_scripts', 'github_theme_remove_wp_bloat', 100);

/**
 * Desactivar jQuery en el Front-end
 * Mejora el rendimiento eliminando una librería pesada.
 * Se mantiene en el admin para no romper el editor de bloques o la gestión de WP.
 */
function github_theme_remove_jquery() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-core');
        wp_deregister_script('jquery-migrate');
    }
}
add_action('wp_enqueue_scripts', 'github_theme_remove_jquery', 1);
