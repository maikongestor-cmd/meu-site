<?php
/**
 * Carrinho e checkout para o Brasil:
 * - campos CPF, número, bairro e celular (compatíveis com Appmax e Mercado Pago);
 * - CEP primeiro, com preenchimento automático do endereço (ViaCEP);
 * - etapas da compra, barra de frete grátis, economia no Pix e selos de segurança.
 *
 * Se o plugin "Brazilian Market on WooCommerce" estiver ativo, os campos dele são
 * usados e os do tema ficam desligados (mesmos nomes: billing_cpf, billing_number...).
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * O plugin Brazilian Market (Extra Checkout Fields for Brazil) está ativo?
 *
 * @return bool
 */
function pm_has_brazilian_market() {
	return class_exists( 'Extra_Checkout_Fields_For_Brazil' ) || class_exists( 'Brazilian_Market_On_WooCommerce' );
}

/* -------------------------------------------------------------------------
 * Campos brasileiros
 * ---------------------------------------------------------------------- */

/**
 * Campos de cobrança na ordem brasileira.
 *
 * @param array $fields Campos.
 * @return array
 */
function pm_br_billing_fields( $fields ) {
	if ( pm_has_brazilian_market() ) {
		return $fields;
	}
	unset( $fields['billing_company'] );

	$fields['billing_first_name']['class'] = array( 'form-row-first' );
	$fields['billing_last_name']['class']  = array( 'form-row-last' );

	$fields['billing_cpf'] = array(
		'label'       => __( 'CPF', 'palestra-mantos' ),
		'placeholder' => '000.000.000-00',
		'required'    => true,
		'class'       => array( 'form-row-first' ),
		'clear'       => false,
		'type'        => 'text',
		'custom_attributes' => array(
			'inputmode'    => 'numeric',
			'autocomplete' => 'off',
			'maxlength'    => '14',
		),
	);
	$fields['billing_email']['class'] = array( 'form-row-last' );

	if ( ! isset( $fields['billing_phone'] ) ) {
		$fields['billing_phone'] = array( 'type' => 'tel', 'validate' => array( 'phone' ), 'autocomplete' => 'tel' );
	}
	$fields['billing_phone']['label']    = __( 'Celular / WhatsApp', 'palestra-mantos' );
	$fields['billing_phone']['required'] = true;
	$fields['billing_phone']['placeholder'] = '(11) 90000-0000';
	$fields['billing_phone']['class']    = array( 'form-row-wide' );

	$fields['billing_postcode']['class']       = array( 'form-row-first', 'pm-cep' );
	$fields['billing_postcode']['placeholder'] = '00000-000';
	$fields['billing_postcode']['custom_attributes'] = array( 'inputmode' => 'numeric', 'maxlength' => '9' );

	$fields['billing_address_1']['label']       = __( 'Rua / Avenida', 'palestra-mantos' );
	$fields['billing_address_1']['placeholder'] = '';
	$fields['billing_address_1']['class']       = array( 'form-row-wide' );

	$fields['billing_number'] = array(
		'label'    => __( 'Número', 'palestra-mantos' ),
		'required' => true,
		'class'    => array( 'form-row-first' ),
		'clear'    => false,
		'custom_attributes' => array( 'inputmode' => 'numeric' ),
	);
	if ( isset( $fields['billing_address_2'] ) ) {
		$fields['billing_address_2']['label']       = __( 'Complemento', 'palestra-mantos' );
		$fields['billing_address_2']['label_class'] = array();
		$fields['billing_address_2']['placeholder'] = __( 'Apto, bloco… (opcional)', 'palestra-mantos' );
		$fields['billing_address_2']['class']       = array( 'form-row-last' );
	} else {
		$fields['billing_number']['class'] = array( 'form-row-wide' );
	}

	$fields['billing_neighborhood'] = array(
		'label'    => __( 'Bairro', 'palestra-mantos' ),
		'required' => true,
		'class'    => array( 'form-row-wide' ),
	);
	$fields['billing_city']['class']  = array( 'form-row-first' );
	$fields['billing_state']['class'] = array( 'form-row-last' );

	$order = array(
		'billing_first_name'   => 10,
		'billing_last_name'    => 20,
		'billing_cpf'          => 30,
		'billing_email'        => 40,
		'billing_phone'        => 50,
		'billing_postcode'     => 60,
		'billing_country'      => 65,
		'billing_address_1'    => 70,
		'billing_number'       => 80,
		'billing_address_2'    => 90,
		'billing_neighborhood' => 100,
		'billing_city'         => 110,
		'billing_state'        => 120,
	);
	foreach ( $order as $key => $priority ) {
		if ( isset( $fields[ $key ] ) ) {
			$fields[ $key ]['priority'] = $priority;
		}
	}
	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'pm_br_billing_fields', 20 );

