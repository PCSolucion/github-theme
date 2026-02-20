<?php
/**
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!is_active_sidebar('sidebar-1')) {
    return;
}
?>

<aside class="sidebar" role="complementary">
    <?php dynamic_sidebar('sidebar-1'); ?>
</aside>




















