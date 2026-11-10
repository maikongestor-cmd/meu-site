<?php
/**
 * Formulário de contato (página "Contato"): salva em Mensagens e avisa por e-mail.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipo de post para guardar as mensagens recebidas.
 */
function pm_contact_register() {
	register_post_type(
		'pm_message',
		array(
			'labels'          => array(
				'name'          => __( 'Mensagens', 'palestra-mantos' ),
				'singular_name' => __( 'Mensagem', 'palestra-mantos' ),
				'menu_name'     => __( 'Mensagens', 'palestra-mantos' ),
				'edit_item'     => __( 'Mensagem', 'palestra-mantos' ),
				'not_found'     => __( 'Nenhuma mensagem ainda.', 'palestra-mantos' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 26,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'pm_contact_register' );

/**
 * Colunas da lista de mensagens.
 *
 * @param array $cols Colunas.
 * @return array
 */
function pm_contact_columns( $cols ) {
	return array(
		'cb'       => $cols['cb'],
		'title'    => __( 'Nome', 'palestra-mantos' ),
		'pm_email' => __( 'E-mail', 'palestra-mantos' ),
		'pm_phone' => __( 'Telefone', 'palestra-mantos' ),
		'pm_msg'   => __( 'Mensagem', 'palestra-mantos' ),
		'date'     => __( 'Data', 'palestra-mantos' ),
	);
}
add_filter( 'manage_pm_message_posts_columns', 'pm_contact_columns' );

/**
 * Conteúdo das colunas.
 *
 * @param string $col     Coluna.
 * @param int    $post_id Post.
 */
function pm_contact_column( $col, $post_id ) {
	if ( 'pm_email' === $col ) {
		$email = get_post_meta( $post_id, '_pm_email', true );
		printf( '<a href="mailto:%1$s">%2$s</a>', esc_attr( $email ), esc_html( $email ) );
	} elseif ( 'pm_phone' === $col ) {
		$phone = get_post_meta( $post_id, '_pm_phone', true );
		$digits = preg_replace( '/\D+/', '', $phone );
		if ( $digits ) {
			printf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( 'https://wa.me/55' . ltrim( $digits, '0' ) ), esc_html( $phone ) );
		}
	} elseif ( 'pm_msg' === $col ) {
		echo esc_html( wp_trim_words( get_post_field( 'post_content', $post_id ), 25 ) );
	}
}
add_action( 'manage_pm_message_posts_custom_column', 'pm_contact_column', 10, 2 );

/**
 * HTML do formulário.
 *
 * @return string
 */
function pm_contact_form() {
	$status = isset( $_GET['contato'] ) ? sanitize_key( wp_unslash( $_GET['contato'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$time   = time();
	ob_start();
	?>
	<div class="pm-contact" id="formulario">
		<?php if ( 'ok' === $status ) : ?>
			<p class="pm-contact__msg pm-contact__msg--ok" role="status"><?php esc_html_e( 'Mensagem enviada! Vamos responder o mais rápido possível.', 'palestra-mantos' ); ?></p>
		<?php elseif ( 'erro' === $status ) : ?>
			<p class="pm-contact__msg pm-contact__msg--error" role="alert"><?php esc_html_e( 'Não foi possível enviar. Confira o nome, o e-mail e a mensagem e tente de novo.', 'palestra-mantos' ); ?></p>
		<?php elseif ( 'limite' === $status ) : ?>
			<p class="pm-contact__msg pm-contact__msg--error" role="alert"><?php esc_html_e( 'Recebemos várias mensagens seguidas. Tente de novo mais tarde ou fale pelo WhatsApp.', 'palestra-mantos' ); ?></p>
		<?php endif; ?>
		<form class="pm-contact__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="pm_contact">
			<input type="hidden" name="pm_t" value="<?php echo esc_attr( $time ); ?>">
			<input type="hidden" name="pm_s" value="<?php echo esc_attr( wp_hash( 'pm_contact_' . $time ) ); ?>">
			<p class="pm-contact__hp" aria-hidden="true"><label>Site <input type="text" name="pm_site" tabindex="-1" autocomplete="off"></label></p>
			<p>
				<label for="pm-c-name"><?php esc_html_e( 'Nome completo', 'palestra-mantos' ); ?></label>
				<input id="pm-c-name" name="pm_name" type="text" autocomplete="name" required maxlength="120">
			</p>
			<p>
				<label for="pm-c-email"><?php esc_html_e( 'E-mail', 'palestra-mantos' ); ?></label>
				<input id="pm-c-email" name="pm_email" type="email" autocomplete="email" required maxlength="120">
			</p>
			<p>
				<label for="pm-c-phone"><?php esc_html_e( 'Telefone', 'palestra-mantos' ); ?></label>
				<input id="pm-c-phone" name="pm_phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="20" placeholder="(11) 90000-0000">
			</p>
			<p>
				<label for="pm-c-msg"><?php esc_html_e( 'Mensagem', 'palestra-mantos' ); ?></label>
				<textarea id="pm-c-msg" name="pm_message" rows="6" required maxlength="3000"></textarea>
			</p>
			<button type="submit" class="pm-btn"><?php esc_html_e( 'Enviar', 'palestra-mantos' ); ?></button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'pm_contato', 'pm_contact_form' );

/**
 * Coloca o formulário no fim da página "Contato".
 *
 * @param string $content Conteúdo.
 * @return string
 */
function pm_contact_append( $content ) {
	if ( is_page( 'contato' ) && in_the_loop() && is_main_query() && false === strpos( $content, 'pm-contact__form' ) ) {
		$content .= pm_contact_form();
	}
	return $content;
}
add_filter( 'the_content', 'pm_contact_append', 20 );

/**
 * Recebe o formulário.
 */
function pm_contact_submit() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- formulário público com cache de página; proteção por assinatura de tempo, campo-isca e limite por IP.
	$back = wp_get_referer();
	$page = get_page_by_path( 'contato' );
	$back = $page ? get_permalink( $page ) : ( $back ? $back : home_url( '/' ) );
	$go   = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'contato', $status, $back ) . '#formulario' );
		exit;
	};

	$time = isset( $_POST['pm_t'] ) ? absint( $_POST['pm_t'] ) : 0;
	$sig  = isset( $_POST['pm_s'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_s'] ) ) : '';
	$trap = isset( $_POST['pm_site'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_site'] ) ) : '';

	// Robôs: preenchem o campo escondido, enviam rápido demais ou sem a assinatura.
	if ( '' !== $trap || ! hash_equals( wp_hash( 'pm_contact_' . $time ), $sig ) || time() - $time < 3 || time() - $time > DAY_IN_SECONDS ) {
		$go( 'ok' );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'pm_contact_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 5 ) {
		$go( 'limite' );
	}

	$name    = isset( $_POST['pm_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_name'] ) ) : '';
	$email   = isset( $_POST['pm_email'] ) ? sanitize_email( wp_unslash( $_POST['pm_email'] ) ) : '';
	$phone   = isset( $_POST['pm_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_phone'] ) ) : '';
	$message = isset( $_POST['pm_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pm_message'] ) ) : '';
	// phpcs:enable

	if ( '' === $name || ! is_email( $email ) || '' === trim( $message ) ) {
		$go( 'erro' );
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$name    = mb_substr( $name, 0, 120 );
	$phone   = mb_substr( $phone, 0, 20 );
	$message = mb_substr( $message, 0, 3000 );

	$id = wp_insert_post(
		array(
			'post_type'    => 'pm_message',
			'post_status'  => 'private',
			'post_title'   => $name,
			'post_content' => $message,
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_pm_email', $email );
		update_post_meta( $id, '_pm_phone', $phone );
	}

	$body = sprintf(
		"Nova mensagem pelo site:\n\nNome: %s\nE-mail: %s\nTelefone: %s\n\n%s\n\nVer todas: %s",
		$name,
		$email,
		$phone ? $phone : '-',
		$message,
		admin_url( 'edit.php?post_type=pm_message' )
	);
	wp_mail(
		get_option( 'admin_email' ),
		/* translators: %s: nome. */
		sprintf( __( '[Palestra Mantos] Mensagem de %s', 'palestra-mantos' ), $name ),
		$body,
		array( 'Reply-To: ' . $name . ' <' . $email . '>' )
	);

	$go( 'ok' );
}
add_action( 'admin_post_pm_contact', 'pm_contact_submit' );
add_action( 'admin_post_nopriv_pm_contact', 'pm_contact_submit' );
