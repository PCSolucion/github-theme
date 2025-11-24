<?php
/**
 * Template para comentarios
 *
 * @package GitHubTheme
 */

if (post_password_required()) {
    return;
}
?>

<div id="comments" class="comments-area">
    <?php if (have_comments()) : ?>
        <h2 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            if ($comment_count === '1') {
                printf(
                    esc_html__('Un comentario', 'github-theme')
                );
            } else {
                printf(
                    esc_html__('%1$s comentarios', 'github-theme'),
                    number_format_i18n($comment_count)
                );
            }
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments(array(
                'style' => 'ol',
                'short_ping' => true,
                'avatar_size' => 40,
                'callback' => 'github_theme_comment',
            ));
            ?>
        </ol>

        <?php
        the_comments_pagination(array(
            'prev_text' => '← ' . esc_html__('Anterior', 'github-theme'),
            'next_text' => esc_html__('Siguiente', 'github-theme') . ' →',
        ));
        ?>
    <?php endif; ?>

    <?php
    if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) :
    ?>
        <p class="no-comments">
            <?php esc_html_e('Los comentarios están cerrados.', 'github-theme'); ?>
        </p>
    <?php endif; ?>

    <?php
    comment_form(array(
        'title_reply' => esc_html__('Deja un comentario', 'github-theme'),
        'title_reply_to' => esc_html__('Responde a %s', 'github-theme'),
        'cancel_reply_link' => esc_html__('Cancelar respuesta', 'github-theme'),
        'label_submit' => esc_html__('Enviar comentario', 'github-theme'),
        'class_submit' => 'button',
        'comment_field' => '<p class="comment-form-comment"><label for="comment">' . esc_html__('Comentario', 'github-theme') . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
        'fields' => array(
            'author' => '<p class="comment-form-author"><label for="author">' . esc_html__('Nombre', 'github-theme') . ' <span class="required">*</span></label><input id="author" name="author" type="text" value="' . esc_attr($commenter['comment_author']) . '" size="30" required /></p>',
            'email' => '<p class="comment-form-email"><label for="email">' . esc_html__('Email', 'github-theme') . ' <span class="required">*</span></label><input id="email" name="email" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" required /></p>',
            'url' => '<p class="comment-form-url"><label for="url">' . esc_html__('Sitio web', 'github-theme') . '</label><input id="url" name="url" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" size="30" /></p>',
        ),
    ));
    ?>
</div>

<?php
/**
 * Callback personalizado para mostrar comentarios
 */
function github_theme_comment($comment, $args, $depth) {
    if ('div' === $args['style']) {
        $tag = 'div';
        $add_below = 'comment';
    } else {
        $tag = 'li';
        $add_below = 'div-comment';
    }
    ?>
    <<?php echo $tag; ?> <?php comment_class(empty($args['has_children']) ? '' : 'parent'); ?> id="comment-<?php comment_ID(); ?>">
        <?php if ('div' !== $args['style']) : ?>
            <div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
        <?php endif; ?>
        
        <div class="comment-author vcard">
            <?php
            if ($args['avatar_size'] != 0) {
                echo get_avatar($comment, $args['avatar_size'], '', '', array('class' => 'avatar'));
            }
            ?>
            <cite class="fn"><?php comment_author_link(); ?></cite>
        </div>
        
        <?php if ($comment->comment_approved == '0') : ?>
            <em class="comment-awaiting-moderation">
                <?php esc_html_e('Tu comentario está esperando moderación.', 'github-theme'); ?>
            </em>
        <?php endif; ?>
        
        <div class="comment-meta commentmetadata">
            <a href="<?php echo htmlspecialchars(get_comment_link($comment->comment_ID)); ?>">
                <?php
                printf(
                    esc_html__('%1$s a las %2$s', 'github-theme'),
                    get_comment_date(),
                    get_comment_time()
                );
                ?>
            </a>
            <?php edit_comment_link(esc_html__('(Editar)', 'github-theme'), '  ', ''); ?>
        </div>
        
        <div class="comment-content">
            <?php comment_text(); ?>
        </div>
        
        <div class="reply">
            <?php
            comment_reply_link(array_merge($args, array(
                'add_below' => $add_below,
                'depth' => $depth,
                'max_depth' => $args['max_depth'],
            )));
            ?>
        </div>
        
        <?php if ('div' !== $args['style']) : ?>
            </div>
        <?php endif; ?>
    <?php
}
?>




















