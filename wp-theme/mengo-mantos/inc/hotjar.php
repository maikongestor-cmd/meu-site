<?php
/**
 * Hotjar (mapas de calor e gravações de sessão).
 *
 * Configuração: Configurações → Hotjar. Aceita o "Site ID" (número) do Hotjar
 * ou o link da tag Contentsquare (contas novas do Hotjar usam essa tag).
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valor salvo (ID numérico ou URL da tag).
 *
 * @return string
 */
function pm_hotjar_value() {
	return trim( (string) get_option( 'pm_hotjar_id', '' ) );
}

/**
 * Limpa o que o usuário colou: aceita o número, o código inteiro do Hotjar ou a URL da tag Contentsquare.
 *
 * @param string $raw Texto colado.
 * @return string
 */
function pm_hotjar_sanitize( $raw ) {
	$raw = trim( wp_unslash( (string) $raw ) );
	if ( preg_match( '#https://t\.contentsquare\.net/uxa/[a-z0-9]+\.js#i', $raw, $m ) ) {
		return $m[0];
	}
	if ( preg_match( '/hjid\s*:\s*(\d+)/', $raw, $m ) ) {
		return $m[1];
	}
	if ( preg_match( '/^\d{5,10}$/', $raw ) ) {
		return $raw;
	}
	return '';
}

/**
 * Script no cabeçalho (não roda para administradores logados nem no painel).
 */
function pm_hotjar_head() {
	$value = pm_hotjar_value();
	if ( '' === $value || is_admin() || current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( 0 === strpos( $value, 'https://' ) ) {
		printf( "<!-- Hotjar / Contentsquare -->\n<script src=\"%s\" async></script>\n", esc_url( $value ) );
		return;
	}
	?>
<!-- Hotjar -->
<script>
(function(h,o,t,j,a,r){h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};h._hjSettings={hjid:<?php echo (int) $value; ?>,hjsv:6};a=o.getElementsByTagName('head')[0];r=o.createElement('script');r.async=1;r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;a.appendChild(r);})(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
	<?php
}
add_action( 'wp_head', 'pm_hotjar_head', 4 );

/**
 * Esconde nas gravações os dados pessoais do checkout e da conta (LGPD).
 *
 * @param array $field Campo do checkout.
 * @return array
 */
function pm_hotjar_suppress_field( $field ) {
	$field['custom_attributes']                    = isset( $field['custom_attributes'] ) ? $field['custom_attributes'] : array();
	$field['custom_attributes']['data-hj-suppress'] = '';
	return $field;
}
add_filter( 'woocommerce_form_field_args', 'pm_hotjar_suppress_field' );

/* -------------------------------------------------------------------------
 * Painel: Configurações → Hotjar
 * ---------------------------------------------------------------------- */

/**
 * Menu.
 */
function pm_hotjar_menu() {
	add_options_page( 'Hotjar', 'Hotjar', 'manage_options', 'pm-hotjar', 'pm_hotjar_page' );
}
add_action( 'admin_menu', 'pm_hotjar_menu' );

/**
 * Salvar.
 */
function pm_hotjar_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'palestra-mantos' ) );
	}
	check_admin_referer( 'pm_hotjar_save' );
	$raw   = isset( $_POST['pm_hotjar_id'] ) ? wp_unslash( $_POST['pm_hotjar_id'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitizado em pm_hotjar_sanitize().
	$clean = pm_hotjar_sanitize( $raw );
	update_option( 'pm_hotjar_id', $clean, true );
	$msg = ( '' !== trim( (string) $raw ) && '' === $clean ) ? 'invalid' : 'saved';
	wp_safe_redirect( add_query_arg( 'pm_hj', $msg, admin_url( 'options-general.php?page=pm-hotjar' ) ) );
	exit;
}
add_action( 'admin_post_pm_hotjar_save', 'pm_hotjar_save' );

/**
 * Tela.
 */
function pm_hotjar_page() {
	$msg = isset( $_GET['pm_hj'] ) ? sanitize_key( wp_unslash( $_GET['pm_hj'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1>Hotjar</h1>
		<?php if ( 'saved' === $msg ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( pm_hotjar_value() ? __( 'Salvo! O Hotjar já está ativo no site (abra o site numa aba anônima para ele registrar a visita).', 'palestra-mantos' ) : __( 'Salvo. O Hotjar está desligado.', 'palestra-mantos' ) ); ?></p></div>
		<?php elseif ( 'invalid' === $msg ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'Não reconheci esse código. Cole só o número do "Site ID" do Hotjar (ex.: 1234567) ou o código de rastreamento inteiro.', 'palestra-mantos' ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Mapas de calor e gravações das visitas. Administradores logados não são gravados. Os campos de nome, CPF, endereço, telefone e cartão ficam ocultos nas gravações.', 'palestra-mantos' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="pm_hotjar_save">
			<?php wp_nonce_field( 'pm_hotjar_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="pm_hotjar_id"><?php esc_html_e( 'Site ID do Hotjar', 'palestra-mantos' ); ?></label></th>
					<td>
						<input name="pm_hotjar_id" id="pm_hotjar_id" type="text" class="regular-text" value="<?php echo esc_attr( pm_hotjar_value() ); ?>" placeholder="1234567">
						<p class="description"><?php esc_html_e( 'No Hotjar: Configurações (engrenagem) → Sites e organizações → "Site ID". Também pode colar o código de rastreamento inteiro. Deixe vazio para desligar.', 'palestra-mantos' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Salvar', 'palestra-mantos' ) ); ?>
		</form>
	</div>
	<?php
}
