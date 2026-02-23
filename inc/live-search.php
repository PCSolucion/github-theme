<?php
/**
 * Live Search functionality.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Live Search: endpoint REST personalizado.
 *
 * Ruta: GET /wp-json/github-theme/v1/search?q=TERMINO&per_page=8
 * Busca SOLO en post_title directamente en la base de datos.
 * Esto es infalible — no depende de hooks ni de la versión de WordPress.
 */
function github_theme_register_live_search_endpoint() {
    register_rest_route( 'github-theme/v1', '/search', array(
        'methods'             => 'GET',
        'callback'            => 'github_theme_live_search_handler',
        'permission_callback' => '__return_true',
        'args'                => array(
            'q' => array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function( $value ) {
                    return is_string( $value ) && strlen( $value ) >= 1;
                },
            ),
            'per_page' => array(
                'default'           => 15,
                'sanitize_callback' => 'absint',
            ),
        ),
    ) );
}
add_action( 'rest_api_init', 'github_theme_register_live_search_endpoint' );

/**
 * Handler for the live search request.
 */
function github_theme_live_search_handler( WP_REST_Request $request ) {
    global $wpdb;

    $term     = $request->get_param( 'q' );
    $per_page = min( (int) $request->get_param( 'per_page' ), 50 );
    $like     = '%' . $wpdb->esc_like( $term ) . '%';

    // Búsqueda directa en post_title y post_content
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT ID, post_title, post_excerpt, post_content, post_date, post_name
             FROM {$wpdb->posts}
             WHERE post_status = 'publish'
                AND post_type  = 'post'
                AND (post_title LIKE %s OR post_content LIKE %s)
             ORDER BY post_date DESC
             LIMIT %d",
            $like,
            $like,
            $per_page
        )
    );

    if ( empty( $results ) ) {
        return rest_ensure_response( array() );
    }

    $data = array();
    foreach ( $results as $post ) {
        $post_id   = (int) $post->ID;
        // Categorías
        $cats       = get_the_category( $post_id );
        $categories = ! empty( $cats ) ? implode( ', ', wp_list_pluck( $cats, 'name' ) ) : '';

        // Excerpt mejorado
        $excerpt_source = ! empty( $post->post_excerpt ) ? $post->post_excerpt : strip_shortcodes( $post->post_content );
        $excerptText = wp_trim_words( strip_tags( $excerpt_source ), 20, '…' );

        $data[] = array(
            'id'         => $post_id,
            'title'      => $post->post_title,
            'excerpt'    => $excerptText,
            'date'       => $post->post_date,
            'link'       => get_permalink( $post_id ),
            'categories' => $categories,
        );
    }

    return rest_ensure_response( $data );
}
