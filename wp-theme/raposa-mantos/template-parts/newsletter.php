<?php
/**
 * Seção de newsletter.
 *
 * @package RaposaMantos
 */

defined( 'ABSPATH' ) || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- só exibe a mensagem.
$pm_nl_status = isset( $_GET['newsletter'] ) ? sanitize_key( wp_unslash( $_GET['newsletter'] ) ) : '';
$pm_nl_msgs   = array(
	'ok'     => array( 'ok', __( 'Inscrição feita! Em breve você recebe as novidades da Raposa Mantos. 💙', 'palestra-mantos' ) ),
	'existe' => array( 'ok', __( 'Esse e-mail já está na nossa lista. Obrigado!', 'palestra-mantos' ) ),
	'erro'   => array( 'erro', __( 'Não foi possível inscrever. Confira o e-mail e marque a autorização.', 'palestra-mantos' ) ),
);
$pm_privacy = get_privacy_policy_url();
?>
<section class="pm-newsletter" id="newsletter">
	<div class="pm-container pm-newsletter__inner">
		<div class="pm-newsletter__text">
			<span class="pm-eyebrow"><?php esc_html_e( 'Newsletter', 'palestra-mantos' ); ?></span>
			<h2><?php echo esc_html( pm_opt( 'news_title' ) ); ?></h2>
			<p><?php echo esc_html( pm_opt( 'news_text' ) ); ?></p>
		</div>
		<form class="pm-newsletter__form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="pm_newsletter">
			<input type="hidden" name="pm_back" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>">
			<div class="pm-newsletter__hp" aria-hidden="true"><label>Site <input type="text" name="pm_site" tabindex="-1" autocomplete="off"></label></div>
			<div class="pm-newsletter__row">
				<label class="screen-reader-text" for="pm-nl-name"><?php esc_html_e( 'Seu nome', 'palestra-mantos' ); ?></label>
				<input id="pm-nl-name" type="text" name="pm_name" placeholder="<?php esc_attr_e( 'Seu nome', 'palestra-mantos' ); ?>" autocomplete="given-name">
				<label class="screen-reader-text" for="pm-nl-email"><?php esc_html_e( 'Seu e-mail', 'palestra-mantos' ); ?></label>
				<input id="pm-nl-email" type="email" name="pm_email" placeholder="<?php esc_attr_e( 'Seu melhor e-mail', 'palestra-mantos' ); ?>" autocomplete="email" required>
				<button type="submit" class="pm-btn pm-btn--light"><?php esc_html_e( 'Quero receber', 'palestra-mantos' ); ?></button>
			</div>
			<label class="pm-newsletter__consent">
				<input type="checkbox" name="pm_consent" value="1" required>
				<span>
					<?php esc_html_e( 'Aceito receber e-mails da Raposa Mantos e posso cancelar quando quiser.', 'palestra-mantos' ); ?>
					<?php if ( $pm_privacy ) : ?>
						<a href="<?php echo esc_url( $pm_privacy ); ?>"><?php esc_html_e( 'Política de Privacidade', 'palestra-mantos' ); ?></a>
					<?php endif; ?>
				</span>
			</label>
			<?php if ( isset( $pm_nl_msgs[ $pm_nl_status ] ) ) : ?>
				<p class="pm-newsletter__msg is-<?php echo esc_attr( $pm_nl_msgs[ $pm_nl_status ][0] ); ?>" role="status"><?php echo esc_html( $pm_nl_msgs[ $pm_nl_status ][1] ); ?></p>
			<?php endif; ?>
		</form>
	</div>
</section>
