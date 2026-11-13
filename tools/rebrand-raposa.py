#!/usr/bin/env python3
"""
Gera o tema "Raposa Mantos" (azul e branco) a partir do tema "Palestra Mantos".

Uso:  python3 tools/rebrand-raposa.py <pasta-com-as-imagens-da-marca>

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
DST = ROOT / 'wp-theme' / 'raposa-mantos'
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
    ('#06321d', '#011544'), ('#0a4a2b', '#012066'), ('#0e5c36', '#002c86'),
    ('#127a45', '#0038a8'), ('#19a058', '#1f5fd6'), ('#e4f3ea', '#e6eefc'),
    ('#3ddc84', '#6fa8ff'), ('#b9f0cf', '#cfe0ff'), ('#e3f3e8', '#e8effc'),
    ('#f5f8f6', '#f5f7fb'), ('#f6fbf8', '#f6f9fe'), ('#d9ffe8', '#dde9ff'),
    ('#e9eeeb', '#e9edf3'), ('#e6ebe8', '#e6eaf2'), ('#e3e8e5', '#e3e7ef'),
    ('#d9dedb', '#d9dee8'), ('#c9d2cd', '#c9d0dd'), ('#f5f7f5', '#f5f7fa'),
    ('#111a15', '#0f1626'), ('#34403a', '#36405a'), ('#6b7a72', '#6b7590'),
    ('#080b09', '#021034'),
    ('rgba(25,160,88', 'rgba(31,95,214'), ('rgba(61,220,132', 'rgba(111,168,255'),
    ('rgba(6,50,29', 'rgba(1,21,68'), ('rgba(6, 50, 29', 'rgba(1, 21, 68'),
    ('rgba(6, 30, 18', 'rgba(1, 14, 44'), ('rgba(120,140,130', 'rgba(120,130,150'),
]

# ---------------------------------------------------------------- textos
TEXTS = [
    ('Palestra<small>Mantos', 'Raposa<small>Mantos'),
    ('palestraa.com.br', 'raposamantos.com.br'),
    ('De torcedor Palestrino para torcedor Palestrino 💚🤍', 'De cruzeirense para cruzeirense 💙🤍'),
    ('manto do Palestra', 'manto celeste'),
    ('verde e branco', 'azul e branco'),
    ('Palestra Mantos', 'Raposa Mantos'),
    ('Palestra no peito', 'Raposa no peito'),
    ('Black Friday Palestra', 'Black Friday Raposa'),
    ('Coleção Palestra', 'Coleção Raposa'),
    ('Avanti!', 'Vamos juntos!'),
    ('@palestra_mantos', '@raposamantos'),
    ("'palestra-' . $file", "'raposa-' . $file"),
    ('newsletter-palestra-mantos-', 'newsletter-raposa-mantos-'),
    ('💚', '💙'),
    # contatos (rascunho: trocar pelos reais da loja)
    ("'5511989958223'", "'5531900000000'"),
    ("'(11) 98995-8223'", "'(31) 90000-0000'"),
    ("https://www.instagram.com/palestra_mantos/", "https://www.instagram.com/raposamantos/"),
    ("https://facebook.com/palestramantos", "https://facebook.com/raposamantos"),
    ("'São Paulo — SP'", "'Belo Horizonte — MG'"),
    ("'cnpj'           => '30.105.559/0001-76'", "'cnpj'           => '00.000.000/0001-00'"),
    ("'ig_reviews_count' => 200", "'ig_reviews_count' => 0"),
    ("define( 'PM_FB_DEFAULT_PIXEL', '1686861222371865' );", "define( 'PM_FB_DEFAULT_PIXEL', '' );"),
    # banners e imagens
    ("'slide3_text'    => 'Camisetas oversized 100% algodão, com logo P e escudo histórico.'",
     "'slide3_text'    => 'Camisetas oversized 100% algodão, com o monograma R em azul e branco.'"),
    ("pm_opt_img( 'slide1_image', 'banner-mapas.webp' )", "pm_opt_img( 'slide1_image', 'banner-colecao.webp' )"),
    ("pm_opt_img( 'slide2_image', 'conjunto-branco-masculino.webp' )", "pm_opt_img( 'slide2_image', 'conjunto-azul.webp' )"),
    ("pm_img( 'conjunto-branco-feminino.webp' )", "pm_img( 'conjunto-branco.webp' )"),
    ("Mapas de São Paulo, do Brasil e da América do Sul em verde com os símbolos do Palestra", "Arte azul e branca com o monograma da Raposa Mantos"),
    ("Conjunto moletom branco Palestra", "Conjunto moletom Raposa"),
    ("'PM-KIT-CAMISETAS'", "'RM-KIT-CAMISETAS'"),
    ("'calcas'    => 'calca-verde.webp'", "'calcas'    => 'calca-azul.webp'"),
    ("'camisetas' => 'camiseta-logo-p.webp'", "'camisetas' => 'camiseta-azul.webp'"),
    ("'conjuntos' => 'conjunto-branco-masculino.webp'", "'conjuntos' => 'conjunto-azul.webp'"),
    ("pm_img( 'banner-mapas.webp' )", "pm_img( 'banner-colecao.webp' )"),
    ("'Palestra'", "'Raposa'"),
    ('Palestra', 'Raposa'),
    ('Palestrino', 'Cruzeirense'),
    # textos de boas-vindas
    ("É carregar histórias, memórias e a identidade de quem vive o futebol todos os dias.",
     "É carregar a história, as glórias e o orgulho de quem vive o azul e branco todos os dias."),
]

CATALOG = """function pm_products_catalog() {
	return array(
		array(
			'sku'     => 'RM-CONJUNTO-AZUL',
			'name'    => 'Conjunto Moletom Raposa Azul – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-azul.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger em moletom flanelado azul, com o monograma R em branco.',
			'order'   => 1,
		),
		array(
			'sku'     => 'RM-CONJUNTO-BRANCO',
			'name'    => 'Conjunto Moletom Raposa Branco – Blusão + Calça',
			'cat'     => 'conjuntos',
			'regular' => '267.00',
			'sale'    => '',
			'images'  => array( 'conjunto-branco.webp' ),
			'short'   => 'Blusão com capuz e bolso canguru + calça jogger em moletom flanelado branco, com o monograma R em azul.',
			'order'   => 3,
		),
		array(
			'sku'     => 'RM-CALCA-AZUL',
			'name'    => 'Calça Moletom Raposa Azul – Faixa Branca',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-azul.webp' ),
			'short'   => 'Calça jogger azul em moletom flanelado, com cordão de ajuste, faixa lateral branca e monograma R.',
			'order'   => 2,
		),
		array(
			'sku'     => 'RM-CALCA-BRANCA',
			'name'    => 'Calça Moletom Raposa Branca – Faixa Azul',
			'cat'     => 'calcas',
			'regular' => '147.00',
			'sale'    => '',
			'images'  => array( 'calca-branca.webp' ),
			'short'   => 'Calça jogger branca em moletom flanelado, cós com elástico, faixa lateral azul e monograma R.',
			'order'   => 5,
		),
		array(
			'sku'     => 'RM-CAMISETA-AZUL',
			'name'    => 'Camiseta Oversized Raposa Azul',
			'cat'     => 'camisetas',
			'regular' => '127.00',
			'sale'    => '',
			'images'  => array( 'camiseta-azul.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, azul, com o monograma R em branco no peito.',
			'order'   => 6,
		),
		array(
			'sku'     => 'RM-CAMISETA-BRANCA',
			'name'    => 'Camiseta Oversized Raposa Branca',
			'cat'     => 'camisetas',
			'regular' => '127.00',
			'sale'    => '',
			'images'  => array( 'camiseta-branca.webp' ),
			'short'   => 'Camiseta oversized 100% algodão, branca, com o monograma R em azul.',
			'order'   => 7,
		),
		array(
			'sku'     => 'RM-KIT-CAMISETAS',
			'name'    => 'Kit 2 Camisetas Oversized Raposa',
			'cat'     => 'camisetas',
			'regular' => '187.00',
			'sale'    => '',
			'images'  => array( 'kit-camisetas.webp' ),
			'short'   => 'Kit com 2 camisetas oversized 100% algodão: uma branca e uma azul, com o monograma R.',
			'order'   => 4,
		),
	);
}
"""

RAPOSA_CSS = """

/* ==========================================================================
   Raposa Mantos — azul e branco
   ========================================================================== */
.pm-slide--dark { background: radial-gradient(circle at 78% 40%, rgba(31,95,214,.55), transparent 55%), #021034; }
.pm-hero__flag i:nth-child(1) { background: #fff; }
.pm-hero__flag i:nth-child(2) { background: #6fa8ff; }
.pm-hero__flag i:nth-child(3) { background: #0038a8; }
.pm-slide--dark .pm-hero__flag i:nth-child(3) { background: #fff; }
.pm-topbar { background: #0038a8; }
.pm-footer { background: #011544; }
"""

FEATURED = "array( 'RM-CONJUNTO-AZUL', 'RM-CONJUNTO-BRANCO', 'RM-CALCA-AZUL', 'RM-KIT-CAMISETAS' )"

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
        # As avaliações do Instagram são de clientes da Palestra: a Raposa começa sem nenhuma.
        s = re.sub(r"function pm_instagram_reviews_data\(\) \{\n\treturn array\(.*?\n\t\);\n\}",
                   "function pm_instagram_reviews_data() {\n\treturn array();\n}", s, count=1, flags=re.S)
    if path.name == 'style.css':
        s += RAPOSA_CSS
        # Faixa de 3 cores: vermelho, branco e preto.
        s = re.sub(r'Theme Name:.*', 'Theme Name: Raposa Mantos', s, count=1)
        s = re.sub(r'Version:.*', 'Version: 1.0.0', s, count=1)
        s = re.sub(r'Description:.*', 'Description: Loja de moletons, calças e camisetas azul e branco. Baseado no tema Palestra Mantos.', s, count=1)
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
