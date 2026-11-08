<?php
/**
 * Palestra Mantos — funções do tema.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

define( 'PM_VERSION', '1.1.0' );
define( 'PM_DIR', get_template_directory() );
define( 'PM_URI', get_template_directory_uri() );

require PM_DIR . '/inc/helpers.php';
require PM_DIR . '/inc/customizer.php';
require PM_DIR . '/inc/setup-content.php';

if ( class_exists( 'WooCommerce' ) ) {
	require PM_DIR . '/inc/woocommerce.php';
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
 * CSS e JS.
 */
function pm_assets() {
	wp_enqueue_style(
		'pm-fonts',
		'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'pm-style', get_stylesheet_uri(), array(), PM_VERSION );
	wp_enqueue_script( 'pm-theme', PM_URI . '/assets/js/theme.js', array(), PM_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'pm_assets' );

/**
 * Pré-conexão com o Google Fonts.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Tipo.
 * @return array
 */
function pm_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'pm_resource_hints', 10, 2 );

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

	foreach ( array( 'guia-de-medidas', 'trocas-e-devolucoes', 'quem-somos', 'contato' ) as $slug ) {
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
