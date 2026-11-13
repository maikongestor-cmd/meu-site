<?php
/**
 * Instalação do conteúdo inicial em um clique:
 * páginas (rascunhos de texto), categorias, produtos com tamanhos P–GG,
 * menus, página inicial e configurações básicas da loja em reais.
 *
 * Aparência → Configurar Mengo Mantos.
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Página do admin.
 */
function pm_setup_admin_menu() {
	add_theme_page(
		__( 'Configurar Mengo Mantos', 'palestra-mantos' ),
		__( 'Configurar Mengo Mantos', 'palestra-mantos' ),
		'manage_options',
		'pm-setup',
		'pm_setup_admin_page'
	);
}
add_action( 'admin_menu', 'pm_setup_admin_menu' );

/**
 * Aviso depois de ativar o tema.
 */
function pm_setup_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$missing_products = class_exists( 'WooCommerce' ) && ! get_option( 'pm_products_done' );
	if ( get_option( 'pm_setup_done' ) && ! $missing_products ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_pm-setup' === $screen->id ) {
		return;
	}
	printf(
		'<div class="notice notice-success"><p><strong>Mengo Mantos:</strong> %s <a class="button button-primary" href="%s">%s</a></p></div>',
		get_option( 'pm_setup_done' )
			? esc_html__( 'O WooCommerce está ativo, mas os produtos e as categorias ainda não foram criados. Rode a configuração de novo.', 'palestra-mantos' )
			: esc_html__( 'Tema ativado! Clique para criar as páginas, os produtos e os menus da loja automaticamente.', 'palestra-mantos' ),
		esc_url( admin_url( 'themes.php?page=pm-setup' ) ),
		esc_html__( 'Configurar agora', 'palestra-mantos' )
	);
}
add_action( 'admin_notices', 'pm_setup_admin_notice' );

/**
 * Tela de configuração.
 */
function pm_setup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$log = array();
	if ( isset( $_POST['pm_run_setup'] ) && check_admin_referer( 'pm_run_setup' ) ) {
		$opts = array(
			'pages'    => ! empty( $_POST['pm_pages'] ),
			'products' => ! empty( $_POST['pm_products'] ),
			'menus'    => ! empty( $_POST['pm_menus'] ),
			'store'    => ! empty( $_POST['pm_store'] ),
			'prices'   => ! empty( $_POST['pm_prices'] ),
			'clean'    => ! empty( $_POST['pm_clean'] ),
			'desc'     => ! empty( $_POST['pm_desc'] ),
			'igrev'    => ! empty( $_POST['pm_igreviews'] ),
		);
		$log = pm_run_setup( $opts );
		update_option( 'pm_setup_done', 1 );
	}
	$has_wc = class_exists( 'WooCommerce' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Configurar Mengo Mantos', 'palestra-mantos' ); ?></h1>

		<?php if ( $log ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Pronto!', 'palestra-mantos' ); ?></strong></p><ul style="list-style:disc;padding-left:20px">
				<?php foreach ( $log as $line ) : ?>
					<li><?php echo esc_html( $line ); ?></li>
				<?php endforeach; ?>
			</ul><p><a class="button button-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank"><?php esc_html_e( 'Ver o site', 'palestra-mantos' ); ?></a></p></div>
		<?php endif; ?>

		<?php if ( ! $has_wc ) : ?>
			<div class="notice notice-warning"><p>
				<?php esc_html_e( 'Instale e ative o plugin WooCommerce (Plugins → Adicionar novo → "WooCommerce") antes de criar os produtos. As páginas e menus podem ser criados agora.', 'palestra-mantos' ); ?>
			</p></div>
		<?php endif; ?>

		<form method="post" style="background:#fff;border:1px solid #ddd;border-radius:8px;padding:20px 24px;max-width:720px">
			<?php wp_nonce_field( 'pm_run_setup' ); ?>
			<p><?php esc_html_e( 'Escolha o que criar. Pode rodar de novo sem duplicar: itens que já existem são mantidos.', 'palestra-mantos' ); ?></p>
			<p><label><input type="checkbox" name="pm_pages" value="1" checked> <?php esc_html_e( 'Páginas: Início, Quem Somos, Trocas e Devoluções, Guia de Medidas, Política de Privacidade e Contato (textos de rascunho)', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_products" value="1" <?php checked( $has_wc ); ?> <?php disabled( ! $has_wc ); ?>> <?php esc_html_e( 'Categorias e produtos com as fotos e tamanhos P, M, G e GG (preços de exemplo)', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_prices" value="1" <?php disabled( ! $has_wc ); ?>> <?php esc_html_e( 'Atualizar os preços dos produtos que já existem para a tabela atual (calças R$ 147, conjuntos R$ 267, camisetas R$ 127, kit R$ 187). Também marca conjunto, kit e calças como destaque. Isso substitui preços editados à mão.', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_desc" value="1" <?php disabled( ! $has_wc ); ?>> <?php esc_html_e( 'Atualizar as descrições de todos os produtos (conjuntos, calças e camisetas) com os textos da Mengo Mantos. Substitui a descrição atual.', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_clean" value="1"> <?php esc_html_e( 'Limpar os textos já salvos: tirar "licenciado/oficial" e "bordado" (Quem Somos, descrições e resumos dos produtos, banner).', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_menus" value="1" checked> <?php esc_html_e( 'Menus do topo e do rodapé', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_store" value="1" <?php checked( $has_wc ); ?> <?php disabled( ! $has_wc ); ?>> <?php esc_html_e( 'Loja em reais (R$), endereço no Brasil, frete grátis acima de R$ 249 e checkout clássico (compatível com Appmax e Mercado Pago)', 'palestra-mantos' ); ?></label></p>
			<p><button class="button button-primary button-hero" name="pm_run_setup" value="1"><?php esc_html_e( 'Criar conteúdo da loja', 'palestra-mantos' ); ?></button></p>
		</form>

		<h2 style="margin-top:32px"><?php esc_html_e( 'Próximos passos', 'palestra-mantos' ); ?></h2>
		<ol style="max-width:720px">
			<li><?php esc_html_e( 'Aparência → Personalizar → Mengo Mantos: WhatsApp, Instagram, e-mail, CNPJ, textos do banner e desconto do Pix.', 'palestra-mantos' ); ?></li>
			<li><?php esc_html_e( 'Plugins: instale "Mercado Pago payments for WooCommerce" e o plugin da Appmax e configure as credenciais em WooCommerce → Configurações → Pagamentos.', 'palestra-mantos' ); ?></li>
			<li><?php esc_html_e( 'Produtos: revise preços, descrições e estoque de cada produto.', 'palestra-mantos' ); ?></li>
			<li><?php esc_html_e( 'Páginas: revise os textos de rascunho (Quem Somos, Trocas, Guia de Medidas, Privacidade).', 'palestra-mantos' ); ?></li>
		</ol>
	</div>
	<?php
}

/**
 * Executa a instalação.
 *
 * @param array $opts Opções.
 * @return array Log.
 */
function pm_run_setup( $opts ) {
	$log = array();
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}

	if ( $opts['pages'] ) {
		$log = array_merge( $log, pm_setup_pages() );
	}
	if ( $opts['store'] && class_exists( 'WooCommerce' ) ) {
		$log = array_merge( $log, pm_setup_store() );
	}
	if ( $opts['products'] && class_exists( 'WooCommerce' ) ) {
		$log = array_merge( $log, pm_setup_products() );
	}
	if ( ! empty( $opts['prices'] ) && class_exists( 'WooCommerce' ) ) {
		$log = array_merge( $log, pm_update_prices() );
	}
	if ( ! empty( $opts['desc'] ) && class_exists( 'WooCommerce' ) ) {
		$log = array_merge( $log, pm_apply_product_descriptions() );
	}
	if ( ! empty( $opts['igrev'] ) && function_exists( 'pm_import_instagram_reviews' ) ) {
		$log = array_merge( $log, pm_import_instagram_reviews() );
	}
	if ( ! empty( $opts['clean'] ) ) {
		$log = array_merge( $log, pm_clean_licensed_texts() );
	}
	if ( $opts['menus'] ) {
		$log = array_merge( $log, pm_setup_menus() );
	}
	flush_rewrite_rules();
	return $log;
}

/* -------------------------------------------------------------------------
 * Páginas
 * ---------------------------------------------------------------------- */

/**
 * Cria uma página se não existir.
 *
 * @param string $slug    Slug.
 * @param string $title   Título.
 * @param string $content Conteúdo.
 * @return int ID.
 */
function pm_ensure_page( $slug, $title, $content ) {
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return (int) $page->ID;
	}
	return (int) wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => $content,
		)
	);
}

