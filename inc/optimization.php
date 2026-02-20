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
    // Eliminar estilos globales de bloques (inline CSS) y variantes
    wp_dequeue_style('global-styles');
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    
    // Eliminar Dashicons y Thickbox del frontend (ahorro de ~40KB)
    if (!is_admin()) {
        wp_dequeue_style('dashicons');
        wp_dequeue_style('thickbox');
        wp_deregister_script('thickbox');
    }
    
    // Eliminar los filtros SVG de duotono de los bloques
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    remove_action('render_block', 'wp_render_duotone_support', 10);
}
add_action('wp_enqueue_scripts', 'github_theme_remove_wp_bloat', 100);

/**
 * Añadir atributo 'defer' a scripts seleccionados para no bloquear el renderizado
 */
function github_theme_add_defer_attribute($tag, $handle) {
    $scripts_to_defer = array('github-theme-live-search');
    
    if (in_array($handle, $scripts_to_defer)) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'github_theme_add_defer_attribute', 10, 2);

/**
 * Desactivar jQuery en el Front-end
 * Mejora el rendimiento eliminando una librería pesada.
 * Se mantiene en el admin para no romper el editor de bloques o la gestión de WP.
 */
function github_theme_remove_jquery() {
    // Permitir jQuery solo en admin y en artículos individuales (donde se usa Thickbox)
    if (!is_admin() && !is_singular()) {
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-core');
        wp_deregister_script('jquery-migrate');
    }
}
add_action('wp_enqueue_scripts', 'github_theme_remove_jquery', 1);

/**
 * Deshabilitar Emojis de WordPress (Performance Bloat)
 */
function github_theme_disable_emojis() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    
    // Eliminar también de TinyMCE si fuera necesario
    add_filter('tiny_mce_plugins', 'github_theme_disable_emojis_tinymce');
    
    // Eliminar el DNS prefetch para s.w.org
    add_filter('wp_resource_hints', 'github_theme_remove_emoji_dns_prefetch', 10, 2);
}
add_action('init', 'github_theme_disable_emojis');

function github_theme_disable_emojis_tinymce($plugins) {
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    }
    return array();
}

function github_theme_remove_emoji_dns_prefetch($urls, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/15.0.3/svg/');
        $urls = array_diff($urls, array($emoji_svg_url));
    }
    return $urls;
}

/**
 * Optimización de Localización: Forzar lang="es-ES"
 * Mejora la precisión del SEO para el mercado español.
 */
function github_theme_localize_html_tag($output) {
    return 'lang="es-ES"';
}
add_filter('language_attributes', 'github_theme_localize_html_tag');

/**
 * Limpieza de Resource Hints: Eliminar dns-prefetch redundantes
 * Si usamos preconnect, el dns-prefetch es innecesario y ensucia el head.
 */
function github_theme_clean_resource_hints($urls, $relation_type) {
    if ('dns-prefetch' === $relation_type) {
        $preconnect_domains = array(
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'cdn.jsdelivr.net'
        );
        
        foreach ($urls as $key => $url) {
            foreach ($preconnect_domains as $domain) {
                if (strpos($url, $domain) !== false) {
                    unset($urls[$key]);
                }
            }
        }
    }
    return $urls;
}
add_filter('wp_resource_hints', 'github_theme_clean_resource_hints', 20, 2);

/**
 * Pre-computar y guardar metadatos técnicos (Hash y Tamaño) al guardar el post.
 * Esto evita cálculos pesados y accesos a disco en cada carga de página.
 */
function github_theme_save_technical_metadata($post_id) {
    // Solo para posts normales y evitar recursión
    if (get_post_type($post_id) !== 'post' || wp_is_post_revision($post_id)) {
        return;
    }

    // 1. Generar y guardar Commit Hash
    $commit_hash = substr(md5($post_id), 0, 7);
    update_post_meta($post_id, '_github_commit_hash', $commit_hash);

    // 2. Calcular y guardar Tamaño Total (Raw bytes para mayor precisión en el meta)
    $post = get_post($post_id);
    if ($post) {
        $content_size = strlen($post->post_content);
        $overhead_size = 14300; 
        $total_bytes = $content_size + $overhead_size;

        // Imágenes destacadas
        if (has_post_thumbnail($post_id)) {
            $thumb_id = get_post_thumbnail_id($post_id);
            $thumb_path = get_attached_file($thumb_id);
            if ($thumb_path && file_exists($thumb_path)) {
                $total_bytes += filesize($thumb_path);
            }
        }

        // Imágenes en contenido
        if (preg_match_all('/class="[^"]*wp-image-(\d+)[^"]*"/', $post->post_content, $matches)) {
            $image_ids = array_unique($matches[1]);
            foreach ($image_ids as $img_id) {
                $img_path = get_attached_file($img_id);
                if ($img_path && file_exists($img_path)) {
                    $total_bytes += filesize($img_path);
                }
            }
        }
        
        update_post_meta($post_id, '_github_total_size', $total_bytes);
    }
}
add_action('save_post', 'github_theme_save_technical_metadata');

