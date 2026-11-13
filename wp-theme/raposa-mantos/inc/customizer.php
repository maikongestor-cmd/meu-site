<?php
/**
 * Opções em Aparência → Personalizar → Raposa Mantos.
 *
 * @package RaposaMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra as opções.
 *
 * @param WP_Customize_Manager $wp_customize Gerenciador.
 */
function pm_customize_register( $wp_customize ) {
	$d = pm_defaults();

	$wp_customize->add_panel(
		'pm_panel',
		array(
			'title'    => __( 'Raposa Mantos', 'palestra-mantos' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'pm_contact' => __( 'Contato e redes sociais', 'palestra-mantos' ),
		'pm_store'   => __( 'Loja: Pix, frete e parcelamento', 'palestra-mantos' ),
		'pm_home'    => __( 'Página inicial', 'palestra-mantos' ),
		'pm_slides'  => __( 'Banners rotativos', 'palestra-mantos' ),
	);
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section(
			$id,
			array(
				'title' => $title,
				'panel' => 'pm_panel',
			)
		);
	}

	$fields = array(
		// key => array( section, label, type, sanitize ).
		'topbar'        => array( 'pm_store', 'Texto da barra do topo', 'text', 'sanitize_text_field' ),
		'pix_discount'  => array( 'pm_store', 'Desconto no Pix (%) — use 0 se o desconto for configurado no plugin de pagamento', 'number', 'absint' ),
		'free_shipping' => array( 'pm_store', 'Frete grátis a partir de (R$) — só informativo; configure em WooCommerce → Configurações → Entrega', 'number', 'absint' ),
		'installments'  => array( 'pm_store', 'Parcelamento máximo no cartão (vezes)', 'number', 'absint' ),
		'whatsapp'      => array( 'pm_contact', 'WhatsApp (com DDI e DDD, só números. Ex.: 5511999999999)', 'text', 'sanitize_text_field' ),
		'whatsapp_msg'  => array( 'pm_contact', 'Mensagem inicial do WhatsApp', 'text', 'sanitize_text_field' ),
		'phone'         => array( 'pm_contact', 'Telefone exibido', 'text', 'sanitize_text_field' ),
		'email'         => array( 'pm_contact', 'E-mail', 'email', 'sanitize_email' ),
		'instagram'     => array( 'pm_contact', 'Instagram (link completo)', 'url', 'esc_url_raw' ),
		'facebook'      => array( 'pm_contact', 'Facebook (link completo, opcional)', 'url', 'esc_url_raw' ),
		'tiktok'        => array( 'pm_contact', 'TikTok (link completo, opcional)', 'url', 'esc_url_raw' ),
		'address'       => array( 'pm_contact', 'Cidade / endereço', 'text', 'sanitize_text_field' ),
		'company'       => array( 'pm_contact', 'Razão social / nome no rodapé', 'text', 'sanitize_text_field' ),
		'cnpj'          => array( 'pm_contact', 'CNPJ', 'text', 'sanitize_text_field' ),
		'slide1_eyebrow' => array( 'pm_slides', 'Banner 1 — texto pequeno', 'text', 'sanitize_text_field' ),
		'slide1_title'   => array( 'pm_slides', 'Banner 1 — título (use <em>destaque</em>)', 'text', 'wp_kses_post' ),
		'slide1_text'    => array( 'pm_slides', 'Banner 1 — texto', 'textarea', 'sanitize_textarea_field' ),
		'slide1_points'  => array( 'pm_slides', 'Banner 1 — destaques (um por linha)', 'textarea', 'sanitize_textarea_field' ),
		'slide1_button'  => array( 'pm_slides', 'Banner 1 — texto do botão', 'text', 'sanitize_text_field' ),
		'slide1_link'    => array( 'pm_slides', 'Banner 1 — link do botão (vazio = loja)', 'url', 'esc_url_raw' ),
		'slide2_eyebrow' => array( 'pm_slides', 'Banner 2 — texto pequeno', 'text', 'sanitize_text_field' ),
		'slide2_title'   => array( 'pm_slides', 'Banner 2 — título (use <em>destaque</em>)', 'text', 'wp_kses_post' ),
		'slide2_text'    => array( 'pm_slides', 'Banner 2 — texto', 'textarea', 'sanitize_textarea_field' ),
		'slide2_points'  => array( 'pm_slides', 'Banner 2 — destaques (um por linha)', 'textarea', 'sanitize_textarea_field' ),
		'slide2_button'  => array( 'pm_slides', 'Banner 2 — texto do botão', 'text', 'sanitize_text_field' ),
		'slide2_link'    => array( 'pm_slides', 'Banner 2 — link do botão (vazio = Conjuntos)', 'url', 'esc_url_raw' ),
		'slide3_eyebrow' => array( 'pm_slides', 'Banner 3 — texto pequeno', 'text', 'sanitize_text_field' ),
		'slide3_title'   => array( 'pm_slides', 'Banner 3 — título (use <em>destaque</em>)', 'text', 'wp_kses_post' ),
		'slide3_text'    => array( 'pm_slides', 'Banner 3 — texto', 'textarea', 'sanitize_textarea_field' ),
		'slide3_points'  => array( 'pm_slides', 'Banner 3 — destaques (um por linha)', 'textarea', 'sanitize_textarea_field' ),
		'slide3_button'  => array( 'pm_slides', 'Banner 3 — texto do botão', 'text', 'sanitize_text_field' ),
		'slide3_link'    => array( 'pm_slides', 'Banner 3 — link do botão (vazio = Kit de camisetas)', 'url', 'esc_url_raw' ),
		'ig_reviews_count' => array( 'pm_home', 'Avaliações no Instagram — total (ex.: 200). Use 0 para esconder.', 'number', 'absint' ),
		'ig_reviews_url'   => array( 'pm_home', 'Avaliações no Instagram — link (destaque "Avaliações" ou perfil). Vazio = perfil do Instagram.', 'url', 'esc_url_raw' ),
		'welcome_title' => array( 'pm_home', 'Apresentação — título', 'text', 'sanitize_text_field' ),
		'welcome_text'  => array( 'pm_home', 'Apresentação — texto (linha em branco separa parágrafos)', 'textarea', 'sanitize_textarea_field' ),
		'welcome_sign'  => array( 'pm_home', 'Apresentação — frase final', 'text', 'sanitize_text_field' ),
		'news_title'    => array( 'pm_home', 'Newsletter — título', 'text', 'sanitize_text_field' ),
		'news_text'     => array( 'pm_home', 'Newsletter — texto', 'text', 'sanitize_text_field' ),
	);

	foreach ( $fields as $key => $f ) {
		$wp_customize->add_setting(
			'pm_' . $key,
			array(
				'default'           => $d[ $key ],
				'sanitize_callback' => $f[3],
			)
		);
		$wp_customize->add_control(
			'pm_' . $key,
			array(
				'section' => $f[0],
				'label'   => $f[1],
				'type'    => $f[2],
			)
		);
	}

	foreach ( array(
		'slide1_image' => 'Banner 1 — imagem',
		'slide2_image' => 'Banner 2 — imagem',
		'slide3_image' => 'Banner 3 — imagem',
	) as $key => $label ) {
		$wp_customize->add_setting(
			'pm_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'pm_' . $key,
				array(
					'section' => 'pm_slides',
					'label'   => $label,
				)
			)
		);
	}
}
add_action( 'customize_register', 'pm_customize_register' );
