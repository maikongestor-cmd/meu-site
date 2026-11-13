<?php
/**
 * Carrinho lateral: abre ao clicar em "Comprar" ou no ícone do carrinho,
 * com barra de progresso do frete grátis e sugestão para completar o pedido.
 *
 * @package RaposaMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sugestão para atingir o frete grátis (ex.: "Combine com uma camiseta…").
 *
 * @param float $missing Valor que falta.
 * @return string HTML.
 */
function pm_freeship_suggestion( $missing ) {
	if ( $missing <= 0 || ! WC()->cart ) {
		return '';
	}
	$cats = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		$pid  = ! empty( $item['product_id'] ) ? (int) $item['product_id'] : 0;
		$cats = array_merge( $cats, wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'slugs' ) ) );
	}
	$options = array(
		'camisetas' => __( 'Combine com uma camiseta e aproveite o frete grátis.', 'palestra-mantos' ),
		'calcas'    => __( 'Que tal uma calça para completar o look e ganhar frete grátis?', 'palestra-mantos' ),
		'conjuntos' => __( 'Leve o conjunto completo e ganhe frete grátis.', 'palestra-mantos' ),
	);
	foreach ( $options as $slug => $text ) {
		if ( ! in_array( $slug, $cats, true ) ) {
			return sprintf( '<p class="pm-suggest"><a href="%s">%s →</a></p>', esc_url( pm_cat_url( $slug ) ), esc_html( $text ) );
		}
	}
	return '<p class="pm-suggest">' . esc_html__( 'Adicione mais uma peça e ganhe frete grátis.', 'palestra-mantos' ) . '</p>';
}

/**
 * Barra de progresso do frete grátis (usada no carrinho lateral).
 *
 * @return string
 */
function pm_freeship_bar_html() {
	$min = (float) pm_opt( 'free_shipping' );
	if ( $min <= 0 || ! WC()->cart ) {
		return '';
	}
	$total   = (float) WC()->cart->get_displayed_subtotal() - (float) WC()->cart->get_discount_total();
	$missing = max( 0, $min - $total );
	$pct     = min( 100, max( 0, round( $total / $min * 100 ) ) );
	$html    = '<div class="pm-freebar pm-freebar--mini">';
	if ( $missing <= 0 ) {
		$html .= '<p>' . pm_icon( 'truck' ) . ' <strong>' . esc_html__( 'Parabéns! Você ganhou frete grátis 🎉', 'palestra-mantos' ) . '</strong></p>';
	} else {
		/* translators: %s: valor. */
		$html .= '<p>' . pm_icon( 'truck' ) . ' ' . wp_kses_post( sprintf( __( 'Faltam <strong>%s</strong> para o frete grátis!', 'palestra-mantos' ), wc_price( $missing ) ) ) . '</p>';
	}
	$html .= sprintf( '<div class="pm-freebar__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%1$d"><span style="width:%1$d%%"></span></div>', (int) $pct );
	$html .= '</div>';
	$html .= pm_freeship_suggestion( $missing );
	return $html;
}

/**
 * Conteúdo do carrinho lateral (atualizado via fragmentos do WooCommerce).
 *
 * @return string
 */
