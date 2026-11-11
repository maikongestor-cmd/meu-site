<?php
/**
 * Banner rotativo da página inicial (3 slides, textos em Aparência → Personalizar → Banners rotativos).
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

$pm_shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : pm_whatsapp_url();
$pm_kit_url  = pm_cat_url( 'camisetas' );
if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
	$pm_kit_id = wc_get_product_id_by_sku( 'PM-KIT-CAMISETAS' );
	if ( $pm_kit_id ) {
		$pm_kit_url = get_permalink( $pm_kit_id );
	}
}

$pm_slides = array(
	array(
		'variant' => 'light',
		'image'   => pm_opt_img( 'slide1_image', 'banner-mapas.webp' ),
		'link'    => pm_opt( 'slide1_link' ) ? pm_opt( 'slide1_link' ) : $pm_shop_url,
		'alt'     => __( 'Mapas de São Paulo, do Brasil e da América do Sul em verde com os símbolos do Palestra', 'palestra-mantos' ),
	),
	array(
		'variant' => 'dark',
		'image'   => pm_opt_img( 'slide2_image', 'conjunto-branco-masculino.webp' ),
		'image2'  => pm_opt( 'slide2_image' ) ? '' : pm_img( 'conjunto-branco-feminino.webp' ),
		'link'    => pm_opt( 'slide2_link' ) ? pm_opt( 'slide2_link' ) : pm_cat_url( 'conjuntos' ),
		'alt'     => __( 'Conjunto moletom branco Palestra', 'palestra-mantos' ),
	),
	array(
		'variant' => 'dark',
		'image'   => pm_opt_img( 'slide3_image', 'kit-camisetas.webp' ),
		'link'    => pm_opt( 'slide3_link' ) ? pm_opt( 'slide3_link' ) : $pm_kit_url,
		'alt'     => __( 'Kit com duas camisetas oversized Palestra', 'palestra-mantos' ),
	),
);
$pm_free = (int) pm_opt( 'free_shipping' );
?>
<section class="pm-slider" aria-roledescription="carrossel" aria-label="<?php esc_attr_e( 'Destaques da loja', 'palestra-mantos' ); ?>">
	<div class="pm-slides">
		<?php
		foreach ( $pm_slides as $pm_i => $pm_slide ) :
			$pm_n = $pm_i + 1;
			?>
			<article class="pm-slide pm-slide--<?php echo esc_attr( $pm_slide['variant'] ); ?><?php echo 0 === $pm_i ? ' is-active' : ''; ?>" aria-roledescription="slide" aria-label="<?php echo esc_attr( $pm_n . ' / ' . count( $pm_slides ) ); ?>"<?php echo 0 === $pm_i ? '' : ' aria-hidden="true"'; ?>>
				<?php if ( 'dark' === $pm_slide['variant'] ) : ?>
					<span class="pm-slide__bf" aria-hidden="true">Black Friday</span>
				<?php endif; ?>
				<div class="pm-container pm-slide__inner">
					<div class="pm-slide__text">
						<div class="pm-hero__flag" aria-hidden="true"><i></i><i></i><i></i></div>
						<span class="pm-eyebrow"><?php echo esc_html( pm_opt( 'slide' . $pm_n . '_eyebrow' ) ); ?></span>
						<?php $pm_tag = 0 === $pm_i ? 'h1' : 'h2'; ?>
						<<?php echo esc_html( $pm_tag ); ?> class="pm-slide__title"><?php echo wp_kses( pm_opt( 'slide' . $pm_n . '_title' ), array( 'em' => array(), 'br' => array() ) ); ?></<?php echo esc_html( $pm_tag ); ?>>
						<p class="pm-slide__desc"><?php echo esc_html( pm_opt( 'slide' . $pm_n . '_text' ) ); ?></p>
						<?php
						$pm_points = array_filter( array_map( 'trim', explode( "\n", (string) pm_opt( 'slide' . $pm_n . '_points' ) ) ) );
						if ( $pm_points ) :
							?>
							<ul class="pm-slide__points">
								<?php foreach ( array_slice( $pm_points, 0, 4 ) as $pm_point ) : ?>
									<li><?php pm_the_icon( 'check' ); ?> <?php echo esc_html( $pm_point ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<div class="pm-hero__cta">
							<a class="pm-btn <?php echo 'dark' === $pm_slide['variant'] ? 'pm-btn--green' : ''; ?>" href="<?php echo esc_url( $pm_slide['link'] ); ?>"<?php echo 0 === $pm_i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( pm_opt( 'slide' . $pm_n . '_button' ) ); ?> <?php pm_the_icon( 'arrow' ); ?></a>
							<?php if ( $pm_free > 0 ) : ?>
								<span class="pm-slide__ship"><?php pm_the_icon( 'truck' ); ?> <?php echo esc_html( sprintf( 'Frete grátis acima de %s', pm_money( $pm_free ) ) ); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<div class="pm-slide__media<?php echo ! empty( $pm_slide['image2'] ) ? ' has-two' : ''; ?>">
						<img src="<?php echo esc_url( $pm_slide['image'] ); ?>" alt="<?php echo esc_attr( $pm_slide['alt'] ); ?>" width="600" height="750"<?php echo 0 === $pm_i ? ' fetchpriority="high"' : ' loading="lazy"'; ?>>
						<?php if ( ! empty( $pm_slide['image2'] ) ) : ?>
							<img src="<?php echo esc_url( $pm_slide['image2'] ); ?>" alt="" width="600" height="750" loading="lazy">
						<?php endif; ?>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<button type="button" class="pm-slider__arrow pm-slider__arrow--prev" aria-label="<?php esc_attr_e( 'Banner anterior', 'palestra-mantos' ); ?>"><?php pm_the_icon( 'arrow' ); ?></button>
	<button type="button" class="pm-slider__arrow pm-slider__arrow--next" aria-label="<?php esc_attr_e( 'Próximo banner', 'palestra-mantos' ); ?>"><?php pm_the_icon( 'arrow' ); ?></button>
	<div class="pm-slider__dots">
		<?php foreach ( $pm_slides as $pm_i => $pm_slide ) : ?>
			<button type="button" class="pm-slider__dot<?php echo 0 === $pm_i ? ' is-active' : ''; ?>" aria-label="<?php echo esc_attr( sprintf( 'Ir para o banner %d', $pm_i + 1 ) ); ?>"></button>
		<?php endforeach; ?>
	</div>
</section>
