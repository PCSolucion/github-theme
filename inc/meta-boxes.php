<?php
/**
 * Custom Meta Boxes para el panel de administración
 *
 * @package GitHubTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =============================================================================
// META BOX: TIPO DE MISIÓN (GUÍAS DE VIDEOJUEGOS)
// =============================================================================

/**
 * Agregar Meta Box para el tipo de misión (Principal/Secundaria)
 */
function github_theme_add_mission_type_meta_box() {
    add_meta_box(
        'github_mission_type',
        'Detalles de la Guía',
        'github_theme_mission_type_meta_box_callback',
        'post',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'github_theme_add_mission_type_meta_box' );

/**
 * Renderizar la interfaz del Meta Box del tipo de misión.
 *
 * @param WP_Post $post El objeto post actual.
 */
function github_theme_mission_type_meta_box_callback( $post ) {
    wp_nonce_field( 'github_theme_save_mission_type', 'github_theme_mission_type_nonce' );
    $value = get_post_meta( $post->ID, '_github_mission_type', true );
    if ( empty( $value ) ) {
        $value = 'principal'; // Por defecto
    }
    ?>
    <div class="github-meta-field" style="margin-bottom: 10px;">
        <label for="github_mission_type_select" style="display:block; margin-bottom: 5px; font-weight: 600;">Tipo de Misión:</label>
        <select name="github_mission_type_select" id="github_mission_type_select" style="width:100%;">
            <option value="principal" <?php selected( $value, 'principal' ); ?>>Historia Principal</option>
            <option value="secundaria" <?php selected( $value, 'secundaria' ); ?>>Misión Secundaria</option>
        </select>
        <p class="description" style="margin-top: 5px; font-size: 11px;">Define si este post pertenece a la trama principal o es secundaria.</p>
    </div>
    <?php
}

/**
 * Guardar el valor del Meta Box del tipo de misión.
 *
 * @param int $post_id ID del post guardado.
 */
function github_theme_save_mission_type( $post_id ) {
    if ( ! isset( $_POST['github_theme_mission_type_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['github_theme_mission_type_nonce'], 'github_theme_save_mission_type' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    if ( isset( $_POST['github_mission_type_select'] ) ) {
        update_post_meta( $post_id, '_github_mission_type', sanitize_text_field( $_POST['github_mission_type_select'] ) );
    }
}
add_action( 'save_post', 'github_theme_save_mission_type' );
