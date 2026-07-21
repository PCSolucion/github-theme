<?php
/**
 * Template Name: Galería de Apuntes
 *
 * Grid de etiquetas de apuntes con filtro alfabético A-Z.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$apuntes_tags = github_theme_get_apuntes_tags_data();

// Build set of available first letters.
$available = array();
foreach ( $apuntes_tags as $g ) {
    $letter = github_theme_normalize_letter( mb_substr( $g['name'], 0, 1, 'UTF-8' ) );
    if ( ctype_alpha( $letter ) ) {
        $available[ $letter ] = true;
    } else {
        $available['#'] = true;
    }
}

$letters = range( 'A', 'Z' );
?>

<div class="site-wrapper">
    <main class="content-area guias-page">

        <!-- HEADER -->
        <header class="guias-header">
            <div class="guias-header-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
            </div>
            <h1 class="guias-title">apuntes</h1>
            <p class="guias-subtitle">
                <span class="guias-count"><?php echo count( $apuntes_tags ); ?></span> apuntes disponibles
            </p>
        </header>

        <!-- A-Z FILTER -->
        <nav class="guias-filter" id="guias-filter" aria-label="Filtrar apuntes por letra">
            <button class="filter-btn active" data-letter="recent" type="button">Recientes</button>
            <button class="filter-btn" data-letter="all" type="button">Todos</button>
            <?php if ( isset( $available['#'] ) ) : ?>
                <button class="filter-btn" data-letter="#" type="button">#</button>
            <?php endif; ?>
            <?php foreach ( $letters as $l ) :
                $disabled = ! isset( $available[ $l ] );
            ?>
                <button
                    class="filter-btn<?php echo $disabled ? ' is-disabled' : ''; ?>"
                    data-letter="<?php echo $l; ?>"
                    type="button"
                    <?php echo $disabled ? 'disabled' : ''; ?>
                ><?php echo $l; ?></button>
            <?php endforeach; ?>
        </nav>

        <!-- GRID -->
        <div class="guias-grid" id="guias-grid">
            <?php foreach ( $apuntes_tags as $g ) :
                $letter = github_theme_normalize_letter( mb_substr( $g['name'], 0, 1, 'UTF-8' ) );
                if ( ! ctype_alpha( $letter ) ) {
                    $letter = '#';
                }
                $is_recent = $g['recent_order'] <= 20;
            ?>
                <a href="<?php echo esc_url( $g['first_post_url'] ); ?>"
                   class="guia-card <?php echo $is_recent ? '' : 'is-hidden'; ?>"
                   data-letter="<?php echo esc_attr( $letter ); ?>"
                   data-slug="<?php echo esc_attr( $g['slug'] ); ?>"
                   data-name="<?php echo esc_attr( $g['name'] ); ?>"
                   data-recent="<?php echo $is_recent ? $g['recent_order'] : ''; ?>"
                   style="<?php echo $is_recent ? 'order: ' . $g['recent_order'] . ';' : ''; ?>">

                    <div class="guia-cover">
                        <?php if ( ! empty( $g['cover'] ) ) : ?>
                            <img src="<?php echo esc_url( $g['cover'] ); ?>"
                                 alt="Carátula de <?php echo esc_attr( $g['name'] ); ?>"
                                 class="guia-cover-img loaded"
                                 loading="lazy" />
                        <?php else : ?>
                            <img src="" alt="Carátula de <?php echo esc_attr( $g['name'] ); ?>"
                                 class="guia-cover-img"
                                 loading="lazy"
                                 style="display:none;" />
                            <div class="guia-cover-placeholder">
                                <span><?php echo esc_html( mb_strtoupper( mb_substr( $g['name'], 0, 2, 'UTF-8' ), 'UTF-8' ) ); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="guia-cover-shine"></div>
                    </div>

                    <div class="guia-info">
                        <div class="guia-title-row">
                            <h3 class="guia-name"><?php echo esc_html( $g['name'] ); ?></h3>
                        </div>
                        <span class="guia-count-badge">
                            <?php echo (int) $g['count']; ?>
                            <?php echo ( (int) $g['count'] === 1 ) ? 'apunte' : 'apuntes'; ?>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- EMPTY STATE -->
        <div class="guias-empty" id="guias-empty" hidden>
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" opacity=".35">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <p>No hay apuntes que empiecen por esta letra.</p>
        </div>

    </main>
</div>

<?php get_footer(); ?>
