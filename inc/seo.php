<?php
/**
 * Funciones de SEO y Meta Tags
 *
 * @package GitHubTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generar meta description dinámica para SEO
 */
function github_theme_meta_description() {
    $description = '';
    
    // Página de inicio
    if (is_front_page() || is_home()) {
        $description = 'Blog de tecnología especializado en tutoriales y guías sobre Windows, Linux y WordPress. Soluciones prácticas, tips y trucos para optimizar tu experiencia tecnológica.';
    }
    // Post individual
    elseif (is_single()) {
        global $post;
        
        // Intentar usar el excerpt si existe
        if (!empty($post->post_excerpt)) {
            $description = wp_strip_all_tags($post->post_excerpt);
        } 
        // Si no hay excerpt, usar las primeras palabras del contenido
        else {
            $content = wp_strip_all_tags($post->post_content);
            $content = preg_replace('/\s+/', ' ', $content); // Normalizar espacios
            $words = explode(' ', $content);
            $description = implode(' ', array_slice($words, 0, 25));
        }
    }
    // Página de categoría
    elseif (is_category()) {
        $category = single_cat_title('', false);
        $cat_description = category_description();
        
        if (!empty($cat_description)) {
            $description = wp_strip_all_tags($cat_description);
        } else {
            $description = "Artículos y tutoriales sobre {$category}. Guías prácticas, tips y soluciones tecnológicas en nuestro blog especializado.";
        }
    }
    // Página de etiqueta
    elseif (is_tag()) {
        $tag = single_tag_title('', false);
        $tag_description = tag_description();
        
        if (!empty($tag_description)) {
            $description = wp_strip_all_tags($tag_description);
        } else {
            $description = "Contenido etiquetado como {$tag}. Encuentra artículos relacionados sobre tecnología, Windows, Linux y WordPress.";
        }
    }
    // Página de autor
    elseif (is_author()) {
        $author = get_the_author();
        $description = "Artículos escritos por {$author}. Tutoriales y guías sobre tecnología, Windows, Linux y WordPress.";
    }
    // Página de archivo
    elseif (is_archive()) {
        if (is_day()) {
            $description = 'Artículos publicados el ' . get_the_date();
        } elseif (is_month()) {
            $description = 'Artículos publicados en ' . get_the_date('F Y');
        } elseif (is_year()) {
            $description = 'Artículos publicados en ' . get_the_date('Y');
        } else {
            $description = 'Archivo de artículos sobre tecnología, Windows, Linux y WordPress.';
        }
    }
    // Página de búsqueda
    elseif (is_search()) {
        $search_query = get_search_query();
        $description = "Resultados de búsqueda para: {$search_query}. Encuentra artículos y tutoriales sobre tecnología.";
    }
    // Página 404
    elseif (is_404()) {
        $description = 'Página no encontrada. Explora nuestro blog de tecnología para encontrar tutoriales sobre Windows, Linux y WordPress.';
    }
    // Página genérica
    elseif (is_page()) {
        global $post;
        
        if (!empty($post->post_excerpt)) {
            $description = wp_strip_all_tags($post->post_excerpt);
        } else {
            $content = wp_strip_all_tags($post->post_content);
            $content = preg_replace('/\s+/', ' ', $content);
            $words = explode(' ', $content);
            $description = implode(' ', array_slice($words, 0, 25));
        }
    }
    // Fallback por defecto
    else {
        $description = get_bloginfo('description');
        if (empty($description)) {
            $description = 'Blog de tecnología con tutoriales sobre Windows, Linux y WordPress. Guías prácticas y soluciones tecnológicas.';
        }
    }
    
    // Limpiar y limitar la longitud (máximo 160 caracteres para SEO óptimo)
    $description = wp_strip_all_tags($description);
    $description = preg_replace('/\s+/', ' ', $description);
    $description = trim($description);
    
    if (strlen($description) > 160) {
        $description = substr($description, 0, 157) . '...';
    }
    
    return $description;
}

/**
 * Generar etiquetas OpenGraph y Twitter Card para redes sociales
 */
