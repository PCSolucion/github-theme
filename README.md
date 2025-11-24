# GitHub Theme para WordPress

Un tema de WordPress inspirado en el diseño limpio y moderno de GitHub. Caracterizado por un diseño oscuro, tipografía clara y experiencia de usuario minimalista.

## 🎨 Características

- **Diseño Oscuro**: Paleta de colores similar a GitHub con fondo oscuro (#0d1117)
- **Responsive**: Completamente adaptable a todos los dispositivos
- **Tipografía Clara**: Fuentes optimizadas para legibilidad
- **Iconos SVG**: Iconos integrados estilo GitHub
- **Soporte Completo**: Compatible con todas las características estándar de WordPress
- **SEO Friendly**: Optimizado para motores de búsqueda
- **Rápido**: Código limpio y optimizado para rendimiento

## 📋 Requisitos

- WordPress 5.0 o superior
- PHP 7.4 o superior

## 🚀 Instalación

### Instalación Manual

1. Descarga o clona el tema en la carpeta `wp-content/themes/` de tu instalación de WordPress
2. Renombra la carpeta a `github-theme` (si aún no lo está)
3. Ve a **Apariencia > Temas** en el panel de administración de WordPress
4. Activa el tema "GitHub Theme"

### Instalación via ZIP

1. Comprime la carpeta del tema en un archivo ZIP
2. Ve a **Apariencia > Temas > Añadir nuevo > Subir tema**
3. Selecciona el archivo ZIP y haz clic en **Instalar ahora**
4. Activa el tema

## ⚙️ Configuración

### Menús

El tema soporta dos ubicaciones de menú:

1. **Menú Principal**: Aparece en el header del sitio
2. **Menú Footer**: Aparece en el footer del sitio

Para configurar los menús:
1. Ve a **Apariencia > Menús**
2. Crea un nuevo menú o edita uno existente
3. Asigna el menú a las ubicaciones "Menú Principal" y/o "Menú Footer"

### Logo Personalizado

1. Ve a **Apariencia > Personalizar > Identidad del sitio**
2. Haz clic en "Seleccionar logo"
3. Sube tu logo personalizado
4. El logo se mostrará en el header del sitio

### Widgets

El tema incluye dos áreas de widgets:

1. **Sidebar Principal**: Aparece en las páginas de blog, entradas y archivos
2. **Footer Widgets**: Aparece en el footer del sitio

Para configurar los widgets:
1. Ve a **Apariencia > Widgets**
2. Arrastra los widgets que desees a las áreas correspondientes

## 📁 Estructura de Archivos

```
github-theme/
├── assets/
│   ├── css/
│   │   └── main.css          # Estilos adicionales
│   └── js/
│       └── main.js            # JavaScript principal
├── style.css                  # Estilos principales y headers del tema
├── functions.php              # Funciones del tema
├── index.php                  # Template principal del blog
├── header.php                 # Header del sitio
├── footer.php                 # Footer del sitio
├── sidebar.php                # Sidebar
├── single.php                 # Template para entradas individuales
├── page.php                   # Template para páginas estáticas
├── archive.php                # Template para archivos (categorías, etiquetas, etc.)
├── search.php                 # Template para resultados de búsqueda
├── 404.php                    # Template para página no encontrada
├── comments.php               # Template para comentarios
├── searchform.php             # Formulario de búsqueda personalizado
└── README.md                  # Este archivo
```

## 🎨 Personalización

### Colores

El tema usa variables CSS para facilitar la personalización. Puedes modificar los colores en `style.css`:

```css
:root {
    --github-bg-primary: #0d1117;
    --github-bg-secondary: #161b22;
    --github-bg-tertiary: #1c2128;
    --github-border: #30363d;
    --github-text-primary: #c9d1d9;
    --github-text-secondary: #8b949e;
    --github-accent: #58a6ff;
    --github-success: #238636;
}
```

### Child Theme

Se recomienda crear un Child Theme para realizar personalizaciones sin perder los cambios al actualizar:

1. Crea una nueva carpeta `github-theme-child` en `wp-content/themes/`
2. Crea un archivo `style.css` con:

```css
/*
Theme Name: GitHub Theme Child
Template: github-theme
Version: 1.0.0
*/

@import url("../github-theme/style.css");

/* Tus estilos personalizados aquí */
```

3. Crea un archivo `functions.php` para agregar funcionalidades personalizadas

## 📝 Características del Tema

- ✅ Soporte para imágenes destacadas
- ✅ Soporte para HTML5
- ✅ Menús de navegación
- ✅ Widgets (Sidebar y Footer)
- ✅ Logo personalizado
- ✅ Formularios de búsqueda personalizados
- ✅ Sistema de comentarios estilizado
- ✅ Paginación
- ✅ Navegación entre posts
- ✅ Tags y categorías
- ✅ Responsive design
- ✅ Soporte para Gutenberg (editor de bloques)

## 🐛 Solución de Problemas

### El tema no aparece en la lista de temas

- Verifica que la carpeta esté en `wp-content/themes/github-theme/`
- Asegúrate de que el archivo `style.css` tenga los headers correctos
- Verifica los permisos de archivos

### Los estilos no se cargan correctamente

- Limpia la caché del navegador
- Verifica que los archivos CSS estén en las ubicaciones correctas
- Desactiva plugins de caché temporalmente

### Los menús no aparecen

- Ve a **Apariencia > Menús** y asigna los menús a las ubicaciones correspondientes
- Asegúrate de tener al menos un menú creado

## 📄 Licencia

Este tema está bajo la licencia GPL v2 o posterior.

## 👨‍💻 Soporte

Para reportar bugs o sugerencias, por favor abre un issue en el repositorio del tema.

## 🔄 Changelog

### Versión 1.0.0
- Lanzamiento inicial
- Diseño completo estilo GitHub
- Todos los templates básicos
- Soporte completo para WordPress

---

**Desarrollado con ❤️ inspirado en GitHub**




















