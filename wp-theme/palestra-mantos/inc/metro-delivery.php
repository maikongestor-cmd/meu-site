<?php
/**
 * Retirada em estação de metrô/trem (Grande São Paulo) com pagamento na entrega.
 *
 * Na página do produto o cliente escolhe linha, estação, data e horário, informa nome e WhatsApp
 * e clica em "Reservar e combinar no WhatsApp". O site cria a reserva (um pedido "Aguardando"),
 * trava o horário para mais ninguém e abre o WhatsApp da loja com a mensagem pronta.
 *
 * Regras:
 * - 1 reserva por horário, somando todas as estações (um entregador só).
 * - Reserva não confirmada expira sozinha depois de X horas e libera o horário.
 * - Pagamento na entrega, sem desconto do Pix; retirada sem frete.
 *
 * Painel: WooCommerce → Entrega no metrô (agenda + configurações).
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Configuração
 * ---------------------------------------------------------------------- */

/**
 * Lista padrão em texto: "número | nome | cor | estação; estação; ...".
 *
 * @return string
 */
function pm_metro_default_lines_text() {
	$lines = array(
		'1 | Azul | #0455A1 | Tucuruvi; Parada Inglesa; Jardim São Paulo-Ayrton Senna; Santana; Carandiru; Portuguesa-Tietê; Armênia; Tiradentes; Luz; São Bento; Sé; Japão-Liberdade; São Joaquim; Vergueiro; Paraíso; Ana Rosa; Vila Mariana; Santa Cruz; Praça da Árvore; Saúde; São Judas; Conceição; Jabaquara',
		'2 | Verde | #007E5E | Vila Madalena; Sumaré; Clínicas; Consolação; Trianon-Masp; Brigadeiro; Paraíso; Ana Rosa; Chácara Klabin; Santos-Imigrantes; Alto do Ipiranga; Sacomã; Tamanduateí; Vila Prudente',
		'3 | Vermelha | #EE372F | Palmeiras-Barra Funda; Marechal Deodoro; Santa Cecília; República; Anhangabaú; Sé; Pedro II; Brás; Bresser-Mooca; Belém; Tatuapé; Carrão; Penha; Vila Matilde; Guilhermina-Esperança; Patriarca-Vila Ré; Artur Alvim; Corinthians-Itaquera',
		'4 | Amarela | #FFC800 | Luz; República; Higienópolis-Mackenzie; Paulista; Oscar Freire; Fradique Coutinho; Faria Lima; Pinheiros; Butantã; São Paulo-Morumbi; Vila Sônia; Taboão da Serra',
		'5 | Lilás | #9B3894 | Capão Redondo; Campo Limpo; Vila das Belezas; Giovanni Gronchi; Santo Amaro; Largo Treze; Adolfo Pinheiro; Alto da Boa Vista; Borba Gato; Brooklin; Campo Belo; Eucaliptos; Moema; AACD-Servidor; Hospital São Paulo; Santa Cruz; Chácara Klabin',
		'7 | Rubi | #CA016B | Palmeiras-Barra Funda; Água Branca; Lapa; Piqueri; Pirituba; Vila Clarice; Jaraguá; Vila Aurora; Perus; Caieiras; Franco da Rocha; Baltazar Fidélis; Francisco Morato; Botujuru; Campo Limpo Paulista; Várzea Paulista; Jundiaí',
		'8 | Diamante | #97A098 | Júlio Prestes; Palmeiras-Barra Funda; Lapa; Domingos de Moraes; Imperatriz Leopoldina; Presidente Altino; Osasco; Comandante Sampaio; Quitaúna; General Miguel Costa; Carapicuíba; Santa Terezinha; Antônio João; Barueri; Jardim Belval; Jardim Silveira; Jandira; Sagrado Coração; Engenheiro Cardoso; Itapevi; Santa Rita; Ambuitá; Amador Bueno',
		'9 | Esmeralda | #01A9A7 | Osasco; Presidente Altino; Ceasa; Villa-Lobos-Jaguaré; Cidade Universitária; Pinheiros; Hebraica-Rebouças; Cidade Jardim; Vila Olímpia; Berrini; Morumbi; Granja Julieta; João Dias; Santo Amaro; Socorro; Jurubatuba; Autódromo; Primavera-Interlagos; Grajaú; Mendes-Vila Natal; Varginha',
		'10 | Turquesa | #008B8B | Brás; Juventus-Mooca; Ipiranga; Tamanduateí; São Caetano do Sul; Utinga; Prefeito Saladino; Santo André; Capuava; Mauá; Guapituba; Ribeirão Pires; Rio Grande da Serra',
		'11 | Coral | #F04E23 | Palmeiras-Barra Funda; Luz; Brás; Tatuapé; Corinthians-Itaquera; Dom Bosco; José Bonifácio; Guaianases; Antônio Gianetti Neto; Ferraz de Vasconcelos; Poá; Calmon Viana; Suzano; Jundiapeba; Braz Cubas; Mogi das Cruzes; Estudantes',
		'12 | Safira | #083E89 | Brás; Tatuapé; Engenheiro Goulart; USP Leste; Comendador Ermelino; São Miguel Paulista; Jardim Helena-Vila Mara; Itaim Paulista; Jardim Romano; Engenheiro Manoel Feio; Itaquaquecetuba; Aracaré; Calmon Viana',
		'13 | Jade | #00B352 | Engenheiro Goulart; Guarulhos-Cecap; Aeroporto-Guarulhos',
		'15 | Prata | #868D91 | Vila Prudente; Oratório; São Lucas; Camilo Haddad; Vila Tolstói; Vila União; Jardim Planalto; Sapopemba; Fazenda da Juta; São Mateus; Jardim Colonial',
		'17 | Ouro | #B8964A | Morumbi; Chucri Zaidan; Vila Cordeiro; Campo Belo; Vereador José Diniz; Brooklin Paulista; Aeroporto de Congonhas',
	);
	return implode( "\n", $lines );
}

