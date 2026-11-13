<?php
/**
 * Formulário de busca (procura produtos quando o WooCommerce está ativo).
 *
 * @package RaposaMantos
 */

defined( 'ABSPATH' ) || exit;
$pm_uid = wp_unique_id( 'pm-search-' );
?>
<form role="search" method="get" class="pm-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $pm_uid ); ?>"><?php esc_html_e( 'Buscar', 'palestra-mantos' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $pm_uid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'O que você está buscando?', 'palestra-mantos' ); ?>">
	<?php if ( class_exists( 'WooCommerce' ) ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<button type="submit"><?php pm_the_icon( 'search' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Buscar', 'palestra-mantos' ); ?></span></button>
</form>
