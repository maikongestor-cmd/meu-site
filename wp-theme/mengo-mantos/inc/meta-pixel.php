<?php
/**
 * Pixel da Meta + API de Conversões (CAPI).
 *
 * Eventos: PageView, ViewContent, Search, AddToCart, InitiateCheckout,
 * AddPaymentInfo, Purchase, Lead (newsletter), Contact (formulário e WhatsApp)
 * e CompleteRegistration. Cada evento sai pelo navegador e pelo servidor com o
 * mesmo event_id, para a Meta descartar a cópia (deduplicação).
 *
 * Configuração: Configurações → Meta Pixel (o token fica só no banco de dados).
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;

define( 'PM_FB_DEFAULT_PIXEL', '' );

/**
 * O plugin oficial "Meta for WooCommerce" está ativo? Nesse caso ele cuida do
 * pixel e da API de Conversões, e o tema fica quieto (senão cada evento conta 2x).
 *
 * @return bool
 */
function pm_fb_plugin_active() {
	return class_exists( 'WC_Facebookcommerce' ) || function_exists( 'facebook_for_woocommerce' );
}

/**
 * ID do pixel.
 *
 * @return string
 */
function pm_fb_pixel_id() {
	if ( pm_fb_plugin_active() ) {
		return '';
	}
	return preg_replace( '/\D+/', '', (string) get_option( 'pm_fb_pixel_id', PM_FB_DEFAULT_PIXEL ) );
}

/**
 * Token da API de Conversões.
 *
 * @return string
 */
function pm_fb_token() {
	return trim( (string) get_option( 'pm_fb_capi_token', '' ) );
}

/**
 * Versão da Graph API.
 *
 * @return string
 */
function pm_fb_api_version() {
	return (string) apply_filters( 'pm_fb_api_version', 'v24.0' );
}

/**
 * O pixel deve rodar nesta página? (Não para administradores logados, para não sujar os dados.)
 *
 * @return bool
 */
function pm_fb_enabled() {
	if ( '' === pm_fb_pixel_id() || is_admin() ) {
		return false;
	}
	return (bool) apply_filters( 'pm_fb_enabled', ! current_user_can( 'manage_options' ) );
}

/* -------------------------------------------------------------------------
 * Dados do cliente (hash SHA-256, como a Meta pede)
 * ---------------------------------------------------------------------- */

/**
 * Normaliza e aplica hash.
 *
 * @param string $value Valor.
 * @param string $type  Tipo (em, ph, fn, ln, ct, st, zp, country, external_id).
 * @return string
 */
function pm_fb_hash( $value, $type ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	switch ( $type ) {
		case 'em':
			$value = strtolower( $value );
			break;
		case 'ph':
			$value = preg_replace( '/\D+/', '', $value );
			if ( strlen( $value ) >= 10 && strlen( $value ) <= 11 ) {
				$value = '55' . ltrim( $value, '0' );
			}
			break;
		case 'zp':
			$value = preg_replace( '/\D+/', '', $value );
			break;
		case 'ct':
			$value = preg_replace( '/[^a-z]/', '', strtolower( remove_accents( $value ) ) );
			break;
		case 'fn':
		case 'ln':
			$value = mb_strtolower( $value, 'UTF-8' );
			break;
		default:
			$value = strtolower( $value );
	}
	return '' === $value ? '' : hash( 'sha256', $value );
}

/**
 * Cookie (sanitizado).
 *
 * @param string $name Nome.
 * @return string
 */
function pm_fb_cookie( $name ) {
	return isset( $_COOKIE[ $name ] ) ? sanitize_text_field( wp_unslash( $_COOKIE[ $name ] ) ) : '';
}

/**
 * IP do visitante.
 *
 * @return string
 */
