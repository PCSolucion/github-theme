<?php
/**
 * Guías Gallery — Game Cover Grid
 *
 * Fetches game covers from RAWG API and displays them in an A-Z filterable grid.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * RAWG API Key.
 * Se recomienda definir esta constante en tu wp-config.php para evitar publicarla en el repositorio:
 * define( 'GITHUB_THEME_RAWG_KEY', 'tu_clave_aqui' );
 */
if ( file_exists( dirname( __FILE__ ) . '/rawg-config.php' ) ) {
    include_once dirname( __FILE__ ) . '/rawg-config.php';
}

if ( ! defined( 'GITHUB_THEME_RAWG_KEY' ) ) {
    define( 'GITHUB_THEME_RAWG_KEY', '' );
}

// =========================================================================
// DATA HELPERS
// =========================================================================

/**
 * Get all game tags (tags attached to posts in the 'videojuegos' category).
 * Each entry includes: id, name, slug, count, first_post_url, cover, metacritic,
 * platforms, needs_data, last_updated, recent_order.
 *
 * Uses the shared github_theme_get_category_tags_data() with a RAWG enrichment callback.
 */
function github_theme_get_game_tags_data() {
    return github_theme_get_category_tags_data( 'videojuegos', 'github_theme_enrich_with_rawg' );
}

/**
 * RAWG enrichment callback for game tags.
 * Returns extra fields (cover, metacritic, platforms, needs_data) from cached RAWG data.
 *
 * @param WP_Term $tag      The tag term object.
 * @param WP_Term $category The category term object (unused here).
 * @return array Extra fields to merge into the tag entry.
 */
function github_theme_enrich_with_rawg( $tag, $category ) {
    $key_v6    = 'rawg_v6_' . md5( $tag->slug );
    $rawg_data = get_transient( $key_v6 );

    if ( $rawg_data === false ) {
        // Check older versions but only if they have a cover (migration).
        $old_prefixes = array( 'rawg_v5_', 'rawg_v3_', 'rawg_data_' );
        foreach ( $old_prefixes as $p ) {
            $old_data = get_transient( $p . md5( $tag->slug ) );
            if ( is_array( $old_data ) && ! empty( $old_data['cover'] ) ) {
                $rawg_data = $old_data;
                set_transient( $key_v6, $rawg_data, 30 * DAY_IN_SECONDS );
                break;
            }
        }
    }

    return array(
        'cover'      => is_array( $rawg_data ) && ! empty( $rawg_data['cover'] ) ? $rawg_data['cover'] : '',
        'metacritic' => is_array( $rawg_data ) && ! empty( $rawg_data['metacritic'] ) ? $rawg_data['metacritic'] : '',
        'platforms'  => is_array( $rawg_data ) && ! empty( $rawg_data['platforms'] ) ? $rawg_data['platforms'] : array(),
        'needs_data' => ( $rawg_data === false ),
    );
}


// =========================================================================
// RAWG API
// =========================================================================

/**
 * Fetch a single game cover from RAWG and cache it for 30 days.
 */
