<?php
/**
 * Footer del tema
 *
 * @package GitHubTheme
 */
?>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-links">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer',
                'menu_class' => 'footer-menu',
                'container' => false,
                'fallback_cb' => 'github_theme_footer_default_menu',
            ));
            ?>
        </div>
    </div>
</footer>

<?php
/**
 * Menú footer por defecto si no hay menú configurado
 */
function github_theme_footer_default_menu() {
    echo '<ul class="footer-menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">Inicio</a></li>';
    if (get_option('show_on_front') == 'page') {
        $page_for_posts = get_option('page_for_posts');
        if ($page_for_posts) {
            echo '<li><a href="' . esc_url(get_permalink($page_for_posts)) . '">Blog</a></li>';
        }
    }
    if (get_privacy_policy_url()) {
        echo '<li><a href="' . esc_url(get_privacy_policy_url()) . '">Privacidad</a></li>';
    }
    wp_list_pages(array(
        'title_li' => '',
        'exclude' => get_option('page_on_front'),
    ));
    echo '</ul>';
}
?>

<?php wp_footer(); ?>
</body>
</html>