/**
 * Generar el HTML de la Tabla de Contenidos a partir del contenido del post
 * 
 * @param string $content El contenido del post
 * @return string El HTML de la lista de contenidos
 */
function github_theme_generate_toc($content) {
    if (empty($content)) {
        return '';
    }

    // Extraer H2 y H3
    preg_match_all('/<(h[2-3])([^>]*)>(.*?)<\/h[2-3]>/i', $content, $matches);
    
    if (empty($matches[0])) {
        return '<p class="no-toc-content">No hay encabezados en este artículo.</p>';
    }

    $toc_html = '<ul class="toc-list">';
    
    foreach ($matches[1] as $index => $tag) {
        $attributes = $matches[2][$index];
        $title = wp_strip_all_tags($matches[3][$index]);
        
        // Obtener el ID (usamos el mismo patrón que github_theme_auto_heading_ids)
        $id = '';
        if (preg_match('/id="([^"]+)"/', $attributes, $id_match)) {
            $id = $id_match[1];
        } else {
            $id = sanitize_title($title);
        }
        
        $class = ($tag === 'h3') ? 'toc-h3' : 'toc-h2';
        $toc_html .= sprintf(
            '<li><a href="#%s" class="%s">%s</a></li>',
            esc_attr($id),
            esc_attr($class),
            esc_html($title)
        );
    }
    
    $toc_html .= '</ul>';
    
    return $toc_html;
}

/**
 * Speculative Loading API (Instant Navigation)
 * Instruye al navegador para precargar páginas antes de que el usuario haga clic.
 */
function github_theme_speculative_loading() {
    if (is_admin()) return;
    ?>
    <script type="speculationrules">
    {
      "prerender": [
        {
          "source": "document",
          "where": {
            "and": [
              { "href_matches": "<?php echo esc_url_raw(home_url('/')); ?>*" },
              { "not": { "href_matches": "<?php echo admin_url(); ?>*" } },
              { "not": { "href_matches": "*/wp-login*" } },
              { "not": { "href_matches": "*/wp-admin*" } },
              { "not": { "href_matches": "*\\?*" } }
            ]
          },
          "eagerness": "moderate"
        }
      ]
    }
    </script>
    <?php
}
add_action('wp_footer', 'github_theme_speculative_loading');

/**
 * Bloqueo Agresivo de Dashicons: Eliminar incluso si se inyectan tarde
 */
add_action('wp_print_styles', function() {
    if (!is_admin()) {
        wp_dequeue_style('dashicons');
    }
}, 100);
function github_theme_code_snippets_schema($content) {
    // Solo en posts o páginas individuales
    if (!is_singular()) {
        return $content;
    }

    // 1. Procesar tags <pre>
    $content = preg_replace_callback('/<pre([^>]*)>/i', function($matches) {
        $attrs = $matches[1];
        
        // Si ya tiene el esquema, no hacemos nada
        if (strpos($attrs, 'SoftwareSourceCode') !== false) {
            return $matches[0];
        }

        $language = 'text';
        if (preg_match('/class="[^"]*language-([^"\s]+)[^"]*"/i', $attrs, $lang_match)) {
            $language = $lang_match[1];
        }

        // Etiqueta visual del lenguaje
        $visual_label = sprintf(
            '<div class="code-language-label">%s</div>',
            esc_html($language)
        );

        return sprintf(
            '<pre%s itemscope itemtype="http://schema.org/SoftwareSourceCode">%s<meta itemprop="programmingLanguage" content="%s">',
            $attrs,
            $visual_label,
            esc_attr($language)
        );
    }, $content);

    // 2. Procesar tags <code> dentro de los <pre> ya marcados (o todos)
    $content = preg_replace_callback('/<code([^>]*)>/i', function($matches) {
        $attrs = $matches[1];
        if (strpos($attrs, 'itemprop="text"') !== false) {
            return $matches[0];
        }
        return "<code{$attrs} itemprop=\"text\">";
    }, $content);

    return $content;
}
add_filter('the_content', 'github_theme_code_snippets_schema', 99);
