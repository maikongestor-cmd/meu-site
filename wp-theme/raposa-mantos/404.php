<?php
/**
 * Página não encontrada.
 *
 * @package RaposaMantos
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="pm-404">
	<strong>404</strong>
	<h1 style="font-size:2.4rem"><?php esc_html_e( 'Página não encontrada', 'palestra-mantos' ); ?></h1>
	<p><?php esc_html_e( 'O endereço que você procurou não existe ou mudou de lugar.', 'palestra-mantos' ); ?></p>
	<a class="pm-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Voltar para o início', 'palestra-mantos' ); ?></a>
</section>
<?php
get_footer();
