<?php
/**
 * "Descubra seu tamanho": provador por altura e peso com boneco que muda de corpo.
 *
 * - Na página do produto: botão que abre o provador numa janela.
 * - Na página "Guia de Medidas": provador aparece no topo automaticamente.
 * - Em qualquer página: shortcode [pm_provador].
 *
 * @package MengoMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tabela usada para sugerir o tamanho (modelagem regular, unissex).
 * Ajuste os limites pelo filtro 'pm_size_table' se as suas peças vestirem diferente.
 *
 * @return array
 */
function pm_size_table() {
	return apply_filters(
		'pm_size_table',
		array(
			'sizes' => array( 'P', 'M', 'G', 'GG' ),
			// Limites superiores de peso (kg) e altura (cm) para P, M e G. Acima do último = GG.
			'm'     => array(
				'weight' => array( 64, 76, 89 ),
				'height' => array( 168, 177, 185 ),
				'max'    => array( 'weight' => 110, 'height' => 196 ),
			),
			'f'     => array(
				'weight' => array( 57, 67, 79 ),
				'height' => array( 160, 167, 174 ),
				'max'    => array( 'weight' => 98, 'height' => 185 ),
			),
		)
	);
}

/**
 * HTML do provador.
 *
 * @param string $type  top (camiseta/moletom), bottom (calça) ou both (conjunto).
 * @param bool   $apply Mostrar botão "Usar este tamanho" (página do produto).
 * @return string
 */
