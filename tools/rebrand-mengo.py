#!/usr/bin/env python3
"""
Gera o tema "Mengo Mantos" a partir do tema "Palestra Mantos".

Uso:  python3 tools/rebrand-mengo.py <pasta-com-as-imagens-da-marca>

O tema da Palestra continua sendo a base: toda melhoria feita nele passa para a
Mengo rodando este script de novo. Aqui só mudam marca, cores, textos, contatos,
imagens e a lista de produtos.
"""
import pathlib
import re
import shutil
import sys

ROOT = pathlib.Path(__file__).resolve().parent.parent
SRC = ROOT / 'wp-theme' / 'palestra-mantos'
DST = ROOT / 'wp-theme' / 'mengo-mantos'
IMG = pathlib.Path(sys.argv[1]).resolve()

if DST.exists():
    shutil.rmtree(DST)
shutil.copytree(SRC, DST)

# Fotos e marca da Palestra não vão para a Mengo.
for name in ['banner-mapas.webp', 'logo-completo.webp']:
    (DST / 'assets/img' / name).unlink(missing_ok=True)
for pat in ['calca-*', 'camiseta-*', 'conjunto-*', 'kit-camisetas*', 'logo-mark*', 'favicon*', 'apple-touch-icon*']:
    for f in (DST / 'assets/img').glob(pat):
        f.unlink()
for f in IMG.rglob('*'):
    if f.is_file():
        shutil.copy(f, DST / 'assets/img' / f.name)

# ---------------------------------------------------------------- cores
COLORS = [
    ('#06321d', '#0d0d0d'), ('#0a4a2b', '#141414'), ('#0e5c36', '#a50e24'),
    ('#127a45', '#c8102e'), ('#19a058', '#e0283c'), ('#e4f3ea', '#fbe7ea'),
    ('#3ddc84', '#ff4d5e'), ('#b9f0cf', '#ffc9cf'), ('#e3f3e8', '#fbe9ec'),
    ('#f5f8f6', '#f8f5f5'), ('#f6fbf8', '#fdf6f7'), ('#d9ffe8', '#ffe3e6'),
    ('#e9eeeb', '#eeeeee'), ('#e6ebe8', '#ebebeb'), ('#e3e8e5', '#e6e6e6'),
    ('#d9dedb', '#dddddd'), ('#c9d2cd', '#d0d0d0'), ('#f5f7f5', '#f6f6f6'),
    ('#111a15', '#151515'), ('#34403a', '#3a3a3a'), ('#6b7a72', '#737373'),
    ('rgba(25,160,88', 'rgba(224,40,60'), ('rgba(61,220,132', 'rgba(255,77,94'),
    ('rgba(6,50,29', 'rgba(20,20,20'), ('rgba(6, 50, 29', 'rgba(20, 20, 20'),
    ('rgba(6, 30, 18', 'rgba(10, 10, 10'), ('rgba(120,140,130', 'rgba(130,130,130'),
]

