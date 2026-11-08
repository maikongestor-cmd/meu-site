<?php
/**
 * Opções em Aparência → Personalizar → Palestra Mantos.
 *
 * @package PalestraMantos
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
			'title'    => __( 'Palestra Mantos', 'palestra-mantos' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'pm_contact' => __( 'Contato e redes sociais', 'palestra-mantos' ),
		'pm_store'   => __( 'Loja: Pix, frete e parcelamento', 'palestra-mantos' ),
		'pm_home'    => __( 'Página inicial (banner)', 'palestra-mantos' ),
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
		'hero_eyebrow'  => array( 'pm_home', 'Banner — texto pequeno acima do título', 'text', 'sanitize_text_field' ),
		'hero_title'    => array( 'pm_home', 'Banner — título (pode usar <em>destaque</em>)', 'text', 'wp_kses_post' ),
		'hero_text'     => array( 'pm_home', 'Banner — texto', 'textarea', 'sanitize_textarea_field' ),
		'split_title'   => array( 'pm_home', 'Seção "dia a dia" — título', 'text', 'sanitize_text_field' ),
		'split_text'    => array( 'pm_home', 'Seção "dia a dia" — texto', 'textarea', 'sanitize_textarea_field' ),
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
		'hero_image_1' => 'Banner — foto principal (vertical 4:5)',
		'hero_image_2' => 'Banner — foto secundária (vertical 4:5)',
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
					'section' => 'pm_home',
					'label'   => $label,
				)
			)
		);
	}
}
add_action( 'customize_register', 'pm_customize_register' );
