<?php
/**
 * Instalação do conteúdo inicial em um clique:
 * páginas (rascunhos de texto), categorias, produtos com tamanhos P–GG,
 * menus, página inicial e configurações básicas da loja em reais.
 *
 * Aparência → Configurar Palestra Mantos.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Página do admin.
 */
function pm_setup_admin_menu() {
	add_theme_page(
		__( 'Configurar Palestra Mantos', 'palestra-mantos' ),
		__( 'Configurar Palestra Mantos', 'palestra-mantos' ),
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
		'<div class="notice notice-success"><p><strong>Palestra Mantos:</strong> %s <a class="button button-primary" href="%s">%s</a></p></div>',
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
		);
		$log = pm_run_setup( $opts );
		update_option( 'pm_setup_done', 1 );
	}
	$has_wc = class_exists( 'WooCommerce' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Configurar Palestra Mantos', 'palestra-mantos' ); ?></h1>

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
			<p><label><input type="checkbox" name="pm_prices" value="1" <?php disabled( ! $has_wc ); ?>> <?php esc_html_e( 'Atualizar os preços dos produtos que já existem para a tabela atual (calças R$ 147, conjunto R$ 267, camisetas R$ 127, kit R$ 187). Isso substitui preços editados à mão.', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_menus" value="1" checked> <?php esc_html_e( 'Menus do topo e do rodapé', 'palestra-mantos' ); ?></label></p>
			<p><label><input type="checkbox" name="pm_store" value="1" <?php checked( $has_wc ); ?> <?php disabled( ! $has_wc ); ?>> <?php esc_html_e( 'Loja em reais (R$), endereço no Brasil, frete grátis acima de R$ 249 e checkout clássico (compatível com Appmax e Mercado Pago)', 'palestra-mantos' ); ?></label></p>
			<p><button class="button button-primary button-hero" name="pm_run_setup" value="1"><?php esc_html_e( 'Criar conteúdo da loja', 'palestra-mantos' ); ?></button></p>
		</form>

		<h2 style="margin-top:32px"><?php esc_html_e( 'Próximos passos', 'palestra-mantos' ); ?></h2>
		<ol style="max-width:720px">
			<li><?php esc_html_e( 'Aparência → Personalizar → Palestra Mantos: WhatsApp, Instagram, e-mail, CNPJ, textos do banner e desconto do Pix.', 'palestra-mantos' ); ?></li>
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
	$draft = '<p class="pm-note"><strong>Rascunho:</strong> revise este texto em Páginas → Editar antes de divulgar o site.</p>';

	return array(
		'inicio'                  => array( 'Início', '<!-- A página inicial é montada pelo tema. Edite os textos em Aparência → Personalizar → Palestra Mantos. -->' ),
		'quem-somos'              => array(
			'Quem Somos',
			$draft .
			'<h2>Paixão que veste</h2>
<p>A <strong>Palestra Mantos</strong> nasceu da paixão pelo verde e branco e da vontade de levar essa história para o dia a dia. Criamos moletons, calças e camisetas licenciados que unem conforto, qualidade e o orgulho de carregar o Palestra no peito.</p>
<h2>Nosso compromisso</h2>
<ul>
<li><strong>Produtos licenciados:</strong> todas as peças são oficiais e autorizadas.</li>
<li><strong>Qualidade:</strong> tecidos de alta gramatura, bordados e estampas que duram.</li>
<li><strong>Atendimento humano:</strong> fale com a gente pelo WhatsApp sempre que precisar.</li>
<li><strong>Entrega para todo o Brasil</strong>, com frete grátis acima de R$ 249.</li>
</ul>
<p>Obrigado por fazer parte da nossa história. <strong>Avanti!</strong></p>',
		),
		'trocas-e-devolucoes'     => array(
			'Trocas e Devoluções',
			$draft .
			'<h2>Primeira troca grátis</h2>
<p>Se o tamanho não serviu ou você mudou de ideia, a primeira troca é por nossa conta.</p>
<h2>Prazos</h2>
<ul>
<li><strong>Arrependimento:</strong> até 7 dias corridos após o recebimento, conforme o Código de Defesa do Consumidor (art. 49). Devolvemos 100% do valor pago.</li>
<li><strong>Troca por tamanho ou modelo:</strong> até 30 dias após o recebimento.</li>
<li><strong>Defeito de fabricação:</strong> até 90 dias após o recebimento.</li>
</ul>
<h2>Condições</h2>
<ul>
<li>A peça deve estar sem uso, sem lavagem, com etiqueta e na embalagem original.</li>
<li>Produtos com marcas de uso, perfume ou avarias causadas pelo cliente não serão aceitos.</li>
</ul>
<h2>Como solicitar</h2>
<ol>
<li>Fale com a gente pelo <a href="' . $whats . '" target="_blank" rel="noopener">WhatsApp</a> ou pelo e-mail ' . $email . ' informando o número do pedido.</li>
<li>Enviaremos o código de postagem para você levar o produto a uma agência dos Correios.</li>
<li>Assim que recebermos e conferirmos a peça, enviamos a troca ou fazemos o reembolso em até 10 dias úteis.</li>
</ol>',
		),
		'guia-de-medidas'         => array(
			'Guia de Medidas',
			$draft .
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
			$draft .
			'<p>A Palestra Mantos respeita a sua privacidade e trata os seus dados pessoais de acordo com a Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD).</p>
<h2>Quais dados coletamos</h2>
<p>Nome, CPF, e-mail, telefone e endereço informados na compra ou no cadastro, além de dados de navegação (cookies) para o funcionamento do site.</p>
<h2>Para que usamos</h2>
<ul>
<li>Processar pedidos, pagamentos e entregas;</li>
<li>Prestar atendimento e enviar informações sobre seus pedidos;</li>
<li>Enviar novidades e promoções, somente com o seu consentimento;</li>
<li>Cumprir obrigações legais e fiscais.</li>
</ul>
<h2>Pagamentos</h2>
<p>Os pagamentos são processados pelos parceiros Appmax e Mercado Pago, em ambiente seguro. Não armazenamos os dados do seu cartão.</p>
<h2>Compartilhamento</h2>
<p>Compartilhamos dados apenas com parceiros necessários para concluir a compra (meios de pagamento, transportadoras e emissão de nota fiscal).</p>
<h2>Seus direitos</h2>
<p>Você pode pedir acesso, correção ou exclusão dos seus dados a qualquer momento pelo e-mail ' . $email . '.</p>',
		),
		'contato'                 => array(
			'Contato',
			'<p>Fale com a gente! Respondemos de segunda a sexta, das 9h às 18h.</p>
<p><a class="pm-btn pm-btn--whats" href="' . $whats . '" target="_blank" rel="noopener">Chamar no WhatsApp</a></p>
<ul>
<li><strong>WhatsApp:</strong> ' . esc_html( pm_opt( 'phone' ) ) . '</li>
<li><strong>E-mail:</strong> <a href="mailto:' . $email . '">' . $email . '</a></li>
<li><strong>Instagram:</strong> <a href="' . esc_url( pm_opt( 'instagram' ) ) . '" target="_blank" rel="noopener">' . esc_html( preg_replace( '#^https?://(www\.)?#', '', pm_opt( 'instagram' ) ) ) . '</a></li>
</ul>',
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
			'name'     => 'palestra-' . $file,
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
			'sku'     => 'PM-CALCA-BRANCA',
			'name'    => 'Calça Moletom Palestra Branca – Logo Verde e Vermelho',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-branca-feminina.webp' ),
			'short'   => 'Calça jogger em moletom flanelado, cós com elástico e punhos nas barras. Logo P bordado em verde e vermelho.',
		),
		array(
			'sku'     => 'PM-CALCA-VERDE',
			'name'    => 'Calça Moletom Palestra Verde – Logo Branco',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-verde.webp' ),
			'short'   => 'Calça jogger verde em moletom flanelado, com cordão de ajuste, bolsos laterais e logo P em branco.',
		),
		array(
			'sku'     => 'PM-CONJUNTO-BRANCO',
			'name'    => 'Conjunto Moletom Palestra Branco – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-branco-feminino.webp', 'conjunto-branco-masculino.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger. Logo P bordado em verde e vermelho e detalhe da bandeira italiana.',
		),
		array(
			'sku'     => 'PM-CAMISETA-P',
			'name'    => 'Camiseta Palestra Branca – Logo P',
			'cat'     => 'camisetas',
			'regular' => '127.00',
			'sale'    => '',
			'images'  => array( 'camiseta-logo-p.webp' ),
			'short'   => 'Camiseta oversized em algodão, logo P no peito e bandeira italiana na barra.',
		),
		array(
			'sku'     => 'PM-CAMISETA-ESCUDO',
			'name'    => 'Camiseta Palestra Branca – Escudo',
			'cat'     => 'camisetas',
			'regular' => '127.00',
			'sale'    => '',
			'images'  => array( 'camiseta-escudo.webp' ),
			'short'   => 'Camiseta oversized em algodão com o escudo histórico no peito e bandeira italiana na barra.',
		),
		array(
			'sku'     => 'PM-KIT-CAMISETAS',
			'name'    => 'Kit 2 Camisetas Oversized Palestra – Logo P + Escudo',
			'cat'     => 'camisetas',
			'regular' => '187.00',
			'sale'    => '',
			'images'  => array( 'kit-camisetas.webp', 'camiseta-logo-p.webp', 'camiseta-escudo.webp' ),
			'short'   => 'Kit com 2 camisetas oversized em algodão: uma com o logo P e outra com o escudo histórico, ambas com a bandeira italiana na barra. As duas peças vêm no tamanho escolhido.',
		),
	);
}

/**
 * Cria categorias e produtos variáveis (P, M, G, GG).
 *
 * @return array
 */
function pm_setup_products() {
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

	$desc = '<p>Peça oficial e licenciada <strong>Palestra Mantos</strong>.</p>
<ul>
<li>Tamanhos: P, M, G e GG — consulte o Guia de Medidas;</li>
<li>Lavar do avesso, à mão ou em ciclo delicado;</li>
<li>Não usar alvejante e não passar ferro sobre o bordado/estampa.</li>
</ul>
<p><em>Descrição de rascunho — complete com composição do tecido e detalhes da peça.</em></p>';

	foreach ( pm_products_catalog() as $item ) {
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
		$product->set_short_description( $item['short'] );
		$product->set_description( $desc );
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
			$variation->set_weight( 'camisetas' === $item['cat'] ? '0.3' : '0.7' );
			$variation->save();
		}
		WC_Product_Variable::sync( $product_id );
		wc_delete_product_transients( $product_id );

		/* translators: %s: produto. */
		$log[] = sprintf( __( 'Produto criado: %s (P, M, G, GG)', 'palestra-mantos' ), $item['name'] );
	}
	update_option( 'pm_products_done', 1 );

	return $log;
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
		if ( $product->is_type( 'variable' ) ) {
			WC_Product_Variable::sync( $id );
		}
		wc_delete_product_transients( $id );
		/* translators: 1: produto, 2: preço. */
		$log[] = sprintf( __( 'Preço atualizado: %1$s → R$ %2$s', 'palestra-mantos' ), $item['name'], number_format( (float) ( '' !== $item['sale'] ? $item['sale'] : $item['regular'] ), 2, ',', '.' ) );
	}
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
		foreach ( array( 'guia-de-medidas', 'trocas-e-devolucoes', 'quem-somos', 'contato' ) as $slug ) {
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
