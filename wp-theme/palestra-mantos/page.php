<?php
/**
 * Páginas.
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	?>
	<header class="pm-page-hero">
		<div class="pm-container"><?php the_title( '<h1>', '</h1>' ); ?></div>
	</header>
	<article id="post-<?php the_ID(); ?>" <?php post_class( 'pm-content' ); ?>>
		<?php
		the_content();
		wp_link_pages();
		?>
	</article>
	<?php
endwhile;

get_footer();
