<?php
/**
 * Script para LIMPIAR CACHE y VERIFICAR datos de contribuciones
 * 
 * INSTRUCCIONES:
 * 1. Sube este archivo a /wp-content/themes/github-theme/
 * 2. Accede a: http://tu-sitio.com/wp-content/themes/github-theme/test-contributions.php
 * 3. Verá los datos de contribuciones y limpiará el cache
 * 4. BORRA este archivo después
 */

// Cargar WordPress
require_once('../../../../wp-load.php');

echo "<h1>Test de Contribuciones - Debug</h1>";
echo "<hr>";

// 1. Limpiar TODO el cache de contribuciones
echo "<h2>1. Limpiando Cache</h2>";
global $wpdb;
$deleted = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_github_theme_contrib_%'");
echo "<p style='color: green;'>✓ Cache limpiado: {$deleted} transients eliminados</p>";

// 2. Obtener posts del año actual
echo "<h2>2. Verificando Posts en la Base de Datos</h2>";
$year = date('Y');
$posts = $wpdb->get_results($wpdb->prepare(
    "SELECT DATE(post_date) as post_date, post_title, post_status
    FROM {$wpdb->posts}
    WHERE post_status = 'publish'
    AND post_type = 'post'
    AND YEAR(post_date) = %d
    ORDER BY post_date DESC
    LIMIT 20",
    $year
));

if (empty($posts)) {
    echo "<p style='color: red;'>✗ No se encontraron posts publicados en {$year}</p>";
} else {
    echo "<p style='color: green;'>✓ Se encontraron " . count($posts) . " posts en {$year}</p>";
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>Fecha</th><th>Título</th><th>Estado</th></tr>";
    foreach ($posts as $post) {
        echo "<tr>";
        echo "<td>" . $post->post_date . "</td>";
        echo "<td>" . esc_html($post->post_title) . "</td>";
        echo "<td>" . $post->post_status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Probar la función de contribuciones
echo "<h2>3. Probando Función de Contribuciones</h2>";
require_once('inc/contributions.php');

$contributions = github_theme_get_contributions_data($year, null);

if (empty($contributions)) {
    echo "<p style='color: red;'>✗ La función de contribuciones devolvió un array vacío</p>";
} else {
    echo "<p style='color: green;'>✓ La función devolvió " . count($contributions) . " días con posts</p>";
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>Fecha</th><th>Posts</th><th>Títulos</th></tr>";
    
    $sample = 0;
    foreach ($contributions as $date => $data) {
        if ($sample++ >= 10) break; // Solo mostrar los primeros 10
        echo "<tr>";
        echo "<td>" . $date . "</td>";
        echo "<td>" . $data['count'] . "</td>";
        echo "<td>" . implode(', ', array_slice($data['titles'], 0, 2)) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 4. Verificar generación de HTML
echo "<h2>4. Ejemplo de HTML Generado</h2>";
echo "<p>Para el día de hoy:</p>";
$today = date('Y-m-d');
if (isset($contributions[$today])) {
    $count = $contributions[$today]['count'];
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
    
    $bg_colors = array(
        'none' => '#161b22',
        'low' => '#0e4429',
        'medium' => '#006d32',
        'high' => '#26a641',
        'very-high' => '#39d353'
    );
    $bg_color = $bg_colors[$intensity];
    
    echo "<p>Fecha: <strong>{$today}</strong></p>";
    echo "<p>Posts: <strong>{$count}</strong></p>";
    echo "<p>Intensidad: <strong>{$intensity}</strong></p>";
    echo "<p>Color: <strong>{$bg_color}</strong></p>";
    echo "<div style='width: 30px; height: 30px; background-color: {$bg_color}; border: 1px solid #fff; display: inline-block;'></div>";
} else {
    echo "<p style='color: orange;'>⚠ No hay posts para hoy ({$today})</p>";
}

echo "<hr>";
echo "<h2>5. Acción Necesaria</h2>";
echo "<p><strong>1.</strong> Recarga tu página principal con Ctrl+F5</p>";
echo "<p><strong>2.</strong> Inspecciona una celda con el navegador (F12)</p>";
echo "<p><strong>3.</strong> Verifica que tenga el atributo style='background-color: ...'</p>";
echo "<p><strong style='color: red;'>4. BORRA este archivo test-contributions.php</strong></p>";