# ---------------------------------------------------------------- textos
TEXTS = [
    ('Palestra<small>Mantos', 'Mengo<small>Mantos'),
    ('palestraa.com.br', 'mengomantos.com.br'),
    ('De torcedor Palestrino para torcedor Palestrino 💚🤍', 'De rubro-negro para rubro-negro ❤️🖤'),
    ('manto do Palestra', 'manto rubro-negro'),
    ('verde e branco', 'vermelho e preto'),
    ('Palestra Mantos', 'Mengo Mantos'),
    ('Palestra no peito', 'Mengo no peito'),
    ('Black Friday Palestra', 'Black Friday Mengo'),
    ('Coleção Palestra', 'Coleção Mengo'),
    ('Avanti!', 'Vamos juntos!'),
    ('@palestra_mantos', '@mengomantos'),
    ("'palestra-' . $file", "'mengo-' . $file"),
    ('<text class="pm-fit__logo" x="116" y="104">P</text>', '<text class="pm-fit__logo" x="114" y="104">M</text>'),
    ('<text class="pm-fit__logo" x="112" y="104">P</text>', '<text class="pm-fit__logo" x="110" y="104">M</text>'),
    ('newsletter-palestra-mantos-', 'newsletter-mengo-mantos-'),
    ('💚', '❤️'),
    # contatos (rascunho: trocar pelos reais da loja)
    ("'5511989958223'", "'5511918214971'"),
    ("'(11) 98995-8223'", "'(11) 91821-4971'"),
    ("https://www.instagram.com/palestra_mantos/", "https://www.instagram.com/mengomantos/"),
    ("https://facebook.com/palestramantos", "https://facebook.com/mengomantos"),
    ("'São Paulo — SP'", "'Rio de Janeiro — RJ'"),
    ("'cnpj'           => '30.105.559/0001-76'", "'cnpj'           => '39.252.739/0001-69'"),
    ("'ig_reviews_count' => 200", "'ig_reviews_count' => 0"),
    # banners e imagens
    ("'slide3_text'    => 'Camisetas oversized 100% algodão, com logo P e escudo histórico.'",
     "'slide3_text'    => 'Camisetas oversized 100% algodão, com o CRF no peito e a arte 81·19·22·25 nas costas.'"),
    ("pm_opt_img( 'slide1_image', 'banner-mapas.webp' )", "pm_opt_img( 'slide1_image', 'banner-estadio-tall.webp' )"),
    ("<?php echo 0 === $pm_i ? ' fetchpriority=\"high\"' : ' loading=\"lazy\"'; ?>>", " loading=\"lazy\">"),
    ("pm_opt_img( 'slide2_image', 'conjunto-branco-masculino.webp' )", "pm_opt_img( 'slide2_image', 'hero-2a.webp' )"),
    ("pm_img( 'conjunto-branco-feminino.webp' )", "pm_img( 'hero-2b.webp' )"),
    ("Mapas de São Paulo, do Brasil e da América do Sul em verde com os símbolos do Palestra", "Estádio iluminado de vermelho à noite"),
    ("Conjunto moletom branco Palestra", "Conjunto moletom Mengo"),
    ("'PM-KIT-CAMISETAS'", "'MM-KIT-CAMISETAS'"),
    ("'calcas'    => 'calca-verde.webp'", "'calcas'    => 'calca-vermelha-ancora.webp'"),
    ("'slide3_title'   => 'Kit 2 camisetas oversized <em>R$ 187</em>'", "'slide3_title'   => 'Camiseta oversized <em>R$ 137</em>'"),
    ("pm_opt_img( 'slide3_image', 'kit-camisetas.webp' ),", "pm_opt_img( 'slide3_image', 'hero-3a.webp' ),\n\t\t'image2'  => pm_opt( 'slide3_image' ) ? '' : pm_img( 'hero-3b.webp' ),"),
    ("'camisetas' => 'camiseta-logo-p.webp'", "'camisetas' => 'camiseta-vermelha-crf-frente.webp'"),
    ("'conjuntos' => 'conjunto-branco-masculino.webp'", "'conjuntos' => 'conjunto-vermelho-preto-crf.webp'"),
    ("pm_img( 'banner-mapas.webp' )", "pm_img( 'welcome-torcida.webp' )"),
    ("'slide3_button'  => 'Comprar kit'", "'slide3_button'  => 'Comprar camiseta'"),
    ("echo wp_kses_post( wpautop( $review->post_content ) );", "echo wp_kses_post( wpautop( pm_mengo_review_text( $review ) ) );"),
    ('<img src="<?php echo esc_url( $pm_slide[\'image2\'] ); ?>" alt="" width="600" height="750" loading="lazy">',
     '<img src="<?php echo esc_url( $pm_slide[\'image2\'] ); ?>" alt="" width="600" height="750"<?php echo 0 === $pm_i ? \'\' : \' loading="lazy"\'; ?>>'),
    ("'Palestra'", "'Mengo'"),
    ('Palestra', 'Mengo'),
    ('Palestrino', 'Rubro-negro'),
    # textos de boas-vindas
    ("É carregar histórias, memórias e a identidade de quem vive o futebol todos os dias.",
     "É carregar a raça, a história e o orgulho de quem vive o vermelho e preto todos os dias."),
]

CATALOG = """function pm_products_catalog() {
	return array(
		array(
			'sku'     => 'MM-CALCA-VERMELHA-CRF',
			'name'    => 'Calça Moletom Mengo Vermelha – CRF e Âncora',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-vermelha-ancora.webp' ),
			'short'   => 'Calça jogger vermelha em moletom flanelado, com cordão de ajuste, CRF e âncora em preto.',
			'order'   => 1,
		),
		array(
			'sku'     => 'MM-CALCA-PRETA-CRF',
			'name'    => 'Calça Moletom Mengo Preta – CRF, Âncora e 81·19·22·25',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-preta-ancora-anos.webp' ),
			'short'   => 'Calça jogger preta em moletom flanelado, com CRF e âncora em vermelho e os anos 81, 19, 22 e 25 na perna.',
			'order'   => 2,
		),
		array(
			'sku'     => 'MM-CALCA-BRANCA-CRF',
			'name'    => 'Calça Moletom Mengo Branca – CRF, Âncora e 81·19·22·25',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-branca-ancora-anos.webp', 'calca-branca-ancora-anos-feminina.webp' ),
			'short'   => 'Calça jogger branca em moletom flanelado, com CRF e âncora em vermelho e os anos 81, 19, 22 e 25 na perna.',
			'order'   => 3,
		),
		array(
			'sku'     => 'MM-CAMISETA-PRETA-LIB',
			'name'    => 'Camiseta Oversized Mengo Preta – 81·19·22·25',
			'cat'     => 'camisetas',
			'regular' => '137.00',
			'sale'    => '',
			'images'  => array( 'camiseta-preta-crf-frente.webp', 'camiseta-preta-crf-costas.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, preta, com o CRF vermelho no peito e a arte 81·19·22·25 nas costas.',
			'order'   => 4,
		),
		array(
			'sku'     => 'MM-CAMISETA-VERMELHA-LIB',
			'name'    => 'Camiseta Oversized Mengo Vermelha – 81·19·22·25',
			'cat'     => 'camisetas',
			'regular' => '137.00',
			'sale'    => '',
			'images'  => array( 'camiseta-vermelha-crf-frente.webp', 'camiseta-vermelha-crf-costas.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, vermelha, com o CRF preto no peito e a arte 81·19·22·25 nas costas.',
			'order'   => 5,
		),
		array(
			'sku'     => 'MM-CONJUNTO-VERMELHO-PRETO-CRF',
			'name'    => 'Conjunto Moletom Mengo Vermelho e Preto – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-vermelho-preto-crf.webp' ),
			'short'   => 'Blusão vermelho com capuz e bolso canguru, com CRF e âncora em preto, + calça jogger preta com o CRF em vermelho. Moletom flanelado.',
			'order'   => 6,
		),
		array(
			'sku'     => 'MM-CONJUNTO-PRETO-CRF',
			'name'    => 'Conjunto Moletom Mengo Preto – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-preto-crf.webp' ),
			'short'   => 'Blusão preto com capuz e bolso canguru + calça jogger preta, com CRF e âncora em vermelho. Moletom flanelado.',
			'order'   => 7,
		),
	);
}
"""