/**
 * Páginas institucionais.
 *
 * @return array
 */
function pm_setup_pages() {
	$log   = array();
	$pages = pm_pages_content();
	foreach ( $pages as $slug => $page ) {
		$exists = get_page_by_path( $slug );
		$id     = pm_ensure_page( $slug, $page[0], $page[1] );
		/* translators: %s: título. */
		$log[] = sprintf( $exists ? __( 'Página já existia: %s', 'palestra-mantos' ) : __( 'Página criada: %s', 'palestra-mantos' ), $page[0] );
		if ( 'inicio' === $slug ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $id );
		}
		if ( 'politica-de-privacidade' === $slug ) {
			update_option( 'wp_page_for_privacy_policy', $id );
		}
	}
	return $log;
}

/**
 * Conteúdo de rascunho das páginas.
 *
 * @return array slug => array( título, conteúdo )
 */
function pm_pages_content() {
	$whats = esc_url( pm_whatsapp_url() );
	$email = esc_html( pm_opt( 'email' ) );

	return array(
		'inicio'                  => array( 'Início', '<!-- A página inicial é montada pelo tema. Edite os textos em Aparência → Personalizar → Mengo Mantos. -->' ),
		'quem-somos'              => array(
			'Quem Somos',
						'<h2>Paixão que veste</h2>
<p>A <strong>Mengo Mantos</strong> nasceu da paixão pelo vermelho e preto e da vontade de levar essa história para o dia a dia. Criamos moletons, calças e camisetas que unem conforto, qualidade e o orgulho de carregar o Mengo no peito.</p>
<h2>Nosso compromisso</h2>
<ul>
<li><strong>Qualidade:</strong> tecidos de alta gramatura e estampas que duram.</li>
<li><strong>Atendimento humano:</strong> fale com a gente pelo WhatsApp sempre que precisar.</li>
<li><strong>Entrega para todo o Brasil</strong>, com frete grátis acima de R$ 249.</li>
</ul>
<p>Obrigado por fazer parte da nossa história. <strong>Vamos juntos!</strong></p>',
		),
		'trocas-e-devolucoes'     => array(
			'Trocas e Devoluções',
			'<p>A satisfação dos nossos clientes é prioridade. Caso seja necessário realizar uma troca ou devolução, seguimos as normas previstas no Código de Defesa do Consumidor.</p>
<h2>Primeira troca grátis</h2>
<p>A <strong>primeira troca é por nossa conta</strong>: você não paga nada para trocar o tamanho. Enviamos o código de postagem e o frete de ida e de volta é gratuito.</p>
<h2>Arrependimento da compra</h2>
<p>Você poderá solicitar a devolução do produto em até <strong>7 dias corridos</strong> após o recebimento.</p>
<p>O produto deverá estar:</p>
<ul>
<li>sem sinais de uso;</li>
<li>com etiqueta original;</li>
<li>na embalagem original;</li>
<li>acompanhado de todos os acessórios enviados.</li>
</ul>
<p>Após o recebimento e análise do produto, realizaremos o estorno conforme a forma de pagamento utilizada.</p>
<h2>Troca por tamanho</h2>
<p>Caso o tamanho escolhido não sirva, você poderá solicitar a troca em até <strong>30 dias corridos</strong> após o recebimento.</p>
<p>O produto deverá estar:</p>
<ul>
<li>sem uso;</li>
<li>limpo;</li>
<li>sem odores;</li>
<li>com etiquetas originais.</li>
</ul>
<p>Após a conferência, enviaremos as orientações para a troca.</p>
<h2>Produtos com defeito</h2>
<p>Caso o produto apresente defeito de fabricação, entre em contato conosco em até <strong>90 dias</strong> após o recebimento. Nossa equipe realizará uma análise para confirmar o defeito e providenciar a solução adequada.</p>
<h2>Como solicitar</h2>
<p>Entre em contato pelo <a href="' . $whats . '" target="_blank" rel="noopener">WhatsApp</a> ou pela nossa <a href="/contato/">página de contato</a>.</p>
<p>Informe:</p>
<ul>
<li>número do pedido;</li>
<li>nome completo;</li>
<li>motivo da solicitação;</li>
<li>fotos do produto (quando necessário).</li>
</ul>',
		),
		'guia-de-medidas'         => array(
			'Guia de Medidas',
						'<p>Medidas aproximadas da peça aberta sobre uma superfície plana, em centímetros. Pode haver variação de 1 a 2 cm.</p>
<h2>Camisetas</h2>
<div class="pm-table-wrap"><table><thead><tr><th>Tamanho</th><th>Largura (peito)</th><th>Comprimento</th></tr></thead><tbody>
<tr><td>P</td><td>50</td><td>70</td></tr>
<tr><td>M</td><td>53</td><td>72</td></tr>
<tr><td>G</td><td>56</td><td>74</td></tr>
<tr><td>GG</td><td>59</td><td>76</td></tr>
</tbody></table></div>
<h2>Moletons</h2>
<div class="pm-table-wrap"><table><thead><tr><th>Tamanho</th><th>Largura (peito)</th><th>Comprimento</th><th>Manga</th></tr></thead><tbody>
<tr><td>P</td><td>54</td><td>66</td><td>60</td></tr>
<tr><td>M</td><td>57</td><td>68</td><td>62</td></tr>
<tr><td>G</td><td>60</td><td>70</td><td>64</td></tr>
<tr><td>GG</td><td>63</td><td>72</td><td>66</td></tr>
</tbody></table></div>
<h2>Calças</h2>
<div class="pm-table-wrap"><table><thead><tr><th>Tamanho</th><th>Cintura (sem esticar)</th><th>Quadril</th><th>Comprimento</th></tr></thead><tbody>
<tr><td>P</td><td>34</td><td>48</td><td>98</td></tr>
<tr><td>M</td><td>36</td><td>51</td><td>100</td></tr>
<tr><td>G</td><td>38</td><td>54</td><td>102</td></tr>
<tr><td>GG</td><td>40</td><td>57</td><td>104</td></tr>
</tbody></table></div>
<p><strong>Ficou em dúvida?</strong> <a href="' . $whats . '" target="_blank" rel="noopener">Chame no WhatsApp</a> com sua altura e peso que ajudamos a escolher.</p>',
		),
		'politica-de-privacidade' => array(
			'Política de Privacidade',
			'<p>Na Mengo Mantos valorizamos a sua privacidade. Todas as informações fornecidas durante a sua navegação e compra são tratadas com segurança e confidencialidade.</p>
<h2>Como usamos os seus dados</h2>
<p>Os dados coletados poderão ser utilizados para:</p>
<ul>
<li>processamento de pedidos;</li>
<li>emissão de notas fiscais;</li>
<li>atendimento ao cliente;</li>
<li>comunicação sobre pedidos;</li>
<li>envio de ofertas (quando autorizado);</li>
<li>melhoria da experiência em nosso site.</li>
</ul>
<h2>Compartilhamento</h2>
<p>Não comercializamos nem compartilhamos seus dados pessoais com terceiros, exceto quando necessário para:</p>
<ul>
<li>processamento de pagamentos;</li>
<li>empresas de transporte;</li>
<li>cumprimento de obrigações legais.</li>
</ul>
<h2>Cookies</h2>
<p>Nosso site utiliza cookies para melhorar a sua experiência de navegação. Ao utilizar nosso site, você concorda com esta Política de Privacidade.</p>
<h2>Seus dados</h2>
<p>Caso deseje solicitar alteração ou exclusão dos seus dados, entre em contato pelos nossos <a href="/contato/">canais oficiais</a>.</p>',
		),
		'contato'                 => array(
			'Contato',
			'<p>Fale com a gente! Respondemos de segunda a sexta, das 9h às 18h.</p>
<p><a class="pm-btn pm-btn--whats" href="' . $whats . '" target="_blank" rel="noopener">Chamar no WhatsApp</a></p>
<ul>
<li><strong>WhatsApp:</strong> (11) 98995-8223</li>
<li><strong>Instagram:</strong> <a href="https://www.instagram.com/mengomantos/" target="_blank" rel="noopener">@mengomantos</a></li>
</ul>
<p>Se preferir, envie uma mensagem pelo formulário abaixo.</p>',
		),
	);
}

