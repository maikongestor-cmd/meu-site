<?php
/**
 * Cabeçalho.
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;
$pm_has_wc = class_exists( 'WooCommerce' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#141414">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#conteudo"><?php esc_html_e( 'Pular para o conteúdo', 'palestra-mantos' ); ?></a>

<?php if ( pm_opt( 'topbar' ) ) : ?>
	<div class="pm-topbar"><span><?php echo esc_html( pm_opt( 'topbar' ) ); ?></span></div>
<?php endif; ?>

<header class="pm-header">
	<div class="pm-container pm-header__main">
		<div style="display:flex;align-items:center;gap:4px">
			<button class="pm-icon-btn pm-menu-toggle" type="button" aria-controls="pm-nav" aria-expanded="false">
				<?php pm_the_icon( 'menu' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Abrir menu', 'palestra-mantos' ); ?></span>
			</button>
			<?php pm_logo(); ?>
		</div>

		<?php get_search_form(); ?>

		<div class="pm-actions">
			<?php if ( $pm_has_wc ) : ?>
				<a class="pm-icon-btn" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
					<?php pm_the_icon( 'user' ); ?>
					<span class="pm-icon-btn__label">
						<?php if ( is_user_logged_in() ) : ?>
							<?php esc_html_e( 'Minha conta', 'palestra-mantos' ); ?><small><?php esc_html_e( 'Pedidos e dados', 'palestra-mantos' ); ?></small>
						<?php else : ?>
							<?php esc_html_e( 'Entrar', 'palestra-mantos' ); ?><small><?php esc_html_e( 'ou cadastre-se', 'palestra-mantos' ); ?></small>
						<?php endif; ?>
					</span>
				</a>
				<a class="pm-icon-btn" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
					<?php pm_the_icon( 'bag' ); ?>
					<?php echo pm_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="pm-icon-btn__label"><?php esc_html_e( 'Carrinho', 'palestra-mantos' ); ?></span>
				</a>
			<?php else : ?>
				<a class="pm-icon-btn" href="<?php echo esc_url( pm_whatsapp_url() ); ?>" target="_blank" rel="noopener">
					<?php pm_the_icon( 'whatsapp' ); ?><span class="pm-icon-btn__label"><?php esc_html_e( 'Atendimento', 'palestra-mantos' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<nav class="pm-nav" id="pm-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'palestra-mantos' ); ?>">
		<div class="pm-drawer__head">
			<span>Menu</span>
			<button type="button" class="pm-menu-close"><?php pm_the_icon( 'close' ); ?><span class="screen-reader-text"><?php esc_html_e( 'Fechar menu', 'palestra-mantos' ); ?></span></button>
		</div>
		<div class="pm-container">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'fallback_cb'    => 'pm_fallback_menu',
					'depth'          => 2,
				)
			);
			?>
		</div>
		<div class="pm-drawer__extra">
			<a class="pm-btn pm-btn--whats" href="<?php echo esc_url( pm_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php pm_the_icon( 'whatsapp' ); ?> <?php esc_html_e( 'Falar no WhatsApp', 'palestra-mantos' ); ?></a>
		</div>
	</nav>
	<div class="pm-overlay" hidden></div>
</header>

<main id="conteudo" class="pm-main">