function pm_fb_ip() {
	foreach ( array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $key ) {
		if ( ! empty( $_SERVER[ $key ] ) ) {
			$ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '';
}

/**
 * Monta o user_data do evento.
 *
 * @param WC_Order|null $order Pedido (Purchase).
 * @return array
 */
function pm_fb_user_data( $order = null ) {
	$data = array(
		'client_ip_address' => pm_fb_ip(),
		'client_user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
		'fbp'               => pm_fb_cookie( '_fbp' ),
		'fbc'               => pm_fb_cookie( '_fbc' ),
	);
	$eid = pm_fb_cookie( 'pm_eid' );

	$fields = array();
	if ( $order instanceof WC_Order ) {
		// Dados guardados no checkout valem mais que os desta requisição (ex.: Pix pago depois).
		foreach ( array( 'client_ip_address' => '_pm_fb_ip', 'client_user_agent' => '_pm_fb_ua', 'fbp' => '_pm_fb_fbp', 'fbc' => '_pm_fb_fbc' ) as $k => $meta ) {
			$saved = (string) $order->get_meta( $meta );
			if ( '' !== $saved ) {
				$data[ $k ] = $saved;
			}
		}
		$eid    = $order->get_meta( '_pm_fb_eid' ) ? (string) $order->get_meta( '_pm_fb_eid' ) : $eid;
		$fields = array(
			'em'      => $order->get_billing_email(),
			'ph'      => $order->get_billing_phone(),
			'fn'      => $order->get_billing_first_name(),
			'ln'      => $order->get_billing_last_name(),
			'ct'      => $order->get_billing_city(),
			'st'      => $order->get_billing_state(),
			'zp'      => $order->get_billing_postcode(),
			'country' => $order->get_billing_country() ? $order->get_billing_country() : 'BR',
		);
	} elseif ( function_exists( 'WC' ) && WC()->customer ) {
		$c      = WC()->customer;
		$fields = array(
			'em'      => $c->get_billing_email(),
			'ph'      => $c->get_billing_phone(),
			'fn'      => $c->get_billing_first_name(),
			'ln'      => $c->get_billing_last_name(),
			'ct'      => $c->get_billing_city(),
			'st'      => $c->get_billing_state(),
			'zp'      => $c->get_billing_postcode(),
			'country' => 'BR',
		);
	} elseif ( is_user_logged_in() ) {
		$fields = array(
			'em'      => wp_get_current_user()->user_email,
			'country' => 'BR',
		);
	} else {
		$fields = array( 'country' => 'BR' );
	}

	foreach ( $fields as $k => $v ) {
		$h = pm_fb_hash( $v, $k );
		if ( '' !== $h ) {
			$data[ $k ] = array( $h );
		}
	}
	if ( '' !== $eid ) {
		$data['external_id'] = array( pm_fb_hash( $eid, 'external_id' ) );
	}
	return array_filter( $data );
}

/* -------------------------------------------------------------------------
 * Envio para a API de Conversões
 * ---------------------------------------------------------------------- */

/**
 * Envia um evento para a Meta pelo servidor.
 *
 * @param string        $name     Nome do evento.
 * @param string        $event_id ID (o mesmo do navegador).
 * @param array         $custom   custom_data.
 * @param string        $url      URL da página.
 * @param WC_Order|null $order    Pedido.
 * @param bool          $blocking Esperar a resposta (teste).
 * @return array|WP_Error|null
 */
function pm_fb_capi( $name, $event_id, $custom = array(), $url = '', $order = null, $blocking = false ) {
	$pixel = pm_fb_pixel_id();
	$token = pm_fb_token();
	if ( '' === $pixel || '' === $token ) {
		return null;
	}
	$event = array(
		'event_name'       => $name,
		'event_time'       => time(),
		'event_id'         => $event_id,
		'action_source'    => 'website',
		'event_source_url' => $url ? $url : home_url( '/' ),
		'user_data'        => pm_fb_user_data( $order ),
	);
	if ( $custom ) {
		$event['custom_data'] = $custom;
	}
	$body = array(
		'data'         => array( $event ),
		'access_token' => $token,
	);
	$test = trim( (string) get_option( 'pm_fb_test_code', '' ) );
	if ( '' !== $test ) {
		$body['test_event_code'] = $test;
	}
	return wp_remote_post(
		'https://graph.facebook.com/' . pm_fb_api_version() . '/' . $pixel . '/events',
		array(
			'headers'  => array( 'Content-Type' => 'application/json' ),
			'body'     => wp_json_encode( $body ),
			'timeout'  => $blocking ? 15 : 3,
			'blocking' => $blocking,
		)
	);
}

/**
 * URL da página atual.
 *
 * @return string
 */
function pm_fb_current_url() {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	return home_url( $uri );
}

/* -------------------------------------------------------------------------
 * Dados dos eventos de loja
 * ---------------------------------------------------------------------- */

/**
 * custom_data de um produto.
 *
 * @param WC_Product $product Produto.
 * @param int        $qty     Quantidade.
 * @return array
 */
function pm_fb_product_data( $product, $qty = 1 ) {
	$parent = $product->get_parent_id() ? wc_get_product( $product->get_parent_id() ) : $product;
	$price  = (float) wc_get_price_to_display( $product );
	$cat    = '';
	$terms  = get_the_terms( $parent->get_id(), 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$cat = $terms[0]->name;
	}
	return array(
		'content_ids'      => array( (string) $parent->get_id() ),
		'content_type'     => 'product',
		'content_name'     => $parent->get_name(),
		'content_category' => $cat,
		'contents'         => array(
			array(
				'id'         => (string) $parent->get_id(),
				'quantity'   => (int) $qty,
				'item_price' => round( $price, 2 ),
			),
		),
		'value'            => round( $price * $qty, 2 ),
		'currency'         => get_woocommerce_currency(),
	);
}

/**
 * custom_data do carrinho.
 *
 * @return array
 */
function pm_fb_cart_data() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array();
	}
	$ids      = array();
	$contents = array();
	$num      = 0;
	foreach ( WC()->cart->get_cart() as $item ) {
		$pid        = (string) $item['product_id'];
		$ids[]      = $pid;
		$num       += (int) $item['quantity'];
		$contents[] = array(
			'id'         => $pid,
			'quantity'   => (int) $item['quantity'],
			'item_price' => $item['quantity'] ? round( ( $item['line_total'] + $item['line_tax'] ) / $item['quantity'], 2 ) : 0,
		);
	}
	return array(
		'content_ids'  => array_values( array_unique( $ids ) ),
		'content_type' => 'product',
		'contents'     => $contents,
		'num_items'    => $num,
		'value'        => round( (float) WC()->cart->get_total( 'edit' ), 2 ),
		'currency'     => get_woocommerce_currency(),
	);
}

