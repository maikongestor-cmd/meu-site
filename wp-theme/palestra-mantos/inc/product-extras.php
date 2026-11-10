<?php
/**
 * Página do produto: aba "Vídeo" e avaliações com foto.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Vídeo do produto
 * ---------------------------------------------------------------------- */

/**
 * Aba própria "Vídeo" na caixa "Dados do produto" (funciona em produtos com tamanhos).
 *
 * @param array $tabs Abas.
 * @return array
 */
function pm_video_data_tab( $tabs ) {
	$tabs['pm_video'] = array(
		'label'    => __( 'Vídeo', 'palestra-mantos' ),
		'target'   => 'pm_video_product_data',
		'class'    => array(),
		'priority' => 75,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'pm_video_data_tab' );

/**
 * Painel da aba "Vídeo".
 */
function pm_video_field() {
	echo '<div id="pm_video_product_data" class="panel woocommerce_options_panel hidden"><div class="options_group">';
	woocommerce_wp_text_input(
		array(
			'id'          => '_pm_video',
			'label'       => __( 'Link do vídeo', 'palestra-mantos' ),
			'placeholder' => 'https://www.youtube.com/watch?v=…',
			'description' => __( 'YouTube, Vimeo, TikTok ou um arquivo .mp4 enviado em Mídia → Adicionar (copie a "URL do arquivo"). Depois clique em Atualizar.', 'palestra-mantos' ),
			'type'        => 'url',
		)
	);
	echo '</div></div>';
}
add_action( 'woocommerce_product_data_panels', 'pm_video_field' );

/**
 * Salva o vídeo.
 *
 * @param WC_Product $product Produto.
 */
function pm_video_save( $product ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- o WooCommerce já verificou o nonce da tela de produto.
	if ( isset( $_POST['_pm_video'] ) ) {
		$product->update_meta_data( '_pm_video', esc_url_raw( wp_unslash( $_POST['_pm_video'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}
}
add_action( 'woocommerce_admin_process_product_object', 'pm_video_save' );

/**
 * HTML do vídeo.
 *
 * @param string $url Link.
 * @return string
 */
function pm_video_html( $url ) {
	if ( ! $url ) {
		return '';
	}
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( preg_match( '/\.(mp4|webm|mov|m4v)$/i', $path ) ) {
		return '<video class="pm-video__file" src="' . esc_url( $url ) . '" controls playsinline preload="metadata"></video>';
	}
	$embed = wp_oembed_get( $url, array( 'width' => 900 ) );
	if ( $embed ) {
		return $embed;
	}
	return '<p><a class="pm-btn pm-btn--outline" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Assistir ao vídeo', 'palestra-mantos' ) . '</a></p>';
}

/**
 * Abas do produto: adiciona "Vídeo" e troca as avaliações nativas pelas do tema.
 *
 * @param array $tabs Abas.
 * @return array
 */
function pm_product_tabs( $tabs ) {
	global $product;
	unset( $tabs['reviews'] );
	if ( $product && $product->get_meta( '_pm_video' ) ) {
		$tabs['pm_video'] = array(
			'title'    => __( 'Vídeo', 'palestra-mantos' ),
			'priority' => 15,
			'callback' => function () {
				global $product;
				echo '<div class="pm-video">' . pm_video_html( $product->get_meta( '_pm_video' ) ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			},
		);
	}
	if ( isset( $tabs['description'] ) ) {
		$tabs['description']['title'] = __( 'Descrição', 'palestra-mantos' );
	}
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'Informações', 'palestra-mantos' );
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'pm_product_tabs', 98 );

/**
 * Atalho "▶ Ver vídeo" ao lado da galeria (abre a aba).
 */
function pm_video_shortcut() {
	global $product;
	if ( $product && $product->get_meta( '_pm_video' ) ) {
		echo '<a class="pm-video-link" href="#tab-pm_video" data-pm-tab="pm_video">▶ ' . esc_html__( 'Ver vídeo do produto', 'palestra-mantos' ) . '</a>';
	}
}
add_action( 'woocommerce_single_product_summary', 'pm_video_shortcut', 7 );

/* -------------------------------------------------------------------------
 * Avaliações
 * ---------------------------------------------------------------------- */

/**
 * Estrelas e total logo abaixo do nome.
 */
function pm_product_rating_line() {
	global $product;
	if ( ! $product || ! function_exists( 'pm_reviews_summary' ) ) {
		return;
	}
	list( $avg, $total ) = pm_reviews_summary( $product->get_id() );
	if ( ! $total ) {
		list( $avg, $total ) = pm_reviews_summary( 0 );
	}
	list( $ig_count ) = pm_ig_reviews();
	if ( ! $total && ! $ig_count ) {
		return;
	}
	$label = $ig_count > 0
		/* translators: %d: total. */
		? sprintf( __( '+%d avaliações de clientes', 'palestra-mantos' ), $ig_count )
		/* translators: %d: total. */
		: sprintf( _n( '%d avaliação', '%d avaliações', $total, 'palestra-mantos' ), $total );
	printf(
		'<div class="pm-rating-line">%1$s %2$s <a href="#avaliacoes">%3$s</a></div>',
		pm_stars( $total ? $avg : 5 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$total ? '<a href="#avaliacoes">(' . esc_html( number_format( $avg, 1, '.', '' ) ) . ')</a>' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html( $label )
	);
}
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'woocommerce_single_product_summary', 'pm_product_rating_line', 6 );

/**
 * Seção de avaliações na página do produto.
 */
function pm_product_reviews_section() {
	global $product;
	if ( ! $product || ! function_exists( 'pm_get_reviews' ) ) {
		return;
	}
	$pid     = $product->get_id();
	$reviews = pm_get_reviews( $pid, 12 );
	$general = false;
	if ( ! $reviews ) {
		$reviews = pm_get_reviews( 0, 6 );
		$general = true;
	}
	list( $avg, $total ) = pm_reviews_summary( $general ? 0 : $pid );
	?>
	<section class="pm-product-reviews" id="avaliacoes">
		<div class="pm-section__head">
			<div>
				<span class="pm-eyebrow"><?php esc_html_e( 'Avaliações', 'palestra-mantos' ); ?></span>
				<h2><?php echo $general ? esc_html__( 'O que falam da Palestra Mantos', 'palestra-mantos' ) : esc_html__( 'Quem comprou, aprovou', 'palestra-mantos' ); ?></h2>
				<?php if ( $total ) : ?>
					<p class="pm-rating-summary"><?php echo pm_stars( $avg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <strong><?php echo esc_html( number_format_i18n( $avg, 1 ) ); ?></strong> <?php echo esc_html( sprintf( _n( '(%d avaliação)', '(%d avaliações)', $total, 'palestra-mantos' ), $total ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( $reviews ) : ?>
			<div class="pm-reviews-grid">
				<?php
				foreach ( $reviews as $rv ) {
					echo pm_review_card( $rv, $general ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		<?php else : ?>
			<p class="pm-reviews-empty"><?php esc_html_e( 'Este produto ainda não tem avaliações. Seja o primeiro a avaliar!', 'palestra-mantos' ); ?></p>
		<?php endif; ?>
		<?php echo pm_ig_reviews_cta(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php pm_review_form( $pid ); ?>
	</section>
	<?php
}
add_action( 'woocommerce_after_single_product_summary', 'pm_product_reviews_section', 30 );
