<?php
/**
 * Moldura das páginas do WooCommerce (loja, categorias e produto).
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="pm-container pm-shop">
	<?php woocommerce_content(); ?>
</div>
<?php
get_footer();