DARK_CSS = """

/* ==========================================================================
   Mengo Mantos — tema escuro (preto e vermelho)
   ========================================================================== */
html, body { background: var(--pm-bg); color: var(--pm-text); }
a:hover { color: #ff4d5e; }
::placeholder { color: #8a8a8a; opacity: 1; }
.pm-logo, .pm-logo__text { color: #fff; }
.pm-logo img { width: 58px !important; margin: -10px 0; }
@media (max-width: 900px) { .pm-logo img { width: 48px !important; } }
.pm-logo__text small { color: #e0283c; }
.pm-header { background: rgba(10,10,10,.96); border-bottom-color: #222; backdrop-filter: blur(6px); }
.pm-nav a, .pm-actions a, .pm-icon-btn, .pm-menu-toggle { color: #fff; }
.pm-search input[type="search"] { background: #161616; border-color: #2a2a2a; color: #fff; }
.pm-search input[type="search"]:focus { background: #1b1b1b; }
input, select, textarea, .input-text { background: #161616 !important; color: #fff !important; border-color: #2e2e2e !important; }
.select2-container--default .select2-selection--single { background: #161616; border-color: #2e2e2e; }
.select2-container--default .select2-selection--single .select2-selection__rendered { color: #fff; }
.select2-dropdown { background: #161616; color: #fff; border-color: #2e2e2e; }
/* Banners: todos escuros, com a fumaça vermelha */
.pm-slide--light, .pm-slide--dark { background: #050505 url(assets/img/bg-fumaca.webp) center / cover no-repeat; color: rgba(255,255,255,.86); }
.pm-slide--light::before, .pm-slide--dark::before { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(0,0,0,.82) 0%, rgba(0,0,0,.45) 55%, rgba(0,0,0,.2) 100%); }
.pm-slide--light .pm-slide__title { color: #fff; }
.pm-slide--light .pm-slide__title em { color: #e0283c; }
.pm-slide--light .pm-eyebrow { color: #ff4d5e; }
.pm-slide--light .pm-slide__ship, .pm-slide--light .pm-slide__points li { color: #fff; }
.pm-slide--light { background: #050505 url(assets/img/banner-estadio-wide.webp) right center / auto 100% no-repeat !important; }
.pm-slide--light::before { background: linear-gradient(90deg, #050505 0%, #050505 30%, rgba(5,5,5,.7) 48%, rgba(5,5,5,0) 70%) !important; }
.pm-slide--light .pm-slide__media { display: none; }
.pm-slide--light .pm-slide__inner { grid-template-columns: minmax(0, 620px); min-height: 600px; }
.pm-slide--light .pm-slide__title { font-size: clamp(3rem, 7vw, 5.6rem); }
.pm-slide--light .pm-eyebrow { color: #ff4d5e !important; opacity: 1; }
.pm-slide--light .pm-btn { background: #d0102b; border-color: #d0102b; color: #fff; box-shadow: 0 10px 30px rgba(208,16,43,.45); }
.pm-slide--light .pm-btn:hover { background: #fff; border-color: #fff; color: #d0102b; }
@media (max-width: 900px) {
	.pm-slide--light { background-image: url(assets/img/banner-estadio-tall.webp) !important; background-position: center 70% !important; }
	.pm-slide--light::before { background: linear-gradient(180deg, rgba(0,0,0,.85) 0%, rgba(0,0,0,.55) 45%, rgba(0,0,0,.1) 80%) !important; }
	.pm-slide--light .pm-slide__inner { min-height: 640px; align-content: start; }
}
.pm-welcome__media img { filter: none; border-radius: 22px; object-fit: cover; box-shadow: 0 0 50px rgba(224,40,60,.35), 0 30px 60px rgba(0,0,0,.5); }
.pm-slider__arrow { background: rgba(255,255,255,.12); color: #fff; box-shadow: none; backdrop-filter: blur(4px); }
.pm-slider__dot { background: rgba(255,255,255,.35); }
/* Seções */
.pm-section--soft { background: #111 !important; }
.pm-benefits { background: #0f0f0f; border-color: #222; }
.pm-benefit + .pm-benefit { border-color: #222; }
.pm-benefit svg { color: #e0283c; }
.pm-cat { background: #151515; }
.pm-newsletter { background: linear-gradient(120deg, #7a0716, #c8102e) !important; }
.pm-welcome { background: #0a0a0a; }
.pm-eyebrow { color: #ff4d5e; }
/* Produtos */
.woocommerce ul.products li.product, .woocommerce-page ul.products li.product { background: transparent; }
.woocommerce ul.products li.product .woocommerce-loop-product__title, .woocommerce ul.products li.product .price { color: #fff !important; }
.woocommerce ul.products li.product a img { background: #151515; }
.woocommerce div.product .product_title, .woocommerce div.product p.price, .woocommerce div.product span.price { color: #fff !important; }
.woocommerce div.product .woocommerce-tabs ul.tabs li { background: #151515 !important; border-color: #2a2a2a !important; }
.woocommerce div.product .woocommerce-tabs ul.tabs li a { color: #ddd !important; }
.woocommerce div.product .woocommerce-tabs ul.tabs li.active { background: #0a0a0a !important; }
.woocommerce div.product .woocommerce-tabs ul.tabs li.active a { color: #fff !important; }
.woocommerce div.product .woocommerce-tabs ul.tabs::before, .woocommerce div.product .woocommerce-tabs ul.tabs li::before, .woocommerce div.product .woocommerce-tabs ul.tabs li::after { border-color: #2a2a2a !important; box-shadow: none !important; }
.woocommerce .woocommerce-breadcrumb, .woocommerce .woocommerce-breadcrumb a { color: #9b9b9b; }
.woocommerce span.onsale { background: #e0283c; }
.pm-swatch, .woocommerce div.product form.cart .variations label { color: #fff; }
.woocommerce .quantity .qty { color: #fff; background: transparent !important; border: 0 !important; }
.pm-qty button { color: #fff; }
.woocommerce-message, .woocommerce-info, .woocommerce-error { background: #161616 !important; color: #ddd !important; border-top-color: #e0283c !important; }
.woocommerce table.shop_table, .woocommerce table.shop_table th, .woocommerce table.shop_table td { color: #ddd; border-color: #2a2a2a !important; }
.woocommerce-checkout #payment div.payment_box { background: #121212 !important; color: #ddd; }
.woocommerce-checkout #payment ul.payment_methods li:has(input:checked) { background: #1e0a0d; border-color: #e0283c; }
.pm-minicart { background: #121212; color: #ddd; }
.pm-minicart__head, .pm-minicart__foot { border-color: #262626 !important; background: #121212; }
.pm-pay, .pm-pay-info { background: #151515 !important; }
.pm-steps__n { background: #181818; }
.pm-stars { color: #f5b301; }
.pm-page-hero, .pm-page-header { background: #111 !important; }
.pm-page-hero h1, .pm-page-header h1 { color: #fff; }
.pm-table-wrap table th { background: #c8102e; color: #fff; }
.pm-table-wrap table td { border-color: #2a2a2a; }
.pm-fit { background: #121212 !important; }
.pm-fit__floor { fill: #1c1c1c; }
.pm-footer { background: #000 !important; border-top: 3px solid #c8102e; }
.pm-topbar { background: #c8102e !important; color: #fff; }
.pm-whats-float__label { background: #fff; color: #111; }
.pm-welcome__sign { color: #ff4d5e !important; }
.pm-freebar--mini .pm-freebar__track, .pm-freebar__track { background: #2a2a2a; }
.pm-steps li.is-done .pm-steps__n { color: #ff4d5e; }
"""

