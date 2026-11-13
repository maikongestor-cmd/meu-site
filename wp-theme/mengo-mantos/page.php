<?php
/**
 * Páginas (inclui carrinho, checkout e minha conta do WooCommerce).
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;
get_header();

$pm_step  = function_exists( 'pm_checkout_step' ) ? pm_checkout_step() : 0;
$pm_wide  = $pm_step || ( function_exists( 'is_account_page' ) && is_account_page() );

while ( have_posts() ) :
	the_post();
	?>
	<header class="pm-page-hero<?php echo $pm_step ? ' pm-page-hero--flow' : ''; ?>">
		<div class="pm-container">
			<?php
			if ( 3 === $pm_step ) {
				echo '<h1>' . esc_html__( 'Pedido recebido', 'palestra-mantos' ) . '</h1>';
			} elseif ( 2 === $pm_step ) {
				echo '<h1>' . esc_html__( 'Finalizar compra', 'palestra-mantos' ) . '</h1>';
			} else {
				the_title( '<h1>', '</h1>' );
			}
			if ( $pm_step ) {
				pm_checkout_steps();
			}
			?>
		</div>
	</header>
	<article id="post-<?php the_ID(); ?>" <?php post_class( $pm_wide ? 'pm-content pm-content--wide' : 'pm-content' ); ?>>
		<?php
		the_content();
		wp_link_pages();
		?>
	</article>
	<?php
endwhile;

get_footer();
