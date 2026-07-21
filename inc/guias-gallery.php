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
 * Normalize accented first-letter to its ASCII equivalent.
 */
function github_theme_normalize_letter( $char ) {
    $char = mb_strtoupper( $char, 'UTF-8' );
    $map  = array(
        'Á'=>'A','À'=>'A','Â'=>'A','Ä'=>'A',
        'É'=>'E','È'=>'E','Ê'=>'E','Ë'=>'E',
        'Í'=>'I','Ì'=>'I','Î'=>'I','Ï'=>'I',
        'Ó'=>'O','Ò'=>'O','Ô'=>'O','Ö'=>'O',
        'Ú'=>'U','Ù'=>'U','Û'=>'U','Ü'=>'U',
        'Ñ'=>'N',
    );
    return isset( $map[ $char ] ) ? $map[ $char ] : $char;
}

/**
 * Get all game tags (tags attached to posts in the 'videojuegos' category).
 * Each entry includes: id, name, slug, count, first_post_url, cover (cached or '').
 */
function github_theme_get_game_tags_data() {

    $videojuegos = get_category_by_slug( 'videojuegos' );
    if ( ! $videojuegos ) {
        return array();
    }

    // Collect unique tag IDs from all videojuegos posts.
    $post_ids = get_posts( array(
        'category'       => $videojuegos->term_id,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ) );

    if ( empty( $post_ids ) ) {
        return array();
    }

    $tag_ids = array();
    foreach ( $post_ids as $pid ) {
        $t = wp_get_post_tags( $pid, array( 'fields' => 'ids' ) );
        if ( $t ) {
            $tag_ids = array_merge( $tag_ids, $t );
        }
    }
    $tag_ids = array_unique( $tag_ids );

    if ( empty( $tag_ids ) ) {
        return array();
    }

    $tags = get_tags( array(
        'include'    => $tag_ids,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'hide_empty' => true,
    ) );

    $result = array();
    foreach ( $tags as $tag ) {
        // First post of this tag inside videojuegos (oldest = guide start).
        $first = get_posts( array(
            'tag_id'         => $tag->term_id,
            'category'       => $videojuegos->term_id,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ) );

        $latest = get_posts( array(
            'tag_id'         => $tag->term_id,
            'category'       => $videojuegos->term_id,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        ) );

        $key_v6 = 'rawg_v6_' . md5( $tag->slug );
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

        $result[] = array(
            'id'             => $tag->term_id,
            'name'           => $tag->name,
            'slug'           => $tag->slug,
            'count'          => $tag->count,
            'first_post_url' => ! empty( $first ) ? get_permalink( $first[0]->ID ) : get_tag_link( $tag->term_id ),
            'cover'          => is_array($rawg_data) && !empty($rawg_data['cover']) ? $rawg_data['cover'] : '',
            'metacritic'     => is_array($rawg_data) && !empty($rawg_data['metacritic']) ? $rawg_data['metacritic'] : '',
            'platforms'      => is_array($rawg_data) && !empty($rawg_data['platforms']) ? $rawg_data['platforms'] : array(),
            'needs_data'     => ( $rawg_data === false ),
            'last_updated'   => ! empty( $latest ) ? strtotime( $latest[0]->post_date ) : 0,
        );
    }

    $recent_sorted = $result;
    usort( $recent_sorted, function( $a, $b ) {
        return $b['last_updated'] - $a['last_updated'];
    } );
    
    $top_20 = array();
    foreach ( array_slice( $recent_sorted, 0, 20 ) as $index => $item ) {
        $top_20[ $item['id'] ] = $index + 1;
    }

    foreach ( $result as &$item ) {
        $item['recent_order'] = isset( $top_20[ $item['id'] ] ) ? $top_20[ $item['id'] ] : 9999;
    }
    unset( $item );

    return $result;
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
