<?php
/**
 * Funciones para el gráfico de contribuciones
 *
 * @package GitHubTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Obtener años disponibles con posts
 */
function github_theme_get_available_years() {
    global $wpdb;
    
    $years = $wpdb->get_col(
        "SELECT DISTINCT YEAR(post_date) as year
        FROM {$wpdb->posts}
        WHERE post_status = 'publish'
        AND post_type = 'post'
        ORDER BY year DESC"
    );
    
    return $years;
}

/**
 * Obtener datos de contribuciones (posts por fecha) para un año específico
 * Retorna un array con los posts agrupados por fecha
 * 
 * @param int|null $year Año para obtener datos. Null para año actual.
 * @return array Array de contribuciones [fecha => cantidad]
 */
/**
 * Obtener datos de contribuciones (posts por fecha) para un año específico
 * Retorna un array con los posts agrupados por fecha
 * 
 * @param int|null $year Año para obtener datos. Null para año actual.
 * @param int|null $category_id ID de categoría para filtrar.
 * @return array Array de contribuciones [fecha => ['count' => int, 'titles' => array]]
 */
function github_theme_get_contributions_data($year = null, $category_id = null) {
    global $wpdb;
    
    if ($year === null) {
        $year = intval(date('Y'));
    }
    
    // 1. Intentar obtener de caché (Transients se guardan en BD, no en cookies)
    $cache_key = 'github_theme_contrib_' . $year;
    // Agregamos categoría al key solo si se usa en el futuro
    if ($category_id) {
        $cache_key .= '_cat_' . $category_id;
    }
    
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // 2. Si no hay caché, realizar consulta SQL
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(post_date) as post_date, post_title
        FROM {$wpdb->posts}
        WHERE post_status = 'publish'
        AND post_type = 'post'
        AND YEAR(post_date) = %d
        ORDER BY post_date ASC",
        $year
    ));
    
    $contributions = array();
    if ($posts) {
        foreach ($posts as $post) {
            $date = $post->post_date;
            if (!isset($contributions[$date])) {
                $contributions[$date] = array(
                    'count' => 0,
                    'titles' => array()
                );
            }
            $contributions[$date]['count']++;
            $contributions[$date]['titles'][] = $post->post_title;
        }
    }
    
    // 3. Guardar en caché por 24 horas
    set_transient($cache_key, $contributions, DAY_IN_SECONDS);
    
    return $contributions;
}

/**
 * Limpiar cache de contribuciones cuando se publica/actualiza un post
 */
function github_theme_clear_contributions_cache($post_id) {
    if (get_post_type($post_id) !== 'post') {
        return;
    }
    
    $year = get_the_date('Y', $post_id);
    
    // Limpiar cache del año
    delete_transient('github_theme_contrib_' . $year);
    
    // También limpiar el año actual por si acaso
    if ($year != date('Y')) {
        delete_transient('github_theme_contrib_' . date('Y'));
    }
}
add_action('save_post', 'github_theme_clear_contributions_cache');
add_action('delete_post', 'github_theme_clear_contributions_cache');

/**
 * Generar la tabla de contribuciones estilo GitHub
 */