/**
 * Campos de entrega (quando o cliente escolhe outro endereço).
 *
 * @param array $fields Campos.
 * @return array
 */
function pm_br_shipping_fields( $fields ) {
	if ( pm_has_brazilian_market() ) {
		return $fields;
	}
	unset( $fields['shipping_company'] );
	$fields['shipping_postcode']['class']       = array( 'form-row-first', 'pm-cep' );
	$fields['shipping_postcode']['placeholder'] = '00000-000';
	$fields['shipping_address_1']['label']      = __( 'Rua / Avenida', 'palestra-mantos' );
	$fields['shipping_address_1']['placeholder'] = '';
	$fields['shipping_number'] = array(
		'label'    => __( 'Número', 'palestra-mantos' ),
		'required' => true,
		'class'    => array( 'form-row-first' ),
	);
	if ( isset( $fields['shipping_address_2'] ) ) {
		$fields['shipping_address_2']['label']       = __( 'Complemento', 'palestra-mantos' );
		$fields['shipping_address_2']['label_class'] = array();
		$fields['shipping_address_2']['class']       = array( 'form-row-last' );
	} else {
		$fields['shipping_number']['class'] = array( 'form-row-wide' );
	}
	$fields['shipping_neighborhood'] = array(
		'label'    => __( 'Bairro', 'palestra-mantos' ),
		'required' => true,
		'class'    => array( 'form-row-wide' ),
	);
	$fields['shipping_city']['class']  = array( 'form-row-first' );
	$fields['shipping_state']['class'] = array( 'form-row-last' );
	$order = array(
		'shipping_first_name'   => 10,
		'shipping_last_name'    => 20,
		'shipping_postcode'     => 30,
		'shipping_country'      => 35,
		'shipping_address_1'    => 40,
		'shipping_number'       => 50,
		'shipping_address_2'    => 60,
		'shipping_neighborhood' => 70,
		'shipping_city'         => 80,
		'shipping_state'        => 90,
	);
	foreach ( $order as $key => $priority ) {
		if ( isset( $fields[ $key ] ) ) {
			$fields[ $key ]['priority'] = $priority;
		}
	}
	return $fields;
}
add_filter( 'woocommerce_shipping_fields', 'pm_br_shipping_fields', 20 );

/**
 * O WooCommerce reordena os campos de endereço pelo país; mantém a nossa ordem no Brasil.
 *
 * @param array $locale Locais.
 * @return array
 */
function pm_br_locale( $locale ) {
	if ( pm_has_brazilian_market() ) {
		return $locale;
	}
	$locale['BR']['postcode']['priority']  = 60;
	$locale['BR']['address_1']['priority'] = 70;
	$locale['BR']['address_2']['priority'] = 90;
	$locale['BR']['city']['priority']      = 110;
	$locale['BR']['state']['priority']     = 120;
	$locale['BR']['address_2']['label']    = __( 'Complemento', 'palestra-mantos' );
	return $locale;
}
add_filter( 'woocommerce_get_country_locale', 'pm_br_locale', 20 );

/**
 * CPF válido? (dígitos verificadores)
 *
 * @param string $cpf CPF.
 * @return bool
 */
function pm_is_cpf( $cpf ) {
	$cpf = preg_replace( '/\D+/', '', (string) $cpf );
	if ( 11 !== strlen( $cpf ) || preg_match( '/^(\d)\1{10}$/', $cpf ) ) {
		return false;
	}
	for ( $t = 9; $t < 11; $t++ ) {
		$sum = 0;
		for ( $i = 0; $i < $t; $i++ ) {
			$sum += (int) $cpf[ $i ] * ( ( $t + 1 ) - $i );
		}
		$digit = ( ( 10 * $sum ) % 11 ) % 10;
		if ( (int) $cpf[ $t ] !== $digit ) {
			return false;
		}
	}
	return true;
}

/**
 * Valida CPF e celular no checkout.
 *
 * @param array    $data   Dados.
 * @param WP_Error $errors Erros.
 */
