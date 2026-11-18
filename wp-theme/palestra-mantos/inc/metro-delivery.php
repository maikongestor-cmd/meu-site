<?php
/**
 * Entrega em estação de metrô/trem (Grande São Paulo).
 *
 * O cliente escolhe na página do produto: linha, estação, data (calendário) e horário.
 * A escolha vale para o pedido inteiro, vira a única opção de frete no carrinho/checkout
 * e fica salva no pedido (painel, e-mails e página de obrigado).
 *
 * Configuração: WooCommerce → Entrega no metrô.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Linhas e estações (padrão; editável no painel)
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
		'fee'       => '0',
		'days'      => array( 1, 2, 3, 4, 5, 6 ),
		'slots'     => "10:00 às 11:00\n11:00 às 12:00\n13:00 às 14:00\n14:00 às 15:00\n15:00 às 16:00",
		'capacity'  => 4,
		'lead'      => 1,
		'cutoff'    => 18,
		'horizon'   => 30,
		'blocked'   => '',
		'lines'     => pm_metro_default_lines_text(),
		'off_lines' => array(),
		'rules'     => 'Tolerância de 15 minutos. Se não puder ir, avise pelo WhatsApp com antecedência para remarcar sem custo.',
	);
	$saved = get_option( 'pm_metro', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), $defaults );
}

/**
 * Linhas ativas, já interpretadas.
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
		$color         = sanitize_hex_color( $parts[2] );
		$lines[ $id ] = array(
			'name'     => $parts[1],
			'color'    => $color ? $color : '#666666',
			'stations' => $stations,
		);
	}
	return $lines;
}

/**
 * Horários configurados.
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
 * Taxa da entrega.
 *
 * @return float
 */
function pm_metro_fee() {
	return max( 0, (float) str_replace( ',', '.', (string) pm_metro_settings()['fee'] ) );
}

/**
 * Datas disponíveis (Y-m-d), já descontando antecedência, horário limite, dias e bloqueios.
 *
 * @return string[]
 */