function github_theme_social_meta_tags() {
    // Variables comunes
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    $site_url = home_url('/');
    
    // Obtener imagen por defecto (logo del sitio o primera imagen del tema)
    $default_image = get_template_directory_uri() . '/assets/img/logo.svg';
    if (has_custom_logo()) {
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($logo) {
            $default_image = $logo[0];
        }
    }
    
    // Inicializar variables
    $og_title = '';
    $og_description = '';
    $og_image = $default_image;
    $og_url = '';
    $og_type = 'website';
    $twitter_card = 'summary_large_image';
    
    // Página de inicio
    if (is_front_page() || is_home()) {
        $og_title = $site_name;
        $og_description = $site_description ?: 'Blog de tecnología especializado en tutoriales y guías sobre Windows, Linux y WordPress.';
        $og_url = $site_url;
        $og_type = 'website';
    }
    // Post individual
    elseif (is_single()) {
        global $post;
        
        $og_title = get_the_title();
        $og_description = github_theme_meta_description();
        $og_url = get_permalink();
        $og_type = 'article';
        
        // Obtener imagen destacada si existe
        if (has_post_thumbnail()) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            if ($thumbnail) {
                $og_image = $thumbnail[0];
            }
        }
    }
    // Página
    elseif (is_page()) {
        $og_title = get_the_title();
        $og_description = github_theme_meta_description();
        $og_url = get_permalink();
        $og_type = 'website';
        
        // Obtener imagen destacada si existe
        if (has_post_thumbnail()) {
            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
            if ($thumbnail) {
                $og_image = $thumbnail[0];
            }
        }
    }
    // Categoría
    elseif (is_category()) {
        $category = get_queried_object();
        $og_title = single_cat_title('', false) . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_category_link($category->term_id);
        $og_type = 'website';
    }
    // Etiqueta
    elseif (is_tag()) {
        $tag = get_queried_object();
        $og_title = single_tag_title('', false) . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_tag_link($tag->term_id);
        $og_type = 'website';
    }
    // Autor
    elseif (is_author()) {
        $author = get_queried_object();
        $og_title = 'Artículos de ' . $author->display_name . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_author_posts_url($author->ID);
        $og_type = 'profile';
    }
    // Búsqueda
    elseif (is_search()) {
        $og_title = 'Resultados de búsqueda: ' . get_search_query() . ' - ' . $site_name;
        $og_description = github_theme_meta_description();
        $og_url = get_search_link();
        $og_type = 'website';
    }
    // Fallback
    else {
        $og_title = $site_name;
        $og_description = $site_description ?: github_theme_meta_description();
        $og_url = $site_url;
        $og_type = 'website';
    }
    
    // Limpiar y escapar valores
    $og_title = wp_strip_all_tags($og_title);
    $og_description = wp_strip_all_tags($og_description);
    
    // Generar las etiquetas
    ?>
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo esc_attr($og_type); ?>">
    <meta property="og:url" content="<?php echo esc_url($og_url); ?>">
    <meta property="og:title" content="<?php echo esc_attr($og_title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($og_description); ?>">
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <meta property="og:locale" content="es_ES">
    
    <?php if ($og_type === 'article' && is_single()) : ?>
    <meta property="article:published_time" content="<?php echo get_the_date('c'); ?>">
    <meta property="article:modified_time" content="<?php echo get_the_modified_date('c'); ?>">
    <meta property="article:author" content="<?php echo esc_attr(get_the_author()); ?>">
    <?php
    $categories = get_the_category();
    if ($categories) {
        foreach ($categories as $category) {
            echo '<meta property="article:section" content="' . esc_attr($category->name) . '">' . "\n    ";
        }
    }
    $tags = get_the_tags();
    if ($tags) {
        foreach ($tags as $tag) {
            echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n    ";
        }
    }
    ?>
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="<?php echo esc_attr($twitter_card); ?>">
    <meta name="twitter:url" content="<?php echo esc_url($og_url); ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($og_title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($og_description); ?>">
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>">
    <?php
    // Si tienes cuenta de Twitter, descomenta y añade tu @usuario
    // echo '<meta name="twitter:site" content="@tu_usuario">' . "\n    ";
    // echo '<meta name="twitter:creator" content="@tu_usuario">' . "\n    ";
    ?>
    
    <!-- URL Canónica -->
    <link rel="canonical" href="<?php echo esc_url($og_url); ?>">
    <?php
}