/**
 * Configurações com padrões.
 *
 * @return array
 */
function pm_metro_settings() {
	$defaults = array(
		'enabled'   => 1,
		'whatsapp'  => '',
		'payment'   => 'Dinheiro, Pix ou cartão',
		'meeting'   => 'Na saída principal da estação, perto das catracas.',
		'days'      => array( 1, 2, 3, 4, 5, 6 ),
		'slots'     => "10:00 às 11:00\n11:00 às 12:00\n13:00 às 14:00\n14:00 às 15:00\n15:00 às 16:00",
		'lead'      => 1,
		'cutoff'    => 18,
		'horizon'   => 30,
		'expiry'    => 3,
		'blocked'   => '',
		'lines'     => pm_metro_default_lines_text(),
		'off_lines' => array(),
		'rules'     => 'Tolerância de 15 minutos. Se não puder ir, avise pelo WhatsApp para remarcar.',
	);
	$saved = get_option( 'pm_metro', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

/**
 * Linhas interpretadas.
 *
 * @param bool $only_on Só as linhas atendidas.
 * @return array id => array( name, color, stations[] )
 */
function pm_metro_lines( $only_on = true ) {
	$s     = pm_metro_settings();
	$off   = array_map( 'strval', (array) $s['off_lines'] );
	$lines = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $s['lines'] ) as $row ) {
		$parts = array_map( 'trim', explode( '|', $row ) );
		if ( count( $parts ) < 4 || '' === $parts[0] ) {
			continue;
		}
		$id = sanitize_key( $parts[0] );
		if ( $only_on && in_array( $id, $off, true ) ) {
			continue;
		}
		$stations = array_values( array_filter( array_map( 'trim', explode( ';', $parts[3] ) ) ) );
		if ( ! $stations ) {
			continue;
		}
		$color        = sanitize_hex_color( $parts[2] );
		$lines[ $id ] = array(
			'name'     => $parts[1],
			'color'    => $color ? $color : '#666666',
			'stations' => $stations,
		);
	}
	return $lines;
}

/**
 * Horários.
 *
 * @return string[]
 */
function pm_metro_slots() {
	return array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) pm_metro_settings()['slots'] ) ) ) );
}

/**
 * Está ligada?
 *
 * @return bool
 */
function pm_metro_enabled() {
	$s = pm_metro_settings();
	return ! empty( $s['enabled'] ) && pm_metro_lines() && pm_metro_slots();
}

/**
 * Datas disponíveis (Y-m-d): antecedência, horário limite, dias da semana e bloqueios.
 *
 * @return string[]
 */
function pm_metro_dates() {
	$s    = pm_metro_settings();
	$now  = new DateTimeImmutable( 'now', wp_timezone() );
	$lead = max( 0, (int) $s['lead'] );
	if ( (int) $now->format( 'G' ) >= (int) $s['cutoff'] ) {
		++$lead;
	}
	$blocked = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $s['blocked'] ) as $b ) {
		if ( preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', trim( $b ), $m ) ) {
			$blocked[] = sprintf( '%04d-%02d-%02d', $m[3], $m[2], $m[1] );
		}
	}
	$days  = array_map( 'intval', (array) $s['days'] );
	$out   = array();
	$start = $now->setTime( 0, 0 )->modify( '+' . $lead . ' day' );
	for ( $i = 0; $i <= max( 1, (int) $s['horizon'] ); $i++ ) {
		$d = $start->modify( '+' . $i . ' day' );
		if ( in_array( (int) $d->format( 'w' ), $days, true ) && ! in_array( $d->format( 'Y-m-d' ), $blocked, true ) ) {
			$out[] = $d->format( 'Y-m-d' );
		}
	}
	return $out;
}

/**
 * Data curta (ex.: "sex, 21/11").
 *
 * @param string $ymd Data.
 * @return string
 */
function pm_metro_date_label( $ymd ) {
	$d = DateTimeImmutable::createFromFormat( 'Y-m-d', (string) $ymd, wp_timezone() );
	if ( ! $d ) {
		return (string) $ymd;
	}
	$wk = array( 'dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb' );
	return $wk[ (int) $d->format( 'w' ) ] . ', ' . $d->format( 'd/m' );
}

/**
 * Número de WhatsApp que recebe as reservas.
 *
 * @return string
 */
function pm_metro_whatsapp() {
	$n = preg_replace( '/\D+/', '', (string) pm_metro_settings()['whatsapp'] );
	return $n ? $n : preg_replace( '/\D+/', '', (string) pm_opt( 'whatsapp' ) );
}

/* -------------------------------------------------------------------------
 * Reservas (pedidos com retirada no metrô)
 * ---------------------------------------------------------------------- */

/**
 * Reservas recentes com retirada no metrô (qualquer armazenamento de pedidos).
 *
 * @param bool $future_only Só de hoje em diante.
 * @return WC_Order[]
 */
function pm_metro_reservations( $future_only = true ) {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return array();
	}
	$orders = wc_get_orders(
		array(
			'limit'        => 500,
			'status'       => array( 'wc-on-hold', 'wc-processing', 'wc-completed' ),
			'date_created' => '>' . ( time() - 90 * DAY_IN_SECONDS ),
			'orderby'      => 'date',
			'order'        => 'DESC',
		)
	);
	$today = wp_date( 'Y-m-d' );
	return array_values(
		array_filter(
			$orders,
			function ( $o ) use ( $today, $future_only ) {
				$d = (string) $o->get_meta( '_pm_metro_date' );
				return '' !== $d && ( ! $future_only || $d >= $today );
			}
		)
	);
}

/**
 * Reserva ainda aguardando a sua confirmação?
 *
 * @param WC_Order $o Pedido.
 * @return bool
 */
function pm_metro_is_pending( $o ) {
	return $o->has_status( 'on-hold' ) && 'confirmed' !== $o->get_meta( '_pm_metro_state' );
}

/**
 * Cancela reservas não confirmadas que passaram do prazo (libera o horário).
 */