function pm_metro_dates() {
	$s       = pm_metro_settings();
	$tz      = wp_timezone();
	$now     = new DateTimeImmutable( 'now', $tz );
	$lead    = max( 0, (int) $s['lead'] );
	if ( (int) $now->format( 'G' ) >= (int) $s['cutoff'] ) {
		++$lead;
	}
	$blocked = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $s['blocked'] ) as $b ) {
		$b = trim( $b );
		if ( preg_match( '#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $b, $m ) ) {
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
 * Pedidos futuros com entrega no metrô.
 *
 * @return WC_Order[]
 */
function pm_metro_upcoming_orders() {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return array();
	}
	// Busca os pedidos recentes e filtra aqui: funciona com qualquer armazenamento de pedidos do WooCommerce.
	$orders = wc_get_orders(
		array(
			'limit'        => 500,
			'status'       => array( 'wc-pending', 'wc-on-hold', 'wc-processing', 'wc-completed' ),
			'date_created' => '>' . ( time() - 90 * DAY_IN_SECONDS ),
			'orderby'      => 'date',
			'order'        => 'DESC',
		)
	);
	$today = wp_date( 'Y-m-d' );
	return array_values(
		array_filter(
			$orders,
			function ( $o ) use ( $today ) {
				$d = (string) $o->get_meta( '_pm_metro_date' );
				return '' !== $d && $d >= $today;
			}
		)
	);
}

/**
 * Vagas já usadas por "data|horário" (pedidos pagos ou aguardando).
 *
 * @return array
 */
function pm_metro_booked() {
	$cached = get_transient( 'pm_metro_booked' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$booked = array();
	foreach ( pm_metro_upcoming_orders() as $order ) {
		if ( $order->has_status( 'pending' ) ) {
			continue; // Pix/cartão não concluído não segura vaga.
		}
		$key            = $order->get_meta( '_pm_metro_date' ) . '|' . $order->get_meta( '_pm_metro_slot' );
		$booked[ $key ] = isset( $booked[ $key ] ) ? $booked[ $key ] + 1 : 1;
	}
	set_transient( 'pm_metro_booked', $booked, 5 * MINUTE_IN_SECONDS );
	return $booked;
}

/**
 * Limpa o cache de vagas quando um pedido muda.
 */
function pm_metro_flush_booked() {
	delete_transient( 'pm_metro_booked' );
}
add_action( 'woocommerce_order_status_changed', 'pm_metro_flush_booked' );
add_action( 'woocommerce_checkout_order_processed', 'pm_metro_flush_booked' );

/**
 * O horário ainda tem vaga?
 *
 * @param string $date Data.
 * @param string $slot Horário.
 * @return bool
 */
function pm_metro_has_room( $date, $slot ) {
	$cap = (int) pm_metro_settings()['capacity'];
	if ( $cap <= 0 ) {
		return true;
	}
	$booked = pm_metro_booked();
	return ( isset( $booked[ $date . '|' . $slot ] ) ? $booked[ $date . '|' . $slot ] : 0 ) < $cap;
}

/**
 * Data por extenso curta (ex.: "sex, 21/11").
 *
 * @param string $ymd Data.
 * @return string
 */
function pm_metro_date_label( $ymd ) {
	$d = DateTimeImmutable::createFromFormat( 'Y-m-d', $ymd, wp_timezone() );
	if ( ! $d ) {
		return $ymd;
	}
	$wk = array( 'dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb' );
	return $wk[ (int) $d->format( 'w' ) ] . ', ' . $d->format( 'd/m' );
}

/**
 * Escolha atual do pedido (sessão).
 *
 * @return array|null
 */
function pm_metro_current() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return null;
	}
	$m = WC()->session->get( 'pm_metro' );
	return is_array( $m ) && ! empty( $m['station'] ) ? $m : null;
}

/**
 * Texto resumido da entrega.
 *
 * @param array $m Escolha.
 * @return string
 */
function pm_metro_summary( $m ) {
	return sprintf(
		/* translators: 1: estação, 2: linha, 3: data, 4: horário */
		__( 'Estação %1$s (Linha %2$s) · %3$s · %4$s', 'palestra-mantos' ),
		$m['station'],
		$m['line_label'],
		pm_metro_date_label( $m['date'] ),
		$m['slot']
	);
}

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
	$fee = pm_metro_fee();
	$cur = pm_metro_current();
	?>
	<div class="pm-metro" data-pm-metro>
		<p class="pm-metro__title"><?php esc_html_e( 'Como você quer receber?', 'palestra-mantos' ); ?></p>
		<div class="pm-metro__modes" role="radiogroup" aria-label="<?php esc_attr_e( 'Forma de entrega', 'palestra-mantos' ); ?>">
			<label class="pm-metro__mode">
				<input type="radio" name="pm_entrega" value="casa" <?php checked( ! $cur ); ?>>
				<span><b><?php esc_html_e( 'Receber em casa', 'palestra-mantos' ); ?></b><small><?php esc_html_e( 'Correios ou transportadora', 'palestra-mantos' ); ?></small></span>
			</label>
			<label class="pm-metro__mode">
				<input type="radio" name="pm_entrega" value="metro" <?php checked( (bool) $cur ); ?>>
				<span><b><?php esc_html_e( 'Retirar no metrô/trem', 'palestra-mantos' ); ?></b><small><?php echo $fee > 0 ? esc_html( sprintf( __( 'Grande SP · %s', 'palestra-mantos' ), wp_strip_all_tags( wc_price( $fee ) ) ) ) : esc_html__( 'Grande SP · grátis', 'palestra-mantos' ); ?></small></span>
			</label>
		</div>

		<div class="pm-metro__panel" hidden>
			<?php if ( $cur ) : ?>
				<p class="pm-metro__note"><?php echo esc_html( sprintf( __( 'Seu pedido já está agendado: %s. Ao mudar aqui, vale para o pedido todo.', 'palestra-mantos' ), pm_metro_summary( $cur ) ) ); ?></p>
			<?php endif; ?>
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
			<div class="pm-metro__sum" aria-live="polite"></div>
			<?php if ( '' !== trim( (string) pm_metro_settings()['rules'] ) ) : ?>
				<p class="pm-metro__rules"><?php echo esc_html( pm_metro_settings()['rules'] ); ?></p>
			<?php endif; ?>
		</div>

		<input type="hidden" name="pm_metro_line" value="<?php echo esc_attr( $cur ? $cur['line'] : '' ); ?>">
		<input type="hidden" name="pm_metro_station" value="<?php echo esc_attr( $cur ? $cur['station'] : '' ); ?>">
		<input type="hidden" name="pm_metro_date" value="<?php echo esc_attr( $cur ? $cur['date'] : '' ); ?>">
		<input type="hidden" name="pm_metro_slot" value="<?php echo esc_attr( $cur ? $cur['slot'] : '' ); ?>">
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
	$cap    = (int) pm_metro_settings()['capacity'];
	$full   = array();
	if ( $cap > 0 ) {
		foreach ( pm_metro_booked() as $key => $n ) {
			if ( $n >= $cap ) {
				$full[] = $key;
			}
		}
	}
	$lines = array();
	foreach ( pm_metro_lines() as $id => $l ) {
		$lines[] = array( 'id' => (string) $id, 'name' => $l['name'], 'color' => $l['color'], 'stations' => $l['stations'] );
	}
	wp_localize_script(
		'pm-metro',
		'pmMetro',
		array(
			'lines'   => $lines,
			'dates'   => pm_metro_dates(),
			'slots'   => pm_metro_slots(),
			'full'    => $full,
			'msgFill' => __( 'Para retirar no metrô, escolha a linha, a estação, a data e o horário.', 'palestra-mantos' ),
			'msgFull' => __( 'lotado', 'palestra-mantos' ),
			'msgSum'  => __( 'Retirada: %s', 'palestra-mantos' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_metro_assets', 24 );

/**
 * Confere a escolha ao clicar em "Comprar".
 *
 * @param bool $passed Já passou.
 * @return bool
 */
function pm_metro_validate( $passed ) {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- formulário padrão do WooCommerce (sem nonce).
	if ( ! $passed || ! isset( $_POST['pm_entrega'] ) || 'metro' !== $_POST['pm_entrega'] ) {
		return $passed;
	}
	$line    = isset( $_POST['pm_metro_line'] ) ? sanitize_key( wp_unslash( $_POST['pm_metro_line'] ) ) : '';
	$station = isset( $_POST['pm_metro_station'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_metro_station'] ) ) : '';
	$date    = isset( $_POST['pm_metro_date'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_metro_date'] ) ) : '';
	$slot    = isset( $_POST['pm_metro_slot'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_metro_slot'] ) ) : '';
	// phpcs:enable
	$lines = pm_metro_lines();
	if ( ! isset( $lines[ $line ] ) || ! in_array( $station, $lines[ $line ]['stations'], true ) || ! in_array( $date, pm_metro_dates(), true ) || ! in_array( $slot, pm_metro_slots(), true ) ) {
		wc_add_notice( __( 'Para retirar no metrô, escolha a linha, a estação, a data e o horário.', 'palestra-mantos' ), 'error' );
		return false;
	}
	$cur = pm_metro_current();
	$same = $cur && $cur['date'] === $date && $cur['slot'] === $slot;
	if ( ! $same && ! pm_metro_has_room( $date, $slot ) ) {
		wc_add_notice( __( 'Esse horário acabou de lotar. Escolha outro, por favor.', 'palestra-mantos' ), 'error' );
		return false;
	}
	WC()->session->set(
		'pm_metro',
		array(
			'line'       => $line,
			'line_label' => $line . ' ' . $lines[ $line ]['name'],
			'station'    => $station,
			'date'       => $date,
			'slot'       => $slot,
		)
	);
	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'pm_metro_validate', 20 );

/* -------------------------------------------------------------------------
 * Carrinho e checkout
 * ---------------------------------------------------------------------- */

/**
 * Faz o frete ser recalculado quando a escolha muda.
 *
 * @param array $packages Pacotes.
 * @return array
 */
function pm_metro_packages( $packages ) {
	$m = pm_metro_current();
	foreach ( $packages as $k => $p ) {
		$packages[ $k ]['pm_metro'] = $m ? implode( '|', $m ) : '';
	}
	return $packages;
}
add_filter( 'woocommerce_cart_shipping_packages', 'pm_metro_packages' );

/**
 * Com retirada no metrô escolhida, ela vira a única opção de frete.
 *
 * @param array $rates   Opções.
 * @param array $package Pacote.
 * @return array
 */
function pm_metro_rates( $rates, $package ) {
	$m = pm_metro_current();
	if ( ! $m || ! pm_metro_enabled() ) {
		return $rates;
	}
	$label = sprintf( __( 'Retirada no metrô · %s', 'palestra-mantos' ), pm_metro_summary( $m ) );
	$rate  = new WC_Shipping_Rate( 'pm_metro:1', $label, pm_metro_fee(), array(), 'pm_metro', 1 );
	return array( 'pm_metro:1' => $rate );
}
add_filter( 'woocommerce_package_rates', 'pm_metro_rates', 100, 2 );

/**
 * Caixa no carrinho com a retirada escolhida e a opção de voltar para entrega em casa.
 */
function pm_metro_cart_box() {
	$m = pm_metro_current();
	if ( ! $m ) {
		return;
	}
	$url = wp_nonce_url( add_query_arg( 'pm_metro', 'casa', wc_get_cart_url() ), 'pm_metro_casa' );
	printf(
		'<div class="pm-metro-box"><span class="pm-metro-box__ic" aria-hidden="true">M</span><div><b>%1$s</b><span>%2$s</span><a href="%3$s">%4$s</a></div></div>',
		esc_html__( 'Retirada no metrô/trem', 'palestra-mantos' ),
		esc_html( pm_metro_summary( $m ) ),
		esc_url( $url ),
		esc_html__( 'Prefiro receber em casa', 'palestra-mantos' )
	);
}
add_action( 'woocommerce_before_cart_totals', 'pm_metro_cart_box', 4 );
add_action( 'woocommerce_checkout_before_order_review', 'pm_metro_cart_box', 4 );

/**
 * Link "Prefiro receber em casa".
 */
function pm_metro_switch_home() {
	if ( isset( $_GET['pm_metro'], $_GET['_wpnonce'] ) && 'casa' === $_GET['pm_metro'] && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'pm_metro_casa' ) && WC()->session ) {
		WC()->session->set( 'pm_metro', null );
		wc_add_notice( __( 'Pronto! Agora é só calcular o frete para receber em casa.', 'palestra-mantos' ) );
		wp_safe_redirect( wc_get_cart_url() );
		exit;
	}
}
add_action( 'template_redirect', 'pm_metro_switch_home' );

/**
 * Carrinho esvaziado: esquece a retirada.
 */
function pm_metro_cart_emptied() {
	if ( WC()->session ) {
		WC()->session->set( 'pm_metro', null );
	}
}
add_action( 'woocommerce_cart_emptied', 'pm_metro_cart_emptied' );

/**
 * Confere de novo no checkout (o horário pode ter lotado no meio do caminho).
 *
 * @param array    $data   Dados.
 * @param WP_Error $errors Erros.
 */
function pm_metro_checkout_validate( $data, $errors ) {
	$m = pm_metro_current();
	if ( ! $m ) {
		return;
	}
	if ( ! in_array( $m['date'], pm_metro_dates(), true ) ) {
		$errors->add( 'pm_metro', __( 'A data escolhida para retirada no metrô não está mais disponível. Volte ao produto e escolha outra.', 'palestra-mantos' ) );
	} elseif ( ! pm_metro_has_room( $m['date'], $m['slot'] ) ) {
		$errors->add( 'pm_metro', __( 'O horário escolhido para retirada no metrô lotou. Volte ao produto e escolha outro.', 'palestra-mantos' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'pm_metro_checkout_validate', 10, 2 );

/**
 * Salva a retirada no pedido.
 *
 * @param WC_Order $order Pedido.
 */
function pm_metro_save_order( $order ) {
	$m = pm_metro_current();
	if ( ! $m ) {
		return;
	}
	$order->update_meta_data( '_pm_metro_line', $m['line_label'] );
	$order->update_meta_data( '_pm_metro_station', $m['station'] );
	$order->update_meta_data( '_pm_metro_date', $m['date'] );
	$order->update_meta_data( '_pm_metro_slot', $m['slot'] );
}
add_action( 'woocommerce_checkout_create_order', 'pm_metro_save_order' );
add_action( 'woocommerce_store_api_checkout_update_order_from_request', 'pm_metro_save_order' );

/**
 * Depois de fechar o pedido, limpa a escolha.
 */
function pm_metro_after_order() {
	if ( WC()->session ) {
		WC()->session->set( 'pm_metro', null );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'pm_metro_after_order', 20 );

/**
 * HTML da retirada de um pedido.
 *
 * @param WC_Order $order Pedido.
 * @param bool     $plain Texto puro (e-mail em texto).
 * @return string
 */
function pm_metro_order_html( $order, $plain = false ) {
	$station = $order->get_meta( '_pm_metro_station' );
	if ( ! $station ) {
		return '';
	}
	$when = pm_metro_date_label( $order->get_meta( '_pm_metro_date' ) ) . ' · ' . $order->get_meta( '_pm_metro_slot' );
	if ( $plain ) {
		return "\n" . __( 'RETIRADA NO METRÔ/TREM', 'palestra-mantos' ) . "\n" . 'Estação ' . $station . ' (Linha ' . $order->get_meta( '_pm_metro_line' ) . ")\n" . $when . "\n";
	}
	return sprintf(
		'<div class="pm-metro-order" style="margin:16px 0;padding:14px 16px;border:1px solid #cfe3d7;border-radius:10px;background:#f2f8f4"><strong style="display:block;margin-bottom:4px">%1$s</strong>%2$s<br>%3$s</div>',
		esc_html__( 'Retirada no metrô/trem', 'palestra-mantos' ),
		esc_html( 'Estação ' . $station . ' (Linha ' . $order->get_meta( '_pm_metro_line' ) . ')' ),
		esc_html( $when )
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
 * Salvar.
 */
function pm_metro_save() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( esc_html__( 'Sem permissão.', 'palestra-mantos' ) );
	}
	check_admin_referer( 'pm_metro_save' );
	$p = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- cada campo é limpo abaixo.
	if ( ! empty( $p['restore_lines'] ) ) {
		$lines = pm_metro_default_lines_text();
	} else {
		$lines = isset( $p['lines'] ) ? sanitize_textarea_field( $p['lines'] ) : '';
	}
	$all_ids = array_keys( pm_metro_lines( false ) );
	$on      = isset( $p['on_lines'] ) ? array_map( 'sanitize_key', (array) $p['on_lines'] ) : array();
	update_option(
		'pm_metro',
		array(
			'enabled'   => empty( $p['enabled'] ) ? 0 : 1,
			'fee'       => isset( $p['fee'] ) ? preg_replace( '/[^0-9,.]/', '', $p['fee'] ) : '0',
			'days'      => isset( $p['days'] ) ? array_map( 'absint', (array) $p['days'] ) : array(),
			'slots'     => isset( $p['slots'] ) ? sanitize_textarea_field( $p['slots'] ) : '',
			'capacity'  => isset( $p['capacity'] ) ? absint( $p['capacity'] ) : 0,
			'lead'      => isset( $p['lead'] ) ? absint( $p['lead'] ) : 1,
			'cutoff'    => isset( $p['cutoff'] ) ? min( 24, absint( $p['cutoff'] ) ) : 18,
			'horizon'   => isset( $p['horizon'] ) ? max( 1, absint( $p['horizon'] ) ) : 30,
			'blocked'   => isset( $p['blocked'] ) ? sanitize_textarea_field( $p['blocked'] ) : '',
			'lines'     => $lines,
			'off_lines' => array_values( array_diff( $all_ids, $on ) ),
			'rules'     => isset( $p['rules'] ) ? sanitize_textarea_field( $p['rules'] ) : '',
		),
		false
	);
	pm_metro_flush_booked();
	wp_safe_redirect( add_query_arg( 'pm_saved', '1', admin_url( 'admin.php?page=pm-metro' ) ) );
	exit;
}
add_action( 'admin_post_pm_metro_save', 'pm_metro_save' );

/**
 * Tela de configuração e agenda.
 */
function pm_metro_page() {
	$s      = pm_metro_settings();
	$wk     = array( 'Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado' );
	$orders = pm_metro_upcoming_orders();
	usort(
		$orders,
		function ( $a, $b ) {
			return strcmp( $a->get_meta( '_pm_metro_date' ) . $a->get_meta( '_pm_metro_slot' ), $b->get_meta( '_pm_metro_date' ) . $b->get_meta( '_pm_metro_slot' ) );
		}
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Entrega no metrô/trem', 'palestra-mantos' ); ?></h1>
		<?php if ( isset( $_GET['pm_saved'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Salvo!', 'palestra-mantos' ); ?></p></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Próximas retiradas', 'palestra-mantos' ); ?></h2>
		<?php if ( ! $orders ) : ?>
			<p><?php esc_html_e( 'Nenhuma retirada agendada ainda.', 'palestra-mantos' ); ?></p>
		<?php else : ?>
			<table class="widefat striped" style="max-width:980px">
				<thead><tr><th><?php esc_html_e( 'Data', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Horário', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Estação', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Pedido', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Cliente', 'palestra-mantos' ); ?></th><th><?php esc_html_e( 'Status', 'palestra-mantos' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $orders as $o ) : ?>
					<tr>
						<td><?php echo esc_html( pm_metro_date_label( $o->get_meta( '_pm_metro_date' ) ) ); ?></td>
						<td><?php echo esc_html( $o->get_meta( '_pm_metro_slot' ) ); ?></td>
						<td><?php echo esc_html( $o->get_meta( '_pm_metro_station' ) . ' (L' . $o->get_meta( '_pm_metro_line' ) . ')' ); ?></td>
						<td><a href="<?php echo esc_url( $o->get_edit_order_url() ); ?>">#<?php echo esc_html( $o->get_order_number() ); ?></a></td>
						<td><?php echo esc_html( $o->get_formatted_billing_full_name() . ' · ' . $o->get_billing_phone() ); ?></td>
						<td><?php echo esc_html( wc_get_order_status_name( $o->get_status() ) ); ?></td>
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
				<tr><th scope="row"><label for="pm-fee"><?php esc_html_e( 'Taxa (R$)', 'palestra-mantos' ); ?></label></th><td><input id="pm-fee" name="fee" type="text" class="small-text" value="<?php echo esc_attr( $s['fee'] ); ?>"> <span class="description"><?php esc_html_e( '0 = grátis', 'palestra-mantos' ); ?></span></td></tr>
				<tr><th scope="row"><?php esc_html_e( 'Dias de entrega', 'palestra-mantos' ); ?></th><td>
					<?php foreach ( $wk as $i => $name ) : ?>
						<label style="margin-right:14px"><input type="checkbox" name="days[]" value="<?php echo esc_attr( $i ); ?>" <?php checked( in_array( $i, array_map( 'intval', (array) $s['days'] ), true ) ); ?>> <?php echo esc_html( $name ); ?></label>
					<?php endforeach; ?>
				</td></tr>
				<tr><th scope="row"><label for="pm-slots"><?php esc_html_e( 'Horários', 'palestra-mantos' ); ?></label></th><td><textarea id="pm-slots" name="slots" rows="4" class="large-text" style="max-width:340px"><?php echo esc_textarea( $s['slots'] ); ?></textarea><p class="description"><?php esc_html_e( 'Um por linha. Ex.: 18:00 às 19:00', 'palestra-mantos' ); ?></p></td></tr>
				<tr><th scope="row"><label for="pm-cap"><?php esc_html_e( 'Entregas por horário', 'palestra-mantos' ); ?></label></th><td><input id="pm-cap" name="capacity" type="number" min="0" class="small-text" value="<?php echo esc_attr( $s['capacity'] ); ?>"> <span class="description"><?php esc_html_e( 'Quando lotar, o horário some. 0 = sem limite.', 'palestra-mantos' ); ?></span></td></tr>
				<tr><th scope="row"><label for="pm-lead"><?php esc_html_e( 'Antecedência', 'palestra-mantos' ); ?></label></th><td><input id="pm-lead" name="lead" type="number" min="0" class="small-text" value="<?php echo esc_attr( $s['lead'] ); ?>"> <?php esc_html_e( 'dia(s). Pedidos feitos depois das', 'palestra-mantos' ); ?> <input name="cutoff" type="number" min="0" max="24" class="small-text" value="<?php echo esc_attr( $s['cutoff'] ); ?>">h <?php esc_html_e( 'ganham mais 1 dia.', 'palestra-mantos' ); ?></td></tr>
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
