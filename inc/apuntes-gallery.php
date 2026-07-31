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
 * Each entry includes: id, name, slug, count, first_post_url, cover, last_updated, recent_order.
 *
 * Uses the shared github_theme_get_category_tags_data() without enrichment.
 */
function github_theme_get_apuntes_tags_data() {
    return github_theme_get_category_tags_data( 'apuntes' );
}
