<?php
/**
 * Filtros y Transformaciones de Contenido (the_content)
 *
 * Centraliza todos los filtros aplicados a the_content ordenados por prioridad de ejecución.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Prioridad 9: Limpiar etiquetas <p> y <br> dentro de bloques <pre>.
 * WordPress a veces envuelve el contenido de <pre> en <p>, rompiendo el formato de código.
 * Se ejecuta antes que wpautop (prioridad 10).
 *
 * @param string $content El contenido del post.
 * @return string Contenido con las etiquetas <pre> saneadas.
 */
function github_theme_fix_pre_tags( $content ) {
    $content = preg_replace_callback( '/<pre([^>]*)>(.*?)<\/pre>/is', function( $matches ) {
        $pre_attrs     = $matches[1];
        $inner_content = $matches[2];

        $inner_content = str_replace( array( '<p>', '</p>' ), '', $inner_content );
        $inner_content = str_replace( array( '<br>', '<br/>', '<br />' ), "\n", $inner_content );

        return '<pre' . $pre_attrs . '>' . $inner_content . '</pre>';
    }, $content );

    return $content;
}
add_filter( 'the_content', 'github_theme_fix_pre_tags', 9 );

/**
 * Prioridad 10: Generar automáticamente IDs y enlaces de ancla para encabezados h2 y h3.
 * Permite enlaces de ancla, mejora el SEO y facilita compartir secciones.
 *
 * @param string $content El contenido del post.
 * @return string Contenido con IDs en encabezados.
 */
function github_theme_auto_heading_ids( $content ) {
    if ( is_singular() && in_the_loop() && is_main_query() ) {
        $content = preg_replace_callback( '/<(h[2-3])([^>]*)>(.*?)<\/h[2-3]>/i', function( $matches ) {
            $tag        = $matches[1];
            $attributes = $matches[2];
            $title      = $matches[3];

            // Si ya tiene un ID, lo extraemos. Si no, lo generamos.
            if ( preg_match( '/id="([^"]+)"/', $attributes, $id_match ) ) {
                $id = $id_match[1];
            } else {
                $id          = sanitize_title( wp_strip_all_tags( $title ) );
                $attributes .= " id=\"{$id}\"";
            }

            // Inyectar el enlace de ancla
            $anchor = sprintf(
                '<a href="#%s" class="heading-anchor" aria-label="Enlace permanente a esta sección" title="Copiar enlace a esta sección">#</a>',
                esc_attr( $id )
            );

            return "<{$tag}{$attributes}>{$anchor}{$title}</{$tag}>";
        }, $content );

        // Dar IDs automáticos a los bloques de código para poder referenciarlos
        $snippet_count = 0;
        $content       = preg_replace_callback( '/<pre([^>]*)>/i', function( $matches ) use ( &$snippet_count ) {
            $attrs = $matches[1];
            if ( strpos( $attrs, 'id=' ) !== false ) {
                return $matches[0];
            }
            $snippet_count++;
            return "<pre{$attrs} id=\"code-snippet-{$snippet_count}\">";
        }, $content );
    }
    return $content;
}
add_filter( 'the_content', 'github_theme_auto_heading_ids', 10 );

/**
 * Prioridad 20: Habilitar lazy loading nativo para imágenes.
 * Mejora el rendimiento al cargar imágenes solo cuando son visibles.
 *
 * @param string $content El contenido del post.
 * @return string Contenido con atributo loading="lazy".
 */
function github_theme_add_lazy_loading( $content ) {
    if ( is_singular() && in_the_loop() && is_main_query() ) {
        $content = preg_replace( '/<img((?![^>]*loading=)[^>]*)>/i', '<img$1 loading="lazy">', $content );
    }
    return $content;
}
add_filter( 'the_content', 'github_theme_add_lazy_loading', 20 );

/**
 * Prioridad 99: Añadir Schema.org (SoftwareSourceCode) y wrappers a bloques de código.
 *
 * @param string $content El contenido del post.
 * @return string Contenido con marcado de esquemas para bloques de código.
 */
function github_theme_code_snippets_schema( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    // 1. Procesar bloques <pre> completos
    $content = preg_replace_callback( '/<pre([^>]*)>(.*?)<\/pre>/is', function( $matches ) {
        $attrs         = $matches[1];
        $inner_content = $matches[2];

        // Extraer lenguaje si existe en las clases
        $language = 'text';
        if ( preg_match( '/class="[^"]*language-([^"\s]+)[^"]*"/i', $attrs, $lang_match ) ) {
            $language = $lang_match[1];
        }

        // Limpiar labels internos duplicados
        $inner_content = preg_replace( '/<div class="code-language-label">.*?<\/div>/is', '', $inner_content );

        // Limpiar atributos de schema si ya existen
        $attrs = preg_replace( '/itemscope|itemtype="[^"]*"|itemprop="[^"]*"/i', '', $attrs );

        return sprintf(
            '<div class="code-block-wrapper">' .
                '<div class="code-block-header">' .
                    '<div class="code-header-meta">' .
                        '<span class="code-language-label">%s</span>' .
                    '</div>' .
                '</div>' .
                '<pre%s itemscope itemtype="http://schema.org/SoftwareSourceCode">' .
                    '<meta itemprop="programmingLanguage" content="%s">' .
                    '%s' .
                '</pre>' .
            '</div>',
            esc_html( $language ),
            $attrs,
            esc_attr( $language ),
            $inner_content
        );
    }, $content );

    // 2. Asegurar que el code tenga el itemprop
    $content = preg_replace_callback( '/<code([^>]*)>/i', function( $matches ) {
        $attrs = $matches[1];
        if ( strpos( $attrs, 'itemprop="text"' ) !== false ) {
            return $matches[0];
        }
        return "<code{$attrs} itemprop=\"text\">";
    }, $content );

    return $content;
}
add_filter( 'the_content', 'github_theme_code_snippets_schema', 99 );