function pm_metro_expire() {
	$hours = (int) pm_metro_settings()['expiry'];
	if ( $hours <= 0 ) {
		return;
	}
	foreach ( pm_metro_reservations( false ) as $o ) {
		$created = $o->get_date_created();
		if ( pm_metro_is_pending( $o ) && $created && $created->getTimestamp() < time() - $hours * HOUR_IN_SECONDS ) {
			/* translators: %d: horas. */
			$o->update_status( 'cancelled', sprintf( __( 'Reserva no metrô não confirmada em %d h: cancelada e horário liberado.', 'palestra-mantos' ), $hours ) );
		}
	}
}

/**
 * Horários ocupados ("data|horário" => número do pedido). Um por horário, todas as estações.
 *
 * @param bool $fresh Ignora o cache.
 * @return array
 */
function pm_metro_taken( $fresh = false ) {
	$cached = $fresh ? false : get_transient( 'pm_metro_taken' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	pm_metro_expire();
	$taken = array();
	foreach ( pm_metro_reservations() as $o ) {
		$taken[ $o->get_meta( '_pm_metro_date' ) . '|' . $o->get_meta( '_pm_metro_slot' ) ] = $o->get_id();
	}
	set_transient( 'pm_metro_taken', $taken, 2 * MINUTE_IN_SECONDS );
	return $taken;
}

/**
 * Limpa o cache de horários ocupados.
 */
function pm_metro_flush() {
	delete_transient( 'pm_metro_taken' );
}
add_action( 'woocommerce_order_status_changed', 'pm_metro_flush' );

/* -------------------------------------------------------------------------
 * Página do produto
 * ---------------------------------------------------------------------- */

/**
 * Bloco "Como você quer receber?" dentro do formulário de compra.
 */
function pm_metro_product_block() {
	global $product;
	if ( ! pm_metro_enabled() || ! $product || ! $product->needs_shipping() ) {
		return;
	}
	$s = pm_metro_settings();
	?>
	<div class="pm-metro" data-pm-metro>
		<p class="pm-metro__title"><?php esc_html_e( 'Como você quer receber?', 'palestra-mantos' ); ?></p>
		<div class="pm-metro__modes" role="radiogroup" aria-label="<?php esc_attr_e( 'Forma de entrega', 'palestra-mantos' ); ?>">
			<label class="pm-metro__mode">
				<input type="radio" name="pm_entrega" value="casa" checked>
				<span><b><?php esc_html_e( 'Receber em casa', 'palestra-mantos' ); ?></b><small><?php esc_html_e( 'Correios · pague online', 'palestra-mantos' ); ?></small></span>
			</label>
			<label class="pm-metro__mode">
				<input type="radio" name="pm_entrega" value="metro">
				<span><b><?php esc_html_e( 'Retirar no metrô/trem', 'palestra-mantos' ); ?></b><small><?php esc_html_e( 'Grande SP · sem frete · pague na entrega', 'palestra-mantos' ); ?></small></span>
			</label>
		</div>

		<div class="pm-metro__panel" hidden>
			<div class="pm-metro__step">
				<span class="pm-metro__label"><b>1</b><?php esc_html_e( 'Linha', 'palestra-mantos' ); ?></span>
				<div class="pm-metro__lines" role="group" aria-label="<?php esc_attr_e( 'Linha', 'palestra-mantos' ); ?>"></div>
			</div>
			<div class="pm-metro__step">
				<label class="pm-metro__label" for="pm-metro-station"><b>2</b><?php esc_html_e( 'Estação', 'palestra-mantos' ); ?></label>
				<select id="pm-metro-station" class="pm-metro__station" disabled><option value=""><?php esc_html_e( 'Escolha a linha primeiro', 'palestra-mantos' ); ?></option></select>
			</div>
			<div class="pm-metro__step">
				<span class="pm-metro__label"><b>3</b><?php esc_html_e( 'Data', 'palestra-mantos' ); ?></span>
				<div class="pm-metro__cal" aria-live="polite"></div>
			</div>
			<div class="pm-metro__step">
				<span class="pm-metro__label"><b>4</b><?php esc_html_e( 'Horário', 'palestra-mantos' ); ?></span>
				<div class="pm-metro__slots" role="group" aria-label="<?php esc_attr_e( 'Horário', 'palestra-mantos' ); ?>"><p class="pm-metro__hint"><?php esc_html_e( 'Escolha a data primeiro.', 'palestra-mantos' ); ?></p></div>
			</div>
			<div class="pm-metro__step">
				<span class="pm-metro__label"><b>5</b><?php esc_html_e( 'Seus dados', 'palestra-mantos' ); ?></span>
				<div class="pm-metro__you">
					<label class="screen-reader-text" for="pm-metro-name"><?php esc_html_e( 'Seu nome', 'palestra-mantos' ); ?></label>
					<input id="pm-metro-name" name="pm_metro_name" type="text" autocomplete="given-name" maxlength="60" placeholder="<?php esc_attr_e( 'Seu nome', 'palestra-mantos' ); ?>">
					<label class="screen-reader-text" for="pm-metro-phone"><?php esc_html_e( 'Seu WhatsApp', 'palestra-mantos' ); ?></label>
					<input id="pm-metro-phone" name="pm_metro_phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="16" placeholder="<?php esc_attr_e( 'Seu WhatsApp (11) 90000-0000', 'palestra-mantos' ); ?>">
					<input type="text" name="pm_metro_site" value="" tabindex="-1" autocomplete="off" class="pm-metro__hp" aria-hidden="true">
				</div>
			</div>
			<div class="pm-metro__sum" aria-live="polite"></div>
			<ul class="pm-metro__info">
				<li><?php echo esc_html( sprintf( __( 'Pagamento na entrega: %s.', 'palestra-mantos' ), $s['payment'] ) ); ?></li>
				<?php if ( '' !== trim( (string) $s['meeting'] ) ) : ?><li><?php echo esc_html( sprintf( __( 'Ponto de encontro: %s', 'palestra-mantos' ), $s['meeting'] ) ); ?></li><?php endif; ?>
				<?php if ( '' !== trim( (string) $s['rules'] ) ) : ?><li><?php echo esc_html( $s['rules'] ); ?></li><?php endif; ?>
			</ul>
		</div>

		<input type="hidden" name="pm_metro_line" value="">
		<input type="hidden" name="pm_metro_station" value="">
		<input type="hidden" name="pm_metro_date" value="">
		<input type="hidden" name="pm_metro_slot" value="">
	</div>
	<?php
}
add_action( 'woocommerce_before_add_to_cart_button', 'pm_metro_product_block', 15 );

/**
 * Script e dados do calendário.
 */
function pm_metro_assets() {
	if ( ! is_product() || ! pm_metro_enabled() ) {
		return;
	}
	wp_enqueue_script( 'pm-metro', PM_URI . '/assets/js/metro.js', array(), PM_VERSION, true );
	$lines = array();
	foreach ( pm_metro_lines() as $id => $l ) {
		$lines[] = array( 'id' => (string) $id, 'name' => $l['name'], 'color' => $l['color'], 'stations' => $l['stations'] );
	}
	wp_localize_script(
		'pm-metro',
		'pmMetro',
		array(
			'url'      => WC_AJAX::get_endpoint( 'pm_metro_reserve' ),
			'lines'    => $lines,
			'dates'    => pm_metro_dates(),
			'slots'    => pm_metro_slots(),
			'full'     => array_keys( pm_metro_taken() ),
			'btn'      => __( 'Reservar e combinar no WhatsApp', 'palestra-mantos' ),
			'msgFill'  => __( 'Para reservar, escolha linha, estação, data, horário e informe nome e WhatsApp.', 'palestra-mantos' ),
			'msgSize'  => __( 'Escolha o tamanho antes de reservar.', 'palestra-mantos' ),
			'msgPhone' => __( 'Digite um WhatsApp válido com DDD.', 'palestra-mantos' ),
			'msgFull'  => __( 'reservado', 'palestra-mantos' ),
			'msgSum'   => __( 'Retirada: %s', 'palestra-mantos' ),
			'msgWait'  => __( 'Reservando…', 'palestra-mantos' ),
			'msgOk'    => __( 'Reserva #%s feita! Abrindo o WhatsApp…', 'palestra-mantos' ),
			'msgOpen'  => __( 'Abrir o WhatsApp', 'palestra-mantos' ),
			'msgError' => __( 'Não foi possível reservar agora. Tente de novo ou fale com a gente no WhatsApp.', 'palestra-mantos' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_metro_assets', 24 );

/**
 * Resposta de erro da reserva.
 *
 * @param string $msg   Mensagem.
 * @param array  $extra Dados extras.
 */
function pm_metro_fail( $msg, $extra = array() ) {
	wp_send_json( array_merge( array( 'ok' => false, 'message' => $msg ), $extra ) );
}

/**
 * AJAX: cria a reserva e devolve o link do WhatsApp.
 */
function pm_metro_ajax_reserve() {
	// Sem nonce de propósito (cache de página quebra nonces); protegido por validação, trava e limite por IP/telefone.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$p = wp_unslash( $_POST );
	// phpcs:enable
	if ( ! pm_metro_enabled() ) {
		pm_metro_fail( __( 'A retirada no metrô está indisponível no momento.', 'palestra-mantos' ) );
	}
	if ( ! empty( $p['pm_metro_site'] ) ) {
		pm_metro_fail( __( 'Não foi possível reservar.', 'palestra-mantos' ) );
	}

	// Produto e tamanho.
	$product_id   = absint( isset( $p['add-to-cart'] ) ? $p['add-to-cart'] : ( isset( $p['product_id'] ) ? $p['product_id'] : 0 ) );
	$variation_id = absint( isset( $p['variation_id'] ) ? $p['variation_id'] : 0 );
	$qty          = max( 1, min( 10, absint( isset( $p['quantity'] ) ? $p['quantity'] : 1 ) ) );
	$parent       = wc_get_product( $product_id );
	if ( ! $parent ) {
		pm_metro_fail( __( 'Produto não encontrado.', 'palestra-mantos' ) );
	}
	if ( $parent->is_type( 'variable' ) && ! $variation_id ) {
		pm_metro_fail( __( 'Escolha o tamanho antes de reservar.', 'palestra-mantos' ) );
	}
	$item = $variation_id ? wc_get_product( $variation_id ) : $parent;
	if ( ! $item || ( $variation_id && $item->get_parent_id() !== $parent->get_id() ) || ! $item->is_purchasable() || ! $item->is_in_stock() || ! $item->has_enough_stock( $qty ) ) {
		pm_metro_fail( __( 'Esse tamanho está sem estoque.', 'palestra-mantos' ) );
	}

	// Retirada.
	$line    = sanitize_key( isset( $p['pm_metro_line'] ) ? $p['pm_metro_line'] : '' );
	$station = sanitize_text_field( isset( $p['pm_metro_station'] ) ? $p['pm_metro_station'] : '' );
	$date    = sanitize_text_field( isset( $p['pm_metro_date'] ) ? $p['pm_metro_date'] : '' );
	$slot    = sanitize_text_field( isset( $p['pm_metro_slot'] ) ? $p['pm_metro_slot'] : '' );
	$lines   = pm_metro_lines();
	if ( ! isset( $lines[ $line ] ) || ! in_array( $station, $lines[ $line ]['stations'], true ) || ! in_array( $date, pm_metro_dates(), true ) || ! in_array( $slot, pm_metro_slots(), true ) ) {
		pm_metro_fail( __( 'Escolha linha, estação, data e horário.', 'palestra-mantos' ) );
	}

	// Cliente.
	$name  = sanitize_text_field( isset( $p['pm_metro_name'] ) ? $p['pm_metro_name'] : '' );
	$phone = preg_replace( '/\D+/', '', isset( $p['pm_metro_phone'] ) ? $p['pm_metro_phone'] : '' );
	if ( 13 === strlen( $phone ) && 0 === strpos( $phone, '55' ) ) {
		$phone = substr( $phone, 2 );
	}
	if ( mb_strlen( $name ) < 2 ) {
		pm_metro_fail( __( 'Digite o seu nome.', 'palestra-mantos' ) );
	}
	if ( ! preg_match( '/^[1-9]{2}9?\d{8}$/', $phone ) ) {
		pm_metro_fail( __( 'Digite um WhatsApp válido com DDD.', 'palestra-mantos' ) );
	}

	// Limite por IP (evita robôs prendendo horários).
	$ip_key = 'pm_metro_ip_' . md5( WC_Geolocation::get_ip_address() );
	$tries  = (int) get_transient( $ip_key );
	if ( $tries >= 5 ) {
		pm_metro_fail( __( 'Muitas reservas seguidas. Fale com a gente no WhatsApp.', 'palestra-mantos' ) );
	}

	// Trava: só uma reserva por vez para o mesmo horário.
	$lock = 'pm_metro_lock_' . md5( $date . '|' . $slot );
	if ( ! add_option( $lock, time(), '', 'no' ) ) {
		if ( (int) get_option( $lock ) < time() - 20 ) {
			delete_option( $lock );
		}
		pm_metro_fail( __( 'Esse horário acabou de ser reservado. Escolha outro, por favor.', 'palestra-mantos' ), array( 'taken' => $date . '|' . $slot ) );
	}
	try {
		$taken = pm_metro_taken( true );
		if ( isset( $taken[ $date . '|' . $slot ] ) ) {
			delete_option( $lock );
			pm_metro_fail( __( 'Esse horário acabou de ser reservado. Escolha outro, por favor.', 'palestra-mantos' ), array( 'taken' => $date . '|' . $slot ) );
		}
		// Um cliente, uma reserva em aberto.
		foreach ( pm_metro_reservations() as $o ) {
			if ( preg_replace( '/\D+/', '', $o->get_billing_phone() ) === $phone && ! $o->has_status( 'completed' ) ) {
				delete_option( $lock );
			pm_metro_fail(
					sprintf( __( 'Você já tem a reserva #%s em aberto. Para mudar ou incluir peças, fale com a gente no WhatsApp.', 'palestra-mantos' ), $o->get_order_number() ),
					array( 'whatsapp' => pm_whatsapp_url( sprintf( 'Olá! Sou %s, da reserva #%s. Quero alterar minha reserva.', $name, $o->get_order_number() ) ) )
				);
			}
		}

		$order = wc_create_order( array( 'status' => 'pending', 'created_via' => 'pm_metro' ) );
		if ( is_wp_error( $order ) ) {
			delete_option( $lock );
			pm_metro_fail( __( 'Não foi possível reservar agora. Tente de novo.', 'palestra-mantos' ) );
		}
		$order->add_product( $item, $qty );
		$order->set_billing_first_name( $name );
		$order->set_billing_phone( $phone );
		$order->set_billing_country( 'BR' );
		$order->set_billing_state( 'SP' );
		$order->set_billing_city( 'São Paulo' );
		$order->set_payment_method( 'pm_metro_entrega' );
		$order->set_payment_method_title( sprintf( __( 'Pagamento na entrega (%s)', 'palestra-mantos' ), pm_metro_settings()['payment'] ) );
		$ship = new WC_Order_Item_Shipping();
		$ship->set_method_title( sprintf( __( 'Retirada no metrô · Estação %s', 'palestra-mantos' ), $station ) );
		$ship->set_method_id( 'pm_metro' );
		$ship->set_total( 0 );
		$order->add_item( $ship );
		$order->update_meta_data( '_pm_metro_line', $line . ' ' . $lines[ $line ]['name'] );
		$order->update_meta_data( '_pm_metro_station', $station );
		$order->update_meta_data( '_pm_metro_date', $date );
		$order->update_meta_data( '_pm_metro_slot', $slot );
		$order->update_meta_data( '_pm_metro_state', 'pending' );
		$order->calculate_totals( false );
		$order->save();
		$order->update_status( 'on-hold', __( 'Reserva de retirada no metrô feita pelo site. Pagamento na entrega.', 'palestra-mantos' ) );
	} finally {
		delete_option( $lock );
	}
	pm_metro_flush();
	set_transient( $ip_key, $tries + 1, HOUR_IN_SECONDS );

	$size = '';
	if ( $variation_id ) {
		$size = wc_get_formatted_variation( $item, true, false, false );
	}
	$msg = sprintf(
		"Olá! Acabei de reservar pelo site:\n• %1\$s%2\$s – %3\$d un. (%4\$s)\n• Retirada: Estação %5\$s (Linha %6\$s)\n• Dia: %7\$s, %8\$s\n• Pagamento na entrega\nReserva #%9\$s – %10\$s",
		$parent->get_name(),
		$size ? ', ' . $size : '',
		$qty,
		html_entity_decode( wp_strip_all_tags( wc_price( $order->get_total() ) ), ENT_QUOTES, 'UTF-8' ),
		$station,
		$line . ' ' . $lines[ $line ]['name'],
		pm_metro_date_label( $date ),
		$slot,
		$order->get_order_number(),
		$name
	);
	$number = pm_metro_whatsapp();
	wp_send_json(
		array(
			'ok'       => true,
			'order'    => $order->get_order_number(),
			'taken'    => $date . '|' . $slot,
			'whatsapp' => 'https://wa.me/' . $number . '?text=' . rawurlencode( $msg ),
		)
	);
}
add_action( 'wc_ajax_pm_metro_reserve', 'pm_metro_ajax_reserve' );

/* -------------------------------------------------------------------------
 * Pedido: exibição
 * ---------------------------------------------------------------------- */

/**
 * HTML da retirada de um pedido.
 *
 * @param WC_Order $order Pedido.
 * @param bool     $plain Texto puro.
 * @return string
 */
function pm_metro_order_html( $order, $plain = false ) {
	$station = $order->get_meta( '_pm_metro_station' );
	if ( ! $station ) {
		return '';
	}
	$where = 'Estação ' . $station . ' (Linha ' . $order->get_meta( '_pm_metro_line' ) . ')';
	$when  = pm_metro_date_label( $order->get_meta( '_pm_metro_date' ) ) . ' · ' . $order->get_meta( '_pm_metro_slot' );
	if ( $plain ) {
		return "\nRETIRADA NO METRÔ/TREM\n" . $where . "\n" . $when . "\nPagamento na entrega\n";
	}
	return sprintf(
		'<div class="pm-metro-order" style="margin:16px 0;padding:14px 16px;border:1px solid #cfe3d7;border-radius:10px;background:#f2f8f4"><strong style="display:block;margin-bottom:4px">%1$s</strong>%2$s<br>%3$s<br>%4$s</div>',
		esc_html__( 'Retirada no metrô/trem', 'palestra-mantos' ),
		esc_html( $where ),
		esc_html( $when ),
		esc_html__( 'Pagamento na entrega', 'palestra-mantos' )
	);
}

add_action(
	'woocommerce_admin_order_data_after_shipping_address',
	function ( $order ) {
		echo pm_metro_order_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escapado na função.
	}
);
add_action(
	'woocommerce_email_after_order_table',
	function ( $order, $sent_to_admin, $plain_text ) {
		echo $plain_text ? esc_html( pm_metro_order_html( $order, true ) ) : pm_metro_order_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	},
	10,
	3
);
add_action(
	'woocommerce_order_details_after_order_table',
	function ( $order ) {
		echo pm_metro_order_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
);

/* -------------------------------------------------------------------------
 * Painel: WooCommerce → Entrega no metrô
 * ---------------------------------------------------------------------- */

/**
 * Menu.
 */
function pm_metro_menu() {
	add_submenu_page( 'woocommerce', __( 'Entrega no metrô', 'palestra-mantos' ), __( 'Entrega no metrô', 'palestra-mantos' ), 'manage_woocommerce', 'pm-metro', 'pm_metro_page' );
}
add_action( 'admin_menu', 'pm_metro_menu', 60 );

/**
 * Confirmar / cancelar / marcar como entregue.
 */
function pm_metro_action() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'palestra-mantos' ) );
	}
	$id  = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
	$act = isset( $_GET['act'] ) ? sanitize_key( wp_unslash( $_GET['act'] ) ) : '';
	check_admin_referer( 'pm_metro_' . $act . '_' . $id );
	$o = wc_get_order( $id );
	if ( $o && $o->get_meta( '_pm_metro_station' ) ) {
		if ( 'confirm' === $act ) {
			$o->update_meta_data( '_pm_metro_state', 'confirmed' );
			$o->save();
			$o->add_order_note( __( 'Reserva confirmada.', 'palestra-mantos' ) );
		} elseif ( 'cancel' === $act ) {
			$o->update_status( 'cancelled', __( 'Reserva cancelada pela loja. Horário liberado.', 'palestra-mantos' ) );
		} elseif ( 'done' === $act ) {
			$o->update_meta_data( '_pm_metro_state', 'confirmed' );
			$o->save();
			$o->update_status( 'completed', __( 'Entregue no metrô e pago na entrega.', 'palestra-mantos' ) );
		}
	}
	pm_metro_flush();
	wp_safe_redirect( admin_url( 'admin.php?page=pm-metro' ) );
	exit;
}
add_action( 'admin_post_pm_metro_action', 'pm_metro_action' );

/**
 * Salvar configurações.
 */
function pm_metro_save() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'palestra-mantos' ) );
	}
	check_admin_referer( 'pm_metro_save' );
	$p       = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cada campo é limpo abaixo.
	$lines   = ! empty( $p['restore_lines'] ) ? pm_metro_default_lines_text() : ( isset( $p['lines'] ) ? sanitize_textarea_field( $p['lines'] ) : '' );
	$all_ids = array_keys( pm_metro_lines( false ) );
	$on      = isset( $p['on_lines'] ) ? array_map( 'sanitize_key', (array) $p['on_lines'] ) : array();
	update_option(
		'pm_metro',
		array(
			'enabled'   => empty( $p['enabled'] ) ? 0 : 1,
			'whatsapp'  => isset( $p['whatsapp'] ) ? preg_replace( '/\D+/', '', $p['whatsapp'] ) : '',
			'payment'   => isset( $p['payment'] ) ? sanitize_text_field( $p['payment'] ) : '',
			'meeting'   => isset( $p['meeting'] ) ? sanitize_text_field( $p['meeting'] ) : '',
			'days'      => isset( $p['days'] ) ? array_map( 'absint', (array) $p['days'] ) : array(),
			'slots'     => isset( $p['slots'] ) ? sanitize_textarea_field( $p['slots'] ) : '',
			'lead'      => isset( $p['lead'] ) ? absint( $p['lead'] ) : 1,
			'cutoff'    => isset( $p['cutoff'] ) ? min( 24, absint( $p['cutoff'] ) ) : 18,
			'horizon'   => isset( $p['horizon'] ) ? max( 1, absint( $p['horizon'] ) ) : 30,
			'expiry'    => isset( $p['expiry'] ) ? absint( $p['expiry'] ) : 3,
			'blocked'   => isset( $p['blocked'] ) ? sanitize_textarea_field( $p['blocked'] ) : '',
			'lines'     => $lines,
			'off_lines' => array_values( array_diff( $all_ids, $on ) ),
			'rules'     => isset( $p['rules'] ) ? sanitize_textarea_field( $p['rules'] ) : '',
		),
		false
	);
	pm_metro_flush();
	wp_safe_redirect( add_query_arg( 'pm_saved', '1', admin_url( 'admin.php?page=pm-metro' ) ) );
	exit;
}
add_action( 'admin_post_pm_metro_save', 'pm_metro_save' );