/**
 * custom_data do pedido.
 *
 * @param WC_Order $order Pedido.
 * @return array
 */
function pm_fb_order_data( $order ) {
	$ids      = array();
	$contents = array();
	$num      = 0;
	foreach ( $order->get_items() as $item ) {
		$pid        = (string) $item->get_product_id();
		$qty        = (int) $item->get_quantity();
		$ids[]      = $pid;
		$num       += $qty;
		$contents[] = array(
			'id'         => $pid,
			'quantity'   => $qty,
			'item_price' => $qty ? round( ( $item->get_total() + $item->get_total_tax() ) / $qty, 2 ) : 0,
		);
	}
	return array(
		'content_ids'  => array_values( array_unique( $ids ) ),
		'content_type' => 'product',
		'contents'     => $contents,
		'num_items'    => $num,
		'value'        => round( (float) $order->get_total(), 2 ),
		'currency'     => $order->get_currency(),
		'order_id'     => (string) $order->get_order_number(),
	);
}

/* -------------------------------------------------------------------------
 * Fila: eventos gerados no servidor que o navegador também precisa disparar
 * ---------------------------------------------------------------------- */

/**
 * Registra um evento: envia pela API agora e guarda para o navegador.
 *
 * @param string $name   Evento.
 * @param array  $custom custom_data.
 * @param string $url    URL.
 */
function pm_fb_queue( $name, $custom = array(), $url = '' ) {
	if ( '' === pm_fb_pixel_id() || ( current_user_can( 'manage_options' ) && ! apply_filters( 'pm_fb_enabled', false ) ) ) {
		return;
	}
	$id = strtolower( $name ) . '.' . wp_generate_uuid4();
	pm_fb_capi( $name, $id, $custom, $url ? $url : pm_fb_current_url() );
	if ( function_exists( 'WC' ) && WC()->session ) {
		$q   = (array) WC()->session->get( 'pm_fb_q', array() );
		$q[] = array(
			'n' => $name,
			'i' => $id,
			'd' => $custom,
		);
		WC()->session->set( 'pm_fb_q', array_slice( $q, -10 ) );
	}
}

/**
 * Retira os eventos da fila.
 *
 * @return array
 */
function pm_fb_take_queue() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return array();
	}
	$q = (array) WC()->session->get( 'pm_fb_q', array() );
	if ( $q ) {
		WC()->session->set( 'pm_fb_q', array() );
	}
	return $q;
}

/* -------------------------------------------------------------------------
 * Navegador
 * ---------------------------------------------------------------------- */

/**
 * Código base do pixel + dados da página.
 */
