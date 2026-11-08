<?php
/**
 * Funções auxiliares: ícones, links de contato.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retorna um ícone SVG inline.
 *
 * @param string $name Nome do ícone.
 * @return string
 */
function pm_icon( $name ) {
	$icons = array(
		'search'   => '<path d="M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm10 2-4.35-4.35" />',
		'user'     => '<path d="M20 21a8 8 0 0 0-16 0" /><circle cx="12" cy="7" r="4" />',
		'bag'      => '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" /><path d="M3 6h18M16 10a4 4 0 0 1-8 0" />',
		'menu'     => '<path d="M3 6h18M3 12h18M3 18h18" />',
		'close'    => '<path d="M18 6 6 18M6 6l12 12" />',
		'arrow'    => '<path d="M5 12h14m-6-6 6 6-6 6" />',
		'pix'      => '<path d="m12 2.5 4.2 4.2-4.2 4.2-4.2-4.2zM12 13.1l4.2 4.2-4.2 4.2-4.2-4.2zM2.5 12l4.2-4.2 4.2 4.2-4.2 4.2zM13.1 12l4.2-4.2 4.2 4.2-4.2 4.2z" />',
		'truck'    => '<path d="M1 3h15v13H1zM16 8h4l3 3v5h-7z" /><circle cx="5.5" cy="18.5" r="2.5" /><circle cx="18.5" cy="18.5" r="2.5" />',
		'refresh'  => '<path d="M21 2v6h-6M3 22v-6h6" /><path d="M20.5 15A9 9 0 0 1 5.6 18.4L3 16M3.5 9A9 9 0 0 1 18.4 5.6L21 8" />',
		'card'     => '<rect x="1" y="4" width="22" height="16" rx="2" /><path d="M1 10h22" />',
		'shield'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z" /><path d="m9 12 2 2 4-4" />',
		'ruler'    => '<path d="M21.3 8.7 8.7 21.3a1 1 0 0 1-1.4 0l-4.6-4.6a1 1 0 0 1 0-1.4L15.3 2.7a1 1 0 0 1 1.4 0l4.6 4.6a1 1 0 0 1 0 1.4ZM7.5 10.5l2 2M10.5 7.5l2 2M13.5 4.5l2 2M4.5 13.5l2 2" />',
		'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2" /><path d="m22 6-10 7L2 6" />',
		'phone'    => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z" />',
		'pin'      => '<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0Z" /><circle cx="12" cy="10" r="3" />',
	);

	if ( 'whatsapp' === $name ) {
		return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.2-.3-.3-.6-.4ZM12 21.8c-1.8 0-3.5-.5-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.8 9.8 0 1 1 12 21.8Zm8.4-18.2A11.8 11.8 0 0 0 1.7 17.8L0 24l6.3-1.7A11.8 11.8 0 0 0 24 12a11.7 11.7 0 0 0-3.6-8.4Z"/></svg>';
	}
	if ( 'instagram' === $name ) {
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>';
	}
	if ( 'facebook' === $name ) {
		return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M14 8h3V4h-3a4 4 0 0 0-4 4v2H8v4h2v8h4v-8h3l1-4h-4V8Z"/></svg>';
	}
	if ( 'tiktok' === $name ) {
		return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.4a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.7a5.7 5.7 0 1 0 4.9 5.7V9a7.4 7.4 0 0 0 4.3 1.4V7.3a4.3 4.3 0 0 1-3.2-1.5Z"/></svg>';
	}
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $icons[ $name ] . '</svg>';
}

/**
 * Imprime um ícone (SVG fixo do tema, seguro).
 *
 * @param string $name Nome.
 */
