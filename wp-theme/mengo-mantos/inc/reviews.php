<?php
/**
 * Avaliações com foto.
 *
 * - Painel: menu "Avaliações" (nome do cliente, cidade, nota, texto, foto e produto).
 * - Página inicial: últimas avaliações publicadas.
 * - Página do produto: avaliações daquele produto + formulário para o cliente enviar
 *   a dele (com foto). Avaliações enviadas pelo site ficam "pendentes" até você aprovar.
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registra o tipo "Avaliação".
 */
function pm_reviews_register() {
	register_post_type(
		'pm_review',
		array(
			'labels'          => array(
				'name'               => __( 'Avaliações', 'palestra-mantos' ),
				'singular_name'      => __( 'Avaliação', 'palestra-mantos' ),
				'add_new'            => __( 'Adicionar avaliação', 'palestra-mantos' ),
				'add_new_item'       => __( 'Nova avaliação', 'palestra-mantos' ),
				'edit_item'          => __( 'Editar avaliação', 'palestra-mantos' ),
				'all_items'          => __( 'Todas as avaliações', 'palestra-mantos' ),
				'not_found'          => __( 'Nenhuma avaliação ainda.', 'palestra-mantos' ),
				'featured_image'     => __( 'Foto do cliente / do produto', 'palestra-mantos' ),
				'set_featured_image' => __( 'Escolher foto', 'palestra-mantos' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-star-filled',
			'menu_position'   => 57,
			'supports'        => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'    => false,
			'capability_type' => 'post',
		)
	);
}
add_action( 'init', 'pm_reviews_register' );

/**
 * "Nome do cliente" no lugar de "Adicionar título".
 *
 * @param string  $title Texto.
 * @param WP_Post $post  Post.
 * @return string
 */
function pm_reviews_title_placeholder( $title, $post ) {
	return 'pm_review' === $post->post_type ? __( 'Nome do cliente (ex.: Ana P.)', 'palestra-mantos' ) : $title;
}
add_filter( 'enter_title_here', 'pm_reviews_title_placeholder', 10, 2 );

/**
 * Caixa com cidade, nota e produto.
 */
function pm_reviews_meta_box() {
	add_meta_box( 'pm_review_data', __( 'Dados da avaliação', 'palestra-mantos' ), 'pm_reviews_meta_box_html', 'pm_review', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'pm_reviews_meta_box' );

/**
 * HTML da caixa.
 *
 * @param WP_Post $post Post.
 */
function pm_reviews_meta_box_html( $post ) {
	wp_nonce_field( 'pm_review_save', 'pm_review_nonce' );
	$rating  = (int) get_post_meta( $post->ID, '_pm_rating', true );
	$rating  = $rating ? $rating : 5;
	$city    = get_post_meta( $post->ID, '_pm_city', true );
	$product = (int) get_post_meta( $post->ID, '_pm_product_id', true );
	?>
	<p><label><strong><?php esc_html_e( 'Nota', 'palestra-mantos' ); ?></strong><br>
		<select name="pm_rating" style="width:100%">
			<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
				<option value="<?php echo (int) $i; ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( str_repeat( '★', $i ) . str_repeat( '☆', 5 - $i ) ); ?></option>
			<?php endfor; ?>
		</select></label></p>
	<p><label><strong><?php esc_html_e( 'Cidade', 'palestra-mantos' ); ?></strong><br>
		<input type="text" name="pm_city" value="<?php echo esc_attr( $city ); ?>" placeholder="São Paulo — SP" style="width:100%"></label></p>
	<?php if ( post_type_exists( 'product' ) ) : ?>
		<p><label><strong><?php esc_html_e( 'Produto avaliado', 'palestra-mantos' ); ?></strong><br>
			<select name="pm_product_id" style="width:100%">
				<option value="0"><?php esc_html_e( '— Avaliação da loja (geral) —', 'palestra-mantos' ); ?></option>
				<?php
				$products = get_posts(
					array(
						'post_type'      => 'product',
						'posts_per_page' => 200,
						'orderby'        => 'title',
						'order'          => 'ASC',
					)
				);
				foreach ( $products as $p ) {
					printf( '<option value="%d" %s>%s</option>', (int) $p->ID, selected( $product, $p->ID, false ), esc_html( get_the_title( $p ) ) );
				}
				?>
			</select></label></p>
	<?php endif; ?>
	<p class="description"><?php esc_html_e( 'O texto da avaliação vai no campo principal e a foto em "Foto do cliente / do produto".', 'palestra-mantos' ); ?></p>
	<?php
}

/**
 * Salva a caixa.
 *
 * @param int $post_id Post.
 */
function pm_reviews_save( $post_id ) {
	if ( ! isset( $_POST['pm_review_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pm_review_nonce'] ) ), 'pm_review_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_pm_rating', max( 1, min( 5, isset( $_POST['pm_rating'] ) ? absint( $_POST['pm_rating'] ) : 5 ) ) );
	update_post_meta( $post_id, '_pm_city', isset( $_POST['pm_city'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_city'] ) ) : '' );
	update_post_meta( $post_id, '_pm_product_id', isset( $_POST['pm_product_id'] ) ? absint( $_POST['pm_product_id'] ) : 0 );
}
add_action( 'save_post_pm_review', 'pm_reviews_save' );

/**
 * Colunas na lista.
 *
 * @param array $cols Colunas.
 * @return array
 */
function pm_reviews_columns( $cols ) {
	return array(
		'cb'         => $cols['cb'],
		'pm_photo'   => __( 'Foto', 'palestra-mantos' ),
		'title'      => __( 'Cliente', 'palestra-mantos' ),
		'pm_rating'  => __( 'Nota', 'palestra-mantos' ),
		'pm_product' => __( 'Produto', 'palestra-mantos' ),
		'date'       => __( 'Data', 'palestra-mantos' ),
	);
}
add_filter( 'manage_pm_review_posts_columns', 'pm_reviews_columns' );

add_action(
	'manage_pm_review_posts_custom_column',
	function ( $col, $post_id ) {
		if ( 'pm_photo' === $col ) {
			echo get_the_post_thumbnail( $post_id, array( 56, 56 ), array( 'style' => 'border-radius:8px;object-fit:cover' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} elseif ( 'pm_rating' === $col ) {
			$r = (int) get_post_meta( $post_id, '_pm_rating', true );
			echo '<span style="color:#f5b301;letter-spacing:2px">' . esc_html( str_repeat( '★', $r ) ) . '</span>';
		} elseif ( 'pm_product' === $col ) {
			$pid = (int) get_post_meta( $post_id, '_pm_product_id', true );
			echo $pid ? esc_html( get_the_title( $pid ) ) : esc_html__( 'Loja (geral)', 'palestra-mantos' );
		}
	},
	10,
	2
);

/* -------------------------------------------------------------------------
 * Leitura
 * ---------------------------------------------------------------------- */

/**
 * Busca avaliações publicadas.
 *
 * @param int $product_id Produto (0 = todas).
 * @param int $limit      Quantidade.
 * @return WP_Post[]
 */
function pm_get_reviews( $product_id = 0, $limit = 6 ) {
	$args = array(
		'post_type'      => 'pm_review',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);
	if ( $product_id ) {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'   => '_pm_product_id',
				'value' => (int) $product_id,
			),
		);
	}
	return get_posts( $args );
}

/**
 * Média e total de um produto (ou da loja inteira).
 *
 * @param int $product_id Produto (0 = loja).
 * @return array array( média, total )
 */
function pm_reviews_summary( $product_id = 0 ) {
	$key    = 'pm_rev_sum_' . (int) $product_id;
	$cached = get_transient( $key );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'pm_review',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => $product_id ? array( array( 'key' => '_pm_product_id', 'value' => (int) $product_id ) ) : array(), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);
	$sum = 0;
	foreach ( $ids as $id ) {
		$sum += max( 1, (int) get_post_meta( $id, '_pm_rating', true ) );
	}
	$out = array( $ids ? round( $sum / count( $ids ), 1 ) : 0, count( $ids ) );
	set_transient( $key, $out, HOUR_IN_SECONDS );
	return $out;
}

/**
 * Limpa o cache de médias ao salvar.
 */
function pm_reviews_flush_cache() {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_pm_rev_sum_%' OR option_name LIKE '_transient_timeout_pm_rev_sum_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
add_action( 'save_post_pm_review', 'pm_reviews_flush_cache', 20 );
add_action( 'trashed_post', 'pm_reviews_flush_cache' );
add_action( 'transition_post_status', 'pm_reviews_flush_cache' );

/**
 * Estrelas em HTML.
 *
 * @param float $rating Nota.
 * @return string
 */
function pm_stars( $rating ) {
	$full = (int) round( $rating );
	return '<span class="pm-stars" aria-label="' . esc_attr( sprintf( '%s de 5 estrelas', number_format_i18n( $rating, 1 ) ) ) . '">' . str_repeat( '★', $full ) . '<span class="pm-stars__off">' . str_repeat( '★', 5 - $full ) . '</span></span>';
}

/**
 * Cartão de uma avaliação.
 *
 * @param WP_Post $review    Avaliação.
 * @param bool    $show_prod Mostrar nome do produto.
 * @return string
 */
function pm_review_card( $review, $show_prod = true ) {
	$rating = max( 1, (int) get_post_meta( $review->ID, '_pm_rating', true ) );
	$city   = get_post_meta( $review->ID, '_pm_city', true );
	$pid    = (int) get_post_meta( $review->ID, '_pm_product_id', true );
	$name   = get_the_title( $review );
	ob_start();
	?>
	<article class="pm-review">
		<?php if ( has_post_thumbnail( $review ) ) : ?>
			<a class="pm-review__photo" href="<?php echo esc_url( get_the_post_thumbnail_url( $review, 'large' ) ); ?>" target="_blank" rel="noopener">
				<?php echo get_the_post_thumbnail( $review, 'medium_large', array( 'loading' => 'lazy', 'alt' => esc_attr( sprintf( 'Foto enviada por %s', $name ) ) ) ); ?>
			</a>
		<?php endif; ?>
		<div class="pm-review__body">
			<?php echo pm_stars( $rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="pm-review__text"><?php echo wp_kses_post( wpautop( $review->post_content ) ); ?></div>
			<footer class="pm-review__author">
				<span class="pm-avatar"><?php echo esc_html( mb_strtoupper( mb_substr( $name, 0, 1 ) ) ); ?></span>
				<span><b><?php echo esc_html( $name ); ?></b><?php if ( 'instagram' === get_post_meta( $review->ID, '_pm_source', true ) ) : ?> <span class="pm-verified">via Instagram</span><?php elseif ( ! get_post_meta( $review->ID, '_pm_from_site', true ) ) : ?> <span class="pm-verified">✓ <?php esc_html_e( 'Compra verificada', 'palestra-mantos' ); ?></span><?php endif; ?><?php echo $city ? '<br>' . esc_html( $city ) : ''; ?>
				<?php if ( $show_prod && $pid ) : ?>
					<br><a href="<?php echo esc_url( get_permalink( $pid ) ); ?>"><?php echo esc_html( get_the_title( $pid ) ); ?></a>
				<?php endif; ?>
				</span>
			</footer>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/* -------------------------------------------------------------------------
 * Envio pelo site (página do produto)
 * ---------------------------------------------------------------------- */

/**
 * Formulário "Avalie este produto".
 *
 * @param int $product_id Produto.
 */
function pm_review_form( $product_id ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$status = isset( $_GET['avaliacao'] ) ? sanitize_key( wp_unslash( $_GET['avaliacao'] ) ) : '';
	?>
	<details class="pm-review-form"<?php echo $status ? ' open' : ''; ?>>
		<summary class="pm-btn pm-btn--outline"><?php esc_html_e( 'Escrever uma avaliação', 'palestra-mantos' ); ?></summary>
		<?php if ( 'ok' === $status ) : ?>
			<p class="pm-form-msg is-ok"><?php esc_html_e( 'Obrigado! Sua avaliação foi enviada e aparece aqui depois de aprovada. ❤️', 'palestra-mantos' ); ?></p>
		<?php elseif ( 'erro' === $status ) : ?>
			<p class="pm-form-msg is-erro"><?php esc_html_e( 'Não foi possível enviar. Confira os campos (foto até 5 MB, em JPG, PNG ou WebP).', 'palestra-mantos' ); ?></p>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="pm_review_submit">
			<input type="hidden" name="pm_product_id" value="<?php echo (int) $product_id; ?>">
			<div class="pm-newsletter__hp" aria-hidden="true"><label>Site <input type="text" name="pm_site" tabindex="-1" autocomplete="off"></label></div>
			<fieldset class="pm-star-input">
				<legend><?php esc_html_e( 'Sua nota', 'palestra-mantos' ); ?></legend>
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<input type="radio" id="pm-star-<?php echo (int) $i; ?>" name="pm_rating" value="<?php echo (int) $i; ?>" <?php checked( 5, $i ); ?>><label for="pm-star-<?php echo (int) $i; ?>" title="<?php echo esc_attr( $i ); ?>">★</label>
				<?php endfor; ?>
			</fieldset>
			<div class="pm-review-form__row">
				<label><?php esc_html_e( 'Seu nome', 'palestra-mantos' ); ?><input type="text" name="pm_name" required maxlength="60"></label>
				<label><?php esc_html_e( 'Cidade (opcional)', 'palestra-mantos' ); ?><input type="text" name="pm_city" maxlength="60"></label>
			</div>
			<label><?php esc_html_e( 'Sua avaliação', 'palestra-mantos' ); ?><textarea name="pm_text" required maxlength="1000" rows="4"></textarea></label>
			<label class="pm-review-form__file"><?php esc_html_e( 'Foto vestindo a peça (opcional)', 'palestra-mantos' ); ?><input type="file" name="pm_photo" accept="image/jpeg,image/png,image/webp"></label>
			<button type="submit" class="pm-btn"><?php esc_html_e( 'Enviar avaliação', 'palestra-mantos' ); ?></button>
		</form>
	</details>
	<?php
}

/**
 * Recebe a avaliação enviada pelo site (fica pendente).
 */
function pm_review_submit() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- formulário público; protegido por honeypot, limite por IP e moderação.
	$product_id = isset( $_POST['pm_product_id'] ) ? absint( $_POST['pm_product_id'] ) : 0;
	$back       = $product_id ? get_permalink( $product_id ) : home_url( '/' );
	$go         = function ( $status ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'avaliacao', $status, $back ) . '#avaliacoes' );
		exit;
	};
	if ( ! empty( $_POST['pm_site'] ) ) {
		$go( 'ok' );
	}
	$name   = isset( $_POST['pm_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_name'] ) ) : '';
	$city   = isset( $_POST['pm_city'] ) ? sanitize_text_field( wp_unslash( $_POST['pm_city'] ) ) : '';
	$text   = isset( $_POST['pm_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pm_text'] ) ) : '';
	$rating = isset( $_POST['pm_rating'] ) ? max( 1, min( 5, absint( $_POST['pm_rating'] ) ) ) : 5;
	// phpcs:enable

	if ( '' === $name || '' === $text || ( $product_id && 'product' !== get_post_type( $product_id ) ) ) {
		$go( 'erro' );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'pm_rv_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 3 ) {
		$go( 'erro' );
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$id = wp_insert_post(
		array(
			'post_type'    => 'pm_review',
			'post_status'  => 'pending',
			'post_title'   => mb_substr( $name, 0, 60 ),
			'post_content' => mb_substr( $text, 0, 1000 ),
		)
	);
	if ( ! $id || is_wp_error( $id ) ) {
		$go( 'erro' );
	}
	update_post_meta( $id, '_pm_rating', $rating );
	update_post_meta( $id, '_pm_city', mb_substr( $city, 0, 60 ) );
	update_post_meta( $id, '_pm_product_id', $product_id );
	update_post_meta( $id, '_pm_from_site', 1 );

	// Foto (opcional): só imagens, até 5 MB.
	if ( ! empty( $_FILES['pm_photo']['name'] ) && isset( $_FILES['pm_photo']['size'], $_FILES['pm_photo']['error'] ) && 0 === (int) $_FILES['pm_photo']['error'] ) {
		$size = (int) $_FILES['pm_photo']['size'];
		$type = wp_check_filetype_and_ext( $_FILES['pm_photo']['tmp_name'], sanitize_file_name( wp_unslash( $_FILES['pm_photo']['name'] ) ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( $size > 0 && $size <= 5 * MB_IN_BYTES && in_array( $type['type'], array( 'image/jpeg', 'image/png', 'image/webp' ), true ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			$att = media_handle_upload( 'pm_photo', $id, array(), array( 'test_form' => false ) );
			if ( ! is_wp_error( $att ) ) {
				set_post_thumbnail( $id, $att );
			}
		}
	}

	/**
	 * Avisa o administrador por e-mail.
	 */
	wp_mail(
		get_option( 'admin_email' ),
		__( 'Nova avaliação para aprovar — Mengo Mantos', 'palestra-mantos' ),
		/* translators: 1: nome, 2: nota, 3: link. */
		sprintf( __( "%1\$s deu nota %2\$d.\nAprove em: %3\$s", 'palestra-mantos' ), $name, $rating, admin_url( 'edit.php?post_status=pending&post_type=pm_review' ) )
	);

	$go( 'ok' );
}
add_action( 'admin_post_nopriv_pm_review_submit', 'pm_review_submit' );
add_action( 'admin_post_pm_review_submit', 'pm_review_submit' );

/**
 * Bolinha com o número de avaliações pendentes no menu.
 */
function pm_reviews_pending_badge() {
	global $menu;
	$count = wp_count_posts( 'pm_review' );
	if ( empty( $count->pending ) || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=pm_review' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod">' . (int) $count->pending . '</span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}
}
add_action( 'admin_menu', 'pm_reviews_pending_badge', 99 );


/* -------------------------------------------------------------------------
 * Avaliações vindas do Instagram (comentários de clientes)
 * ---------------------------------------------------------------------- */

/**
 * Comentários reais enviados pela loja: nome, semanas atrás, nota, texto.
 *
 * @return array
 */
function pm_instagram_reviews_data() {
	return array();
}

/**
 * Cadastra as avaliações do Instagram (não duplica).
 *
 * @return array Log.
 */
function pm_import_instagram_reviews() {
	$added = 0;
	foreach ( pm_instagram_reviews_data() as $r ) {
		list( $key, $name, $weeks, $rating, $text ) = $r;
		$exists = get_posts(
			array(
				'post_type'      => 'pm_review',
				'post_status'    => 'any',
				'meta_key'       => '_pm_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'fields'         => 'ids',
				'posts_per_page' => 1,
			)
		);
		if ( $exists ) {
			continue;
		}
		$date = gmdate( 'Y-m-d H:i:s', time() - $weeks * WEEK_IN_SECONDS );
		$id   = wp_insert_post(
			array(
				'post_type'     => 'pm_review',
				'post_status'   => 'publish',
				'post_title'    => $name,
				'post_content'  => $text,
				'post_date_gmt' => $date,
				'post_date'     => get_date_from_gmt( $date ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_pm_rating', $rating );
			update_post_meta( $id, '_pm_city', 'Raquel S.' === $name ? 'Goiás' : '' );
			update_post_meta( $id, '_pm_product_id', 0 );
			update_post_meta( $id, '_pm_source', 'instagram' );
			update_post_meta( $id, '_pm_import_key', $key );
			$added++;
		}
	}
	pm_reviews_flush_cache();
	/* translators: %d: quantidade. */
	return array( sprintf( __( 'Avaliações do Instagram adicionadas: %d (as que já existiam foram mantidas).', 'palestra-mantos' ), $added ) );
}

/**
 * Remove avaliações importadas que saíram da lista (ex.: a que fala do símbolo bordado).
 * Roda uma vez por versão da lista.
 */
function pm_remove_retired_reviews() {
	if ( '1' === get_option( 'pm_retired_reviews_v1' ) ) {
		return;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'pm_review',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_pm_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'ig-nubia', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);
	foreach ( $ids as $id ) {
		wp_trash_post( $id );
	}
	update_option( 'pm_retired_reviews_v1', '1', false );
}
add_action( 'init', 'pm_remove_retired_reviews', 20 );