function pm_fb_head() {
	if ( ! pm_fb_enabled() ) {
		return;
	}
	$pixel  = pm_fb_pixel_id();
	$page   = array();
	$events = array();

	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$page['view'] = pm_fb_product_data( $product );
		}
	}
	if ( is_search() ) {
		$page['search'] = get_search_query( false );
	}

	// Páginas sem cache (carrinho, checkout, conta): eventos do servidor + dados para correspondência avançada.
	$private = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
	if ( $private ) {
		if ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) && WC()->cart && ! WC()->cart->is_empty() && 'GET' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : 'GET' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			pm_fb_queue( 'InitiateCheckout', pm_fb_cart_data() );
		}
		$events = pm_fb_take_queue();
	}

	$config = array(
		'pixel'  => $pixel,
		'beacon' => class_exists( 'WC_AJAX' ) ? WC_AJAX::get_endpoint( 'pm_fb' ) : admin_url( 'admin-ajax.php?action=pm_fb' ),
		'page'   => $page,
		'events' => $events,
	);
	?>
<!-- Meta Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
window.pmFb=<?php echo wp_json_encode( $config ); ?>;
(function(){var m=document.cookie.match(/(?:^|; )pm_eid=([^;]+)/),id=m?m[1]:'';if(!id){id=(Date.now().toString(36)+Math.random().toString(36).slice(2,10));document.cookie='pm_eid='+id+';path=/;max-age=63072000;SameSite=Lax';}window.pmFb.eid=id;fbq('init','<?php echo esc_js( $pixel ); ?>',{external_id:id});})();
</script>
<noscript><img height="1" width="1" style="display:none" alt="" src="https://www.facebook.com/tr?id=<?php echo esc_attr( $pixel ); ?>&ev=PageView&noscript=1"></noscript>
<!-- /Meta Pixel -->
	<?php
}
add_action( 'wp_head', 'pm_fb_head', 3 );

/**
 * Script dos eventos.
 */
