<?php
/**
 * Newsletter: guarda os e-mails no próprio WordPress (menu "Newsletter" no painel)
 * e permite exportar a lista em CSV para Mailchimp, Hostinger Reach, RD Station etc.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tipo de conteúdo privado para os inscritos.
 */
function pm_newsletter_register() {
	register_post_type(
		'pm_subscriber',
		array(
			'labels'              => array(
				'name'          => __( 'Newsletter', 'palestra-mantos' ),
				'singular_name' => __( 'Inscrito', 'palestra-mantos' ),
				'all_items'     => __( 'Inscritos', 'palestra-mantos' ),
				'search_items'  => __( 'Buscar inscritos', 'palestra-mantos' ),
				'not_found'     => __( 'Nenhum inscrito ainda.', 'palestra-mantos' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 58,
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'        => true,
			'exclude_from_search' => true,
			'show_in_rest'        => false,
		)
	);
}
add_action( 'init', 'pm_newsletter_register' );

/**
 * Recebe o formulário.
 */
function pm_newsletter_subscribe() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- formulário público (cache de página quebraria o nonce); protegido por honeypot e limite por IP.
	$back = isset( $_POST['pm_back'] ) ? esc_url_raw( wp_unslash( $_POST['pm_back'] ) ) : home_url( '/' );
	$back = wp_validate_redirect( $back, home_url( '/' ) );
	$go   = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'newsletter', $status, remove_query_arg( 'newsletter', $back ) ) . '#newsletter' );
		exit;
	};

	// Robôs preenchem o campo escondido.
	if ( ! empty( $_POST['pm_site'] ) ) {
		$go( 'ok' );
	}

	$email   = isset( $_POST['pm_email'] ) ? sanitize_email( wp_unslash( $_POST['pm_email'] ) ) : '';
	$name    = isset( $_POST['pm_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_name'] ) ) : '';
	$consent = ! empty( $_POST['pm_consent'] );
	// phpcs:enable

	if ( ! is_email( $email ) || ! $consent ) {
		$go( 'erro' );
	}

	// No máximo 5 envios por hora por IP.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'pm_nl_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 5 ) {
		$go( 'erro' );
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$email  = strtolower( $email );
	$exists = get_posts(
		array(
			'post_type'      => 'pm_subscriber',
			'post_status'    => 'any',
			'meta_key'       => '_pm_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'fields'         => 'ids',
			'posts_per_page' => 1,
		)
	);
	if ( $exists ) {
		$go( 'existe' );
	}

	$id = wp_insert_post(
		array(
			'post_type'   => 'pm_subscriber',
			'post_status' => 'private',
			'post_title'  => $email,
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		$go( 'erro' );
	}
	update_post_meta( $id, '_pm_email', $email );
	update_post_meta( $id, '_pm_name', $name );
	update_post_meta( $id, '_pm_consent', current_time( 'mysql' ) );

	/**
	 * Depois de um novo inscrito (para integrar com outra ferramenta de e-mail).
	 *
	 * @param string $email E-mail.
	 * @param string $name  Nome.
	 */
	do_action( 'pm_newsletter_subscribed', $email, $name );

	$go( 'ok' );
}
add_action( 'admin_post_nopriv_pm_newsletter', 'pm_newsletter_subscribe' );
add_action( 'admin_post_pm_newsletter', 'pm_newsletter_subscribe' );

/**
 * Colunas da lista no painel.
 *
 * @param array $cols Colunas.
 * @return array
 */
function pm_newsletter_columns( $cols ) {
	return array(
		'cb'       => $cols['cb'],
		'title'    => __( 'E-mail', 'palestra-mantos' ),
		'pm_name'  => __( 'Nome', 'palestra-mantos' ),
		'date'     => __( 'Inscrito em', 'palestra-mantos' ),
	);
}
add_filter( 'manage_pm_subscriber_posts_columns', 'pm_newsletter_columns' );

add_action(
	'manage_pm_subscriber_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'pm_name' === $col ) {
			echo esc_html( get_post_meta( $post_id, '_pm_name', true ) );
		}
	},
	10,
	2
);

/**
 * Botão "Exportar CSV" no topo da lista.
 */
function pm_newsletter_export_button() {
	$screen = get_current_screen();
	if ( ! $screen || 'edit-pm_subscriber' !== $screen->id ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=pm_newsletter_export' ), 'pm_newsletter_export' );
	printf(
		'<div class="notice notice-info"><p>%s <a class="button button-primary" href="%s">%s</a></p></div>',
		esc_html__( 'Baixe a lista para importar no Mailchimp, Hostinger Reach, RD Station ou outra ferramenta de e-mail.', 'palestra-mantos' ),
		esc_url( $url ),
		esc_html__( 'Exportar CSV', 'palestra-mantos' )
	);
}
add_action( 'admin_notices', 'pm_newsletter_export_button' );

/**
 * Exporta CSV.
 */
function pm_newsletter_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'palestra-mantos' ) );
	}
	check_admin_referer( 'pm_newsletter_export' );
	$ids = get_posts(
		array(
			'post_type'      => 'pm_subscriber',
			'post_status'    => 'any',
			'fields'         => 'ids',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
		)
	);
	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=newsletter-palestra-mantos-' . gmdate( 'Y-m-d' ) . '.csv' );
	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
	fwrite( $out, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- BOM para o Excel abrir acentos.
	fputcsv( $out, array( 'email', 'nome', 'inscrito_em' ) );
	foreach ( $ids as $id ) {
		fputcsv( $out, array( get_post_meta( $id, '_pm_email', true ), get_post_meta( $id, '_pm_name', true ), get_post_field( 'post_date', $id ) ) );
	}
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	exit;
}
add_action( 'admin_post_pm_newsletter_export', 'pm_newsletter_export' );