function pm_br_validate_checkout( $data, $errors ) {
	if ( pm_has_brazilian_market() ) {
		return;
	}
	if ( ! empty( $data['billing_cpf'] ) && ! pm_is_cpf( $data['billing_cpf'] ) ) {
		$errors->add( 'validation', __( '<strong>CPF</strong> inválido. Confira os números.', 'palestra-mantos' ) );
	}
	if ( ! empty( $data['billing_phone'] ) && strlen( preg_replace( '/\D+/', '', $data['billing_phone'] ) ) < 10 ) {
		$errors->add( 'validation', __( '<strong>Celular</strong> inválido. Use DDD + número.', 'palestra-mantos' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'pm_br_validate_checkout', 10, 2 );

/**
 * Guarda o CPF formatado (000.000.000-00), como os gateways brasileiros esperam.
 *
 * @param array $data Dados postados.
 * @return array
 */
function pm_br_format_posted( $data ) {
	if ( ! empty( $data['billing_cpf'] ) ) {
		$d = preg_replace( '/\D+/', '', $data['billing_cpf'] );
		if ( 11 === strlen( $d ) ) {
			$data['billing_cpf'] = substr( $d, 0, 3 ) . '.' . substr( $d, 3, 3 ) . '.' . substr( $d, 6, 3 ) . '-' . substr( $d, 9, 2 );
		}
	}
	return $data;
}
add_filter( 'woocommerce_checkout_posted_data', 'pm_br_format_posted' );

/**
 * Número e bairro dentro do endereço formatado (e-mails, pedido, painel).
 *
 * @param array $formats Formatos.
 * @return array
 */
function pm_br_address_format( $formats ) {
	if ( pm_has_brazilian_market() ) {
		return $formats;
	}
	$formats['BR'] = "{name}\n{address_1}, {number}\n{address_2}\n{neighborhood}\n{city} - {state}\n{postcode}\n{country}";
	return $formats;
}
add_filter( 'woocommerce_localisation_address_formats', 'pm_br_address_format', 20 );

add_filter(
	'woocommerce_formatted_address_replacements',
	function ( $replacements, $args ) {
		$replacements['{number}']       = isset( $args['number'] ) ? $args['number'] : '';
		$replacements['{neighborhood}'] = isset( $args['neighborhood'] ) ? $args['neighborhood'] : '';
		return $replacements;
	},
	20,
	2
);

foreach ( array( 'billing', 'shipping' ) as $pm_type ) {
	add_filter(
		'woocommerce_order_formatted_' . $pm_type . '_address',
		function ( $address, $order ) use ( $pm_type ) {
			if ( is_array( $address ) && $order instanceof WC_Order ) {
				$address['number']       = $order->get_meta( '_' . $pm_type . '_number' );
				$address['neighborhood'] = $order->get_meta( '_' . $pm_type . '_neighborhood' );
			}
			return $address;
		},
		20,
		2
	);
}

/**
 * CPF e celular no painel do pedido.
 *
 * @param WC_Order $order Pedido.
 */
function pm_br_admin_order_data( $order ) {
	if ( pm_has_brazilian_market() ) {
		return;
	}
	$cpf = $order->get_meta( '_billing_cpf' );
	if ( $cpf ) {
		printf( '<p><strong>%s:</strong> %s</p>', esc_html__( 'CPF', 'palestra-mantos' ), esc_html( $cpf ) );
	}
}
add_action( 'woocommerce_admin_order_data_after_billing_address', 'pm_br_admin_order_data' );

/* -------------------------------------------------------------------------
 * Etapas da compra
 * ---------------------------------------------------------------------- */

/**
 * Etapa atual: 1 carrinho, 2 entrega e pagamento, 3 confirmação.
 *
 * @return int
 */
function pm_checkout_step() {
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		return 3;
	}
	if ( is_checkout() ) {
		return 2;
	}
	if ( is_cart() ) {
		return 1;
	}
	return 0;
}

/**
 * Barra de etapas.
 */
function pm_checkout_steps() {
	$step = pm_checkout_step();
	if ( ! $step ) {
		return;
	}
	$steps = array(
		1 => __( 'Carrinho', 'palestra-mantos' ),
		2 => __( 'Entrega e pagamento', 'palestra-mantos' ),
		3 => __( 'Confirmação', 'palestra-mantos' ),
	);
	echo '<ol class="pm-steps" aria-label="' . esc_attr__( 'Etapas da compra', 'palestra-mantos' ) . '">';
	foreach ( $steps as $n => $label ) {
		$state = $n < $step ? 'is-done' : ( $n === $step ? 'is-current' : '' );
		printf(
			'<li class="%s"%s><span class="pm-steps__n">%s</span><span class="pm-steps__label">%s</span></li>',
			esc_attr( $state ),
			$n === $step ? ' aria-current="step"' : '',
			$n < $step ? '✓' : (int) $n,
			esc_html( $label )
		);
	}
	echo '</ol>';
}

/* -------------------------------------------------------------------------
 * Carrinho
 * ---------------------------------------------------------------------- */

/**
 * Barra "faltam R$ X para o frete grátis".
 */
function pm_free_shipping_bar() {
	$min = (float) pm_opt( 'free_shipping' );
	if ( $min <= 0 || ! WC()->cart ) {
		return;
	}
	$total = (float) WC()->cart->get_displayed_subtotal() - (float) WC()->cart->get_discount_total();
	$pct   = min( 100, max( 0, round( $total / $min * 100 ) ) );
	echo '<div class="pm-freebar">';
	if ( $total >= $min ) {
		echo '<p>' . pm_icon( 'truck' ) . ' <strong>' . esc_html__( 'Você ganhou frete grátis!', 'palestra-mantos' ) . '</strong></p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	} else {
		/* translators: %s: valor. */
		echo '<p>' . pm_icon( 'truck' ) . ' ' . wp_kses_post( sprintf( __( 'Faltam <strong>%s</strong> para ganhar <strong>frete grátis</strong>', 'palestra-mantos' ), wc_price( $min - $total ) ) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	printf( '<div class="pm-freebar__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="%1$d"><span style="width:%1$d%%"></span></div>', (int) $pct );
	echo '</div>';
}
add_action( 'woocommerce_before_cart_totals', 'pm_free_shipping_bar', 5 );
add_action( 'woocommerce_checkout_before_order_review', 'pm_free_shipping_bar', 5 );

/**
 * Linha "no Pix" nos totais do carrinho.
 */
function pm_cart_pix_row() {
	$pct = (int) pm_opt( 'pix_discount' );
	if ( $pct <= 0 || ! WC()->cart ) {
		return;
	}
	$total = (float) WC()->cart->get_total( 'edit' );
	if ( $total <= 0 ) {
		return;
	}
	$base = (float) WC()->cart->get_subtotal() - (float) WC()->cart->get_discount_total();
	printf(
		'<tr class="pm-pix-row"><th>%s</th><td data-title="%s">%s<small>%s</small></td></tr>',
		esc_html__( 'Pagando no Pix', 'palestra-mantos' ),
		esc_attr__( 'Pagando no Pix', 'palestra-mantos' ),
		wp_kses_post( wc_price( $total - round( $base * $pct / 100, 2 ) ) ),
		/* translators: %d: porcentagem. */
		esc_html( sprintf( __( '%d%% de desconto no checkout', 'palestra-mantos' ), $pct ) )
	);
}
add_action( 'woocommerce_cart_totals_after_order_total', 'pm_cart_pix_row' );

/**
 * Botão "Finalizar compra" + continuar comprando + selos.
 */
function pm_cart_proceed_button() {
	printf(
		'<a href="%s" class="checkout-button button alt wc-forward pm-checkout-btn">%s %s</a>',
		esc_url( wc_get_checkout_url() ),
		pm_icon( 'shield' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Finalizar compra', 'palestra-mantos' )
	);
	printf(
		'<a class="pm-continue" href="%s">%s</a>',
		esc_url( wc_get_page_permalink( 'shop' ) ),
		esc_html__( '← Continuar comprando', 'palestra-mantos' )
	);
}
remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
add_action( 'woocommerce_proceed_to_checkout', 'pm_cart_proceed_button', 20 );

/**
 * Selos de confiança.
 */
function pm_trust_badges() {
	?>
	<ul class="pm-trust">
		<li><?php pm_the_icon( 'shield' ); ?> <?php esc_html_e( 'Compra 100% segura', 'palestra-mantos' ); ?></li>
		<li><?php pm_the_icon( 'refresh' ); ?> <?php esc_html_e( 'Primeira troca grátis', 'palestra-mantos' ); ?></li>
		<li><?php pm_the_icon( 'truck' ); ?> <?php esc_html_e( 'Envio para todo o Brasil', 'palestra-mantos' ); ?></li>
	</ul>
	<?php
}
add_action( 'woocommerce_after_cart_totals', 'pm_trust_badges' );
add_action( 'woocommerce_review_order_after_payment', 'pm_trust_badges' );

/**
 * Carrinho vazio: atalhos para as categorias.
 */
function pm_empty_cart_links() {
	echo '<div class="pm-empty-cart">';
	echo '<p>' . esc_html__( 'Que tal começar por aqui?', 'palestra-mantos' ) . '</p><div class="pm-empty-cart__links">';
	foreach ( pm_category_slugs() as $slug => $label ) {
		printf( '<a class="pm-btn pm-btn--outline" href="%s">%s</a>', esc_url( pm_cat_url( $slug ) ), esc_html( $label ) );
	}
	echo '</div></div>';
}
add_action( 'woocommerce_cart_is_empty', 'pm_empty_cart_links', 20 );

/* -------------------------------------------------------------------------
 * Checkout
 * ---------------------------------------------------------------------- */

/**
 * Foto do produto no resumo do pedido.
 *
 * @param string $name      Nome.
 * @param array  $cart_item Item.
 * @return string
 */
function pm_checkout_item_thumb( $name, $cart_item ) {
	if ( ! is_checkout() || empty( $cart_item['data'] ) ) {
		return $name;
	}
	$thumb = $cart_item['data']->get_image( array( 56, 56 ) );
	return '<span class="pm-co-item">' . $thumb . '<span>' . $name . '</span></span>';
}
add_filter( 'woocommerce_cart_item_name', 'pm_checkout_item_thumb', 20, 2 );

/**
 * Títulos das colunas do checkout.
 */
function pm_checkout_heading() {
	echo '<p class="pm-co-secure">' . pm_icon( 'shield' ) . ' ' . esc_html__( 'Ambiente seguro. Seus dados são protegidos e usados só para entregar o seu pedido.', 'palestra-mantos' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'woocommerce_checkout_before_customer_details', 'pm_checkout_heading', 5 );

/**
 * Texto do botão final.
 *
 * @return string
 */
function pm_order_button_text() {
	return __( 'Finalizar pedido', 'palestra-mantos' );
}
add_filter( 'woocommerce_order_button_text', 'pm_order_button_text' );

/**
 * Página de confirmação: WhatsApp e continuar comprando.
 *
 * @param int $order_id Pedido.
 */
function pm_thankyou_extra( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	/* translators: %s: número do pedido. */
	$msg = sprintf( __( 'Olá! Acabei de fazer o pedido #%s no site da Palestra Mantos.', 'palestra-mantos' ), $order->get_order_number() );
	?>
	<div class="pm-thanks">
		<h2><?php esc_html_e( 'Obrigado por vestir o manto! 💚🤍', 'palestra-mantos' ); ?></h2>
		<p><?php esc_html_e( 'Você vai receber a confirmação e o código de rastreio por e-mail. Qualquer dúvida, fale com a gente.', 'palestra-mantos' ); ?></p>
		<div class="pm-thanks__cta">
			<a class="pm-btn pm-btn--whats" href="<?php echo esc_url( pm_whatsapp_url( $msg ) ); ?>" target="_blank" rel="noopener"><?php pm_the_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Falar no WhatsApp', 'palestra-mantos' ); ?></a>
			<a class="pm-btn pm-btn--outline" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Continuar comprando', 'palestra-mantos' ); ?></a>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_thankyou', 'pm_thankyou_extra', 5 );

/**
 * Dados para o JavaScript do checkout.
 */
function pm_checkout_script_data() {
	if ( ! ( is_checkout() || is_cart() ) ) {
		return;
	}
	wp_localize_script(
		'pm-theme',
		'pmCheckout',
		array(
			'viacep'  => 'https://viacep.com.br/ws/%s/json/',
			'msgCep'  => __( 'CEP não encontrado. Preencha o endereço manualmente.', 'palestra-mantos' ),
			'hasBrMk' => pm_has_brazilian_market(),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_checkout_script_data', 21 );

/**
 * Corpo da página com classe para layout largo.
 *
 * @param array $classes Classes.
 * @return array
 */
function pm_checkout_body_class( $classes ) {
	if ( pm_checkout_step() ) {
		$classes[] = 'pm-store-flow';
	}
	return $classes;
}
add_filter( 'body_class', 'pm_checkout_body_class' );

/**
 * Quando o frete grátis vale, esconde os fretes pagos (mantém retirada no local, se houver).
 *
 * @param array $rates Fretes.
 * @return array
 */
function pm_only_free_shipping( $rates ) {
	$free = array();
	foreach ( $rates as $id => $rate ) {
		if ( in_array( $rate->get_method_id(), array( 'free_shipping', 'local_pickup', 'pickup_location' ), true ) ) {
			$free[ $id ] = $rate;
		}
	}
	foreach ( $free as $rate ) {
		if ( 'free_shipping' === $rate->get_method_id() ) {
			return $free;
		}
	}
	return $rates;
}
add_filter( 'woocommerce_package_rates', 'pm_only_free_shipping', 100 );
