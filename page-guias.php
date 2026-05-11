<?php
/**
 * Template Name: Galería de Guías
 *
 * Grid de carátulas de videojuegos con filtro alfabético A-Z.
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

function github_theme_get_platform_svg($slug) {
    $svgs = array(
        'pc' => '<svg viewBox="0 0 16 16" width="14" height="14" xmlns="http://www.w3.org/2000/svg"><path d="M0 13.772l6.545.902V8.426H0zM0 7.62h6.545V1.296L0 2.198zm7.265 7.15l8.704 1.2V8.425H7.265zm0-13.57v6.42h8.704V0z" fill="currentColor"/></svg>',
        'playstation' => '<svg viewBox="0 0 21 16" width="16" height="12" xmlns="http://www.w3.org/2000/svg"><path d="M11.112 16L8 14.654V0s6.764 1.147 7.695 3.987c.931 2.842-.52 4.682-1.03 4.736-1.42.15-1.96-.748-1.96-.748V3.39l-1.544-.648L11.112 16zM12 14.32V16s7.666-2.338 8.794-3.24c1.128-.9-2.641-3.142-4.666-2.704 0 0-2.152.099-4.102.901-.019.008 0 1.51 0 1.51l4.948-1.095 1.743.73L12 14.32zm-5.024-.773s-.942.476-3.041.452c-2.1-.024-3.959-.595-3.935-1.833C.024 10.928 3.476 9.571 6.952 9v1.738l-3.693.952s-.632.786.217.81A11.934 11.934 0 007 12.046l-.024 1.5z" fill="currentColor"/></svg>',
        'xbox' => '<svg viewBox="0 0 16 16" width="14" height="14" xmlns="http://www.w3.org/2000/svg"><path fill="currentColor" d="M3.564 1.357l-.022.02c.046-.048.11-.1.154-.128C4.948.435 6.396 0 8 0c1.502 0 2.908.415 4.11 1.136.086.052.324.215.446.363C11.4.222 7.993 2.962 7.993 2.962c-1.177-.908-2.26-1.526-3.067-1.746-.674-.185-1.14-.03-1.362.141zm10.305 1.208c-.035-.04-.074-.076-.109-.116-.293-.322-.653-.4-.978-.378-.295.092-1.66.584-3.342 2.172 0 0 1.894 1.841 3.053 3.723 1.159 1.883 1.852 3.362 1.426 5.415A7.969 7.969 0 0016 7.999a7.968 7.968 0 00-2.13-5.434zM10.98 8.77a55.416 55.416 0 00-2.287-2.405 52.84 52.84 0 00-.7-.686l-.848.854c-.614.62-1.411 1.43-1.853 1.902-.787.84-3.043 3.479-3.17 4.958 0 0-.502-1.174.6-3.88.72-1.769 2.893-4.425 3.801-5.29 0 0-.83-.913-1.87-1.544l-.007-.002s-.011-.009-.03-.02c-.5-.3-1.047-.53-1.573-.56a1.391 1.391 0 00-.878.431A8 8 0 0013.92 13.381c0-.002-.169-1.056-1.245-2.57-.253-.354-1.178-1.46-1.696-2.04z"/></svg>',
        'ios' => '<svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M12.016 1.868c-.96.064-2.457.653-3.076 1.385-.624.74-.95 1.87-.84 2.802 1.05-.05 2.527-.69 3.11-1.393.606-.723.95-1.85.806-2.794zm-3.642 5.09c-1.31-.052-2.52.827-3.197.827-.68 0-1.68-.806-2.75-.78-1.38.03-2.66.8-3.37 2.05-1.45 2.51-.37 6.22 1.03 8.24.69 1 1.5 2.11 2.59 2.07 1.05-.04 1.46-.68 2.73-.68 1.26 0 1.65.68 2.75.66 1.13-.02 1.82-1.02 2.5-2.02.79-1.15 1.12-2.27 1.14-2.33-.02-.01-2.18-.83-2.2-3.32-.02-2.08 1.7-3.07 1.78-3.12-1-1.45-2.54-1.66-3.08-1.68z"/></svg>',
        'android' => '<svg viewBox="0 0 24 24" fill="currentColor" width="12" height="12"><path d="M17.523 15.34c-.635 0-1.15-.515-1.15-1.15s.515-1.15 1.15-1.15 1.15.515 1.15 1.15-.515 1.15-1.15 1.15zm-11.046 0c-.635 0-1.15-.515-1.15-1.15s.515-1.15 1.15-1.15 1.15.515 1.15 1.15-.515 1.15-1.15 1.15zm11.45-7.53l1.9-3.29c.115-.2.046-.445-.154-.56-.2-.116-.446-.046-.56.155l-1.925 3.336A11.758 11.758 0 0 0 12 6.55c-1.892 0-3.67.45-5.188 1.236L4.887 4.45c-.115-.2-.36-.27-.56-.155-.2.115-.27.36-.155.56l1.9 3.29C2.705 9.877 0 14.613 0 20h24c0-5.387-2.705-10.123-6.073-12.19z"/></svg>',
    );
    return isset($svgs[$slug]) ? $svgs[$slug] : '';
}

$game_tags = github_theme_get_game_tags_data();

// Build set of available first letters.
$available = array();
foreach ( $game_tags as $g ) {
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
                    <rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/><polyline points="7 8 10 11 7 14"/><line x1="13" y1="14" x2="17" y2="14"/>
                </svg>
            </div>
            <h1 class="guias-title">guías de videojuegos</h1>
            <p class="guias-subtitle">
                <span class="guias-count"><?php echo count( $game_tags ); ?></span> guías disponibles
            </p>
        </header>

        <!-- A-Z FILTER -->
        <nav class="guias-filter" id="guias-filter" aria-label="Filtrar guías por letra">
            <button class="filter-btn active" data-letter="recent" type="button">Recientes</button>
            <button class="filter-btn" data-letter="all" type="button">Todas</button>
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

        <!-- GAME GRID -->
        <div class="guias-grid" id="guias-grid">
            <?php foreach ( $game_tags as $g ) :
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
                   data-name="<?php echo esc_attr( mb_strtolower( $g['name'], 'UTF-8' ) ); ?>"
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

                        <div class="guia-metacritic" style="<?php echo empty($g['metacritic']) ? 'display:none;' : ''; ?>">
                            <span class="mc-score"><?php echo esc_html($g['metacritic']); ?></span>
                        </div>
                        
                        <div class="guia-cover-shine"></div>
                    </div>

                    <div class="guia-info">
                        <div class="guia-title-row">
                            <h3 class="guia-name"><?php echo esc_html( $g['name'] ); ?></h3>
                            <div class="guia-platforms">
                                <?php 
                                if (!empty($g['platforms'])) {
                                    foreach ($g['platforms'] as $plat) {
                                        echo github_theme_get_platform_svg($plat);
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <span class="guia-count-badge">
                            <?php echo (int) $g['count']; ?>
                            <?php echo ( (int) $g['count'] === 1 ) ? 'capítulo' : 'capítulos'; ?>
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
            <p>No hay guías que empiecen por esta letra.</p>
        </div>

    </main>
</div>

<?php get_footer(); ?>