function github_theme_fetch_rawg_cover( $slug, $name = '' ) {
    $key    = 'rawg_v6_' . md5( $slug );
    $cached = get_transient( $key );

    if ( $cached !== false ) {
        return $cached;
    }

    $args = array(
        'timeout'    => 12,
        'user-agent' => 'GitHubTheme-GameGuide/' . GITHUB_THEME_VERSION . '; ' . home_url(),
    );

    // Step 1: Try direct slug match (faster and more precise if slug matches RAWG)
    $url  = sprintf( 'https://api.rawg.io/api/games/%s?key=%s', $slug, GITHUB_THEME_RAWG_KEY );
    $resp = wp_remote_get( $url, $args );
    $code = wp_remote_retrieve_response_code( $resp );

    if ( $code === 200 ) {
        $game = json_decode( wp_remote_retrieve_body( $resp ), true );
    } else {
        // Step 2: Fallback to search using Name (more reliable) or Slug
        $search = ! empty( $name ) ? $name : str_replace( '-', ' ', $slug );
        $url    = sprintf(
            'https://api.rawg.io/api/games?key=%s&search=%s&page_size=1',
            GITHUB_THEME_RAWG_KEY,
            urlencode( $search )
        );

        $resp = wp_remote_get( $url, $args );
        $code = wp_remote_retrieve_response_code( $resp );

        if ( is_wp_error( $resp ) || $code !== 200 ) {
            return array();
        }

        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( empty( $body['results'][0] ) ) {
            set_transient( $key, array( 'cover' => '', 'metacritic' => '', 'platforms' => array() ), DAY_IN_SECONDS );
            return array();
        }
        $game = $body['results'][0];
    }

    $data = array(
        'cover'      => ! empty( $game['background_image'] ) ? $game['background_image'] : '',
        'metacritic' => isset( $game['metacritic'] ) ? $game['metacritic'] : '',
        'platforms'  => array(),
    );

    if (!empty($game['parent_platforms'])) {
        foreach ($game['parent_platforms'] as $p) {
            $data['platforms'][] = strtolower($p['platform']['slug']);
        }
    }

    set_transient( $key, $data, 30 * DAY_IN_SECONDS );
    return $data;
}

// =========================================================================
// REST ENDPOINT  (async cover fetching from frontend)
// =========================================================================

add_action( 'rest_api_init', function () {
    register_rest_route( 'github-theme/v1', '/game-cover/(?P<slug>[a-zA-Z0-9_-]+)', array(
        'methods'             => 'GET',
        'callback'            => 'github_theme_rawg_endpoint',
        'permission_callback' => '__return_true',
    ) );
} );

function github_theme_rawg_endpoint( $request ) {
    $slug  = sanitize_title( $request['slug'] );
    $name  = isset( $_GET['name'] ) ? sanitize_text_field( $_GET['name'] ) : '';
    $data = github_theme_fetch_rawg_cover( $slug, $name );
    return rest_ensure_response( $data );
}

// =========================================================================
// ASSET ENQUEUE
// =========================================================================

add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page_template( 'page-guias.php' ) && ! is_page( 'guias' ) && ! is_page_template( 'page-apuntes.php' ) && ! is_page( 'apuntes' ) ) {
        return;
    }

    wp_enqueue_style(
        'github-theme-guias',
        get_template_directory_uri() . '/assets/css/guias.css',
        array( 'github-theme-main' ),
        GITHUB_THEME_VERSION
    );

    wp_enqueue_script(
        'github-theme-guias',
        get_template_directory_uri() . '/assets/js/guias.js',
        array(),
        GITHUB_THEME_VERSION,
        true
    );

    if ( is_page_template( 'page-guias.php' ) || is_page( 'guias' ) ) {
        wp_localize_script( 'github-theme-guias', 'guiasData', array(
            'restUrl' => esc_url_raw( rest_url( 'github-theme/v1/game-cover/' ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        ) );
    }
} );

/**
 * Obtener el código SVG del icono de la plataforma especificada.
 *
 * @param string $slug Slug de la plataforma (pc, playstation, xbox, ios, android).
 * @return string Código SVG o cadena vacía.
 */
