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
function github_theme_get_contributions_data($year = null) {
    global $wpdb;
    
    // Si no se especifica año, usar el año actual
    if ($year === null) {
        $year = intval(date('Y'));
    }
    
    // Intentar obtener del cache (transient)
    $transient_key = 'github_theme_contrib_' . $year;
    $cached_data = get_transient($transient_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    $start_date = $year . '-01-01';
    $end_date = $year . '-12-31';
    
    // Si es el año actual, solo hasta hoy
    if ($year == intval(date('Y'))) {
        $end_date = date('Y-m-d');
    }
    
    // Obtener todos los posts publicados en el año especificado
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(post_date) as post_date, COUNT(*) as count
        FROM {$wpdb->posts}
        WHERE post_status = 'publish'
        AND post_type = 'post'
        AND YEAR(post_date) = %d
        AND DATE(post_date) >= %s
        AND DATE(post_date) <= %s
        GROUP BY DATE(post_date)
        ORDER BY post_date ASC",
        $year,
        $start_date,
        $end_date
    ));
    
    $contributions = array();
    foreach ($posts as $post) {
        $contributions[$post->post_date] = intval($post->count);
    }
    
    // Guardar en cache por 12 horas
    set_transient($transient_key, $contributions, 12 * HOUR_IN_SECONDS);
    
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
    // Obtener año seleccionado (por defecto año actual)
    $selected_year = isset($_GET['contrib_year']) ? intval($_GET['contrib_year']) : intval(date('Y'));
    
    // Obtener años disponibles
    $available_years = github_theme_get_available_years();
    
    // Si el año seleccionado no está disponible, usar el más reciente
    if (!in_array($selected_year, $available_years) && !empty($available_years)) {
        $selected_year = $available_years[0];
    }
    
    $contributions = github_theme_get_contributions_data($selected_year);
    
    // Crear array con todos los días del año seleccionado (siempre el año completo)
    $year_start = new DateTime($selected_year . '-01-01');
    $year_end = new DateTime($selected_year . '-12-31');
    
    $current_date = clone $year_start;
    
    // Obtener el día de la semana del primer día (0 = domingo, 6 = sábado)
    $first_day_week = intval($current_date->format('w'));
    
    // Ajustar para que la semana comience en lunes (GitHub style)
    // 0 = Domingo -> 6, 1 = Lunes -> 0, etc.
    $first_day_week = ($first_day_week == 0) ? 6 : $first_day_week - 1;
    
    // Organizar días por semanas (cada semana es una columna)
    $weeks = array();
    
    // Agregar días vacíos al inicio si la semana no comienza en lunes
    if ($first_day_week > 0) {
        $weeks[0] = array();
        for ($i = 0; $i < $first_day_week; $i++) {
            $weeks[0][] = null;
        }
    }
    
    $week_index = isset($weeks[0]) ? 0 : -1;
    
    while ($current_date <= $year_end) {
        if (!isset($weeks[$week_index])) {
            $weeks[$week_index] = array();
        }
        
        // Si la semana tiene 7 días, empezar nueva semana
        if (count($weeks[$week_index]) >= 7) {
            $week_index++;
            $weeks[$week_index] = array();
        }
        
        $date_str = $current_date->format('Y-m-d');
        $count = isset($contributions[$date_str]) ? $contributions[$date_str] : 0;
        
        // Determinar nivel de intensidad
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
        
        $date_formatted = $current_date->format('d/m/Y');
        $tooltip = $count > 0 ? sprintf('%d %s el %s', $count, $count == 1 ? 'post' : 'posts', $date_formatted) : 'Sin posts el ' . $date_formatted;
        
        $weeks[$week_index][] = array(
            'date' => $date_str,
            'count' => $count,
            'intensity' => $intensity,
            'tooltip' => $tooltip,
            'date_formatted' => $date_formatted
        );
        
        $current_date->modify('+1 day');
    }
    
    // Completar la última semana con días vacíos si es necesario
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
    echo '<div class="contributions-header">';
    echo '<h2 class="contributions-title">Actividad</h2>';
    
    // Selector de años a la derecha
    if (!empty($available_years)) {
        echo '<div class="contributions-year-selector">';
        foreach ($available_years as $year) {
            $active_class = ($year == $selected_year) ? ' active' : '';
            $current_url = add_query_arg('contrib_year', $year);
            echo '<a href="' . esc_url($current_url) . '" class="year-link' . esc_attr($active_class) . '">' . esc_html($year) . '</a>';
        }
        echo '</div>';
    }
    
    echo '</div>';
    echo '<div class="contributions-calendar">';
    
    // Etiquetas de meses en la parte superior
    echo '<div class="contributions-months">';
    echo '<div class="month-spacer"></div>'; // Espacio para los días de la semana
    $week_col = 0;
    $last_shown_month = 0;
    
    foreach ($weeks as $week_idx => $week) {
        if (isset($month_positions[$week_col])) {
            $month_num = $month_positions[$week_col];
            // Solo mostrar si es un mes diferente al último mostrado
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
    
    // Grid principal con días y semanas
    echo '<div class="contributions-grid">';
    
    // Días de la semana (etiquetas verticales)
    $weekdays = array('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo');
    echo '<div class="contributions-weekdays">';
    foreach ($weekdays as $day) {
        echo '<span class="weekday-label">' . esc_html($day) . '</span>';
    }
    echo '</div>';
    
    // Renderizar semanas como columnas
    foreach ($weeks as $week) {
        echo '<div class="contributions-week">';
        foreach ($week as $day) {
            if ($day === null) {
                echo '<div class="contribution-cell empty"></div>';
            } else {
                echo '<div class="contribution-cell ' . esc_attr($day['intensity']) . '" title="' . esc_attr($day['tooltip']) . '" data-date="' . esc_attr($day['date']) . '" data-count="' . esc_attr($day['count']) . '"></div>';
            }
        }
        echo '</div>';
    }
    
    echo '</div>';
    
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
    
    echo '</div>';
    echo '</div>';
}
