<?php
/**
 * Página inicial da loja.
 *
 * @package PalestraMantos
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
				'calcas'    => 'calca-verde.webp',
				'camisetas' => 'camiseta-logo-p.webp',
				'conjuntos' => 'conjunto-branco-masculino.webp',
			);
			foreach ( pm_category_slugs() as $pm_slug => $pm_label ) :
				?>
				<a class="pm-cat" href="<?php echo esc_url( pm_cat_url( $pm_slug ) ); ?>">
					<img src="<?php echo esc_url( pm_img( $pm_cat_imgs[ $pm_slug ] ) ); ?>" alt="" loading="lazy" width="500" height="625">
					<span class="pm-cat__label"><strong><?php echo esc_html( $pm_label ); ?></strong><span><?php pm_the_icon( 'arrow' ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php if ( $pm_has_wc ) : ?>
<section class="pm-section pm-section--soft">
	<div class="pm-container">
		<div class="pm-section__head">
			<div><span class="pm-eyebrow"><?php esc_html_e( 'Destaques', 'palestra-mantos' ); ?></span><h2><?php esc_html_e( 'Mais vendidos', 'palestra-mantos' ); ?></h2></div>
			<a class="pm-link-arrow" href="<?php echo esc_url( $pm_shop_url ); ?>"><?php esc_html_e( 'Ver todos', 'palestra-mantos' ); ?></a>
		</div>
		<?php echo do_shortcode( '[products limit="8" columns="4" orderby="date" order="DESC"]' ); ?>
	</div>
</section>
<?php endif; ?>

<section class="pm-section">
	<div class="pm-container">
		<div class="pm-split">
			<div class="pm-split__img"><img src="<?php echo esc_url( pm_img( 'conjunto-branco-masculino.webp' ) ); ?>" alt="<?php esc_attr_e( 'Conjunto moletom Palestra masculino', 'palestra-mantos' ); ?>" loading="lazy" width="600" height="750"></div>
			<div class="pm-split__body">
				<span class="pm-eyebrow"><?php esc_html_e( 'Qualidade Palestra', 'palestra-mantos' ); ?></span>
				<h2><?php echo esc_html( pm_opt( 'split_title' ) ); ?></h2>
				<p><?php echo esc_html( pm_opt( 'split_text' ) ); ?></p>
				<ul class="pm-split__list">
					<li><?php esc_html_e( 'Produtos oficiais e licenciados', 'palestra-mantos' ); ?></li>
					<li><?php esc_html_e( 'Tamanhos do P ao GG', 'palestra-mantos' ); ?></li>
					<li><?php esc_html_e( 'Envio para todo o Brasil', 'palestra-mantos' ); ?></li>
				</ul>
				<div><a class="pm-btn pm-btn--light" href="<?php echo esc_url( pm_cat_url( 'conjuntos' ) ); ?>"><?php esc_html_e( 'Ver conjuntos', 'palestra-mantos' ); ?> <?php pm_the_icon( 'arrow' ); ?></a></div>
			</div>
		</div>
	</div>
</section>

<section class="pm-section pm-section--soft">
	<div class="pm-container">
		<div class="pm-section__head">
			<div><span class="pm-eyebrow"><?php esc_html_e( 'Depoimentos', 'palestra-mantos' ); ?></span><h2><?php esc_html_e( 'Veja o que falam da nossa loja', 'palestra-mantos' ); ?></h2></div>
		</div>
		<div class="pm-testimonials">
			<?php
			// Rascunho — troque pelos depoimentos reais dos clientes.
			$pm_testimonials = array(
				array( 'Ana P.', 'São Paulo — SP', 'O conjunto é lindo e muito confortável. O bordado é perfeito e chegou super rápido!' ),
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
	</div>
</section>

<section class="pm-cta-band">
	<div class="pm-container pm-cta-band__inner">
		<div>
			<h2><?php esc_html_e( 'Entre para o grupo VIP', 'palestra-mantos' ); ?></h2>
			<p><?php esc_html_e( 'Receba lançamentos e cupons exclusivos direto no seu WhatsApp.', 'palestra-mantos' ); ?></p>
		</div>
		<a class="pm-btn pm-btn--light" href="<?php echo esc_url( pm_whatsapp_url( 'Olá! Quero entrar no grupo VIP da Palestra Mantos.' ) ); ?>" target="_blank" rel="noopener"><?php pm_the_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Quero participar', 'palestra-mantos' ); ?></a>
	</div>
</section>

<?php
get_footer();
