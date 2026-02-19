<?php
/**
 * Template Functions
 *
 * Funciones de utilidad para el renderizado del tema.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Obtener el color asociado a una categoría (estilo GitHub language color).
 *
 * @param int $category_id ID de la categoría.
 * @return string Color en formato hexadecimal.
 */
function github_theme_get_category_color( $category_id ) {
    $category = get_category( $category_id );

    if ( $category && ! is_wp_error( $category ) ) {
        $category_colors = array(
            'css'          => '#563d7c', // Purple
            'gameplays'    => '#db6b00', // Orange
            'linux'        => '#f1e05a', // Yellow
            'noticias'     => '#24292e', // Dark Grey
            'programacion' => '#f34b7d', // Pink
            'seguridad'    => '#d73a49', // Red
            'streaming'    => '#9146ff', // Twitch Purple
            'tecnologia'   => '#00bcd4', // Cyan
            'videojuegos'  => '#2ea44f', // Green
            'windows'      => '#0078d7', // Blue
            'wordpress'    => '#21759b', // WordPress Blue
        );

        if ( array_key_exists( $category->slug, $category_colors ) ) {
            return $category_colors[ $category->slug ];
        }
    }

    // Paleta de fallback estilo GitHub language colors
    $colors = array(
        '#f34b7d', // JavaScript
        '#3178c6', // TypeScript
        '#3776ab', // Python
        '#e34c26', // HTML
        '#563d7c', // CSS
        '#f1e05a', // JavaScript (alt)
        '#701516', // Ruby
        '#b07219', // Java
        '#c72d0f', // PHP
        '#00add8', // Go
        '#178600', // Go (alt)
        '#f18e33', // Kotlin
        '#4F5D95', // PHP (alt)
        '#a97bff', // Vue
        '#61dafb', // React
        '#42b883', // Vue.js
        '#000000', // C
        '#00599c', // C++
        '#e38c00', // Rust
        '#4479a1', // Swift
    );

    return $colors[ $category_id % count( $colors ) ];
}

/**
 * Menú de navegación por defecto cuando no hay menú configurado en el admin.
 */
function github_theme_default_menu() {
    echo '<ul class="nav-menu">';
    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . __( 'Inicio', 'github-theme' ) . '</a></li>';

    if ( get_option( 'show_on_front' ) === 'page' ) {
        $page_for_posts = get_option( 'page_for_posts' );
        if ( $page_for_posts ) {
            echo '<li><a href="' . esc_url( get_permalink( $page_for_posts ) ) . '">' . __( 'Blog', 'github-theme' ) . '</a></li>';
        }
    }

    wp_list_pages( array(
        'title_li' => '',
        'exclude'  => get_option( 'page_on_front' ),
    ) );

    echo '</ul>';
}

/**
 * Obtener la URL del logo personalizado del sitio.
 *
 * @return string|false URL del logo o false si no hay logo configurado.
 */
function github_theme_get_logo_url() {
    if ( has_custom_logo() ) {
        $logo = wp_get_attachment_image_src( get_theme_mod( 'custom_logo' ), 'full' );
        return ( $logo && isset( $logo[0] ) ) ? $logo[0] : false;
    }
    return false;
}

/**
 * Imprimir las categorías del post con el estilo de colores de GitHub.
 *
 * @param int|WP_Post $post_id ID del post o objeto post. Por defecto el actual.
 */
function github_theme_post_categories( $post_id = null ) {
    if ( ! has_category( $post_id ) ) {
        return;
    }

    $categories = get_the_category( $post_id );
    echo '<div class="post-categories">';
    foreach ( $categories as $category ) {
        $category_color = github_theme_get_category_color( $category->term_id );
        printf(
            '<a href="%1$s" class="post-category" style="--category-color: %2$s">
                <span class="repo-language-color" style="background-color: %2$s"></span>
                <span class="category-name">%3$s</span>
            </a>',
            esc_url( get_category_link( $category->term_id ) ),
            esc_attr( $category_color ),
            esc_html( $category->name )
        );
    }
    echo '</div>';
}

/**
 * Calcular el peso total del post (Contenido + HTML base + Imágenes adjuntas).
 *
 * @param int|null $post_id ID del post. Por defecto el actual.
 * @return string Peso formateado (ej. "12.5kb").
 */
function github_theme_get_total_download_size( $post_id = null ) {
    $post = get_post( $post_id );
    if ( ! $post ) {
        return '0kb';
    }

    // 1. Peso del contenido texto + Overhead del tema HTML (~14KB)
    $content_size = strlen( $post->post_content );
    $overhead_size = 14300; // 14KB aprox de boilerplate HTML/CSS/JS del theme
    $total_bytes = $content_size + $overhead_size;

    // 2. Imagen Destacada
    if ( has_post_thumbnail( $post ) ) {
        $thumb_id = get_post_thumbnail_id( $post );
        $thumb_path = get_attached_file( $thumb_id );
        if ( $thumb_path && file_exists( $thumb_path ) ) {
            $total_bytes += filesize( $thumb_path );
        }
    }

    // 3. Imágenes dentro del contenido
    if ( preg_match_all( '/class="[^"]*wp-image-(\d+)[^"]*"/', $post->post_content, $matches ) ) {
        $image_ids = array_unique( $matches[1] );
        foreach ( $image_ids as $img_id ) {
            $img_path = get_attached_file( $img_id );
            if ( $img_path && file_exists( $img_path ) ) {
                $total_bytes += filesize( $img_path );
            }
        }
    }

    // Formatear (minúsculas y sin espacio para estilo git/hash)
    if ( $total_bytes >= 1048576 ) {
        return number_format( $total_bytes / 1048576, 1 ) . 'mb';
    } else {
        return number_format( $total_bytes / 1024, 1 ) . 'kb';
    }
}
