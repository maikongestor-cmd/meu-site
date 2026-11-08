<?php
/**
 * Integração com o WooCommerce.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Layout
 * ---------------------------------------------------------------------- */

// Remove o wrapper e a sidebar padrão — o tema usa woocommerce.php.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_filter(
	'loop_shop_columns',
	function () {
		return 4;
	}
);

add_filter(
	'woocommerce_output_related_products_args',
	function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

/**
 * Selo de promoção com porcentagem.
 *
 * @param string     $html    HTML.
 * @param WP_Post    $post    Post.
 * @param WC_Product $product Produto.
 * @return string
 */
function pm_sale_flash( $html, $post, $product ) {
	$regular = 0;
	$sale    = 0;
	if ( $product->is_type( 'variable' ) ) {
		$regular = (float) $product->get_variation_regular_price( 'max' );
		$sale    = (float) $product->get_variation_sale_price( 'min' );
	} else {
		$regular = (float) $product->get_regular_price();
		$sale    = (float) $product->get_sale_price();
	}
	if ( $regular > 0 && $sale > 0 && $sale < $regular ) {
		$pct = round( ( ( $regular - $sale ) / $regular ) * 100 );
		/* translators: %d: porcentagem. */
		return '<span class="onsale">' . esc_html( sprintf( __( '-%d%% OFF', 'palestra-mantos' ), $pct ) ) . '</span>';
	}
	return $html;
}
add_filter( 'woocommerce_sale_flash', 'pm_sale_flash', 10, 3 );

/* -------------------------------------------------------------------------
 * Preço no Pix e parcelamento
 * ---------------------------------------------------------------------- */

/**
 * Preço atual mínimo do produto.
 *
 * @param WC_Product $product Produto.
 * @return float
 */
function pm_product_price( $product ) {
	if ( $product->is_type( 'variable' ) ) {
		return (float) $product->get_variation_price( 'min', true );
	}
	return (float) wc_get_price_to_display( $product );
}

/**
 * Linha "R$ X no Pix" na vitrine.
 */
function pm_loop_pix_price() {
	global $product;
	$pct = (int) pm_opt( 'pix_discount' );
	if ( ! $product || $pct <= 0 ) {
		return;
	}
	$price = pm_product_price( $product );
	if ( $price <= 0 ) {
		return;
	}
	printf(
		'<div class="pm-pix-loop"><b>%s</b> no Pix</div>',
		wp_kses_post( wc_price( $price * ( 1 - $pct / 100 ) ) )
	);
}
add_action( 'woocommerce_after_shop_loop_item_title', 'pm_loop_pix_price', 11 );

/**
 * Bloco de formas de pagamento na página do produto.
 */
function pm_single_pay_info() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$price = pm_product_price( $product );
	if ( $price <= 0 ) {
		return;
	}
	$pct   = (int) pm_opt( 'pix_discount' );
	$times = max( 1, (int) pm_opt( 'installments' ) );
	$free  = (int) pm_opt( 'free_shipping' );

	echo '<div class="pm-pay-info">';
	if ( $pct > 0 ) {
		printf(
			'<div>%s<span><b>%s</b> no Pix (%d%% de desconto)</span></div>',
			pm_icon( 'pix' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_kses_post( wc_price( $price * ( 1 - $pct / 100 ) ) ),
			(int) $pct
		);
	}
	if ( $times > 1 ) {
		printf(
			'<div>%s<span>ou em até <b>%dx de %s</b> no cartão</span></div>',
			pm_icon( 'card' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			(int) $times,
			wp_kses_post( wc_price( $price / $times ) )
		);
	}
	if ( $free > 0 ) {
		printf(
			'<div>%s<span>Frete grátis em compras acima de <b>%s</b></span></div>',
			pm_icon( 'truck' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			wp_kses_post( wc_price( $free ) )
		);
	}
	echo '</div>';
}
add_action( 'woocommerce_single_product_summary', 'pm_single_pay_info', 11 );

/**
 * Link do guia de medidas acima do formulário de compra.
 */
function pm_size_guide_link() {
	$page = get_page_by_path( 'guia-de-medidas' );
	if ( ! $page ) {
		return;
	}
	printf(
		'<a class="pm-size-guide" href="%s" target="_blank" rel="noopener">%s %s</a>',
		esc_url( get_permalink( $page ) ),
		pm_icon( 'ruler' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Guia de medidas', 'palestra-mantos' )
	);
}
add_action( 'woocommerce_before_add_to_cart_form', 'pm_size_guide_link', 5 );

/**
 * Botão "comprar pelo WhatsApp".
 */
function pm_single_whatsapp() {
	global $product;
	if ( ! $product ) {
		return;
	}
	/* translators: 1: produto, 2: link. */
	$msg = sprintf( __( 'Olá! Tenho interesse no produto "%1$s": %2$s', 'palestra-mantos' ), $product->get_name(), get_permalink( $product->get_id() ) );
	printf(
		'<div class="pm-buy-whats"><a class="pm-btn pm-btn--outline" href="%s" target="_blank" rel="noopener">%s %s</a></div>',
		esc_url( pm_whatsapp_url( $msg ) ),
		pm_icon( 'whatsapp' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Tirar dúvidas no WhatsApp', 'palestra-mantos' )
	);
}
add_action( 'woocommerce_single_product_summary', 'pm_single_whatsapp', 31 );

/* -------------------------------------------------------------------------
 * Desconto no Pix no carrinho / checkout
 * ---------------------------------------------------------------------- */

/**
 * O método de pagamento selecionado é Pix?
 * Funciona com Mercado Pago (woo-mercado-pago-pix) e com gateways Appmax
 * cujo ID contenha "pix".
 *
 * @param string $gateway_id ID.
 * @return bool
 */
function pm_is_pix_gateway( $gateway_id ) {
	$is_pix = is_string( $gateway_id ) && false !== stripos( $gateway_id, 'pix' );
	return (bool) apply_filters( 'pm_is_pix_gateway', $is_pix, $gateway_id );
}

/**
 * Aplica o desconto do Pix como taxa negativa.
 *
 * @param WC_Cart $cart Carrinho.
 */
function pm_pix_discount_fee( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return;
	}
	$pct = (int) pm_opt( 'pix_discount' );
	if ( $pct <= 0 || ! WC()->session ) {
		return;
	}
	$chosen = WC()->session->get( 'chosen_payment_method' );
	if ( ! pm_is_pix_gateway( $chosen ) ) {
		return;
	}
	$base = (float) $cart->get_subtotal() - (float) $cart->get_discount_total();
	if ( $base <= 0 ) {
		return;
	}
	/* translators: %d: porcentagem. */
	$cart->add_fee( sprintf( __( 'Desconto Pix (%d%%)', 'palestra-mantos' ), $pct ), -round( $base * $pct / 100, 2 ), false );
}
add_action( 'woocommerce_cart_calculate_fees', 'pm_pix_discount_fee', 20 );

/* -------------------------------------------------------------------------
 * Carrinho no cabeçalho (atualização via AJAX)
 * ---------------------------------------------------------------------- */

/**
 * HTML do contador.
 *
 * @return string
 */
function pm_cart_count_html() {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="pm-cart-count" data-count="' . (int) $count . '"' . ( $count ? '' : ' hidden' ) . '>' . (int) $count . '</span>';
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span.pm-cart-count'] = pm_cart_count_html();
		return $fragments;
	}
);