RED_CSS = """

/* ==========================================================================
   Mengo Mantos — vermelho como cor principal, preto como secundária
   ========================================================================== */
:root {
	--pm-bg: #b80d22;
	--pm-bg-soft: #9b0a1c;
	--pm-surface: #111111;
	--pm-line: rgba(0,0,0,.28);
	--pm-text: #ffffff;
	--pm-muted: rgba(255,255,255,.78);
	--pm-green-600: #0d0d0d;
	--pm-green-500: #000000;
	--pm-green-700: #ffffff;
	--pm-green-100: rgba(0,0,0,.28);
}
html, body { background: var(--pm-bg); }
body { background: linear-gradient(180deg, #c8102e 0%, #a90b1f 100%) fixed; }
.pm-topbar { background: #000 !important; }
.pm-header { background: rgba(0,0,0,.94); border-bottom-color: #000; }
.pm-nav a:hover, .pm-nav .current-menu-item > a, .pm-nav .current_page_item > a { color: #ff4d5e !important; }
.pm-section--soft { background: rgba(0,0,0,.16) !important; }
.pm-benefits { background: #0d0d0d; border-color: #000; }
.pm-benefit svg { color: #ff4d5e; }
.pm-eyebrow { color: #fff !important; opacity: .85; }
h1, h2, h3, h4, .pm-section__head h2 { color: #fff; }
.pm-link-arrow, .pm-link-arrow:hover { color: #fff; }
/* Botões: pretos sobre o vermelho */
.pm-btn, .woocommerce a.button, .woocommerce button.button, .woocommerce input.button, .woocommerce a.button.alt, .woocommerce button.button.alt { background: #0d0d0d; border-color: #0d0d0d; color: #fff; }
.pm-btn:hover, .woocommerce a.button:hover, .woocommerce button.button:hover, .woocommerce button.button.alt:hover { background: #fff; border-color: #fff; color: #c8102e; }
.pm-btn--outline { background: transparent; border-color: #fff; color: #fff; }
.pm-btn--outline:hover { background: #fff; color: #c8102e; }
.woocommerce ul.products li.product .button { background: transparent !important; border: 2px solid #fff !important; color: #fff !important; }
.woocommerce ul.products li.product .button:hover { background: #fff !important; color: #c8102e !important; }
/* Banners */
.pm-slide--light::before, .pm-slide--dark::before { background: linear-gradient(90deg, rgba(120,6,20,.92) 0%, rgba(160,8,28,.6) 55%, rgba(0,0,0,.15) 100%); }
.pm-slide--light .pm-slide__title em, .pm-slide--dark .pm-slide__title em { color: #fff; text-shadow: 3px 3px 0 #000; }
.pm-search button { background: #c8102e; }
.pm-slide--dark .pm-eyebrow { background: #000 !important; color: #fff !important; opacity: 1; box-shadow: none; }
.pm-slide__points svg { color: #fff !important; }
.pm-btn--green { background: #000; border-color: #000; color: #fff; }
.pm-btn--green:hover { background: #fff; border-color: #fff; color: #c8102e; }
.pm-slider__dot.is-active { background: #000; }
/* Produtos e preços */
.pm-pix-loop, .pm-pix-loop b { color: #fff !important; }
.woocommerce ul.products li.product a img { background: #111; }
.pm-pay, .pm-pay-info { background: #0d0d0d !important; }
.pm-pay b, .pm-pay strong, .pm-pay-info b { color: #ff4d5e !important; }
.pm-pay svg, .pm-pay-info svg { color: #ff4d5e !important; }
.pm-size-link, .pm-size-link svg, .pm-size-links a, .pm-size-links a svg, .pm-size-guide, .pm-size-guide svg { color: #000 !important; }
.pm-swatch { background: rgba(255,255,255,.12) !important; border: 2px solid rgba(255,255,255,.75) !important; color: #fff !important; }
.pm-swatch:hover { border-color: #fff !important; background: rgba(255,255,255,.22) !important; }
.pm-swatch.is-active, .pm-swatch.is-selected, .pm-swatch[aria-pressed="true"] { background: #000 !important; border-color: #000 !important; color: #fff !important; box-shadow: 0 0 0 3px rgba(255,255,255,.9); transform: scale(1.06); }
.woocommerce div.product form.cart .single_add_to_cart_button { background: #000 !important; color: #fff !important; }
.pm-ship, .pm-cat, .pm-review, .pm-testimonial, .pm-co-reviews, .pm-fit { background: #0d0d0d !important; border-color: #000 !important; }
.pm-ship__list li { background: #1a1a1a; }
.pm-ship__list li.is-free b, .pm-ship__note { color: #ff4d5e; }
.pm-stars { color: #f5b301; }
.pm-rating-line, .pm-rating-line a, .pm-rating-line a:hover, .pm-rating-line span, .pm-rating-line b, .pm-rating-line strong { color: #000 !important; font-weight: 700; }
/* Carrinho e checkout: cartões pretos sobre o vermelho */
.pm-freebar { background: #0d0d0d; }
.pm-freebar p svg { color: #ff4d5e; }
.pm-freebar__fill, .pm-freebar__bar span { background: #ff4d5e !important; }
.pm-minicart { background: #0d0d0d; }
.pm-minicart .pm-btn, .pm-minicart .button.checkout { background: #c8102e !important; border-color: #c8102e !important; color: #fff !important; }
.pm-minicart .pm-btn:hover { background: #fff !important; color: #c8102e !important; }
.woocommerce-checkout #payment #place_order, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button { background: #e0283c !important; color: #fff !important; border: 2px solid #e0283c !important; box-shadow: 0 10px 26px rgba(224,40,60,.35); }
.woocommerce-checkout #payment #place_order:hover, .woocommerce-cart .wc-proceed-to-checkout a.checkout-button:hover { background: #c8102e !important; border-color: #c8102e !important; color: #fff !important; }
.woocommerce-checkout #payment ul.payment_methods li:has(input:checked) { border-color: #fff; }
.pm-steps li.is-current .pm-steps__n { background: #000; border-color: #000; }
.pm-steps li.is-done, .pm-steps li.is-done .pm-steps__n { color: #fff; border-color: #fff; background: transparent; }
.pm-steps li.is-done + li::before, .pm-steps li.is-current + li::before { background: #fff; }
.woocommerce-message, .woocommerce-info, .woocommerce-error { border-top-color: #000 !important; }
.pm-newsletter { background: #000 !important; }
.pm-newsletter .pm-btn { background: #c8102e; border-color: #c8102e; }
.pm-welcome { background: transparent; }
.pm-welcome__sign { color: #fff !important; }
.pm-footer { border-top-color: #000; }
.pm-page-hero, .pm-page-header { background: rgba(0,0,0,.25) !important; }
/* Descubra seu tamanho: boneco com camiseta vermelha e calça preta */
.pm-fit__stage { background: radial-gradient(circle at 50% 35%, #4a0a14, #0b0b0b 75%) !important; }
.pm-fit__floor { stroke: rgba(255,255,255,.35); }
.pm-fit__top, .pm-fit[data-type="top"] .pm-fit__top, .pm-fit[data-type="both"] .pm-fit__top { fill: #d0102b !important; stroke: #000 !important; }
.pm-fit__collar { stroke: #000; }
.pm-fit__logo, .pm-fit[data-type="top"] .pm-fit__logo, .pm-fit[data-type="both"] .pm-fit__logo { fill: #fff !important; }
.pm-fit__pants, .pm-fit[data-type="bottom"] .pm-fit__pants, .pm-fit[data-type="both"] .pm-fit__pants { fill: #111 !important; stroke: #000 !important; }
.pm-fit__pants-cuff, .pm-fit__pants-waist, .pm-fit[data-type="bottom"] .pm-fit__pants-cuff, .pm-fit[data-type="both"] .pm-fit__pants-cuff, .pm-fit[data-type="bottom"] .pm-fit__pants-waist, .pm-fit[data-type="both"] .pm-fit__pants-waist { fill: #d0102b !important; stroke: #000 !important; }
.pm-fit__shoe { fill: #fff; stroke: #000; }
.pm-fit__height-tag { background: #d0102b !important; color: #fff !important; }
.pm-fit__sex { background: #1a1a1a !important; box-shadow: none; }
.pm-fit__sex input:checked + span { background: #d0102b; color: #fff; }
.pm-fit input[type="range"], .pm-fit__pref input { accent-color: #d0102b; }
.pm-fit__result { background: #d0102b !important; }
.pm-fit__result strong { color: #fff !important; }
/* Tabelas e links das páginas (Guia de Medidas, Trocas…) */
.pm-content table:not(.shop_table):not(.variations) thead th { background: #000 !important; color: #fff !important; border-color: #000 !important; }
.pm-content table:not(.shop_table):not(.variations) td { background: #111; color: #fff; border-color: #000 !important; }
.pm-content table:not(.shop_table):not(.variations) tbody tr:nth-child(even) td { background: #1b1b1b; }
.pm-content a:not(.pm-btn) { color: #fff; text-decoration: underline; text-underline-offset: 3px; font-weight: 700; }
.pm-content a:not(.pm-btn):hover { color: #000; }
/* Opções de frete (PAC / SEDEX) no carrinho e checkout: a escolhida fica preta e destacada */
.woocommerce ul#shipping_method { display: grid; gap: 8px; margin: 4px 0 0 !important; padding: 0 !important; }
.woocommerce ul#shipping_method li { position: relative; margin: 0 !important; padding: 0 !important; list-style: none; text-align: left; }
.woocommerce ul#shipping_method li input.shipping_method { position: absolute; opacity: 0; pointer-events: none; }
.woocommerce ul#shipping_method li label { display: block; margin: 0; padding: 11px 14px; border-radius: 12px; border: 2px solid rgba(255,255,255,.35); background: rgba(255,255,255,.06); color: #fff; font-weight: 600; cursor: pointer; transition: .15s; }
.woocommerce ul#shipping_method li label:hover { border-color: #fff; }
.woocommerce ul#shipping_method li input.shipping_method:checked + label { background: #000; border-color: #e0283c; box-shadow: 0 0 0 1px #e0283c; }
.woocommerce ul#shipping_method li input.shipping_method:checked + label::after { content: "✓"; float: right; color: #ff4d5e; font-weight: 800; }
.woocommerce ul#shipping_method li input.shipping_method:focus-visible + label { outline: 2px solid #fff; outline-offset: 2px; }
.woocommerce ul#shipping_method li input[type="hidden"] + label { background: #000; border-color: #e0283c; }
.pm-table-wrap table th { background: #000; }
.pm-table-wrap table td { border-color: rgba(0,0,0,.3); }
"""

