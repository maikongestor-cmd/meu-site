<?php
/**
 * Pesquisa de saída no checkout: "Por que você desistiu da compra?".
 * Respostas anônimas, vistas em WooCommerce → Desistências.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Motivos (chave => texto). Filtro 'pm_exit_reasons' para mudar.
 *
 * @return array
 */
function pm_exit_reasons() {
	return apply_filters(
		'pm_exit_reasons',
		array(
			'frete'     => __( 'O frete ficou caro', 'palestra-mantos' ),
			'prazo'     => __( 'O prazo de entrega é longo', 'palestra-mantos' ),
			'pagamento' => __( 'Não encontrei a forma de pagamento que queria', 'palestra-mantos' ),
			'tamanho'   => __( 'Fiquei em dúvida sobre o tamanho', 'palestra-mantos' ),
			'pesquisa'  => __( 'Só estava pesquisando preços', 'palestra-mantos' ),
			'confianca' => __( 'Não me senti seguro para comprar', 'palestra-mantos' ),
			'outro'     => __( 'Outro motivo', 'palestra-mantos' ),
		)
	);
}

/**
 * Tipo privado para guardar as respostas.
 */
function pm_exit_register() {
	register_post_type(
		'pm_exit',
		array(
			'labels'       => array( 'name' => __( 'Desistências', 'palestra-mantos' ) ),
			'public'       => false,
			'show_ui'      => false,
			'supports'     => array( 'title' ),
			'show_in_rest' => false,
		)
	);
}
add_action( 'init', 'pm_exit_register' );

/**
 * Janela da pesquisa (só no checkout, com carrinho cheio).
 */
