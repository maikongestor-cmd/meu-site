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
	global $product;
	if ( ! $product || ! $product->is_type( 'variable' ) ) {
		return;
	}
	printf(
		'<div class="pm-size-links"><a class="pm-size-guide" href="#descubra-seu-tamanho">%s %s</a></div>',
		pm_icon( 'user' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Descubra seu tamanho', 'palestra-mantos' )
	);
}
add_action( 'woocommerce_before_add_to_cart_form', 'pm_size_guide_link', 5 );

/**
 * Provador fixo na página do produto (abaixo das fotos e do botão Comprar).
 */
function pm_size_finder_section() {
	global $product;
	if ( ! $product || ! $product->is_type( 'variable' ) || ! function_exists( 'pm_size_finder_html' ) ) {
		return;
	}
	?>
	<section class="pm-fit-section" id="descubra-seu-tamanho">
		<?php echo pm_size_finder_html( pm_product_fit_type( $product->get_id() ), true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</section>
	<?php
}
add_action( 'woocommerce_after_single_product_summary', 'pm_size_finder_section', 5 );

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
add_action( 'woocommerce_single_product_summary', 'pm_single_whatsapp', 33 );

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

/* -------------------------------------------------------------------------
 * Botão "Comprar" → adiciona e leva para o carrinho
 * ---------------------------------------------------------------------- */

add_filter(
	'woocommerce_product_single_add_to_cart_text',
	function () {
		return __( 'Comprar', 'palestra-mantos' );
	}
);

/**
 * Depois de clicar em "Comprar" na página do produto, vai para o carrinho.
 * Desligue com: add_filter( 'pm_redirect_to_cart', '__return_false' );
 *
 * @param string $url URL.
 * @return string
 */
function pm_add_to_cart_redirect( $url ) {
	if ( apply_filters( 'pm_redirect_to_cart', true ) && ! wp_doing_ajax() ) {
		return wc_get_cart_url();
	}
	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'pm_add_to_cart_redirect' );

/* -------------------------------------------------------------------------
 * Frete por CEP
 * ---------------------------------------------------------------------- */

/**
 * UF a partir do CEP (faixas dos Correios).
 *
 * @param string $cep CEP.
 * @return string Sigla ou ''.
 */
function pm_cep_to_uf( $cep ) {
	$n = (int) substr( preg_replace( '/\D+/', '', (string) $cep ), 0, 5 );
	$ranges = array(
		array( 1000, 19999, 'SP' ), array( 20000, 28999, 'RJ' ), array( 29000, 29999, 'ES' ),
		array( 30000, 39999, 'MG' ), array( 40000, 48999, 'BA' ), array( 49000, 49999, 'SE' ),
		array( 50000, 56999, 'PE' ), array( 57000, 57999, 'AL' ), array( 58000, 58999, 'PB' ),
		array( 59000, 59999, 'RN' ), array( 60000, 63999, 'CE' ), array( 64000, 64999, 'PI' ),
		array( 65000, 65999, 'MA' ), array( 66000, 68899, 'PA' ), array( 68900, 68999, 'AP' ),
		array( 69000, 69299, 'AM' ), array( 69300, 69399, 'RR' ), array( 69400, 69899, 'AM' ),
		array( 69900, 69999, 'AC' ), array( 70000, 72799, 'DF' ), array( 72800, 72999, 'GO' ),
		array( 73000, 73699, 'DF' ), array( 73700, 76799, 'GO' ), array( 76800, 76999, 'RO' ),
		array( 77000, 77999, 'TO' ), array( 78000, 78899, 'MT' ), array( 79000, 79999, 'MS' ),
		array( 80000, 87999, 'PR' ), array( 88000, 89999, 'SC' ), array( 90000, 99999, 'RS' ),
	);
	foreach ( $ranges as $r ) {
		if ( $n >= $r[0] && $n <= $r[1] ) {
			return $r[2];
		}
	}
	return '';
}

/**
 * Formulário de CEP na página do produto.
 */
function pm_single_shipping_calc() {
	global $product;
	if ( ! $product || ! $product->needs_shipping() || ! wc_shipping_enabled() ) {
		return;
	}
	?>
	<form class="pm-ship" data-product="<?php echo esc_attr( $product->get_id() ); ?>" novalidate>
		<label for="pm-ship-cep"><?php pm_the_icon( 'truck' ); ?> <?php esc_html_e( 'Calcular frete e prazo', 'palestra-mantos' ); ?></label>
		<div class="pm-ship__row">
			<input id="pm-ship-cep" name="cep" type="text" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" maxlength="9" required>
			<button type="submit" class="pm-btn pm-btn--outline"><?php esc_html_e( 'Calcular', 'palestra-mantos' ); ?></button>
		</div>
		<a class="pm-ship__help" href="https://buscacepinter.correios.com.br/app/endereco/index.php" target="_blank" rel="noopener"><?php esc_html_e( 'Não sei meu CEP', 'palestra-mantos' ); ?></a>
		<div class="pm-ship__result" aria-live="polite"></div>
	</form>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'pm_single_shipping_calc', 32 );

/**
 * Dados para o JavaScript da calculadora.
 */
function pm_shipping_script_data() {
	if ( ! is_product() ) {
		return;
	}
	wp_localize_script(
		'pm-theme',
		'pmShip',
		array(
			'url'      => WC_AJAX::get_endpoint( 'pm_calc_shipping' ),
			'msgCep'   => __( 'Digite um CEP válido com 8 números.', 'palestra-mantos' ),
			'msgError' => __( 'Não foi possível calcular agora. Tente de novo.', 'palestra-mantos' ),
			'msgNone'  => __( 'Não encontramos opções de entrega para este CEP. Fale com a gente no WhatsApp.', 'palestra-mantos' ),
			'days'     => __( '%d dias úteis', 'palestra-mantos' ),
			'day'      => __( '1 dia útil', 'palestra-mantos' ),
			'loading'  => __( 'Calculando…', 'palestra-mantos' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_shipping_script_data', 20 );

/**
 * AJAX: calcula as opções de frete para um produto e CEP.
 */
function pm_ajax_calc_shipping() {
	// Sem nonce de propósito: é uma simulação pública (só leitura) e nonces quebram
	// com cache de página e com a sessão de visitantes do WooCommerce.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$cep          = preg_replace( '/\D+/', '', isset( $_POST['cep'] ) ? sanitize_text_field( wp_unslash( $_POST['cep'] ) ) : '' );
	$product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$variation_id = isset( $_POST['variation_id'] ) ? absint( $_POST['variation_id'] ) : 0;
	$qty          = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
	// phpcs:enable

	if ( 8 !== strlen( $cep ) ) {
		wp_send_json_error( array( 'message' => __( 'Digite um CEP válido com 8 números.', 'palestra-mantos' ) ) );
	}

	$product = wc_get_product( $variation_id ? $variation_id : $product_id );
	if ( $product && $product->is_type( 'variable' ) ) {
		// Sem tamanho escolhido: usa a primeira variação disponível (preço/peso aproximados).
		$children = $product->get_visible_children();
		$product  = $children ? wc_get_product( $children[0] ) : $product;
	}
	if ( ! $product || ! $product->needs_shipping() ) {
		wp_send_json_error( array( 'message' => __( 'Produto inválido.', 'palestra-mantos' ) ) );
	}

	if ( function_exists( 'wc_load_cart' ) && null === WC()->cart ) {
		wc_load_cart();
	}

	$price    = (float) wc_get_price_to_display( $product );
	$line     = $price * $qty;
	$uf       = pm_cep_to_uf( $cep );
	$postcode = substr( $cep, 0, 5 ) . '-' . substr( $cep, 5 );
	$parent   = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

	$package = array(
		'contents'        => array(
			'pm_estimate' => array(
				'key'               => 'pm_estimate',
				'product_id'        => $parent,
				'variation_id'      => $product->get_parent_id() ? $product->get_id() : 0,
				'variation'         => array(),
				'quantity'          => $qty,
				'data'              => $product,
				'data_hash'         => '',
				'line_total'        => $line,
				'line_tax'          => 0,
				'line_subtotal'     => $line,
				'line_subtotal_tax' => 0,
			),
		),
		'contents_cost'   => $line,
		'applied_coupons' => array(),
		'user'            => array( 'ID' => get_current_user_id() ),
		'destination'     => array(
			'country'   => 'BR',
			'state'     => $uf,
			'postcode'  => $postcode,
			'city'      => '',
			'address'   => '',
			'address_1' => '',
			'address_2' => '',
		),
		'cart_subtotal'   => $line,
	);

	$result = WC()->shipping()->calculate_shipping_for_package( $package, 'pm_estimate' );
	$rates  = ( is_array( $result ) && ! empty( $result['rates'] ) ) ? $result['rates'] : array();

	// O frete grátis do WooCommerce olha o carrinho inteiro; aqui avaliamos só este produto.
	$free_min = null;
	$zone     = WC_Shipping_Zones::get_zone_matching_package( $package );
	foreach ( $zone->get_shipping_methods( true ) as $method ) {
		if ( 'free_shipping' === $method->id ) {
			$requires = $method->get_option( 'requires' );
			$free_min = in_array( $requires, array( 'min_amount', 'either' ), true ) ? (float) wc_format_decimal( $method->get_option( 'min_amount' ) ) : 0.0;
		}
	}

	$options = array();
	if ( null !== $free_min && $line >= $free_min ) {
		$options[] = array(
			'label' => __( 'Frete grátis', 'palestra-mantos' ),
			'cost'  => __( 'Grátis', 'palestra-mantos' ),
			'free'  => true,
		);
	}
	foreach ( $rates as $rate ) {
		if ( 'free_shipping' === $rate->get_method_id() ) {
			continue;
		}
		$cost      = (float) $rate->get_cost() + array_sum( (array) $rate->get_taxes() );
		$options[] = array(
			'label' => pm_ship_label( wp_strip_all_tags( html_entity_decode( $rate->get_label(), ENT_QUOTES, 'UTF-8' ) ) ),
			'cost'  => $cost > 0 ? html_entity_decode( wp_strip_all_tags( wc_price( $cost ) ), ENT_QUOTES, 'UTF-8' ) : __( 'Grátis', 'palestra-mantos' ),
			'free'  => $cost <= 0,
		);
	}

	$note = '';
	if ( null !== $free_min && $free_min > 0 && $line < $free_min ) {
		/* translators: %s: valor que falta. */
		$note = sprintf( __( 'Faltam %s para ganhar frete grátis.', 'palestra-mantos' ), html_entity_decode( wp_strip_all_tags( wc_price( $free_min - $line ) ), ENT_QUOTES, 'UTF-8' ) );
	}

	// Guarda o CEP para já vir preenchido no carrinho e no checkout.
	if ( WC()->customer ) {
		WC()->customer->set_shipping_location( 'BR', $uf, $postcode );
		if ( ! WC()->customer->get_billing_postcode() ) {
			WC()->customer->set_billing_country( 'BR' );
			WC()->customer->set_billing_postcode( $postcode );
			if ( $uf ) {
				WC()->customer->set_billing_state( $uf );
			}
		}
		WC()->customer->set_calculated_shipping( true );
		WC()->customer->save();
	}

	// Sem opções aqui o JS ainda tenta a cotação do Melhor Envio antes de mostrar o erro.
	wp_send_json_success(
		array(
			'options' => $options,
			'note'    => $note,
			'uf'      => $uf,
		)
	);
}
add_action( 'wc_ajax_pm_calc_shipping', 'pm_ajax_calc_shipping' );

/**
 * "Correios - SEDEX (2 dias úteis)" → "Correios SEDEX · 2 dias úteis".
 *
 * @param string $label Nome do frete.
 * @return string
 */
function pm_ship_label( $label ) {
	if ( preg_match( '/^(.+?)\s+-\s+(.+?)\s*\((.+)\)\s*$/u', $label, $m ) ) {
		return $m[1] . ' ' . $m[2] . ' · ' . $m[3];
	}
	return $label;
}

/* Calculadora do carrinho: só o CEP (o estado sai do CEP). */
add_filter( 'woocommerce_shipping_calculator_enable_state', '__return_false' );
add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );

/**
 * Preenche o estado pelo CEP depois de calcular no carrinho.
 */
function pm_cart_calc_set_state() {
	$customer = WC()->customer;
	if ( $customer && 'BR' === $customer->get_shipping_country() ) {
		$uf = pm_cep_to_uf( $customer->get_shipping_postcode() );
		if ( $uf ) {
			$customer->set_shipping_state( $uf );
			if ( ! $customer->get_billing_state() ) {
				$customer->set_billing_state( $uf );
			}
		}
		// Leva o CEP para o checkout, que completa o endereço sozinho.
		if ( ! $customer->get_billing_postcode() ) {
			$customer->set_billing_postcode( $customer->get_shipping_postcode() );
			$customer->set_billing_country( 'BR' );
		}
		$customer->save();
	}
}
add_action( 'woocommerce_calculated_shipping', 'pm_cart_calc_set_state' );


/* -------------------------------------------------------------------------
 * Peso e medidas padrão para o frete (Melhor Envio / Correios)
 * ---------------------------------------------------------------------- */

/**
 * Medidas da embalagem por categoria (cm e kg), usadas só quando o produto
 * não tem peso/medidas cadastrados. Ajuste pelo filtro 'pm_default_package'.
 *
 * @param WC_Product $product Produto ou variação.
 * @return array
 */
function pm_default_package( $product ) {
	$pid  = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
	$cats = wp_get_post_terms( $pid, 'product_cat', array( 'fields' => 'slugs' ) );
	$cats = is_wp_error( $cats ) ? array() : $cats;
	$sku  = (string) $product->get_sku();
	if ( in_array( 'conjuntos', $cats, true ) ) {
		$pkg = array( 'weight' => 1.2, 'length' => 35, 'width' => 30, 'height' => 10 );
	} elseif ( false !== stripos( $sku, 'KIT' ) ) {
		$pkg = array( 'weight' => 0.6, 'length' => 30, 'width' => 25, 'height' => 6 );
	} elseif ( in_array( 'camisetas', $cats, true ) ) {
		$pkg = array( 'weight' => 0.3, 'length' => 30, 'width' => 25, 'height' => 3 );
	} else {
		$pkg = array( 'weight' => 0.7, 'length' => 30, 'width' => 25, 'height' => 6 );
	}
	return apply_filters( 'pm_default_package', $pkg, $product );
}

foreach ( array( 'weight', 'length', 'width', 'height' ) as $pm_dim ) {
	foreach ( array( 'woocommerce_product_get_', 'woocommerce_product_variation_get_' ) as $pm_hook ) {
		add_filter(
			$pm_hook . $pm_dim,
			function ( $value, $product ) use ( $pm_dim ) {
				if ( '' !== (string) $value && (float) $value > 0 ) {
					return $value;
				}
				if ( is_admin() && ! wp_doing_ajax() ) {
					return $value; // No painel, mostra o que está realmente cadastrado.
				}
				$pkg = pm_default_package( $product );
				return (string) $pkg[ $pm_dim ];
			},
			20,
			2
		);
	}
}