MENGO_REVIEWS = """function pm_instagram_reviews_data() {
	return array(
		array( 'ig-guilherme', 'Guilherme B.', 90, 5, 'Já comprei duas vezes na Mengo Mantos e só tenho elogios. Chegou rápido e a qualidade é excelente. Parabéns!' ),
		array( 'ig-rhe', 'Rhe P.', 91, 5, 'Peguei a camiseta oversized preta com o CRF... Tecido confortável do jeito que eu gosto. Obrigado 👏' ),
		array( 'ig-raquel', 'Raquel S.', 80, 5, 'Sou de Goiás!! A calça branca chegou hoje e amei a modelagem feminina 😍 vou comprar mais' ),
		array( 'ig-adriana', 'Adriana A.', 83, 5, 'Linda a calça vermelha, amei e chegou direitinho 😉' ),
		array( 'ig-nanda', 'Nanda L.', 83, 5, 'Chegou a tão esperada calça! Muito rápido. Comprei uma para mim e uma para meu filho, ficaram lindas. Agora ele já quer o conjunto hahaha. Obrigada pela atenção ❤️🖤' ),
		array( 'ig-sheila', 'Sheila L.', 85, 5, 'Linda calça, confortável!!! Chegou no prazo e sem intercorrências!!!! Recomendo muito!!!!!' ),
		array( 'ig-alex', 'Alex A.', 86, 5, "Atendimento pós-venda sempre atencioso.\\nProduto corresponde à foto.\\nEntrega regular.\\nRecomendo a compra, entrega garantida." ),
		array( 'ig-diego', 'Diego S.', 89, 5, 'Chegou certinho, conjunto rubro-negro tooop!' ),
	);
}"""