function pm_fb_script() {
	if ( ! pm_fb_enabled() ) {
		return;
	}
	wp_enqueue_script(
		'pm-fb',
		PM_URI . '/assets/js/meta-pixel.js',
		array(),
		PM_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_fb_script' );

/* -------------------------------------------------------------------------
 * Eventos do servidor
 * ---------------------------------------------------------------------- */

/**
 * AddToCart.
 *
 * @param string $key          Chave do item.
 * @param int    $product_id   Produto.
 * @param int    $quantity     Quantidade.
 * @param int    $variation_id Variação.
 */
function pm_fb_add_to_cart( $key, $product_id, $quantity, $variation_id = 0 ) {
	$product = wc_get_product( $variation_id ? $variation_id : $product_id );
	if ( ! $product ) {
		return;
	}
	$url = wp_get_referer();
	pm_fb_queue( 'AddToCart', pm_fb_product_data( $product, max( 1, (int) $quantity ) ), $url ? $url : pm_fb_current_url() );
}
add_action( 'woocommerce_add_to_cart', 'pm_fb_add_to_cart', 20, 4 );

/**
 * Eventos pendentes vão junto da resposta AJAX do carrinho.
 *
 * @param array $fragments Fragmentos.
 * @return array
 */
function pm_fb_fragments( $fragments ) {
	$q = pm_fb_take_queue();
	if ( $q ) {
		$fragments['pm_fb_events'] = $q;
	}
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'pm_fb_fragments', 99 );

/**
 * Eventos pendentes em páginas comuns (ex.: depois de "Comprar" sem AJAX).
 */
function pm_fb_footer_queue() {
	if ( ! pm_fb_enabled() || ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) ) {
		return;
	}
	$q = pm_fb_take_queue();
	if ( $q ) {
		echo '<script>window.pmFbLate=' . wp_json_encode( $q ) . ';</script>' . "\n";
	}
}
add_action( 'wp_footer', 'pm_fb_footer_queue', 5 );

/**
 * CompleteRegistration.
 */
function pm_fb_registration() {
	pm_fb_queue( 'CompleteRegistration', array( 'status' => 'registered' ) );
}
add_action( 'woocommerce_created_customer', 'pm_fb_registration' );

/**
 * Guarda no pedido os dados do navegador (para o Purchase enviado depois, ex.: Pix).
 *
 * @param int|WC_Order $order Pedido.
 */
function pm_fb_store_order_meta( $order ) {
	$order = $order instanceof WC_Order ? $order : wc_get_order( $order );
	if ( ! $order ) {
		return;
	}
	$ud = pm_fb_user_data();
	$order->update_meta_data( '_pm_fb_ip', isset( $ud['client_ip_address'] ) ? $ud['client_ip_address'] : '' );
	$order->update_meta_data( '_pm_fb_ua', isset( $ud['client_user_agent'] ) ? $ud['client_user_agent'] : '' );
	$order->update_meta_data( '_pm_fb_fbp', pm_fb_cookie( '_fbp' ) );
	$order->update_meta_data( '_pm_fb_fbc', pm_fb_cookie( '_fbc' ) );
	$order->update_meta_data( '_pm_fb_eid', pm_fb_cookie( 'pm_eid' ) );
	$order->update_meta_data( '_pm_fb_url', wc_get_checkout_url() );
	$order->save();
}
add_action( 'woocommerce_checkout_order_processed', 'pm_fb_store_order_meta', 5 );
add_action( 'woocommerce_store_api_checkout_order_processed', 'pm_fb_store_order_meta', 5 );

/**
 * Envia o Purchase pela API uma única vez.
 *
 * @param WC_Order $order Pedido.
 */
function pm_fb_send_purchase( $order ) {
	if ( ! $order || $order->get_meta( '_pm_fb_purchase_sent' ) ) {
		return;
	}
	$url = (string) $order->get_meta( '_pm_fb_url' );
	pm_fb_capi( 'Purchase', 'purchase.' . $order->get_id(), pm_fb_order_data( $order ), $url ? $url : $order->get_checkout_order_received_url(), $order );
	if ( '' !== pm_fb_token() ) {
		$order->update_meta_data( '_pm_fb_purchase_sent', time() );
		$order->save();
	}
}

/**
 * Pedido pago (cartão na hora, Pix quando o pagamento cai).
 *
 * @param int $order_id Pedido.
 */
function pm_fb_order_paid( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( $order && $order->is_paid() ) {
		pm_fb_send_purchase( $order );
	}
}
add_action( 'woocommerce_payment_complete', 'pm_fb_order_paid' );
add_action( 'woocommerce_order_status_processing', 'pm_fb_order_paid' );
add_action( 'woocommerce_order_status_completed', 'pm_fb_order_paid' );

/**
 * Página "Pedido recebido": Purchase no navegador (só pedidos pagos; Pix pendente vai pelo servidor quando pagar).
 *
 * @param int $order_id Pedido.
 */
function pm_fb_thankyou( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || ! pm_fb_enabled() ) {
		return;
	}
	if ( ! $order->is_paid() ) {
		return;
	}
	pm_fb_send_purchase( $order );
	printf(
		'<script>window.pmFbLate=(window.pmFbLate||[]).concat([%s]);</script>',
		wp_json_encode(
			array(
				'n' => 'Purchase',
				'i' => 'purchase.' . $order->get_id(),
				'd' => pm_fb_order_data( $order ),
			)
		)
	);
}
add_action( 'woocommerce_thankyou', 'pm_fb_thankyou', 5 );

/* -------------------------------------------------------------------------
 * Beacon: eventos que nascem no navegador (páginas com cache)
 * ---------------------------------------------------------------------- */

/**
 * Recebe o evento do navegador e repete pela API.
 */
