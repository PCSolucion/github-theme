<?php
/**
 * Template personalizado para el formulario de búsqueda
 *
 * @package GitHubTheme
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label for="search-field" class="screen-reader-text">
        <?php esc_html_e('Buscar:', 'github-theme'); ?>
    </label>
    <input 
        type="search" 
        id="search-field" 
        class="search-field" 
        placeholder="<?php esc_attr_e('Buscar...', 'github-theme'); ?>" 
        value="<?php echo esc_attr(get_search_query()); ?>" 
        name="s" 
        required 
        maxlength="100"
        pattern="[^*()\|&amp;!]*"
        title="No se permiten caracteres especiales como *, (, ), |, &amp;"
    />
    <button type="submit" class="search-submit">
        <svg aria-hidden="true" viewBox="0 0 16 16" version="1.1" width="16" height="16">
            <path fill-rule="evenodd" d="M11.5 7a4.499 4.499 0 11-8.998 0A4.499 4.499 0 0111.5 7zm-.82 4.74a6 6 0 111.06-1.06l3.04 3.04a.75.75 0 11-1.06 1.06l-3.04-3.04z"></path>
        </svg>
        <span class="screen-reader-text"><?php esc_html_e('Buscar', 'github-theme'); ?></span>
    </button>
</form>