function github_theme_render_contributions_table() {
    // Obtener año seleccionado (sin filtro de categoría)
    $selected_year = isset($_GET['contrib_year']) ? intval($_GET['contrib_year']) : intval(date('Y'));
    
    // Obtener años disponibles
    $available_years = github_theme_get_available_years();
    
    // Si el año seleccionado no está disponible, usar el más reciente
    if (!in_array($selected_year, $available_years) && !empty($available_years)) {
        $selected_year = $available_years[0];
    }
    
    // Obtener datos SIN filtro de categoría
    $contributions = github_theme_get_contributions_data($selected_year, null);
    
    // Crear array con todos los días del año seleccionado
    $year_start = new DateTime($selected_year . '-01-01');
    $year_end = new DateTime($selected_year . '-12-31');
    
    // Ajustar para que la primera semana comience en lunes
    $first_day_week = intval($year_start->format('w'));
    $first_day_week = ($first_day_week == 0) ? 6 : $first_day_week - 1;
    
    // Organizar días por semanas
    $weeks = array();
    
    // Días vacíos al inicio si no comienza en lunes
    if ($first_day_week > 0) {
        $weeks[0] = array();
        for ($i = 0; $i < $first_day_week; $i++) {
            $weeks[0][] = null;
        }
    }
    
    $week_index = isset($weeks[0]) ? 0 : -1;
    $current_date = clone $year_start;
    
    while ($current_date <= $year_end) {
        if (!isset($weeks[$week_index])) {
            $weeks[$week_index] = array();
        }
        
        if (count($weeks[$week_index]) >= 7) {
            $week_index++;
            $weeks[$week_index] = array();
        }
        
        $date_str = $current_date->format('Y-m-d');
        
        // Buscar datos de contribuciones para este día
        $count = 0;
        $titles = array();
        if (isset($contributions[$date_str])) {
            $count = $contributions[$date_str]['count'];
            $titles = $contributions[$date_str]['titles'];
        }
        
        // Determinar color según cantidad
        $intensity = 'none';
        if ($count > 0) {
            if ($count == 1) {
                $intensity = 'low';
            } elseif ($count <= 3) {
                $intensity = 'medium';
            } elseif ($count <= 5) {
                $intensity = 'high';
            } else {
                $intensity = 'very-high';
            }
        }
        
        $tooltip = $count > 0 ? $count . ' post' . ($count == 1 ? '' : 's') . ' el ' . $current_date->format('d/m/Y') : 'Sin posts el ' . $current_date->format('d/m/Y');
        $tooltip_titles = $count > 0 ? implode('|||', $titles) : '';
        
        $weeks[$week_index][] = array(
            'date' => $date_str,
            'count' => $count,
            'intensity' => $intensity,
            'tooltip' => $tooltip,
            'titles' => $tooltip_titles
        );
        
        $current_date->modify('+1 day');
    }
    
    // Completar última semana si es necesario
    if (isset($weeks[$week_index]) && count($weeks[$week_index]) < 7) {
        while (count($weeks[$week_index]) < 7) {
            $weeks[$week_index][] = null;
        }
    }
    
    // Preparar etiquetas de meses
    $months = array(
        1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
        5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
        9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
    );
    
    // Calcular qué semana corresponde a cada mes
    $month_positions = array();
    $last_month = 0;
    $week_col = 0;
    
    foreach ($weeks as $week_idx => $week) {
        $first_day_of_week = null;
        foreach ($week as $day) {
            if ($day !== null) {
                $first_day_of_week = new DateTime($day['date']);
                break;
            }
        }
        
        if ($first_day_of_week) {
            $current_month = intval($first_day_of_week->format('n'));
            // Si cambió el mes o es la primera semana con un mes
            if ($current_month != $last_month) {
                $month_positions[$week_col] = $current_month;
                $last_month = $current_month;
            }
        }
        $week_col++;
    }
    
    echo '<div class="contributions-container">';
    
    // Header con desplegable de años
    if (!empty($available_years)) {
        echo '<div class="contributions-header">';
        echo '<div class="contributions-year-dropdown">';
        echo '<select class="year-select" onchange="window.location.href=this.value" aria-label="Seleccionar año">';
        foreach ($available_years as $year) {
            $selected = ($year == $selected_year) ? ' selected' : '';
            $current_url = add_query_arg('contrib_year', $year, remove_query_arg('contrib_cat'));
            echo '<option value="' . esc_url($current_url) . '"' . $selected . '>' . esc_html($year) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        echo '</div>'; // End contributions-header
    }
    
    echo '<div class="contributions-body">';
    
    echo '<div class="contributions-calendar">';
    
    // Etiquetas de meses
    echo '<div class="contributions-months">';
    echo '<div class="month-spacer"></div>';
    $week_col = 0;
    $last_shown_month = 0;
    
    foreach ($weeks as $week_idx => $week) {
        if (isset($month_positions[$week_col])) {
            $month_num = $month_positions[$week_col];
            if ($month_num != $last_shown_month) {
                echo '<span class="month-label">' . esc_html($months[$month_num]) . '</span>';
                $last_shown_month = $month_num;
            } else {
                echo '<span class="month-label empty"></span>';
            }
        } else {
            echo '<span class="month-label empty"></span>';
        }
        $week_col++;
    }
    echo '</div>';
    
    // Grid principal
    echo '<div class="contributions-grid">';
    
    // Días de la semana
    $weekdays = array('L', 'M', 'X', 'J', 'V', 'S', 'D');
    echo '<div class="contributions-weekdays">';
    foreach ($weekdays as $day) {
        echo '<span class="weekday-label">' . esc_html($day) . '</span>';
    }
    echo '</div>';
    
    // Renderizar semanas
    foreach ($weeks as $week) {
        echo '<div class="contributions-week">';
        foreach ($week as $day) {
            if ($day === null) {
                echo '<div class="contribution-cell empty"></div>';
            } else {
                
                echo '<div class="contribution-cell ' . esc_attr($day['intensity']) . '" 
                      data-tooltip="' . esc_attr($day['tooltip']) . '" 
                      data-titles="' . esc_attr($day['titles']) . '" 
                      data-date="' . esc_attr($day['date']) . '" 
                      data-count="' . esc_attr($day['count']) . '"></div>';
            }
        }
        echo '</div>';
    }
    
    echo '</div>';
    
    // Footer solo con Leyenda (sin rachas)
    echo '<div class="contributions-footer">';
    
    // Leyenda
    echo '<div class="contributions-legend">';
    echo '<span class="legend-label">Menos</span>';
    echo '<div class="legend-cells">';
    echo '<div class="legend-cell none"></div>';
    echo '<div class="legend-cell low"></div>';
    echo '<div class="legend-cell medium"></div>';
    echo '<div class="legend-cell high"></div>';
    echo '<div class="legend-cell very-high"></div>';
    echo '</div>';
    echo '<span class="legend-label">Más</span>';
    echo '</div>';
    
    echo '</div>'; // End contributions-footer
    
    echo '</div>'; // End contributions-calendar
    
    echo '</div>'; // End contributions-body
    echo '</div>'; // End contributions-container
}
