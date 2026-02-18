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
