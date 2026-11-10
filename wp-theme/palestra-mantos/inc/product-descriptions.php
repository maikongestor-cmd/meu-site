<?php
/**
 * Descrições dos produtos por categoria (textos da Palestra Mantos).
 *
 * @package PalestraMantos
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bloco de perguntas frequentes reaproveitado.
 *
 * @param string $peca "O moletom" / "A camiseta".
 * @return string
 */
function pm_desc_faq( $peca ) {
	return '<h3>Perguntas que podem ajudar com esse produto</h3>
<p><strong>Devolução gratuita</strong><br>Aqui prezamos a sua satisfação: caso não goste ou não se convença do produto, pode trocar ou devolver no prazo de 7 dias corridos!</p>
<p><strong>P: ' . esc_html( $peca ) . ' desbota fácil?</strong><br>R: Não, os tecidos são produzidos com fio penteado 30.1, considerada a melhor fibra do mundo, deixando o tecido mais macio e durável.</p>';
}

/**
 * Descrições (HTML) por categoria.
 *
 * @return array slug => html
 */
function pm_product_descriptions() {
	$about = '<h3>Sobre a Palestra Mantos</h3>
<p>Palestra Mantos nasceu da paixão de um torcedor que sempre acreditou que vestir uma camisa vai muito além da roupa. É carregar histórias, memórias e a identidade de quem vive o futebol todos os dias.</p>
<p>Criamos esta marca para pessoas como você: torcedores que procuram qualidade, conforto e peças exclusivas para representar seu amor pelo clube dentro e fora dos estádios.</p>';

	return array(
		'conjuntos' => '<p>O Conjunto Moletom Palestra foi desenvolvido para quem busca conforto, qualidade e um visual moderno em qualquer ocasião. Produzido com tecido premium de toque macio, oferece excelente caimento, resistência e aquecimento na medida certa para os dias mais frios.</p>
<p>Com modelagem exclusiva, proporciona liberdade de movimentos e um estilo urbano inspirado na cultura do futebol, sendo ideal para usar no dia a dia, em viagens, passeios ou para acompanhar os jogos do seu time.</p>
<p>Cada detalhe foi pensado para entregar uma peça durável, confortável e versátil, mantendo a qualidade e o acabamento que fazem parte da identidade do Palestra.</p>
<p><strong>Conforto, estilo e qualidade para quem carrega sua paixão em qualquer ocasião.</strong></p>
<p>O Conjunto Moletom Palestra foi desenvolvido com tecido macio, resistente e de excelente acabamento, proporcionando conforto térmico e um caimento moderno para o dia a dia. Ideal para os dias frios, a peça combina durabilidade, conforto e versatilidade, sendo perfeita para acompanhar você em jogos, passeios ou na rotina.</p>
<h3>Características</h3>
<ul>
<li>Tecido confortável e resistente</li>
<li>Excelente acabamento</li>
<li>Modelagem moderna</li>
<li>Toque macio</li>
<li>Ótimo caimento</li>
<li>Ideal para temperaturas mais baixas</li>
<li>Alta durabilidade</li>
</ul>
' . pm_desc_faq( 'O moletom' ) . '
' . $about,

		'calcas'    => '<p><strong>Não é só uma calça. É a peça que acompanha sua paixão 💚</strong></p>
<p>Conforto para o dia a dia, caimento moderno e acabamento premium em uma calça desenvolvida para quem vive o futebol dentro e fora dos estádios.</p>
<ul class="pm-checklist">
<li>Tecido macio e resistente</li>
<li>Modelagem confortável</li>
<li>Excelente acabamento</li>
<li>Ideal para dias frios</li>
<li>Combina com qualquer ocasião</li>
</ul>
' . pm_desc_faq( 'O moletom' ) . '
<h3>Como escolher meu tamanho?</h3>
<p>Use o <a href="#descubra-seu-tamanho">Descubra seu tamanho</a> aqui na página ou consulte nossa tabela de medidas antes da compra. Se ainda tiver dúvidas, nossa equipe está pronta para ajudar.</p>',

		'camisetas' => '<p><strong>Você está a um passo de ter um excelente manto do Palestra para os jogos.</strong></p>
<p>E por que eu falo isso?</p>
<ul class="pm-checklist">
<li>100% algodão premium</li>
<li>Fio 30.1 penteado</li>
<li>Reforçada de ombro a ombro</li>
<li>Qualidade animal</li>
<li>Modelagem que se adapta ao corpo</li>
<li>Matéria-prima 100% nacional</li>
</ul>
' . pm_desc_faq( 'A camiseta' ) . '
<p><strong>P: De onde é a loja?</strong><br>R: Somos de São Paulo, Capital – SP.</p>
<p><strong>P: Possui loja física?</strong><br>R: Ainda não.</p>',
	);
}

/**
 * Aplica as descrições em todos os produtos das categorias.
 *
 * @return array Log.
 */
function pm_apply_product_descriptions() {
	$log   = array();
	$descs = pm_product_descriptions();
	foreach ( $descs as $slug => $html ) {
		$ids = get_posts(
			array(
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'tax_query'      => array( array( 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $slug ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);
		foreach ( $ids as $id ) {
			wp_update_post( array( 'ID' => $id, 'post_content' => $html ) );
			/* translators: %s: produto. */
			$log[] = sprintf( __( 'Descrição atualizada: %s', 'palestra-mantos' ), get_the_title( $id ) );
		}
	}
	if ( ! $log ) {
		$log[] = __( 'Nenhum produto encontrado nas categorias Calças, Camisetas ou Conjuntos.', 'palestra-mantos' );
	}
	return $log;
}