/* -------------------------------------------------------------------------
 * Loja
 * ---------------------------------------------------------------------- */

/**
 * Configurações básicas do WooCommerce para o Brasil.
 *
 * @return array
 */
function pm_setup_store() {
	$log = array();

	update_option( 'woocommerce_currency', 'BRL' );
	update_option( 'woocommerce_currency_pos', 'left_space' );
	update_option( 'woocommerce_price_thousand_sep', '.' );
	update_option( 'woocommerce_price_decimal_sep', ',' );
	update_option( 'woocommerce_price_num_decimals', 2 );
	if ( ! get_option( 'woocommerce_default_country' ) || 0 !== strpos( get_option( 'woocommerce_default_country' ), 'BR' ) ) {
		update_option( 'woocommerce_default_country', 'BR:SP' );
	}
	update_option( 'woocommerce_weight_unit', 'kg' );
	update_option( 'woocommerce_dimension_unit', 'cm' );
	update_option( 'woocommerce_allowed_countries', 'specific' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'BR' ) );
	update_option( 'woocommerce_ship_to_countries', '' );
	update_option( 'woocommerce_enable_shipping_calc', 'yes' );
	update_option( 'woocommerce_shipping_cost_requires_address', 'yes' );
	update_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' );
	update_option( 'woocommerce_enable_guest_checkout', 'yes' );
	update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
	$log[] = __( 'Loja configurada em reais (R$ 1.234,56) e Brasil.', 'palestra-mantos' );

	// Carrinho e checkout clássicos (os plugins Appmax e Mercado Pago funcionam melhor assim).
	foreach ( array(
		'cart'     => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
		'checkout' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
	) as $page => $content ) {
		$page_id = wc_get_page_id( $page );
		if ( $page_id > 0 ) {
			wp_update_post(
				array(
					'ID'           => $page_id,
					'post_content' => $content,
				)
			);
		}
	}
	$log[] = __( 'Carrinho e checkout trocados para o modelo clássico.', 'palestra-mantos' );

	// Zona de entrega Brasil com frete fixo + frete grátis acima de R$ 249.
	if ( class_exists( 'WC_Shipping_Zones' ) && ! get_option( 'pm_shipping_zone_id' ) ) {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'Brasil' );
		$zone->add_location( 'BR', 'country' );
		$zone->save();

		$flat_id = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $flat_id . '_settings',
			array(
				'title'      => 'Entrega padrão',
				'tax_status' => 'none',
				'cost'       => '19.90',
			)
		);

		$free_id = $zone->add_shipping_method( 'free_shipping' );
		update_option(
			'woocommerce_free_shipping_' . $free_id . '_settings',
			array(
				'title'                => 'Frete grátis',
				'requires'             => 'min_amount',
				'min_amount'           => (string) (int) pm_opt( 'free_shipping' ),
				'ignore_discounts'     => 'no',
			)
		);
		update_option( 'pm_shipping_zone_id', $zone->get_id() );
		WC_Cache_Helper::get_transient_version( 'shipping', true );
		$log[] = __( 'Entrega: zona Brasil com frete fixo de R$ 19,90 (exemplo) e frete grátis acima de R$ 249.', 'palestra-mantos' );
	}

	return $log;
}