function pm_fb_beacon() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- evento público de navegação; dados de valor são recalculados no servidor.
	$name = isset( $_POST['n'] ) ? sanitize_text_field( wp_unslash( $_POST['n'] ) ) : '';
	$id   = isset( $_POST['i'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['i'] ) ), 0, 80 ) : '';
	$url  = isset( $_POST['u'] ) ? esc_url_raw( wp_unslash( $_POST['u'] ) ) : '';
	$pid  = isset( $_POST['p'] ) ? absint( $_POST['p'] ) : 0;
	$q    = isset( $_POST['q'] ) ? substr( sanitize_text_field( wp_unslash( $_POST['q'] ) ), 0, 100 ) : '';
	// phpcs:enable

	$allowed = array( 'PageView', 'ViewContent', 'Search', 'AddPaymentInfo', 'Contact', 'Lead' );
	if ( ! in_array( $name, $allowed, true ) || '' === $id || '' === pm_fb_pixel_id() || current_user_can( 'manage_options' ) ) {
		wp_die( '', '', array( 'response' => 204 ) );
	}
	if ( $url && wp_parse_url( $url, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		$url = home_url( '/' );
	}

	// Limite por IP contra abuso.
	$key = 'pm_fb_' . md5( pm_fb_ip() );
	$hit = (int) get_transient( $key );
	if ( $hit > 120 ) {
		wp_die( '', '', array( 'response' => 204 ) );
	}
	set_transient( $key, $hit + 1, 10 * MINUTE_IN_SECONDS );

	$custom = array();
	if ( in_array( $name, array( 'ViewContent', 'AddPaymentInfo' ), true ) && ! function_exists( 'wc_get_product' ) ) {
		wp_die( '', '', array( 'response' => 204 ) );
	}
	if ( 'ViewContent' === $name && $pid ) {
		$product = wc_get_product( $pid );
		if ( ! $product ) {
			wp_die( '', '', array( 'response' => 204 ) );
		}
		$custom = pm_fb_product_data( $product );
	} elseif ( 'Search' === $name ) {
		$custom = array( 'search_string' => $q );
	} elseif ( 'AddPaymentInfo' === $name ) {
		$custom = pm_fb_cart_data();
	}

	pm_fb_capi( $name, $id, $custom, $url );
	wp_die( '', '', array( 'response' => 204 ) );
}
add_action( 'wc_ajax_pm_fb', 'pm_fb_beacon' );
add_action( 'wp_ajax_pm_fb', 'pm_fb_beacon' );
add_action( 'wp_ajax_nopriv_pm_fb', 'pm_fb_beacon' );

/* -------------------------------------------------------------------------
 * Painel: Configurações → Meta Pixel
 * ---------------------------------------------------------------------- */

/**
 * Menu.
 */
function pm_fb_admin_menu() {
	add_options_page( __( 'Meta Pixel', 'palestra-mantos' ), __( 'Meta Pixel', 'palestra-mantos' ), 'manage_options', 'pm-meta-pixel', 'pm_fb_admin_page' );
}
add_action( 'admin_menu', 'pm_fb_admin_menu' );

/**
 * Salva as configurações.
 */
