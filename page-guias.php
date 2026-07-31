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
