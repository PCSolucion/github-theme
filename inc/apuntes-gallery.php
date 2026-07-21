<?php
/**
 * Apuntes Gallery — Category Tags Grid
 *
 * Fetches tags from the 'apuntes' category.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get all tags attached to posts in the 'apuntes' category.
 * Each entry includes: id, name, slug, count, first_post_url, cover.
 */
function github_theme_get_apuntes_tags_data() {

    $apuntes = get_category_by_slug( 'apuntes' );
    if ( ! $apuntes ) {
        return array();
    }

    // Collect unique tag IDs from all apuntes posts.
    $post_ids = get_posts( array(
        'category'       => $apuntes->term_id,
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
        // First post of this tag inside apuntes
        $first = get_posts( array(
            'tag_id'         => $tag->term_id,
            'category'       => $apuntes->term_id,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'ASC',
            'post_status'    => 'publish',
        ) );

        $latest = get_posts( array(
            'tag_id'         => $tag->term_id,
            'category'       => $apuntes->term_id,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post_status'    => 'publish',
        ) );

        $result[] = array(
            'id'             => $tag->term_id,
            'name'           => $tag->name,
            'slug'           => $tag->slug,
            'count'          => $tag->count,
            'first_post_url' => ! empty( $first ) ? get_permalink( $first[0]->ID ) : get_tag_link( $tag->term_id ),
            'cover'          => '', // La imagen se puede añadir manualmente más adelante
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