/* -------------------------------------------------------------------------
 * Produtos
 * ---------------------------------------------------------------------- */

/**
 * Importa uma imagem do tema para a Biblioteca de Mídia (uma vez só).
 *
 * @param string $file Arquivo em assets/img.
 * @param string $alt  Texto alternativo.
 * @return int ID do anexo ou 0.
 */
function pm_import_theme_image( $file, $alt ) {
	$map = get_option( 'pm_imported_images', array() );
	if ( ! empty( $map[ $file ] ) && get_post( $map[ $file ] ) ) {
		return (int) $map[ $file ];
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$src = PM_DIR . '/assets/img/' . $file;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload(
		array(
			'name'     => 'mengo-' . $file,
			'tmp_name' => $tmp,
		),
		0,
		$alt
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	$map[ $file ] = $id;
	update_option( 'pm_imported_images', $map, false );
	return (int) $id;
}

/**
 * Garante o atributo global "Tamanho" com P, M, G e GG.
 *
 * @return array array( attribute_id, taxonomy, term_ids[] )
 */
function pm_ensure_size_attribute() {
	$taxonomy = 'pa_tamanho';
	$attr_id  = wc_attribute_taxonomy_id_by_name( 'tamanho' );
	if ( ! $attr_id ) {
		$attr_id = wc_create_attribute(
			array(
				'name'         => 'Tamanho',
				'slug'         => 'tamanho',
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);
		if ( is_wp_error( $attr_id ) ) {
			return array( 0, $taxonomy, array() );
		}
	}
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy( $taxonomy, array( 'product', 'product_variation' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	}
	$ids   = array();
	$order = 0;
	foreach ( array( 'P', 'M', 'G', 'GG' ) as $size ) {
		$term = get_term_by( 'slug', strtolower( $size ), $taxonomy );
		if ( ! $term ) {
			$res = wp_insert_term( $size, $taxonomy, array( 'slug' => strtolower( $size ) ) );
			if ( is_wp_error( $res ) ) {
				continue;
			}
			$term_id = (int) $res['term_id'];
		} else {
			$term_id = (int) $term->term_id;
		}
		update_term_meta( $term_id, 'order', $order++ );
		$ids[] = $term_id;
	}
	return array( (int) $attr_id, $taxonomy, $ids );
}

/**
 * Catálogo inicial (preços de exemplo — ajuste depois no painel).
 *
 * @return array
 */
function pm_products_catalog() {
	return array(
		array(
			'sku'     => 'MM-CALCA-VERMELHA-CRF',
			'name'    => 'Calça Moletom Mengo Vermelha – CRF e Âncora',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-vermelha-ancora.webp' ),
			'short'   => 'Calça jogger vermelha em moletom flanelado, com cordão de ajuste, CRF e âncora em preto.',
			'order'   => 1,
		),
		array(
			'sku'     => 'MM-CALCA-PRETA-CRF',
			'name'    => 'Calça Moletom Mengo Preta – CRF, Âncora e 81·19·22·25',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-preta-ancora-anos.webp' ),
			'short'   => 'Calça jogger preta em moletom flanelado, com CRF e âncora em vermelho e os anos 81, 19, 22 e 25 na perna.',
			'order'   => 2,
		),
		array(
			'sku'     => 'MM-CALCA-BRANCA-CRF',
			'name'    => 'Calça Moletom Mengo Branca – CRF, Âncora e 81·19·22·25',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-branca-ancora-anos.webp', 'calca-branca-ancora-anos-feminina.webp' ),
			'short'   => 'Calça jogger branca em moletom flanelado, com CRF e âncora em vermelho e os anos 81, 19, 22 e 25 na perna.',
			'order'   => 3,
		),
		array(
			'sku'     => 'MM-CAMISETA-PRETA-LIB',
			'name'    => 'Camiseta Oversized Mengo Preta – 81·19·22·25',
			'cat'     => 'camisetas',
			'regular' => '137.00',
			'sale'    => '',
			'images'  => array( 'camiseta-preta-crf-frente.webp', 'camiseta-preta-crf-costas.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, preta, com o CRF vermelho no peito e a arte 81·19·22·25 nas costas.',
			'order'   => 4,
		),
		array(
			'sku'     => 'MM-CAMISETA-VERMELHA-LIB',
			'name'    => 'Camiseta Oversized Mengo Vermelha – 81·19·22·25',
			'cat'     => 'camisetas',
			'regular' => '137.00',
			'sale'    => '',
			'images'  => array( 'camiseta-vermelha-crf-frente.webp', 'camiseta-vermelha-crf-costas.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, vermelha, com o CRF preto no peito e a arte 81·19·22·25 nas costas.',
			'order'   => 5,
		),
		array(
			'sku'     => 'MM-CONJUNTO-PRETO',
			'name'    => 'Conjunto Moletom Mengo Preto – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-preto.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger em moletom flanelado preto.',
			'order'   => 6,
		),
		array(
			'sku'     => 'MM-CONJUNTO-VERMELHO',
			'name'    => 'Conjunto Moletom Mengo Vermelho – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-vermelho.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger em moletom flanelado vermelho.',
			'order'   => 7,
		),
	);
}

/**
 * Cria categorias e produtos variáveis (P, M, G, GG).
 *
 * @return array
 */
function pm_setup_products( $only_skus = array() ) {
	$log = array();

	// Categorias.
	$cat_ids = array();
	foreach ( pm_category_slugs() as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( ! $term ) {
			$res = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $res ) ) {
				$cat_ids[ $slug ] = (int) $res['term_id'];
				/* translators: %s: categoria. */
				$log[] = sprintf( __( 'Categoria criada: %s', 'palestra-mantos' ), $name );
			}
		} else {
			$cat_ids[ $slug ] = (int) $term->term_id;
		}
	}

	list( $attr_id, $taxonomy, $size_ids ) = pm_ensure_size_attribute();
	if ( ! $attr_id || ! $size_ids ) {
		$log[] = __( 'Não foi possível criar o atributo Tamanho.', 'palestra-mantos' );
		return $log;
	}

	$desc = '<ul>
<li>Tamanhos: P, M, G e GG — consulte o Guia de Medidas;</li>
<li>Lavar do avesso, à mão ou em ciclo delicado;</li>
<li>Não usar alvejante e não passar ferro sobre a estampa.</li>
</ul>
<p><em>Descrição de rascunho — complete com composição do tecido e detalhes da peça.</em></p>';

	foreach ( pm_products_catalog() as $item ) {
		if ( $only_skus && ! in_array( $item['sku'], $only_skus, true ) ) {
			continue;
		}
		// Não cria um segundo kit se a loja já tem um produto "Kit" (mesmo com outro código).
		if ( false !== stripos( $item['name'], 'Kit' ) && pm_store_has_kit() ) {
			/* translators: %s: produto. */
			$log[] = sprintf( __( 'Já existe um kit na loja, não criei outro: %s', 'palestra-mantos' ), $item['name'] );
			continue;
		}
		if ( wc_get_product_id_by_sku( $item['sku'] ) ) {
			/* translators: %s: produto. */
			$log[] = sprintf( __( 'Produto já existia: %s', 'palestra-mantos' ), $item['name'] );
			continue;
		}

		$image_ids = array();
		foreach ( $item['images'] as $img ) {
			$id = pm_import_theme_image( $img, $item['name'] );
			if ( $id ) {
				$image_ids[] = $id;
			}
		}

		$product = new WC_Product_Variable();
		$product->set_name( $item['name'] );
		$product->set_sku( $item['sku'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_featured( in_array( $item['sku'], array( 'MM-CALCA-VERMELHA-CRF', 'MM-CALCA-PRETA-CRF', 'MM-CALCA-BRANCA-CRF', 'MM-CAMISETA-PRETA-LIB' ), true ) );
		$product->set_short_description( $item['short'] );
		$descs = function_exists( 'pm_product_descriptions' ) ? pm_product_descriptions() : array();
		$product->set_description( isset( $descs[ $item['cat'] ] ) ? $descs[ $item['cat'] ] : $desc );
		if ( isset( $cat_ids[ $item['cat'] ] ) ) {
			$product->set_category_ids( array( $cat_ids[ $item['cat'] ] ) );
		}
		if ( $image_ids ) {
			$product->set_image_id( $image_ids[0] );
			$product->set_gallery_image_ids( array_slice( $image_ids, 1 ) );
		}

		$attribute = new WC_Product_Attribute();
		$attribute->set_id( $attr_id );
		$attribute->set_name( $taxonomy );
		$attribute->set_options( $size_ids );
		$attribute->set_position( 0 );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$product->set_attributes( array( $attribute ) );
		if ( isset( $item['order'] ) ) {
			$product->set_menu_order( (int) $item['order'] );
		}
		$product_id = $product->save();

		foreach ( array( 'p', 'm', 'g', 'gg' ) as $size_slug ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );
			$variation->set_attributes( array( $taxonomy => $size_slug ) );
			$variation->set_regular_price( $item['regular'] );
			if ( '' !== $item['sale'] ) {
				$variation->set_sale_price( $item['sale'] );
			}
			$variation->set_sku( $item['sku'] . '-' . strtoupper( $size_slug ) );
			$variation->set_manage_stock( false );
			$variation->set_stock_status( 'instock' );
			$variation->set_weight( 'conjuntos' === $item['cat'] ? '1.2' : ( 'camisetas' === $item['cat'] ? '0.3' : '0.7' ) );
			$variation->set_length( 'conjuntos' === $item['cat'] ? '35' : '30' );
			$variation->set_width( 'conjuntos' === $item['cat'] ? '30' : '25' );
			$variation->set_height( 'conjuntos' === $item['cat'] ? '10' : ( 'camisetas' === $item['cat'] ? '3' : '6' ) );
			$variation->save();
		}
		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );

		/* translators: %s: produto. */
		$log[] = sprintf( __( 'Produto criado: %s (P, M, G, GG)', 'palestra-mantos' ), $item['name'] );
	}
	if ( ! $only_skus ) {
		update_option( 'pm_products_done', 1 );
	}

	return $log;
}

/**
 * Produtos novos que entram sozinhos ao atualizar o tema (uma vez só, ao abrir o painel).
 */
function pm_auto_add_new_products() {
	if ( ! class_exists( 'WooCommerce' ) || ! current_user_can( 'manage_woocommerce' ) || wp_doing_ajax() ) {
		return;
	}
	$new = array( 'MM-CALCA-VERMELHA-CRF', 'MM-CALCA-PRETA-CRF', 'MM-CALCA-BRANCA-CRF', 'MM-CAMISETA-PRETA-LIB', 'MM-CAMISETA-VERMELHA-LIB' );
	$done = (array) get_option( 'pm_auto_products', array() );
	$todo = array_values( array_diff( $new, $done ) );
	if ( ! $todo ) {
		return;
	}
	update_option( 'pm_auto_products', array_values( array_unique( array_merge( $done, $todo ) ) ), false );
	$log = pm_setup_products( $todo );
	set_transient( 'pm_auto_products_log', $log, HOUR_IN_SECONDS );
}
add_action( 'admin_init', 'pm_auto_add_new_products', 30 );

/**
 * Aviso no painel com o que foi criado.
 */
function pm_auto_products_notice() {
	$log = get_transient( 'pm_auto_products_log' );
	if ( ! $log || ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}
	delete_transient( 'pm_auto_products_log' );
	echo '<div class="notice notice-success is-dismissible"><p><strong>Mengo Mantos:</strong> ' . esc_html( implode( ' · ', (array) $log ) ) . '</p></div>';
}
add_action( 'admin_notices', 'pm_auto_products_notice' );

/**
 * A loja já tem algum produto com "Kit" no nome?
 *
 * @return bool
 */
function pm_store_has_kit() {
	foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
		if ( false !== stripos( get_the_title( $id ), 'Kit' ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Atualiza os preços dos produtos do catálogo que já existem (todas as variações).
 *
 * @return array
 */
function pm_update_prices() {
	$log = array();
	foreach ( pm_products_catalog() as $item ) {
		$id = wc_get_product_id_by_sku( $item['sku'] );
		if ( ! $id ) {
			continue;
		}
		$product = wc_get_product( $id );
		if ( ! $product ) {
			continue;
		}
		// Se o produto foi transformado (ex.: camiseta virou kit), não mexe no preço.
		if ( ( false !== stripos( $item['name'], 'Kit' ) ) !== ( false !== stripos( $product->get_name(), 'Kit' ) ) ) {
			/* translators: %s: produto. */
			$log[] = sprintf( __( 'Preço mantido (produto foi alterado por você): %s', 'palestra-mantos' ), $product->get_name() );
			continue;
		}
		$targets = $product->is_type( 'variable' ) ? $product->get_children() : array( $id );
		foreach ( $targets as $target_id ) {
			$p = wc_get_product( $target_id );
			if ( ! $p ) {
				continue;
			}
			$p->set_regular_price( $item['regular'] );
			$p->set_sale_price( $item['sale'] );
			$p->save();
		}
		if ( in_array( $item['sku'], array( 'MM-CALCA-VERMELHA-CRF', 'MM-CALCA-PRETA-CRF', 'MM-CALCA-BRANCA-CRF', 'MM-CAMISETA-PRETA-LIB' ), true ) ) {
			$product = wc_get_product( $id );
			$product->set_featured( true );
			$product->save();
		}
		if ( $product->is_type( 'variable' ) ) {
			WC_Product_Variable::sync( $id );
		}
		wc_delete_product_transients( $id );
		/* translators: 1: produto, 2: preço. */
		$log[] = sprintf( __( 'Preço atualizado: %1$s → R$ %2$s', 'palestra-mantos' ), $item['name'], number_format( (float) ( '' !== $item['sale'] ? $item['sale'] : $item['regular'] ), 2, ',', '.' ) );
	}
	return $log;
}

/**
 * Remove "licenciado/oficial" dos textos criados pelo tema que já estão salvos.
 *
 * @return array
 */
function pm_clean_licensed_texts() {
	$pairs = array(
		'<p>Peça oficial e licenciada <strong>Mengo Mantos</strong>.</p>' => '',
		'<li><strong>Produtos licenciados:</strong> todas as peças são oficiais e autorizadas.</li>' => '',
		'camisetas licenciados que unem' => 'camisetas que unem',
		'camisetas licenciados para vestir' => 'camisetas para vestir',
		'Logo P bordado em'                  => 'Logo P em',
		'bordados e estampas que duram'      => 'estampas que duram',
		'sobre o bordado/estampa'            => 'sobre a estampa',
	);
	$log   = array();
	$ids   = array();
	$about = get_page_by_path( 'quem-somos' );
	if ( $about ) {
		$ids[] = $about->ID;
	}
	if ( post_type_exists( 'product' ) ) {
		$ids = array_merge( $ids, get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) );
	}
	$count = 0;
	foreach ( $ids as $id ) {
		$post = get_post( $id );
		$new  = str_replace( array_keys( $pairs ), array_values( $pairs ), $post->post_content );
		$new  = preg_replace( "/\n{3,}/", "\n\n", $new );
		$exc  = str_replace( array_keys( $pairs ), array_values( $pairs ), $post->post_excerpt );
		if ( $new !== $post->post_content || $exc !== $post->post_excerpt ) {
			wp_update_post( array( 'ID' => $id, 'post_content' => $new, 'post_excerpt' => $exc ) );
			$count++;
		}
	}
	foreach ( array( 'pm_hero_text', 'pm_slide1_text' ) as $mod ) {
		$val = get_theme_mod( $mod );
		if ( is_string( $val ) && false !== stripos( $val, 'licenciad' ) ) {
			set_theme_mod( $mod, trim( preg_replace( '/\s+licenciad[oa]s?/iu', '', $val ) ) );
			$count++;
		}
	}
	/* translators: %d: quantidade. */
	$log[] = sprintf( __( 'Textos limpos: %d item(ns) sem "licenciado".', 'palestra-mantos' ), $count );
	return $log;
}

/* -------------------------------------------------------------------------
 * Menus
 * ---------------------------------------------------------------------- */

/**
 * Cria (ou completa) os menus e atribui às posições do tema.
 * Se rodar de novo depois de ativar o WooCommerce, acrescenta as categorias
 * e a loja que faltarem, sem duplicar itens.
 *
 * @return array
 */
function pm_setup_menus() {
	$log       = array();
	$locations = get_theme_mod( 'nav_menu_locations', array() );

	// Menu principal: Início, categorias, todos os produtos e páginas.
	$menu_id = ! empty( $locations['primary'] ) ? (int) $locations['primary'] : pm_get_or_create_menu( 'Menu Principal' );
	if ( $menu_id ) {
		$added = 0;
		$added += pm_menu_ensure_custom( $menu_id, 'Início', home_url( '/' ) );
		if ( taxonomy_exists( 'product_cat' ) ) {
			foreach ( pm_category_slugs() as $slug => $name ) {
				$term = get_term_by( 'slug', $slug, 'product_cat' );
				if ( $term ) {
					$added += pm_menu_ensure_object( $menu_id, 'taxonomy', 'product_cat', (int) $term->term_id, $name );
				}
			}
		}
		if ( function_exists( 'wc_get_page_id' ) && wc_get_page_id( 'shop' ) > 0 ) {
			$added += pm_menu_ensure_object( $menu_id, 'post_type', 'page', wc_get_page_id( 'shop' ), 'Todos os produtos' );
		}
		foreach ( array( 'guia-de-medidas', 'trocas-e-devolucoes', 'quem-somos', 'politica-de-privacidade', 'contato' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				$added += pm_menu_ensure_object( $menu_id, 'post_type', 'page', (int) $page->ID, get_the_title( $page ) );
			}
		}
		pm_menu_sort( $menu_id );
		$locations['primary'] = $menu_id;
		/* translators: %d: quantidade. */
		$log[] = sprintf( __( 'Menu principal pronto (%d itens adicionados).', 'palestra-mantos' ), $added );
	}

	// Menu do rodapé.
	$footer_id = ! empty( $locations['footer'] ) ? (int) $locations['footer'] : pm_get_or_create_menu( 'Rodapé Institucional' );
	if ( $footer_id ) {
		foreach ( array( 'quem-somos', 'trocas-e-devolucoes', 'guia-de-medidas', 'politica-de-privacidade', 'contato' ) as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				pm_menu_ensure_object( $footer_id, 'post_type', 'page', (int) $page->ID, get_the_title( $page ) );
			}
		}
		$locations['footer'] = $footer_id;
		$log[]               = __( 'Menu do rodapé pronto.', 'palestra-mantos' );
	}

	set_theme_mod( 'nav_menu_locations', $locations );
	return $log;
}

/**
 * Ordena o menu: Início, categorias, todos os produtos, páginas.
 *
 * @param int $menu_id Menu.
 */
function pm_menu_sort( $menu_id ) {
	$cats  = array_keys( pm_category_slugs() );
	$pages = array( 'guia-de-medidas', 'trocas-e-devolucoes', 'quem-somos', 'contato' );
	$shop  = function_exists( 'wc_get_page_id' ) ? (int) wc_get_page_id( 'shop' ) : 0;
	foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue;
		}
		$rank = 100 + (int) $item->menu_order;
		if ( 'custom' === $item->type && untrailingslashit( $item->url ) === untrailingslashit( home_url( '/' ) ) ) {
			$rank = 1;
		} elseif ( 'product_cat' === $item->object ) {
			$term = get_term( (int) $item->object_id, 'product_cat' );
			$pos  = $term && ! is_wp_error( $term ) ? array_search( $term->slug, $cats, true ) : false;
			$rank = 10 + ( false === $pos ? 9 : (int) $pos );
		} elseif ( 'page' === $item->object && $shop && (int) $item->object_id === $shop ) {
			$rank = 30;
		} elseif ( 'page' === $item->object ) {
			$pos  = array_search( get_post_field( 'post_name', (int) $item->object_id ), $pages, true );
			$rank = 40 + ( false === $pos ? 9 : (int) $pos );
		}
		wp_update_post(
			array(
				'ID'         => $item->ID,
				'menu_order' => $rank,
			)
		);
	}
}

/**
 * Busca um menu pelo nome ou cria.
 *
 * @param string $name Nome.
 * @return int
 */
function pm_get_or_create_menu( $name ) {
	$menu = wp_get_nav_menu_object( $name );
	if ( $menu ) {
		return (int) $menu->term_id;
	}
	$id = wp_create_nav_menu( $name );
	return is_wp_error( $id ) ? 0 : (int) $id;
}

/**
 * Adiciona página/categoria ao menu se ainda não estiver nele.
 *
 * @param int    $menu_id   Menu.
 * @param string $type      post_type ou taxonomy.
 * @param string $object    page, product_cat...
 * @param int    $object_id ID.
 * @param string $title     Título.
 * @return int 1 se adicionou.
 */
function pm_menu_ensure_object( $menu_id, $type, $object, $object_id, $title ) {
	foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $item ) {
		if ( $item->type === $type && $item->object === $object && (int) $item->object_id === (int) $object_id ) {
			return 0;
		}
	}
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $title,
			'menu-item-object'    => $object,
			'menu-item-object-id' => $object_id,
			'menu-item-type'      => $type,
			'menu-item-status'    => 'publish',
		)
	);
	return 1;
}