function pm_fb_admin_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'palestra-mantos' ) );
	}
	check_admin_referer( 'pm_fb_save' );
	$msg = 'saved';

	$pixel = isset( $_POST['pm_fb_pixel_id'] ) ? preg_replace( '/\D+/', '', sanitize_text_field( wp_unslash( $_POST['pm_fb_pixel_id'] ) ) ) : '';
	update_option( 'pm_fb_pixel_id', $pixel, true );
	update_option( 'pm_fb_test_code', isset( $_POST['pm_fb_test_code'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_fb_test_code'] ) ) : '', false );

	$token = isset( $_POST['pm_fb_capi_token'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['pm_fb_capi_token'] ) ) ) : '';
	if ( '' !== $token ) {
		update_option( 'pm_fb_capi_token', $token, false );
	}
	if ( ! empty( $_POST['pm_fb_remove_token'] ) ) {
		delete_option( 'pm_fb_capi_token' );
	}

	if ( ! empty( $_POST['pm_fb_test'] ) ) {
		$res = pm_fb_capi( 'PageView', 'teste.' . wp_generate_uuid4(), array(), home_url( '/' ), null, true );
		if ( null === $res ) {
			$msg = 'notoken';
		} elseif ( is_wp_error( $res ) ) {
			set_transient( 'pm_fb_test_result', $res->get_error_message(), 300 );
			$msg = 'testfail';
		} else {
			$code = (int) wp_remote_retrieve_response_code( $res );
			$body = json_decode( wp_remote_retrieve_body( $res ), true );
			if ( 200 === $code ) {
				$msg = 'testok';
			} else {
				set_transient( 'pm_fb_test_result', isset( $body['error']['message'] ) ? $body['error']['message'] : 'HTTP ' . $code, 300 );
				$msg = 'testfail';
			}
		}
	}

	wp_safe_redirect( add_query_arg( 'pm_fb', $msg, admin_url( 'options-general.php?page=pm-meta-pixel' ) ) );
	exit;
}
add_action( 'admin_post_pm_fb_save', 'pm_fb_admin_save' );

/**
 * Tela de configuração.
 */
function pm_fb_admin_page() {
	$msg       = isset( $_GET['pm_fb'] ) ? sanitize_key( wp_unslash( $_GET['pm_fb'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$has_token = '' !== pm_fb_token();
	$notices   = array(
		'saved'    => array( 'success', __( 'Configurações salvas.', 'palestra-mantos' ) ),
		'testok'   => array( 'success', __( 'Conexão OK! A Meta recebeu o evento de teste. Se você preencheu o código de teste, ele aparece em Gerenciador de Eventos → Eventos de teste.', 'palestra-mantos' ) ),
		'notoken'  => array( 'warning', __( 'Cole o token da API de Conversões para testar.', 'palestra-mantos' ) ),
		'testfail' => array( 'error', __( 'A Meta recusou o teste: ', 'palestra-mantos' ) . (string) get_transient( 'pm_fb_test_result' ) ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Meta Pixel e API de Conversões', 'palestra-mantos' ); ?></h1>
		<?php if ( pm_fb_plugin_active() ) : ?>
			<div class="notice notice-info"><p><strong><?php esc_html_e( 'O plugin "Meta for WooCommerce" está ativo: ele está cuidando do pixel e da API de Conversões, e o pixel do tema foi desligado para não contar os eventos em dobro.', 'palestra-mantos' ); ?></strong></p></div>
		<?php endif; ?>
		<?php if ( isset( $notices[ $msg ] ) ) : ?>
			<div class="notice notice-<?php echo esc_attr( $notices[ $msg ][0] ); ?>"><p><?php echo esc_html( $notices[ $msg ][1] ); ?></p></div>
		<?php endif; ?>
		<p><?php esc_html_e( 'Eventos enviados: PageView, ViewContent, Search, AddToCart, InitiateCheckout, AddPaymentInfo, Purchase, Lead (newsletter), Contact (formulário e WhatsApp) e CompleteRegistration. Cada um vai pelo navegador e pelo servidor com o mesmo ID, e a Meta descarta a cópia.', 'palestra-mantos' ); ?></p>
		<p><?php esc_html_e( 'Administradores logados não são rastreados, para não misturar seus testes com as vendas.', 'palestra-mantos' ); ?></p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="pm_fb_save">
			<?php wp_nonce_field( 'pm_fb_save' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="pm_fb_pixel_id"><?php esc_html_e( 'ID do pixel', 'palestra-mantos' ); ?></label></th>
					<td><input name="pm_fb_pixel_id" id="pm_fb_pixel_id" type="text" class="regular-text" value="<?php echo esc_attr( preg_replace( '/\D+/', '', (string) get_option( 'pm_fb_pixel_id', PM_FB_DEFAULT_PIXEL ) ) ); ?>"></td>
				</tr>
				<tr>
					<th scope="row"><label for="pm_fb_capi_token"><?php esc_html_e( 'Token da API de Conversões', 'palestra-mantos' ); ?></label></th>
					<td>
						<input name="pm_fb_capi_token" id="pm_fb_capi_token" type="password" class="regular-text" autocomplete="new-password" placeholder="<?php echo esc_attr( $has_token ? __( '•••••••• (salvo — deixe em branco para manter)', 'palestra-mantos' ) : __( 'Cole aqui o token', 'palestra-mantos' ) ); ?>">
						<p class="description"><?php esc_html_e( 'Gerenciador de Eventos → seu pixel → Configurações → API de Conversões → Gerar token de acesso.', 'palestra-mantos' ); ?></p>
						<?php if ( $has_token ) : ?>
							<p><strong style="color:#c8102e"><?php esc_html_e( '✓ Token salvo.', 'palestra-mantos' ); ?></strong> <label><input type="checkbox" name="pm_fb_remove_token" value="1"> <?php esc_html_e( 'Remover token', 'palestra-mantos' ); ?></label></p>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="pm_fb_test_code"><?php esc_html_e( 'Código de teste (opcional)', 'palestra-mantos' ); ?></label></th>
					<td>
						<input name="pm_fb_test_code" id="pm_fb_test_code" type="text" class="regular-text" value="<?php echo esc_attr( get_option( 'pm_fb_test_code', '' ) ); ?>" placeholder="TEST12345">
						<p class="description"><?php esc_html_e( 'Use só durante os testes (Gerenciador de Eventos → Eventos de teste) e apague depois: com ele preenchido, os eventos do servidor não contam como reais.', 'palestra-mantos' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Salvar', 'palestra-mantos' ); ?></button>
				<button type="submit" name="pm_fb_test" value="1" class="button"><?php esc_html_e( 'Salvar e testar conexão', 'palestra-mantos' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}