function github_theme_get_platform_svg( $slug ) {
    $svgs = array(
        'pc'          => '<svg viewBox="0 0 16 16" width="14" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M0 13.772l6.545.902V8.426H0zM0 7.62h6.545V1.296L0 2.198zm7.265 7.15l8.704 1.2V8.425H7.265zm0-13.57v6.42h8.704V0z" fill="currentColor"/></svg>',
        'playstation' => '<svg viewBox="0 0 21 16" width="16" height="12" xmlns="http://www.w3.org/2000/svg"><path d="M11.112 16L8 14.654V0s6.764 1.147 7.695 3.987c.931 2.842-.52 4.682-1.03 4.736-1.42.15-1.96-.748-1.96-.748V3.39l-1.544-.648L11.112 16zM12 14.32V16s7.666-2.338 8.794-3.24c1.128-.9-2.641-3.142-4.666-2.704 0 0-2.152.099-4.102.901-.019.008 0 1.51 0 1.51l4.948-1.095 1.743.73L12 14.32zm-5.024-.773s-.942.476-3.041.452c-2.1-.024-3.959-.595-3.935-1.833C.024 10.928 3.476 9.571 6.952 9v1.738l-3.693.952s-.632.786.217.81A11.934 11.934 0 007 12.046l-.024 1.5z" fill="currentColor"/></svg>',
        'xbox'        => '<svg viewBox="0 0 16 16" width="14" height="14" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M3.564 1.357l-.022.02c.046-.048.11-.1.154-.128C4.948.435 6.396 0 8 0c1.502 0 2.908.415 4.11 1.136.086.052.324.215.446.363C11.4.222 7.993 2.962 7.993 2.962c-1.177-.908-2.26-1.526-3.067-1.746-.674-.185-1.14-.03-1.362.141zm10.305 1.208c-.035-.04-.074-.076-.109-.116-.293-.322-.653-.4-.978-.378-.295.092-1.66.584-3.342 2.172 0 0 1.894 1.841 3.053 3.723 1.159 1.883 1.852 3.362 1.426 5.415A7.969 7.969 0 0016 7.999a7.968 7.968 0 00-2.13-5.434zM10.98 8.77a55.416 55.416 0 00-2.287-2.405 52.84 52.84 0 00-.7-.686l-.848.854c-.614.62-1.411 1.43-1.853 1.902-.787.84-3.043 3.479-3.17 4.958 0 0-.502-1.174.6-3.88.72-1.769 2.893-4.425 3.801-5.29 0 0-.83-.913-1.87-1.544l-.007-.002s-.011-.009-.03-.02c-.5-.3-1.047-.53-1.573-.56a1.391 1.391 0 00-.878.431A8 8 0 0013.92 13.381c0-.002-.169-1.056-1.245-2.57-.253-.354-1.178-1.46-1.696-2.04z"/></svg>',
        'ios'         => '<svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12.016 1.868c-.96.064-2.457.653-3.076 1.385-.624.74-.95 1.87-.84 2.802 1.05-.05 2.527-.69 3.11-1.393.606-.723.95-1.85.806-2.794zm-3.642 5.09c-1.31-.052-2.52.827-3.197.827-.68 0-1.68-.806-2.75-.78-1.38.03-2.66.8-3.37 2.05-1.45 2.51-.37 6.22 1.03 8.24.69 1 1.5 2.11 2.59 2.07 1.05-.04 1.46-.68 2.73-.68 1.26 0 1.65.68 2.75.66 1.13-.02 1.82-1.02 2.5-2.02.79-1.15 1.12-2.27 1.14-2.33-.02-.01-2.18-.83-2.2-3.32-.02-2.08 1.7-3.07 1.78-3.12-1-1.45-2.54-1.66-3.08-1.68z"/></svg>',
        'android'     => '<svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M17.523 15.34c-.635 0-1.15-.515-1.15-1.15s.515-1.15 1.15-1.15 1.15.515 1.15 1.15-.515 1.15-1.15 1.15zm-11.046 0c-.635 0-1.15-.515-1.15-1.15s.515-1.15 1.15-1.15 1.15.515 1.15 1.15-.515 1.15-1.15 1.15zm11.45-7.53l1.9-3.29c.115-.2.046-.445-.154-.56-.2-.116-.446-.046-.56.155l-1.925 3.336A11.758 11.758 0 0 0 12 6.55c-1.892 0-3.67.45-5.188 1.236L4.887 4.45c-.115-.2-.36-.27-.56-.155-.2.115-.27.36-.155.56l1.9 3.29C2.705 9.877 0 14.613 0 20h24c0-5.387-2.705-10.123-6.073-12.19z"/></svg>',
    );
    return isset( $svgs[ $slug ] ) ? $svgs[ $slug ] : '';
}
