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
for f in IMG.iterdir():
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
    ('💚', '❤️'),
    # contatos (rascunho: trocar pelos reais da loja)
    ("'5511989958223'", "'5521900000000'"),
    ("'(11) 98995-8223'", "'(21) 90000-0000'"),
    ("https://www.instagram.com/palestra_mantos/", "https://www.instagram.com/mengomantos/"),
    ("https://facebook.com/palestramantos", "https://facebook.com/mengomantos"),
    ("'São Paulo — SP'", "'Rio de Janeiro — RJ'"),
    ("'cnpj'           => '30.105.559/0001-76'", "'cnpj'           => '00.000.000/0001-00'"),
    ("'ig_reviews_count' => 200", "'ig_reviews_count' => 0"),
    ("define( 'PM_FB_DEFAULT_PIXEL', '1686861222371865' );", "define( 'PM_FB_DEFAULT_PIXEL', '' );"),
    # banners e imagens
    ("'slide3_text'    => 'Camisetas oversized 100% algodão, com logo P e escudo histórico.'",
     "'slide3_text'    => 'Camisetas oversized 100% algodão, com o monograma M em vermelho e preto.'"),
    ("pm_opt_img( 'slide1_image', 'banner-mapas.webp' )", "pm_opt_img( 'slide1_image', 'banner-colecao.webp' )"),
    ("pm_opt_img( 'slide2_image', 'conjunto-branco-masculino.webp' )", "pm_opt_img( 'slide2_image', 'conjunto-preto.webp' )"),
    ("pm_img( 'conjunto-branco-feminino.webp' )", "pm_img( 'conjunto-vermelho.webp' )"),
    ("Mapas de São Paulo, do Brasil e da América do Sul em verde com os símbolos do Palestra", "Arte rubro-negra com o monograma da Mengo Mantos"),
    ("Conjunto moletom branco Palestra", "Conjunto moletom Mengo"),
    ("'PM-KIT-CAMISETAS'", "'MM-KIT-CAMISETAS'"),
    ("'calcas'    => 'calca-verde.webp'", "'calcas'    => 'calca-preta.webp'"),
    ("'camisetas' => 'camiseta-logo-p.webp'", "'camisetas' => 'camiseta-preta.webp'"),
    ("'conjuntos' => 'conjunto-branco-masculino.webp'", "'conjuntos' => 'conjunto-vermelho.webp'"),
    ("pm_img( 'banner-mapas.webp' )", "pm_img( 'banner-colecao.webp' )"),
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
			'sku'     => 'MM-CONJUNTO-PRETO',
			'name'    => 'Conjunto Moletom Mengo Preto – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-preto.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger em moletom flanelado preto, com o monograma M em vermelho.',
			'order'   => 1,
		),
		array(
			'sku'     => 'MM-CONJUNTO-VERMELHO',
			'name'    => 'Conjunto Moletom Mengo Vermelho – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-vermelho.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger em moletom flanelado vermelho, com o monograma M em preto.',
			'order'   => 3,
		),
		array(
			'sku'     => 'MM-CALCA-PRETA',
			'name'    => 'Calça Moletom Mengo Preta – Faixa Rubro-Negra',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-preta.webp' ),
			'short'   => 'Calça jogger preta em moletom flanelado, com cordão de ajuste, faixa lateral vermelha e branca e monograma M.',
			'order'   => 2,
		),
		array(
			'sku'     => 'MM-CALCA-VERMELHA',
			'name'    => 'Calça Moletom Mengo Vermelha',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-vermelha.webp' ),
			'short'   => 'Calça jogger vermelha em moletom flanelado, cós com elástico, punhos nas barras e monograma M em preto.',
			'order'   => 5,
		),
		array(
			'sku'     => 'MM-CAMISETA-PRETA',
			'name'    => 'Camiseta Oversized Mengo Preta',
			'cat'     => 'camisetas',
			'regular' => '127.00',
			'sale'    => '',
			'images'  => array( 'camiseta-preta.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, preta, com o monograma M em vermelho no peito.',
			'order'   => 6,
		),
		array(
			'sku'     => 'MM-CAMISETA-BRANCA',
			'name'    => 'Camiseta Oversized Mengo Branca',
			'cat'     => 'camisetas',
			'regular' => '127.00',
			'sale'    => '',
			'images'  => array( 'camiseta-branca.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, branca, com o monograma M em vermelho e preto.',
			'order'   => 7,
		),
		array(
			'sku'     => 'MM-KIT-CAMISETAS',
			'name'    => 'Kit 2 Camisetas Oversized Mengo',
			'cat'     => 'camisetas',
			'regular' => '187.00',
			'sale'    => '',
			'images'  => array( 'kit-camisetas.webp' ),
			'short'   => 'Kit com 2 camisetas oversized 100% algodão: uma preta e uma vermelha, com o monograma M.',
			'order'   => 4,
		),
	);
}
"""

FEATURED = "array( 'MM-CONJUNTO-PRETO', 'MM-CONJUNTO-VERMELHO', 'MM-CALCA-PRETA', 'MM-KIT-CAMISETAS' )"

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
        s = s.replace("$new = array( 'PM-CALCA-BRANCA-FAIXA' );", "$new = array();")
    if path.name == 'setup-content.php':
        # Sem avaliações do Instagram para importar na loja nova.
        s = re.sub(r"\t\t\t<p><label><input type=\"checkbox\" name=\"pm_igreviews\".*?</label></p>\n", '', s)
    if path.name == 'reviews.php':
        # As avaliações do Instagram são de clientes da Palestra: a Mengo começa sem nenhuma.
        s = re.sub(r"function pm_instagram_reviews_data\(\) \{\n\treturn array\(.*?\n\t\);\n\}",
                   "function pm_instagram_reviews_data() {\n\treturn array();\n}", s, count=1, flags=re.S)
    if path.name == 'style.css':
        # Faixa de 3 cores: vermelho, branco e preto.
        s = s.replace('.pm-hero__flag i:nth-child(3) { background: var(--pm-red); }', '.pm-hero__flag i:nth-child(3) { background: #111; }')
        s = re.sub(r'Theme Name:.*', 'Theme Name: Mengo Mantos', s, count=1)
        s = re.sub(r'Version:.*', 'Version: 1.0.0', s, count=1)
        s = re.sub(r'Description:.*', 'Description: Loja de moletons, calças e camisetas rubro-negras. Baseado no tema Palestra Mantos.', s, count=1)
    if path.name == 'functions.php':
        s = re.sub(r"define\( 'PM_VERSION', '[^']*' \);", "define( 'PM_VERSION', '1.0.0' );", s)
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