function pm_minicart_body() {
	ob_start();
	echo '<div class="pm-minicart__body">';
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		echo '<div class="pm-minicart__empty">' . pm_icon( 'bag' ) . '<p>' . esc_html__( 'Seu carrinho está vazio.', 'palestra-mantos' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		printf( '<a class="pm-btn" href="%s">%s</a></div>', esc_url( wc_get_page_permalink( 'shop' ) ), esc_html__( 'Ver produtos', 'palestra-mantos' ) );
		echo '</div>';
		return (string) ob_get_clean();
	}

	echo '<ul class="pm-minicart__items">';
	foreach ( WC()->cart->get_cart() as $key => $item ) {
		$product = $item['data'];
		if ( ! $product || ! $product->exists() ) {
			continue;
		}
		$parent = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
		$attrs  = array();
		foreach ( (array) $item['variation'] as $attr_key => $value ) {
			$tax  = str_replace( 'attribute_', '', $attr_key );
			$term = taxonomy_exists( $tax ) ? get_term_by( 'slug', $value, $tax ) : false;
			$attrs[] = wc_attribute_label( $tax ) . ' ' . ( $term ? $term->name : $value );
		}
		printf(
			'<li class="pm-minicart__item"><a class="pm-minicart__thumb" href="%1$s">%2$s</a><div class="pm-minicart__info"><a class="pm-minicart__name" href="%1$s">%3$s</a><span class="pm-minicart__meta">%4$s</span><span class="pm-minicart__price">%5$s × %6$d</span></div><a class="pm-minicart__remove" href="%7$s" aria-label="%8$s" data-cart-key="%9$s">×</a></li>',
			esc_url( $parent ? $parent->get_permalink() : '#' ),
			$product->get_image( array( 96, 96 ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $parent ? $parent->get_name() : $product->get_name() ),
			esc_html( implode( ' · ', $attrs ) ),
			wp_kses_post( WC()->cart->get_product_price( $product ) ),
			(int) $item['quantity'],
			esc_url( wc_get_cart_remove_url( $key ) ),
			esc_attr__( 'Remover do carrinho', 'palestra-mantos' ),
			esc_attr( $key )
		);
	}
	echo '</ul>';

	echo pm_freeship_bar_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	$pct = (int) pm_opt( 'pix_discount' );
	echo '<div class="pm-minicart__totals">';
	printf( '<p><span>%s</span><strong>%s</strong></p>', esc_html__( 'Subtotal', 'palestra-mantos' ), wp_kses_post( WC()->cart->get_cart_subtotal() ) );
	if ( $pct > 0 ) {
		$sub = (float) WC()->cart->get_subtotal() - (float) WC()->cart->get_discount_total();
		/* translators: %d: porcentagem. */
		printf( '<p class="pm-minicart__pix"><span>%s</span><strong>%s</strong></p>', esc_html( sprintf( __( 'No Pix (%d%% OFF)', 'palestra-mantos' ), $pct ) ), wp_kses_post( wc_price( $sub * ( 1 - $pct / 100 ) ) ) );
	}
	echo '</div>';
	printf( '<a class="pm-btn pm-minicart__checkout" href="%s">%s %s</a>', esc_url( wc_get_checkout_url() ), pm_icon( 'shield' ), esc_html__( 'Finalizar compra', 'palestra-mantos' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	printf( '<a class="pm-minicart__cart" href="%s">%s</a>', esc_url( wc_get_cart_url() ), esc_html__( 'Ver carrinho completo', 'palestra-mantos' ) );
	echo '</div>';
	return (string) ob_get_clean();
}

/**
 * Gaveta no rodapé (fora do carrinho e do checkout).
 */
function pm_minicart_drawer() {
	if ( is_cart() || is_checkout() ) {
		return;
	}
	?>
	<div class="pm-minicart-overlay" hidden></div>
	<aside class="pm-minicart" id="pm-minicart" aria-label="<?php esc_attr_e( 'Seu carrinho', 'palestra-mantos' ); ?>" aria-hidden="true">
		<header class="pm-minicart__head">
			<strong><?php esc_html_e( 'Seu carrinho', 'palestra-mantos' ); ?></strong>
			<button type="button" class="pm-minicart__close" aria-label="<?php esc_attr_e( 'Fechar carrinho', 'palestra-mantos' ); ?>"><?php pm_the_icon( 'close' ); ?></button>
		</header>
		<p class="pm-minicart__added" hidden>✓ <?php esc_html_e( 'Produto adicionado ao carrinho', 'palestra-mantos' ); ?></p>
		<?php echo pm_minicart_body(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</aside>
	<?php
}
add_action( 'wp_footer', 'pm_minicart_drawer', 5 );

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['div.pm-minicart__body'] = pm_minicart_body();
		return $fragments;
	}
);

/**
 * Remover item pelo carrinho lateral sem recarregar.
 */
function pm_minicart_remove() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- só remove item do próprio carrinho do visitante.
	$key = isset( $_POST['cart_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_key'] ) ) : '';
	// phpcs:enable
	if ( $key && WC()->cart ) {
		WC()->cart->remove_cart_item( $key );
	}
	WC_AJAX::get_refreshed_fragments();
}
add_action( 'wc_ajax_pm_minicart_remove', 'pm_minicart_remove' );

/**
 * Endereços para o JavaScript.
 */
function pm_minicart_script_data() {
	wp_localize_script(
		'pm-theme',
		'pmCart',
		array(
			'add'    => WC_AJAX::get_endpoint( 'add_to_cart' ),
			'remove' => WC_AJAX::get_endpoint( 'pm_minicart_remove' ),
			'drawer' => ! ( is_cart() || is_checkout() ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_minicart_script_data', 22 );