/**
 * Adiciona link personalizado se ainda não estiver no menu.
 *
 * @param int    $menu_id Menu.
 * @param string $title   Título.
 * @param string $url     URL.
 * @return int 1 se adicionou.
 */
function pm_menu_ensure_custom( $menu_id, $title, $url ) {
	foreach ( (array) wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) ) as $item ) {
		if ( 'custom' === $item->type && untrailingslashit( $item->url ) === untrailingslashit( $url ) ) {
			return 0;
		}
	}
	pm_menu_add_custom( $menu_id, $title, $url );
	return 1;
}

/**
 * Adiciona uma página ao menu.
 *
 * @param int    $menu_id Menu.
 * @param int    $page_id Página.
 * @param string $title   Título opcional.
 * @return int ID do item.
 */
function pm_menu_add_page( $menu_id, $page_id, $title = '' ) {
	return (int) wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $title ? $title : get_the_title( $page_id ),
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $page_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}

/**
 * Adiciona um link ao menu.
 *
 * @param int    $menu_id Menu.
 * @param string $title   Título.
 * @param string $url     URL.
 * @return int
 */
function pm_menu_add_custom( $menu_id, $title, $url ) {
	return (int) wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'  => $title,
			'menu-item-url'    => $url,
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		)
	);
}

/**
 * Camisetas que entraram com a foto desenhada recebem as fotos reais (frente e costas).
 */
