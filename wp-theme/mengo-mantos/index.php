<?php
/**
 * Modelo padrão (blog, busca e arquivos).
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;
get_header();
?>
<header class="pm-page-hero">
	<div class="pm-container">
		<h1>
			<?php
			if ( is_search() ) {
				/* translators: %s: termo buscado. */
				printf( esc_html__( 'Resultados para: %s', 'palestra-mantos' ), esc_html( get_search_query() ) );
			} elseif ( is_archive() ) {
				echo wp_kses_post( get_the_archive_title() );
			} elseif ( is_home() && ! is_front_page() ) {
				single_post_title();
			} else {
				bloginfo( 'name' );
			}
			?>
		</h1>
	</div>
</header>
<div class="pm-content">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?> style="margin-bottom:40px">
				<?php if ( is_singular() ) : ?>
					<?php the_title( '<h2>', '</h2>' ); ?>
					<?php the_content(); ?>
				<?php else : ?>
					<?php the_title( '<h2><a href="' . esc_url( get_permalink() ) . '">', '</a></h2>' ); ?>
					<?php the_excerpt(); ?>
				<?php endif; ?>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nada encontrado.', 'palestra-mantos' ); ?></p>
		<?php get_search_form(); ?>
	<?php endif; ?>
</div>
<?php
get_footer();