function pm_exit_modal() {
	if ( ! is_checkout() || is_order_received_page() || ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}
	?>
	<div class="pm-exit" id="pm-exit" role="dialog" aria-modal="true" aria-labelledby="pm-exit-title" hidden>
		<div class="pm-exit__box">
			<button type="button" class="pm-exit__close" aria-label="<?php esc_attr_e( 'Fechar', 'palestra-mantos' ); ?>"><?php pm_the_icon( 'close' ); ?></button>
			<div class="pm-exit__step" data-step="ask">
				<h2 id="pm-exit-title"><?php esc_html_e( 'Antes de ir… o que te fez não finalizar a compra?', 'palestra-mantos' ); ?></h2>
				<p><?php esc_html_e( 'Sua resposta é anônima e nos ajuda a melhorar. Leva 1 segundo.', 'palestra-mantos' ); ?></p>
				<div class="pm-exit__options">
					<?php foreach ( pm_exit_reasons() as $key => $label ) : ?>
						<button type="button" class="pm-exit__opt" data-reason="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
				<div class="pm-exit__other" hidden>
					<label for="pm-exit-text"><?php esc_html_e( 'Conta pra gente:', 'palestra-mantos' ); ?></label>
					<textarea id="pm-exit-text" maxlength="300" rows="3"></textarea>
					<button type="button" class="pm-btn pm-exit__send"><?php esc_html_e( 'Enviar', 'palestra-mantos' ); ?></button>
				</div>
			</div>
			<div class="pm-exit__step" data-step="thanks" hidden>
				<h2><?php esc_html_e( 'Obrigado pela resposta! 💚', 'palestra-mantos' ); ?></h2>
				<p><?php esc_html_e( 'Se ficou alguma dúvida, a gente resolve agora pelo WhatsApp.', 'palestra-mantos' ); ?></p>
				<div class="pm-exit__cta">
					<a class="pm-btn pm-btn--whats" href="<?php echo esc_url( pm_whatsapp_url( __( 'Olá! Estava finalizando uma compra no site e fiquei com uma dúvida.', 'palestra-mantos' ) ) ); ?>" target="_blank" rel="noopener"><?php pm_the_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Falar no WhatsApp', 'palestra-mantos' ); ?></a>
					<button type="button" class="pm-btn pm-btn--outline pm-exit__back"><?php esc_html_e( 'Continuar minha compra', 'palestra-mantos' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'pm_exit_modal', 6 );

/**
 * Dados para o JavaScript.
 */
function pm_exit_script_data() {
	if ( ! is_checkout() || is_order_received_page() ) {
		return;
	}
	wp_localize_script( 'pm-theme', 'pmExit', array( 'url' => WC_AJAX::get_endpoint( 'pm_exit_reason' ) ) );
}
add_action( 'wp_enqueue_scripts', 'pm_exit_script_data', 23 );

/**
 * Recebe a resposta.
 */
function pm_exit_save() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- resposta anônima; limite por IP.
	$reasons = pm_exit_reasons();
	$reason  = isset( $_POST['reason'] ) ? sanitize_key( wp_unslash( $_POST['reason'] ) ) : '';
	$text    = isset( $_POST['text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['text'] ) ) : '';
	// phpcs:enable
	if ( ! isset( $reasons[ $reason ] ) ) {
		wp_send_json_error();
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'pm_ex_' . md5( $ip );
	if ( (int) get_transient( $key ) >= 3 ) {
		wp_send_json_success();
	}
	set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );

	$id = wp_insert_post(
		array(
			'post_type'    => 'pm_exit',
			'post_status'  => 'private',
			'post_title'   => $reasons[ $reason ],
			'post_content' => mb_substr( $text, 0, 300 ),
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		update_post_meta( $id, '_pm_reason', $reason );
		update_post_meta( $id, '_pm_cart_total', WC()->cart ? (float) WC()->cart->get_subtotal() : 0 );
	}
	wp_send_json_success();
}
add_action( 'wc_ajax_pm_exit_reason', 'pm_exit_save' );

/**
 * Relatório em WooCommerce → Desistências.
 */
function pm_exit_admin_menu() {
	add_submenu_page( 'woocommerce', __( 'Desistências no checkout', 'palestra-mantos' ), __( 'Desistências', 'palestra-mantos' ), 'manage_woocommerce', 'pm-exit-survey', 'pm_exit_admin_page' );
}
add_action( 'admin_menu', 'pm_exit_admin_menu', 60 );

/**
 * Página do relatório.
 */
function pm_exit_admin_page() {
	$ids = get_posts(
		array(
			'post_type'      => 'pm_exit',
			'post_status'    => 'private',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);
	$reasons = pm_exit_reasons();
	$count   = array_fill_keys( array_keys( $reasons ), 0 );
	foreach ( $ids as $id ) {
		$r = get_post_meta( $id, '_pm_reason', true );
		if ( isset( $count[ $r ] ) ) {
			$count[ $r ]++;
		}
	}
	arsort( $count );
	$total = max( 1, count( $ids ) );
	$texts = get_posts(
		array(
			'post_type'      => 'pm_exit',
			'post_status'    => 'private',
			'posts_per_page' => 30,
			'meta_key'       => '_pm_reason', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'outro', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Por que os clientes desistiram no checkout', 'palestra-mantos' ); ?></h1>
		<p><?php echo esc_html( sprintf( _n( '%d resposta no total.', '%d respostas no total.', count( $ids ), 'palestra-mantos' ), count( $ids ) ) ); ?></p>
		<table class="widefat striped" style="max-width:760px">
			<thead><tr><th><?php esc_html_e( 'Motivo', 'palestra-mantos' ); ?></th><th style="width:90px"><?php esc_html_e( 'Respostas', 'palestra-mantos' ); ?></th><th style="width:260px">%</th></tr></thead>
			<tbody>
			<?php foreach ( $count as $key => $n ) : $pct = round( $n / $total * 100 ); ?>
				<tr>
					<td><?php echo esc_html( $reasons[ $key ] ); ?></td>
					<td><strong><?php echo (int) $n; ?></strong></td>
					<td><div style="background:#e4f3ea;border-radius:4px;height:14px;width:100%"><div style="background:#127a45;height:14px;border-radius:4px;width:<?php echo (int) $pct; ?>%"></div></div> <?php echo (int) $pct; ?>%</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<?php if ( $texts ) : ?>
			<h2 style="margin-top:28px"><?php esc_html_e( '"Outro motivo" — o que escreveram', 'palestra-mantos' ); ?></h2>
			<ul style="max-width:760px;list-style:disc;padding-left:20px">
				<?php foreach ( $texts as $t ) : ?>
					<?php if ( $t->post_content ) : ?>
						<li><?php echo esc_html( $t->post_content ); ?> <em style="color:#777">— <?php echo esc_html( get_the_date( 'd/m/Y H:i', $t ) ); ?></em></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
	<?php
}