function pm_mengo_tshirt_photos() {
	if ( ! class_exists( 'WooCommerce' ) || ! current_user_can( 'manage_woocommerce' ) || wp_doing_ajax() || get_option( 'pm_mengo_tshirt_photos' ) ) {
		return;
	}
	update_option( 'pm_mengo_tshirt_photos', 1, false );
	$fotos = array(
		'MM-CAMISETA-PRETA-LIB'    => array( 'camiseta-preta-crf-frente.webp', 'camiseta-preta-crf-costas.webp' ),
		'MM-CAMISETA-VERMELHA-LIB' => array( 'camiseta-vermelha-crf-frente.webp', 'camiseta-vermelha-crf-costas.webp' ),
	);
	foreach ( $fotos as $sku => $files ) {
		$id      = wc_get_product_id_by_sku( $sku );
		$product = $id ? wc_get_product( $id ) : null;
		if ( ! $product ) {
			continue;
		}
		$current = basename( (string) get_attached_file( $product->get_image_id() ) );
		if ( $current && false === strpos( $current, 'crf-frente' ) ) {
			$ids = array();
			foreach ( $files as $file ) {
				$img = pm_import_theme_image( $file, $product->get_name() );
				if ( $img ) {
					$ids[] = $img;
				}
			}
			if ( $ids ) {
				$product->set_image_id( $ids[0] );
				$product->set_gallery_image_ids( array_slice( $ids, 1 ) );
				$product->save();
			}
		}
	}
}
add_action( 'admin_init', 'pm_mengo_tshirt_photos', 40 );
