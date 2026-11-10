<?php
/**
 * Rodapé.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="pm-footer">
	<div class="pm-container pm-footer__grid">
		<div>
			<?php pm_logo(); ?>
			<p><?php esc_html_e( 'Moletons, calças e camisetas para quem carrega o Palestra no peito. Qualidade, conforto e paixão em cada peça.', 'palestra-mantos' ); ?></p>
			<div class="pm-social">
				<?php
				foreach ( array( 'instagram', 'facebook', 'tiktok' ) as $pm_net ) {
					if ( pm_opt( $pm_net ) ) {
						printf(
							'<a href="%s" target="_blank" rel="noopener" aria-label="%s">%s</a>',
							esc_url( pm_opt( $pm_net ) ),
							esc_attr( ucfirst( $pm_net ) ),
							pm_icon( $pm_net ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						);
					}
				}
				?>
				<a href="<?php echo esc_url( pm_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php pm_the_icon( 'whatsapp' ); ?></a>
			</div>
		</div>

		<div>
			<h4><?php esc_html_e( 'Departamentos', 'palestra-mantos' ); ?></h4>
			<ul>
				<?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Todos os produtos', 'palestra-mantos' ); ?></a></li>
				<?php endif; ?>
				<?php foreach ( pm_category_slugs() as $pm_slug => $pm_label ) : ?>
					<li><a href="<?php echo esc_url( pm_cat_url( $pm_slug ) ); ?>"><?php echo esc_html( $pm_label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div>
			<h4><?php esc_html_e( 'Institucional', 'palestra-mantos' ); ?></h4>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
					)
				);
			} else {
				echo '<ul>';
				foreach ( array( 'quem-somos', 'trocas-e-devolucoes', 'guia-de-medidas', 'politica-de-privacidade', 'contato' ) as $pm_page_slug ) {
					$pm_page = get_page_by_path( $pm_page_slug );
					if ( $pm_page ) {
						printf( '<li><a href="%s">%s</a></li>', esc_url( get_permalink( $pm_page ) ), esc_html( get_the_title( $pm_page ) ) );
					}
				}
				echo '</ul>';
			}
			?>
		</div>

		<div>
			<h4><?php esc_html_e( 'Atendimento', 'palestra-mantos' ); ?></h4>
			<ul>
				<li class="pm-contact-item"><?php pm_the_icon( 'whatsapp' ); ?><a href="<?php echo esc_url( pm_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php echo esc_html( pm_opt( 'phone' ) ); ?></a></li>
				<?php if ( pm_opt( 'email' ) ) : ?>
					<li class="pm-contact-item"><?php pm_the_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( antispambot( pm_opt( 'email' ) ) ); ?>"><?php echo esc_html( antispambot( pm_opt( 'email' ) ) ); ?></a></li>
				<?php endif; ?>
				<?php if ( pm_opt( 'address' ) ) : ?>
					<li class="pm-contact-item"><?php pm_the_icon( 'pin' ); ?><span><?php echo esc_html( pm_opt( 'address' ) ); ?></span></li>
				<?php endif; ?>
				<li class="pm-contact-item"><?php pm_the_icon( 'phone' ); ?><span><?php esc_html_e( 'Seg. a sex. das 9h às 18h', 'palestra-mantos' ); ?></span></li>
			</ul>
		</div>
	</div>

	<div class="pm-footer__pay">
		<div class="pm-container pm-footer__pay-inner">
			<div>
				<h4 style="margin-bottom:10px"><?php esc_html_e( 'Formas de pagamento', 'palestra-mantos' ); ?></h4>
				<div class="pm-paylist" aria-label="<?php esc_attr_e( 'Formas de pagamento aceitas', 'palestra-mantos' ); ?>">
					<?php
					$pm_pay = array(
						'visa.svg'       => 'Visa',
						'mastercard.svg' => 'Mastercard',
						'amex.svg'       => 'American Express',
						'diners.svg'     => 'Diners Club',
						'aura.png'       => 'Aura',
						'elo.svg'        => 'Elo',
						'discover.svg'   => 'Discover',
						'pix.png'        => 'Pix',
					);
					foreach ( $pm_pay as $pm_file => $pm_label ) {
						printf( '<img src="%s" alt="%s" title="%s" width="52" height="33" loading="lazy">', esc_url( pm_img( 'pay/' . $pm_file ) ), esc_attr( $pm_label ), esc_attr( $pm_label ) );
					}
					?>
				</div>
			</div>
			<span class="pm-secure"><?php pm_the_icon( 'shield' ); ?> <?php esc_html_e( 'Compra 100% segura', 'palestra-mantos' ); ?></span>
		</div>
	</div>

	<div class="pm-footer__bottom">
		<div class="pm-container">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( pm_opt( 'company' ) ); ?><?php echo pm_opt( 'cnpj' ) ? ' — CNPJ ' . esc_html( pm_opt( 'cnpj' ) ) : ''; ?>. <?php esc_html_e( 'Todos os direitos reservados.', 'palestra-mantos' ); ?></span>
		</div>
	</div>
</footer>

<a class="pm-whats-float" href="<?php echo esc_url( pm_whatsapp_url() ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Fale conosco pelo WhatsApp', 'palestra-mantos' ); ?>"><?php pm_the_icon( 'whatsapp' ); ?></a>

<?php wp_footer(); ?>
</body>
</html>
