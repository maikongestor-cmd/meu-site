<?php
/**
 * Mengo Mantos — funções do tema.
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;

define( 'PM_VERSION', '1.2.9' );
define( 'PM_DIR', get_template_directory() );
define( 'PM_URI', get_template_directory_uri() );

require PM_DIR . '/inc/helpers.php';
require PM_DIR . '/inc/customizer.php';
require PM_DIR . '/inc/setup-content.php';
require PM_DIR . '/inc/product-descriptions.php';
require PM_DIR . '/inc/size-finder.php';
require PM_DIR . '/inc/newsletter.php';
require PM_DIR . '/inc/reviews.php';
require PM_DIR . '/inc/contact.php';
require PM_DIR . '/inc/meta-pixel.php';
require PM_DIR . '/inc/hotjar.php';

if ( class_exists( 'WooCommerce' ) ) {
	require PM_DIR . '/inc/woocommerce.php';
	require PM_DIR . '/inc/checkout.php';
	require PM_DIR . '/inc/product-extras.php';
	require PM_DIR . '/inc/minicart.php';
	require PM_DIR . '/inc/exit-survey.php';
}

/**
 * Recursos do tema.
 */
function pm_setup() {
	load_theme_textdomain( 'palestra-mantos', PM_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 120,
			'width'       => 360,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'palestra-mantos' ),
			'footer'  => __( 'Rodapé — Institucional', 'palestra-mantos' ),
		)
	);
}
add_action( 'after_setup_theme', 'pm_setup' );

/**
 * CSS e JS (fontes locais; JS com defer no rodapé).
 */
function pm_assets() {
	wp_enqueue_style( 'pm-style', get_stylesheet_uri(), array(), PM_VERSION );
	wp_enqueue_script(
		'pm-theme',
		PM_URI . '/assets/js/theme.js',
		array(),
		PM_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'pm_assets' );

/**
 * Favicon do tema (só quando não há "Ícone do site" em Aparência → Personalizar).
 */
function pm_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" sizes="any">' . "\n", esc_url( PM_URI . '/assets/img/favicon.ico?v=' . PM_VERSION ) );
	printf( '<link rel="icon" type="image/png" href="%s" sizes="512x512">' . "\n", esc_url( PM_URI . '/assets/img/favicon.png?v=' . PM_VERSION ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( PM_URI . '/assets/img/apple-touch-icon.png?v=' . PM_VERSION ) );
}
add_action( 'wp_head', 'pm_favicon', 2 );
add_action( 'admin_head', 'pm_favicon', 2 );

/**
 * Menu padrão quando nenhum menu foi atribuído.
 */
function pm_fallback_menu() {
	$items = array();
	$items[] = array( home_url( '/' ), __( 'Início', 'palestra-mantos' ) );

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$items[] = array( wc_get_page_permalink( 'shop' ), __( 'Produtos', 'palestra-mantos' ) );
		foreach ( pm_category_slugs() as $slug => $label ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );
			if ( $term ) {
				$items[] = array( get_term_link( $term ), $label );
			}
		}
	}

	foreach ( array( 'guia-de-medidas', 'trocas-e-devolucoes', 'quem-somos', 'politica-de-privacidade', 'contato' ) as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$items[] = array( get_permalink( $page ), get_the_title( $page ) );
		}
	}

	echo '<ul class="menu">';
	foreach ( $items as $item ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $item[0] ), esc_html( $item[1] ) );
	}
	echo '</ul>';
}

/**
 * Categorias padrão da loja.
 *
 * @return array slug => nome
 */
function pm_category_slugs() {
	return array(
		'calcas'    => __( 'Calças', 'palestra-mantos' ),
		'camisetas' => __( 'Camisetas', 'palestra-mantos' ),
		'conjuntos' => __( 'Conjuntos', 'palestra-mantos' ),
	);
}

/**
 * Tamanho do resumo.
 */
add_filter(
	'excerpt_length',
	function () {
		return 24;
	}
);

/**
 * Pré-carrega a foto do 1º banner (uma para computador, outra para celular).
 */
function pm_mengo_preload_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	printf( '<link rel="preload" as="image" href="%s" media="(min-width: 901px)" fetchpriority="high">' . "\n", esc_url( PM_URI . '/assets/img/banner-estadio-wide.webp' ) );
	printf( '<link rel="preload" as="image" href="%s" media="(max-width: 900px)" fetchpriority="high">' . "\n", esc_url( PM_URI . '/assets/img/banner-estadio-tall.webp' ) );
}
add_action( 'wp_head', 'pm_mengo_preload_hero', 1 );