function pm_the_icon( $name ) {
	echo pm_icon( $name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Lê uma opção do Personalizador com valor padrão.
 *
 * @param string $key Chave sem prefixo.
 * @return mixed
 */
function pm_opt( $key ) {
	$defaults = pm_defaults();
	return get_theme_mod( 'pm_' . $key, isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
}

/**
 * Valores padrão (rascunho — editar em Aparência → Personalizar → Palestra Mantos).
 *
 * @return array
 */
function pm_defaults() {
	return array(
		'topbar'         => '5% de desconto no Pix · Frete grátis acima de R$ 249 · Troca gratuita',
		'whatsapp'       => '5511999999999',
		'whatsapp_msg'   => 'Olá! Vim pelo site da Palestra Mantos e gostaria de mais informações.',
		'phone'          => '(11) 99999-9999',
		'email'          => 'contato@palestraa.com.br',
		'instagram'      => 'https://instagram.com/palestramantos',
		'facebook'       => '',
		'tiktok'         => '',
		'address'        => 'São Paulo — SP',
		'cnpj'           => '00.000.000/0001-00',
		'company'        => 'Palestra Mantos',
		'pix_discount'   => 5,
		'free_shipping'  => 249,
		'installments'   => 12,
		'hero_eyebrow'   => 'Coleção Palestra',
		'hero_title'     => 'Paixão e <em>Estilo!</em>',
		'hero_text'      => 'Moletons, calças e camisetas licenciados para vestir o manto no dia a dia — com conforto, qualidade e a nossa história no peito.',
		'hero_image_1'   => '',
		'hero_image_2'   => '',
		'split_title'    => 'Vista o manto no dia a dia',
		'split_text'     => 'Peças em moletom flanelado e algodão de alta gramatura, bordados que não desbotam e modelagem pensada para o conforto — da arquibancada ao rolê.',
	);
}

/**
 * Link do WhatsApp.
 *
 * @param string $message Mensagem opcional.
 * @return string
 */
function pm_whatsapp_url( $message = '' ) {
	$number = preg_replace( '/\D+/', '', (string) pm_opt( 'whatsapp' ) );
	if ( '' === $message ) {
		$message = pm_opt( 'whatsapp_msg' );
	}
	return 'https://wa.me/' . $number . '?text=' . rawurlencode( $message );
}

/**
 * URL de uma imagem do tema.
 *
 * @param string $file Arquivo em assets/img.
 * @return string
 */
function pm_img( $file ) {
	return PM_URI . '/assets/img/' . $file;
}

/**
 * Imagem do Personalizador ou padrão do tema.
 *
 * @param string $key      Opção.
 * @param string $fallback Arquivo padrão.
 * @return string
 */
function pm_opt_img( $key, $fallback ) {
	$url = pm_opt( $key );
	return $url ? $url : pm_img( $fallback );
}

/**
 * Formata dinheiro em reais (fora do WooCommerce também).
 *
 * @param float $value Valor.
 * @return string
 */
function pm_money( $value ) {
	return 'R$ ' . number_format( (float) $value, 2, ',', '.' );
}

/**
 * Link de uma categoria de produto ou da loja.
 *
 * @param string $slug Slug.
 * @return string
 */
function pm_cat_url( $slug ) {
	if ( taxonomy_exists( 'product_cat' ) ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			return get_term_link( $term );
		}
	}
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Logo do cabeçalho/rodapé.
 */
function pm_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="pm-logo" href="%1$s" rel="home"><img src="%2$s" alt="" width="46" height="60"><span class="pm-logo__text">Palestra<small>Mantos</small></span><span class="screen-reader-text">%3$s</span></a>',
		esc_url( home_url( '/' ) ),
		esc_url( pm_img( 'logo-mark.png' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Aviso no site (só para administradores) quando a loja ainda não está pronta.
 */
function pm_admin_store_warning() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$msg  = '';
	$link = admin_url( 'themes.php?page=pm-setup' );
	if ( ! class_exists( 'WooCommerce' ) ) {
		$msg  = __( 'O plugin WooCommerce não está ativo, então ainda não existem produtos, categorias nem carrinho. Ative em Plugins e depois rode a configuração da loja.', 'palestra-mantos' );
		$link = admin_url( 'plugins.php' );
	} else {
		$count = wp_count_posts( 'product' );
		if ( empty( $count->publish ) ) {
			$msg = __( 'O WooCommerce está ativo, mas ainda não há produtos publicados. Clique aqui e depois em "Criar conteúdo da loja".', 'palestra-mantos' );
		}
	}
	if ( $msg ) {
		printf(
			'<div class="pm-admin-warning"><a href="%s">⚠️ %s</a> <small>%s</small></div>',
			esc_url( $link ),
			esc_html( $msg ),
			esc_html__( '(aviso visível só para administradores)', 'palestra-mantos' )
		);
	}
}
add_action( 'wp_body_open', 'pm_admin_store_warning', 5 );