FEATURED = "array( 'MM-CALCA-VERMELHA-CRF', 'MM-CALCA-PRETA-CRF', 'MM-CALCA-BRANCA-CRF', 'MM-CAMISETA-PRETA-LIB' )"

for path in DST.rglob('*'):
    if path.suffix not in ('.php', '.css', '.js', '.txt') or not path.is_file():
        continue
    s = path.read_text(encoding='utf-8')
    o = s
    for a, b in COLORS:
        s = s.replace(a, b)
    for a, b in TEXTS:
        s = s.replace(a, b)
    if path.name == 'setup-content.php':
        s = re.sub(r"function pm_products_catalog\(\) \{.*?\n\}\n", lambda m: CATALOG, s, count=1, flags=re.S)
        s = re.sub(r"array\( 'PM-CONJUNTO-BRANCO'[^)]*\)", FEATURED, s)
        s = s.replace("$new = array( 'PM-CALCA-BRANCA-FAIXA' );", "$new = array( 'MM-CALCA-VERMELHA-CRF', 'MM-CALCA-PRETA-CRF', 'MM-CALCA-BRANCA-CRF', 'MM-CAMISETA-PRETA-LIB', 'MM-CAMISETA-VERMELHA-LIB', 'MM-CONJUNTO-VERMELHO-PRETO-CRF', 'MM-CONJUNTO-PRETO-CRF' );")
    if path.name == 'setup-content.php':
        # Troca a foto desenhada das camisetas pela foto real em lojas já montadas (uma vez só).
        s += """
/**
 * Camisetas que entraram com a foto desenhada recebem as fotos reais (frente e costas).
 */
function pm_mengo_tshirt_photos() {
	if ( ! class_exists( 'WooCommerce' ) || ! current_user_can( 'manage_woocommerce' ) || wp_doing_ajax() || get_option( 'pm_mengo_tshirt_photos' ) ) {
		return;
	}
	update_option( 'pm_mengo_tshirt_photos', 1, false );
	$fotos = array(
		'MM-CAMISETA-PRETA-LIB'    => array( 'camiseta-preta-crf-frente.webp', 'camiseta-preta-crf-costas.webp' ),
		'MM-CAMISETA-VERMELHA-LIB' => array( 'camiseta-vermelha-crf-frente.webp', 'camiseta-vermelha-crf-costas.webp' ),
	);
	foreach ( $fotos as $sku => $files ) {
		$id      = wc_get_product_id_by_sku( $sku );
		$product = $id ? wc_get_product( $id ) : null;
		if ( ! $product ) {
			continue;
		}
		$current = basename( (string) get_attached_file( $product->get_image_id() ) );
		if ( $current && false === strpos( $current, 'crf-frente' ) ) {
			$ids = array();
			foreach ( $files as $file ) {
				$img = pm_import_theme_image( $file, $product->get_name() );
				if ( $img ) {
					$ids[] = $img;
				}
			}
			if ( $ids ) {
				$product->set_image_id( $ids[0] );
				$product->set_gallery_image_ids( array_slice( $ids, 1 ) );
				$product->save();
			}
		}
	}
}
add_action( 'admin_init', 'pm_mengo_tshirt_photos', 40 );

/**
 * Manda para a lixeira os produtos de exemplo (fotos desenhadas), uma vez só.
 */
function pm_mengo_trash_demo_products() {
	if ( ! class_exists( 'WooCommerce' ) || ! current_user_can( 'manage_woocommerce' ) || wp_doing_ajax() || get_option( 'pm_mengo_demo_trashed' ) ) {
		return;
	}
	update_option( 'pm_mengo_demo_trashed', 1, false );
	foreach ( array( 'MM-CONJUNTO-PRETO', 'MM-CONJUNTO-VERMELHO', 'MM-CALCA-PRETA', 'MM-CALCA-VERMELHA', 'MM-CAMISETA-PRETA', 'MM-CAMISETA-BRANCA', 'MM-KIT-CAMISETAS' ) as $sku ) {
		$id = wc_get_product_id_by_sku( $sku );
		if ( $id ) {
			wp_trash_post( $id );
		}
	}
}
add_action( 'admin_init', 'pm_mengo_trash_demo_products', 45 );
"""
    if path.name == 'reviews.php':
        # Avaliações de exemplo voltadas para a Mengo (rascunho: entram como "pendentes" para revisão).
        s = re.sub(r"function pm_instagram_reviews_data\(\) \{\n\treturn array\(.*?\n\t\);\n\}", lambda m: MENGO_REVIEWS, s, count=1, flags=re.S)
        a = s.index('function pm_import_instagram_reviews')
        b = s.index('\n}\n', a)
        s = s[:a] + s[a:b].replace("'post_status'   => 'publish',", "'post_status'   => 'pending',") + s[b:]
        s += """
/**
 * Atualiza (uma vez) o texto das avaliações de exemplo que já tinham sido importadas.
 */
function pm_mengo_update_sample_reviews() {
	if ( ! current_user_can( 'edit_posts' ) || wp_doing_ajax() || get_option( 'pm_mengo_reviews_v3' ) ) {
		return;
	}
	update_option( 'pm_mengo_reviews_v3', 1, false );
	foreach ( pm_instagram_reviews_data() as $row ) {
		$ids = get_posts(
			array(
				'post_type'      => 'pm_review',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_pm_import_key', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $row[0], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			)
		);
		if ( $ids ) {
			wp_update_post( array( 'ID' => $ids[0], 'post_content' => $row[4] ) );
		}
	}
}
add_action( 'admin_init', 'pm_mengo_update_sample_reviews', 50 );

/**
 * Texto da avaliação na tela: usa o texto da Mengo nas avaliações de exemplo e tira símbolos do Palmeiras.
 *
 * @param WP_Post $review Avaliação.
 * @return string
 */
function pm_mengo_review_text( $review ) {
	$text = $review->post_content;
	$key  = (string) get_post_meta( $review->ID, '_pm_import_key', true );
	if ( $key ) {
		foreach ( pm_instagram_reviews_data() as $row ) {
			if ( $row[0] === $key ) {
				$text = $row[4];
				break;
			}
		}
	}
	$text = str_replace( array( '🐷', '🐖', '🇮🇹', '💚', 'Palestra Mantos', 'Palestra', 'palmeiras', 'Palmeiras', 'Verdão', 'verdão' ), array( '', '', '', '❤️', 'Mengo Mantos', 'Mengo', 'Mengo', 'Mengo', 'Mengão', 'Mengão' ), $text );
	return trim( preg_replace( '/[ \t]+$/m', '', $text ) );
}
"""
    if path.name == 'style.css':
        # Tema escuro: preto com vermelho.
        s = s.replace('background: #fff', 'background: var(--pm-surface)')
        s = s.replace('.pm-hero__flag i:nth-child(2) { background: var(--pm-surface); }', '.pm-hero__flag i:nth-child(2) { background: #fff; }')
        s = s.replace('.pm-btn--light { background: var(--pm-surface);', '.pm-btn--light { background: #fff;')
        # Textos que eram verde-escuro sobre fundo claro passam a ser brancos.
        s = s.replace('color: var(--pm-green-800)', 'color: #fff').replace('color: var(--pm-green-900)', 'color: #fff')
        s = s.replace('.pm-btn--light { background: #fff; border-color: #fff; color: #fff; }', '.pm-btn--light { background: #fff; border-color: #fff; color: #111; }')
        s = s.replace('\t--pm-ink: #151515;\n\t--pm-text: #3a3a3a;\n\t--pm-muted: #737373;\n\t--pm-line: #e6e6e6;\n\t--pm-bg: #ffffff;\n\t--pm-bg-soft: #f6f6f6;',
                      '\t--pm-ink: #ffffff;\n\t--pm-text: #d2d2d2;\n\t--pm-muted: #9b9b9b;\n\t--pm-line: #2a2a2a;\n\t--pm-bg: #0a0a0a;\n\t--pm-bg-soft: #131313;\n\t--pm-surface: #181818;')
        s = s.replace('\t--pm-green-100: #fbe7ea;', '\t--pm-green-100: #2b0b10;')
        s = s.replace('\t--pm-shadow: 0 10px 30px rgba(20, 20, 20, .10);', '\t--pm-shadow: 0 10px 30px rgba(0, 0, 0, .55);')
        s += DARK_CSS + RED_CSS
        # Faixa de 3 cores: vermelho, branco e preto.
        s = s.replace('.pm-hero__flag i:nth-child(3) { background: var(--pm-red); }', '.pm-hero__flag i:nth-child(3) { background: #111; }')
        s = re.sub(r'Theme Name:.*', 'Theme Name: Mengo Mantos', s, count=1)
        s = re.sub(r'Version:.*', 'Version: 1.2.9', s, count=1)
        s = re.sub(r'Description:.*', 'Description: Loja de moletons, calças e camisetas rubro-negras. Baseado no tema Palestra Mantos.', s, count=1)
    if path.name == 'functions.php':
        s += """
/**
 * Pré-carrega a foto do 1º banner (uma para computador, outra para celular).
 */
function pm_mengo_preload_hero() {
	if ( ! is_front_page() ) {
		return;
	}
	printf( '<link rel="preload" as="image" href="%s" media="(min-width: 901px)" fetchpriority="high">' . "\\n", esc_url( PM_URI . '/assets/img/banner-estadio-wide.webp' ) );
	printf( '<link rel="preload" as="image" href="%s" media="(max-width: 900px)" fetchpriority="high">' . "\\n", esc_url( PM_URI . '/assets/img/banner-estadio-tall.webp' ) );
}
add_action( 'wp_head', 'pm_mengo_preload_hero', 1 );
"""
        s = re.sub(r"define\( 'PM_VERSION', '[^']*' \);", "define( 'PM_VERSION', '1.2.9' );", s)
    if s != o:
        path.write_text(s, encoding='utf-8')

left = []
for path in DST.rglob('*'):
    if path.suffix in ('.php', '.css', '.js') and path.is_file():
        for i, line in enumerate(path.read_text(encoding='utf-8').splitlines(), 1):
            if re.search(r'Palestra|palestra_mantos|Itália|italian', line) and '@package' not in line:
                left.append(f'{path.relative_to(DST)}:{i}: {line.strip()[:120]}')
print('Tema gerado em', DST)
print('Sobras da Palestra:', len(left))
for l in left[:30]:
    print('  ', l)
