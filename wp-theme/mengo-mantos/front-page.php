<?php
/**
 * Página inicial da loja.
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;
get_header();

$pm_has_wc   = class_exists( 'WooCommerce' );
$pm_shop_url = $pm_has_wc ? wc_get_page_permalink( 'shop' ) : pm_whatsapp_url();
$pm_pix      = (int) pm_opt( 'pix_discount' );
$pm_free     = (int) pm_opt( 'free_shipping' );
$pm_times    = (int) pm_opt( 'installments' );
?>

<?php get_template_part( 'template-parts/hero-slider' ); ?>

<section class="pm-benefits" aria-label="<?php esc_attr_e( 'Vantagens', 'palestra-mantos' ); ?>">
	<div class="pm-container pm-benefits__grid">
		<div class="pm-benefit"><?php pm_the_icon( 'pix' ); ?><div><strong><?php echo esc_html( $pm_pix ); ?>% OFF no Pix</strong><span><?php esc_html_e( 'Desconto aplicado no checkout', 'palestra-mantos' ); ?></span></div></div>
		<div class="pm-benefit"><?php pm_the_icon( 'truck' ); ?><div><strong><?php esc_html_e( 'Frete grátis', 'palestra-mantos' ); ?></strong><span><?php echo esc_html( sprintf( 'Acima de %s', pm_money( $pm_free ) ) ); ?></span></div></div>
		<div class="pm-benefit"><?php pm_the_icon( 'refresh' ); ?><div><strong><?php esc_html_e( 'Troca grátis', 'palestra-mantos' ); ?></strong><span><?php esc_html_e( 'Primeira troca por nossa conta', 'palestra-mantos' ); ?></span></div></div>
		<div class="pm-benefit"><?php pm_the_icon( 'card' ); ?><div><strong><?php echo esc_html( sprintf( 'Até %dx no cartão', $pm_times ) ); ?></strong><span><?php esc_html_e( 'Compra 100% segura', 'palestra-mantos' ); ?></span></div></div>
	</div>
</section>

<section class="pm-section">
	<div class="pm-container">
		<div class="pm-section__head">
			<div><span class="pm-eyebrow"><?php esc_html_e( 'Categorias', 'palestra-mantos' ); ?></span><h2><?php esc_html_e( 'Escolha o seu manto', 'palestra-mantos' ); ?></h2></div>
		</div>
		<div class="pm-cats">
			<?php
			$pm_cat_imgs = array(
				'calcas'    => 'calca-vermelha-ancora.webp',
				'camisetas' => 'camiseta-vermelha-crf-frente.webp',
				'conjuntos' => 'conjunto-vermelho-preto-crf.webp',
			);
			foreach ( pm_category_slugs() as $pm_slug => $pm_label ) :
				?>
				<a class="pm-cat" href="<?php echo esc_url( pm_cat_url( $pm_slug ) ); ?>">
					<?php
					// Usa a "Miniatura" da categoria (Produtos → Categorias); sem ela, a foto padrão do tema.
					$pm_cat_src  = pm_img( $pm_cat_imgs[ $pm_slug ] );
					$pm_cat_term = taxonomy_exists( 'product_cat' ) ? get_term_by( 'slug', $pm_slug, 'product_cat' ) : false;
					if ( $pm_cat_term ) {
						$pm_thumb_id = (int) get_term_meta( $pm_cat_term->term_id, 'thumbnail_id', true );
						if ( $pm_thumb_id ) {
							$pm_cat_src = wp_get_attachment_image_url( $pm_thumb_id, 'large' ) ?: $pm_cat_src;
						}
					}
					?>
					<img src="<?php echo esc_url( $pm_cat_src ); ?>" alt="" loading="lazy" width="500" height="625">
					<span class="pm-cat__label"><strong><?php echo esc_html( $pm_label ); ?></strong><span><?php pm_the_icon( 'arrow' ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( $pm_has_wc ) : ?>
<section class="pm-section pm-section--soft" id="destaques">
	<div class="pm-container">
		<div class="pm-section__head">
			<div><span class="pm-eyebrow"><?php esc_html_e( 'Os favoritos da torcida', 'palestra-mantos' ); ?></span><h2><?php esc_html_e( 'Produtos em destaque', 'palestra-mantos' ); ?></h2></div>
			<a class="pm-link-arrow" href="<?php echo esc_url( $pm_shop_url ); ?>"><?php esc_html_e( 'Ver todos', 'palestra-mantos' ); ?></a>
		</div>
		<?php
		// Produtos marcados com estrela (destaque) em Produtos → Todos. Sem nenhum marcado, mostra a vitrine.
		$pm_featured = wc_get_featured_product_ids();
		echo do_shortcode( $pm_featured ? '[products limit="8" columns="4" visibility="featured" orderby="menu_order" order="ASC"]' : '[products limit="8" columns="4" orderby="price" order="DESC"]' );
		?>
	</div>
</section>
<?php endif; ?>


<section class="pm-section pm-welcome" id="sobre">
	<div class="pm-container pm-welcome__inner">
		<div class="pm-welcome__media" aria-hidden="true">
			<img src="<?php echo esc_url( pm_img( 'banner-colecao.webp' ) ); ?>" alt="" width="368" height="490" loading="lazy">
		</div>
		<div class="pm-welcome__text">
			<span class="pm-eyebrow"><?php esc_html_e( 'Nossa história', 'palestra-mantos' ); ?></span>
			<h2><?php echo esc_html( pm_opt( 'welcome_title' ) ); ?></h2>
			<?php echo wp_kses_post( wpautop( pm_opt( 'welcome_text' ) ) ); ?>
			<p class="pm-welcome__sign"><?php echo esc_html( pm_opt( 'welcome_sign' ) ); ?></p>
			<a class="pm-btn pm-btn--outline" href="<?php echo esc_url( ( $pm_about = get_page_by_path( 'quem-somos' ) ) ? get_permalink( $pm_about ) : $pm_shop_url ); ?>"><?php esc_html_e( 'Conheça a Mengo Mantos', 'palestra-mantos' ); ?> <?php pm_the_icon( 'arrow' ); ?></a>
		</div>
	</div>
</section>


<?php if ( $pm_has_wc ) : ?>
<section class="pm-section pm-section--soft" id="lancamentos">
	<div class="pm-container">
		<div class="pm-section__head">
			<div><span class="pm-eyebrow"><?php esc_html_e( 'Acabou de chegar', 'palestra-mantos' ); ?></span><h2><?php esc_html_e( 'Lançamentos', 'palestra-mantos' ); ?></h2></div>
			<a class="pm-link-arrow" href="<?php echo esc_url( add_query_arg( 'orderby', 'date', $pm_shop_url ) ); ?>"><?php esc_html_e( 'Ver novidades', 'palestra-mantos' ); ?></a>
		</div>
		<?php echo do_shortcode( '[products limit="4" columns="4" orderby="date" order="DESC"]' ); ?>
	</div>
</section>
<?php endif; ?>

<?php get_template_part( 'template-parts/newsletter' ); ?>

<section class="pm-section pm-section--soft" id="avaliacoes">
	<div class="pm-container">
		<?php
		$pm_reviews = function_exists( 'pm_get_reviews' ) ? pm_get_reviews( 0, 8 ) : array();
		list( $pm_avg, $pm_total ) = function_exists( 'pm_reviews_summary' ) ? pm_reviews_summary( 0 ) : array( 0, 0 );
		?>
		<div class="pm-section__head">
			<div><span class="pm-eyebrow"><?php esc_html_e( 'Avaliações', 'palestra-mantos' ); ?></span><h2><?php esc_html_e( 'Veja o que falam da nossa loja', 'palestra-mantos' ); ?></h2></div>
			<?php if ( $pm_total ) : ?>
				<p class="pm-rating-summary"><?php echo pm_stars( $pm_avg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <strong><?php echo esc_html( number_format_i18n( $pm_avg, 1 ) ); ?></strong> <?php echo esc_html( sprintf( _n( '(%d avaliação)', '(%d avaliações)', $pm_total, 'palestra-mantos' ), $pm_total ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $pm_reviews ) : ?>
			<div class="pm-reviews-grid">
				<?php
				foreach ( $pm_reviews as $pm_rv ) {
					echo pm_review_card( $pm_rv ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</div>
		<?php else : ?>
		<div class="pm-testimonials">
			<?php
			// Rascunho — troque pelos depoimentos reais dos clientes.
			$pm_testimonials = array(
				array( 'Ana P.', 'Rio de Janeiro — RJ', 'O conjunto é lindo e muito confortável. O acabamento é perfeito e chegou super rápido!' ),
				array( 'Rafael M.', 'Campinas — SP', 'Qualidade excelente, moletom grosso de verdade. Já estou de olho na próxima peça.' ),
				array( 'Juliana S.', 'Santos — SP', 'Atendimento pelo WhatsApp nota 10. Troquei o tamanho sem nenhuma dor de cabeça.' ),
			);
			foreach ( $pm_testimonials as $pm_t ) :
				?>
				<blockquote class="pm-testimonial">
					<div class="pm-stars" aria-label="5 estrelas">★★★★★</div>
					<p><?php echo esc_html( $pm_t[2] ); ?></p>
					<footer><span class="pm-avatar"><?php echo esc_html( mb_substr( $pm_t[0], 0, 1 ) ); ?></span><span><b><?php echo esc_html( $pm_t[0] ); ?></b><?php echo esc_html( $pm_t[1] ); ?></span></footer>
				</blockquote>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
		<?php echo function_exists( 'pm_ig_reviews_cta' ) ? pm_ig_reviews_cta() : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
</section>


<?php
get_footer();