function pm_size_finder_html( $type = 'both', $apply = false ) {
	$type = in_array( $type, array( 'top', 'bottom', 'both' ), true ) ? $type : 'both';
	$uid  = wp_unique_id( 'pm-fit-' );
	ob_start();
	?>
	<div class="pm-fit" data-sex="f" data-type="<?php echo esc_attr( $type ); ?>" data-table="<?php echo esc_attr( wp_json_encode( pm_size_table() ) ); ?>">
		<div class="pm-fit__stage" aria-hidden="true">
			<svg class="pm-fit__svg" viewBox="0 0 200 380" role="presentation" focusable="false">
				<line class="pm-fit__floor" x1="20" y1="362" x2="180" y2="362" />
				<g class="pm-fit__body pm-fit__body--m">
					<circle class="pm-fit__skin" cx="100" cy="42" r="19" />
					<path class="pm-fit__hair" d="M81 38 Q81 20 100 20 Q119 20 119 38 Q114 29 100 29 Q86 29 81 38 Z" />
					<rect class="pm-fit__skin" x="92" y="58" width="16" height="14" rx="4" />
					<path class="pm-fit__skin" d="M66 76 Q100 66 134 76 L141 92 L152 168 Q153 176 147 177 L141 177 L130 104 L128 186 L72 186 L70 104 L59 177 L53 177 Q47 176 48 168 L59 92 Z" />
					<path class="pm-fit__skin" d="M72 184 L128 184 L130 236 L124 352 L106 352 L101 236 L99 236 L94 352 L76 352 L70 236 Z" />
					<path class="pm-fit__shoe" d="M74 350 L96 350 L97 360 L68 360 Q68 352 74 350 Z M104 350 L126 350 Q132 352 132 360 L103 360 Z" />
					<path class="pm-fit__pants" d="M70 178 L130 178 L132 236 L127 342 Q116 346 104 342 L101 240 L99 240 L96 342 Q84 346 73 342 L68 236 Z" />
					<path class="pm-fit__pants-cuff" d="M73 336 L96 336 L96 346 L73 346 Z M104 336 L127 336 L127 346 L104 346 Z" />
					<path class="pm-fit__pants-waist" d="M70 178 L130 178 L130 186 L70 186 Z" />
					<path class="pm-fit__top" d="M64 74 Q100 64 136 74 L158 112 L141 122 L132 106 L133 192 Q100 200 67 192 L68 106 L59 122 L42 112 Z" />
					<path class="pm-fit__collar" d="M88 70 Q100 82 112 70" />
					<text class="pm-fit__logo" x="116" y="104">P</text>
				</g>
				<g class="pm-fit__body pm-fit__body--f">
					<path class="pm-fit__hair" d="M80 40 Q78 16 100 16 Q122 16 120 40 L126 96 Q113 104 100 102 Q87 104 74 96 Z" />
					<circle class="pm-fit__skin" cx="100" cy="42" r="17" />
					<path class="pm-fit__hair" d="M83 38 Q86 22 100 22 Q114 22 117 38 Q110 30 100 30 Q90 30 83 38 Z" />
					<rect class="pm-fit__skin" x="93" y="56" width="14" height="14" rx="4" />
					<path class="pm-fit__skin" d="M72 78 Q100 70 128 78 L135 94 L144 166 Q145 174 139 175 L134 175 L124 106 Q118 140 125 186 L75 186 Q82 140 76 106 L66 175 L61 175 Q55 174 56 166 L65 94 Z" />
					<path class="pm-fit__skin" d="M74 184 L126 184 L131 236 L122 352 L105 352 L101 236 L99 236 L95 352 L78 352 L69 236 Z" />
					<path class="pm-fit__shoe" d="M76 350 L96 350 L97 360 L70 360 Q70 352 76 350 Z M104 350 L124 350 Q130 352 130 360 L103 360 Z" />
					<path class="pm-fit__pants" d="M71 178 L129 178 L134 236 L125 342 Q115 346 104 342 L101 240 L99 240 L96 342 Q85 346 75 342 L66 236 Z" />
					<path class="pm-fit__pants-cuff" d="M75 336 L96 336 L96 346 L75 346 Z M104 336 L125 336 L125 346 L104 346 Z" />
					<path class="pm-fit__pants-waist" d="M71 178 L129 178 L129 186 L71 186 Z" />
					<path class="pm-fit__top" d="M70 76 Q100 68 130 76 L150 110 L136 119 L128 104 Q123 148 130 192 Q100 199 70 192 Q77 148 72 104 L64 119 L50 110 Z" />
					<path class="pm-fit__collar" d="M90 72 Q100 82 110 72" />
					<text class="pm-fit__logo" x="112" y="104">P</text>
				</g>
			</svg>
			<div class="pm-fit__height-tag"><span data-out="height-m">1,75 m</span></div>
		</div>

		<div class="pm-fit__panel">
			<p class="pm-fit__title"><?php esc_html_e( 'Descubra seu tamanho', 'palestra-mantos' ); ?></p>

			<fieldset class="pm-fit__sex">
				<legend class="screen-reader-text"><?php esc_html_e( 'Modelagem', 'palestra-mantos' ); ?></legend>
				<label><input type="radio" name="<?php echo esc_attr( $uid ); ?>-sex" value="f" checked> <span><?php esc_html_e( 'Feminino', 'palestra-mantos' ); ?></span></label>
				<label><input type="radio" name="<?php echo esc_attr( $uid ); ?>-sex" value="m"> <span><?php esc_html_e( 'Masculino', 'palestra-mantos' ); ?></span></label>
			</fieldset>

			<div class="pm-fit__field">
				<label for="<?php echo esc_attr( $uid ); ?>-h"><?php esc_html_e( 'Altura', 'palestra-mantos' ); ?> <output data-out="height">175 cm</output></label>
				<input id="<?php echo esc_attr( $uid ); ?>-h" type="range" name="height" min="145" max="205" step="1" value="175">
			</div>

			<div class="pm-fit__field">
				<label for="<?php echo esc_attr( $uid ); ?>-w"><?php esc_html_e( 'Peso', 'palestra-mantos' ); ?> <output data-out="weight">72 kg</output></label>
				<input id="<?php echo esc_attr( $uid ); ?>-w" type="range" name="weight" min="40" max="130" step="1" value="72">
			</div>

			<fieldset class="pm-fit__pref">
				<legend><?php esc_html_e( 'Como você gosta que a roupa fique?', 'palestra-mantos' ); ?></legend>
				<label><input type="radio" name="<?php echo esc_attr( $uid ); ?>-fit" value="-1"> <?php esc_html_e( 'Mais justa', 'palestra-mantos' ); ?></label>
				<label><input type="radio" name="<?php echo esc_attr( $uid ); ?>-fit" value="0" checked> <?php esc_html_e( 'Normal', 'palestra-mantos' ); ?></label>
				<label><input type="radio" name="<?php echo esc_attr( $uid ); ?>-fit" value="1"> <?php esc_html_e( 'Mais solta', 'palestra-mantos' ); ?></label>
			</fieldset>

			<div class="pm-fit__result" aria-live="polite">
				<span><?php esc_html_e( 'Sugerimos o tamanho', 'palestra-mantos' ); ?></span>
				<strong data-out="size">M</strong>
				<small data-out="note"></small>
			</div>

			<?php if ( $apply ) : ?>
				<button type="button" class="pm-btn pm-fit__apply"><?php esc_html_e( 'Usar este tamanho', 'palestra-mantos' ); ?> <span data-out="size-btn">M</span></button>
			<?php endif; ?>

			<p class="pm-fit__disclaimer"><?php esc_html_e( 'Sugestão aproximada para a nossa modelagem regular. Em dúvida entre dois tamanhos, escolha o maior ou chame a gente no WhatsApp.', 'palestra-mantos' ); ?></p>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Shortcode [pm_provador tipo="top|bottom|both"].
 *
 * @param array $atts Atributos.
 * @return string
 */
function pm_size_finder_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'tipo' => 'both' ), $atts, 'pm_provador' );
	return pm_size_finder_html( $atts['tipo'], false );
}
add_shortcode( 'pm_provador', 'pm_size_finder_shortcode' );

/**
 * Coloca o provador no topo da página Guia de Medidas (se ainda não tiver o shortcode).
 *
 * @param string $content Conteúdo.
 * @return string
 */
function pm_size_finder_on_guide_page( $content ) {
	if ( is_page( 'guia-de-medidas' ) && in_the_loop() && is_main_query() && false === strpos( $content, 'pm-fit' ) && ! has_shortcode( get_post_field( 'post_content', get_the_ID() ), 'pm_provador' ) ) {
		$content = pm_size_finder_html( 'both', false ) . $content;
	}
	return $content;
}
add_filter( 'the_content', 'pm_size_finder_on_guide_page', 20 );

/**
 * Tipo de peça a partir da categoria do produto.
 *
 * @param int $product_id Produto.
 * @return string
 */
function pm_product_fit_type( $product_id ) {
	if ( has_term( 'calcas', 'product_cat', $product_id ) ) {
		return 'bottom';
	}
	if ( has_term( 'conjuntos', 'product_cat', $product_id ) ) {
		return 'both';
	}
	return 'top';
}