/**
 * Link de ação do painel.
 *
 * @param WC_Order $o     Pedido.
 * @param string   $act   Ação.
 * @param string   $label Rótulo.
 * @param string   $class Classe do botão.
 * @return string
 */
function pm_metro_action_link( $o, $act, $label, $class = 'button' ) {
	$url = wp_nonce_url( admin_url( 'admin-post.php?action=pm_metro_action&act=' . $act . '&order=' . $o->get_id() ), 'pm_metro_' . $act . '_' . $o->get_id() );
	return sprintf( '<a class="%1$s" href="%2$s"%4$s>%3$s</a>', esc_attr( $class ), esc_url( $url ), esc_html( $label ), 'cancel' === $act ? ' onclick="return confirm(\'Cancelar esta reserva e liberar o horário?\')"' : '' );
}

/**
 * Tela: agenda + configurações.
 */
function pm_metro_page() {
	pm_metro_expire();
	pm_metro_flush();
	$s      = pm_metro_settings();
	$wk     = array( 'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado' );
	$orders = array_filter(
		pm_metro_reservations(),
		function ( $o ) {
			return ! $o->has_status( 'completed' );
		}
	);
	usort(
		$orders,
		function ( $a, $b ) {
			return strcmp( $a->get_meta( '_pm_metro_date' ) . $a->get_meta( '_pm_metro_slot' ), $b->get_meta( '_pm_metro_date' ) . $b->get_meta( '_pm_metro_slot' ) );
		}
	);
	$expiry = (int) $s['expiry'];
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Entrega no metrô/trem', 'palestra-mantos' ); ?></h1>
		<?php if ( isset( $_GET['pm_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Salvo!', 'palestra-mantos' ); ?></p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Próximas retiradas', 'palestra-mantos' ); ?></h2>
		<p class="description"><?php echo esc_html( $expiry > 0 ? sprintf( __( 'Reservas novas ficam "Aguardando" e são canceladas sozinhas se não forem confirmadas em %d h.', 'palestra-mantos' ), $expiry ) : __( 'Reservas novas ficam "Aguardando" até você confirmar ou cancelar.', 'palestra-mantos' ) ); ?></p>
		<?php if ( ! $orders ) : ?>
			<p><?php esc_html_e( 'Nenhuma retirada agendada.', 'palestra-mantos' ); ?></p>
		<?php else : ?>
			<table class="widefat striped" style="max-width:1200px">
				<thead><tr><th><?php esc_html_e( 'Quando', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Estação', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Cliente', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Peças', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'A receber', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Situação', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Ações', 'palestra-mantos' ); ?></th></tr></thead>
				<tbody>
				<?php
				foreach ( $orders as $o ) :
					$items = array();
					foreach ( $o->get_items() as $it ) {
						$items[] = $it->get_quantity() . '× ' . $it->get_name();
					}
					$phone   = preg_replace( '/\D+/', '', $o->get_billing_phone() );
					$wa_text = sprintf( 'Olá %s! Aqui é da Palestra Mantos sobre a sua reserva #%s: retirada na Estação %s, %s às %s.', $o->get_billing_first_name(), $o->get_order_number(), $o->get_meta( '_pm_metro_station' ), pm_metro_date_label( $o->get_meta( '_pm_metro_date' ) ), $o->get_meta( '_pm_metro_slot' ) );
					$pending = pm_metro_is_pending( $o );
					?>
					<tr>
						<td><strong><?php echo esc_html( pm_metro_date_label( $o->get_meta( '_pm_metro_date' ) ) ); ?></strong><br><?php echo esc_html( $o->get_meta( '_pm_metro_slot' ) ); ?></td>
						<td><?php echo esc_html( $o->get_meta( '_pm_metro_station' ) ); ?><br><small><?php echo esc_html( 'Linha ' . $o->get_meta( '_pm_metro_line' ) ); ?></small></td>
						<td><?php echo esc_html( $o->get_billing_first_name() ); ?><br><a href="<?php echo esc_url( 'https://wa.me/55' . $phone . '?text=' . rawurlencode( $wa_text ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $o->get_billing_phone() ); ?> ↗</a></td>
						<td><?php echo esc_html( implode( ' · ', $items ) ); ?><br><a href="<?php echo esc_url( $o->get_edit_order_url() ); ?>">#<?php echo esc_html( $o->get_order_number() ); ?></a></td>
						<td><strong><?php echo wp_kses_post( wc_price( $o->get_total() ) ); ?></strong></td>
						<td><?php echo $pending ? '<span style="color:#b26200;font-weight:600">' . esc_html__( 'Aguardando confirmação', 'palestra-mantos' ) . '</span>' : '<span style="color:#127a45;font-weight:600">' . esc_html__( 'Confirmada', 'palestra-mantos' ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
						<td style="white-space:nowrap">
							<?php
							if ( $pending ) {
								echo pm_metro_action_link( $o, 'confirm', __( 'Confirmar', 'palestra-mantos' ), 'button button-primary' ) . ' '; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							echo pm_metro_action_link( $o, 'done', __( 'Entregue', 'palestra-mantos' ) ) . ' ' . pm_metro_action_link( $o, 'cancel', __( 'Cancelar', 'palestra-mantos' ), 'button-link-delete' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<h2 style="margin-top:32px"><?php esc_html_e( 'Configurações', 'palestra-mantos' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="pm_metro_save">
			<?php wp_nonce_field( 'pm_metro_save' ); ?>
			<table class="form-table" role="presentation">
				<tr><th scope="row"><?php esc_html_e( 'Ativar', 'palestra-mantos' ); ?></th><td><label><input type="checkbox" name="enabled" value="1" <?php checked( ! empty( $s['enabled'] ) ); ?>> <?php esc_html_e( 'Mostrar a opção na página dos produtos', 'palestra-mantos' ); ?></label></td></tr>
				<tr><th scope="row"><label for="pm-wa"><?php esc_html_e( 'WhatsApp das reservas', 'palestra-mantos' ); ?></label></th><td><input id="pm-wa" name="whatsapp" type="text" class="regular-text" value="<?php echo esc_attr( $s['whatsapp'] ); ?>" placeholder="<?php echo esc_attr( pm_opt( 'whatsapp' ) ); ?>"><p class="description"><?php esc_html_e( 'Vazio = usa o WhatsApp da loja. Com DDI e DDD, só números.', 'palestra-mantos' ); ?></p></td></tr>
				<tr><th scope="row"><label for="pm-pay"><?php esc_html_e( 'Formas de pagamento na entrega', 'palestra-mantos' ); ?></label></th><td><input id="pm-pay" name="payment" type="text" class="regular-text" value="<?php echo esc_attr( $s['payment'] ); ?>"></td></tr>
				<tr><th scope="row"><label for="pm-meet"><?php esc_html_e( 'Ponto de encontro', 'palestra-mantos' ); ?></label></th><td><input id="pm-meet" name="meeting" type="text" class="large-text" value="<?php echo esc_attr( $s['meeting'] ); ?>"></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Dias de entrega', 'palestra-mantos' ); ?></th><td>
					<?php foreach ( $wk as $i => $name ) : ?>
						<label style="margin-right:14px"><input type="checkbox" name="days[]" value="<?php echo esc_attr( $i ); ?>" <?php checked( in_array( $i, array_map( 'intval', (array) $s['days'] ), true ) ); ?>> <?php echo esc_html( $name ); ?></label>
					<?php endforeach; ?>
				</td></tr>
				<tr><th scope="row"><label for="pm-slots"><?php esc_html_e( 'Horários', 'palestra-mantos' ); ?></label></th><td><textarea id="pm-slots" name="slots" rows="5" class="large-text" style="max-width:340px"><?php echo esc_textarea( $s['slots'] ); ?></textarea><p class="description"><?php esc_html_e( 'Um por linha. Cada horário aceita 1 reserva, somando todas as estações.', 'palestra-mantos' ); ?></p></td></tr>
				<tr><th scope="row"><label for="pm-exp"><?php esc_html_e( 'Reserva expira em', 'palestra-mantos' ); ?></label></th><td><input id="pm-exp" name="expiry" type="number" min="0" class="small-text" value="<?php echo esc_attr( $s['expiry'] ); ?>"> <?php esc_html_e( 'horas se você não confirmar (0 = nunca).', 'palestra-mantos' ); ?></td></tr>
				<tr><th scope="row"><label for="pm-lead"><?php esc_html_e( 'Antecedência', 'palestra-mantos' ); ?></label></th><td><input id="pm-lead" name="lead" type="number" min="0" class="small-text" value="<?php echo esc_attr( $s['lead'] ); ?>"> <?php esc_html_e( 'dia(s). Reservas feitas depois das', 'palestra-mantos' ); ?> <input name="cutoff" type="number" min="0" max="24" class="small-text" value="<?php echo esc_attr( $s['cutoff'] ); ?>">h <?php esc_html_e( 'ganham mais 1 dia.', 'palestra-mantos' ); ?></td></tr>
				<tr><th scope="row"><label for="pm-hor"><?php esc_html_e( 'Agenda aberta por', 'palestra-mantos' ); ?></label></th><td><input id="pm-hor" name="horizon" type="number" min="1" class="small-text" value="<?php echo esc_attr( $s['horizon'] ); ?>"> <?php esc_html_e( 'dias', 'palestra-mantos' ); ?></td></tr>
				<tr><th scope="row"><label for="pm-blk"><?php esc_html_e( 'Datas bloqueadas', 'palestra-mantos' ); ?></label></th><td><textarea id="pm-blk" name="blocked" rows="3" class="large-text" style="max-width:340px" placeholder="25/12/2026"><?php echo esc_textarea( $s['blocked'] ); ?></textarea><p class="description"><?php esc_html_e( 'Feriados, folgas e dias de jogo. Uma data por linha (dd/mm/aaaa).', 'palestra-mantos' ); ?></p></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Linhas atendidas', 'palestra-mantos' ); ?></th><td>
					<?php foreach ( pm_metro_lines( false ) as $id => $l ) : ?>
						<label style="display:inline-block;min-width:170px;margin:0 8px 8px 0"><input type="checkbox" name="on_lines[]" value="<?php echo esc_attr( $id ); ?>" <?php checked( ! in_array( (string) $id, array_map( 'strval', (array) $s['off_lines'] ), true ) ); ?>> <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?php echo esc_attr( $l['color'] ); ?>;vertical-align:-1px"></span> <?php echo esc_html( 'Linha ' . $id . ' ' . $l['name'] . ' (' . count( $l['stations'] ) . ')' ); ?></label>
					<?php endforeach; ?>
				</td></tr>
				<tr><th scope="row"><label for="pm-lines"><?php esc_html_e( 'Estações', 'palestra-mantos' ); ?></label></th><td><textarea id="pm-lines" name="lines" rows="10" class="large-text code"><?php echo esc_textarea( $s['lines'] ); ?></textarea><p class="description"><?php esc_html_e( 'Uma linha por linha de metrô/trem: número | nome | cor | estações separadas por ponto e vírgula. Para tirar uma estação, apague o nome dela.', 'palestra-mantos' ); ?></p><label><input type="checkbox" name="restore_lines" value="1"> <?php esc_html_e( 'Restaurar a lista padrão ao salvar', 'palestra-mantos' ); ?></label></td></tr>
				<tr><th scope="row"><label for="pm-rules"><?php esc_html_e( 'Aviso ao cliente', 'palestra-mantos' ); ?></label></th><td><textarea id="pm-rules" name="rules" rows="3" class="large-text"><?php echo esc_textarea( $s['rules'] ); ?></textarea></td></tr>
			</table>
			<?php submit_button( __( 'Salvar', 'palestra-mantos' ) ); ?>
		</form>
	</div>
	<?php
}
