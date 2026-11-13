import sys
from PIL import Image, ImageDraw, ImageFont, ImageFilter
OUT=sys.argv[1]
RED=(200,16,46); DRED=(150,8,28); BLK=(12,12,12); GRY=(34,34,34); WHT=(255,255,255)
FB='/usr/share/fonts/opentype/inter/Inter-Black.otf'
try: ImageFont.truetype(FB,10)
except Exception: FB='/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf'
def font(s): return ImageFont.truetype(FB,s)
S=2  # supersample

def bg(w,h,top=(44,44,44),bot=(16,16,16)):
    im=Image.new('RGB',(w,h),top); d=ImageDraw.Draw(im)
    for y in range(h):
        t=y/h; d.line([(0,y),(w,y)],fill=tuple(int(top[i]+(bot[i]-top[i])*t) for i in range(3)))
    # brilho vermelho atrás da peça
    glow=Image.new('L',(w,h),0); ImageDraw.Draw(glow).ellipse([w*0.1,h*0.12,w*0.9,h*0.78],fill=120)
    glow=glow.filter(ImageFilter.GaussianBlur(w*0.12))
    im.paste(Image.new('RGB',(w,h),(150,10,28)),(0,0),glow)
    return im

def mono(d,cx,cy,size,fill,stroke=None):
    f=font(size); txt='M'
    bb=d.textbbox((0,0),txt,font=f)
    x=cx-(bb[2]-bb[0])/2-bb[0]; y=cy-(bb[3]-bb[1])/2-bb[1]
    if stroke: d.text((x,y),txt,font=f,fill=fill,stroke_width=max(2,size//14),stroke_fill=stroke)
    else: d.text((x,y),txt,font=f,fill=fill)

def stripes(d,x0,y0,x1,y1,n=6,c1=RED,c2=BLK):
    h=(y1-y0)/n
    for i in range(n): d.rectangle([x0,y0+i*h,x1,y0+(i+1)*h],fill=c1 if i%2==0 else c2)

def shadow(im,box):
    sh=Image.new('L',im.size,0); ImageDraw.Draw(sh).ellipse(box,fill=90)
    sh=sh.filter(ImageFilter.GaussianBlur(30)); dark=Image.new('RGB',im.size,(0,0,0))
    im.paste(dark,(0,0),sh)

def hoodie(d,cx,top,w,col,trim,logo=WHT):
    # w = body width
    bw=w; bh=int(w*1.05); x0=cx-bw//2
    sl=int(w*0.28)
    # sleeves
    d.polygon([(x0+10,top+60),(x0-sl,top+bh*0.55),(x0-sl+60,top+bh*0.98),(x0+40,top+bh*0.5)],fill=col)
    d.polygon([(x0+bw-10,top+60),(x0+bw+sl,top+bh*0.55),(x0+bw+sl-60,top+bh*0.98),(x0+bw-40,top+bh*0.5)],fill=col)
    d.rectangle([x0-sl+40,top+bh*0.9,x0-sl+90,top+bh*1.0],fill=trim); d.rectangle([x0+bw+sl-90,top+bh*0.9,x0+bw+sl-40,top+bh*1.0],fill=trim)
    # body
    d.rounded_rectangle([x0,top+30,x0+bw,top+bh],radius=40,fill=col)
    d.rectangle([x0,top+bh-50,x0+bw,top+bh],fill=trim)
    # hood
    d.ellipse([cx-w*0.28,top-w*0.18,cx+w*0.28,top+w*0.22],fill=col)
    d.ellipse([cx-w*0.17,top-w*0.06,cx+w*0.17,top+w*0.17],fill=tuple(max(0,c-35) for c in col))
    # strings
    d.line([(cx-25,top+w*0.17),(cx-30,top+w*0.42)],fill=WHT,width=8); d.line([(cx+25,top+w*0.17),(cx+30,top+w*0.42)],fill=WHT,width=8)
    # pocket
    d.polygon([(cx-w*0.3,top+bh*0.6),(cx+w*0.3,top+bh*0.6),(cx+w*0.36,top+bh*0.86),(cx-w*0.36,top+bh*0.86)],fill=tuple(max(0,c-18) for c in col))
    mono(d,cx+w*0.22,top+bh*0.33,int(w*0.17),logo)
    return top+bh

def pants(d,cx,top,w,col,trim,logo=WHT,stripe=None):
    h=int(w*1.9); x0=cx-w//2
    d.rectangle([x0,top,x0+w,top+60],fill=trim)
    d.polygon([(x0,top+50),(cx-6,top+50),(cx-20,top+h),(x0+20,top+h)],fill=col)
    d.polygon([(cx+6,top+50),(x0+w,top+50),(x0+w-20,top+h),(cx+20,top+h)],fill=col)
    d.rectangle([x0+22,top+h-55,cx-22,top+h],fill=trim); d.rectangle([cx+22,top+h-55,x0+w-22,top+h],fill=trim)
    d.line([(cx-18,top+10),(cx-24,top+150)],fill=WHT,width=7); d.line([(cx+18,top+10),(cx+24,top+150)],fill=WHT,width=7)
    if stripe:
        d.line([(x0+8,top+80),(x0+30,top+h-60)],fill=stripe[0],width=16)
        d.line([(x0+28,top+80),(x0+50,top+h-60)],fill=stripe[1],width=16)
    mono(d,x0+w*0.78,top+170,int(w*0.13),logo)
    return top+h

def tee(d,cx,top,w,col,logo=RED,logo_stroke=None):
    x0=cx-w//2; h=int(w*1.15)
    d.polygon([(x0+w*0.18,top),(x0-w*0.2,top+w*0.25),(x0-w*0.06,top+w*0.48),(x0+w*0.05,top+w*0.38),(x0+w*0.05,top+h),(x0+w*0.95,top+h),(x0+w*0.95,top+w*0.38),(x0+w*1.06,top+w*0.48),(x0+w*1.2,top+w*0.25),(x0+w*0.82,top)],fill=col)
    d.ellipse([cx-w*0.14,top-w*0.07,cx+w*0.14,top+w*0.1],fill=(236,236,236))
    mono(d,cx,top+h*0.4,int(w*0.28),logo,logo_stroke)
    return top+h

def save(im,name,size=None):
    im=im.resize(size or (im.width//S,im.height//S),Image.LANCZOS)
    im.save(f'{OUT}/{name}','WEBP',quality=86,method=6)

W,H=1000*S,1250*S
def card(fn):
    im=bg(W,H); d=ImageDraw.Draw(im); fn(im,d); return im

def conj(col,trim,name,logo=WHT):
    def f(im,d):
        shadow(im,(W*0.25,H*0.9,W*0.75,H*0.98))
        b=hoodie(d,W//2,int(H*0.12),int(W*0.46),col,trim,logo)
        pants(d,W//2,b-40,int(W*0.36),col,trim,logo)
    # pants under hoodie: draw pants first
    def g(im,d):
        shadow(im,(W*0.28,H*0.92,W*0.72,H*0.99))
        pants(d,W//2,int(H*0.43),int(W*0.34),col,trim,logo)
        hoodie(d,W//2,int(H*0.1),int(W*0.46),col,trim,logo)
    save(card(g),name)

conj(BLK,GRY,'conjunto-preto.webp',RED)
conj(RED,DRED,'conjunto-vermelho.webp',BLK)
def calc(col,trim,name,logo,stripe=None):
    def f(im,d):
        shadow(im,(W*0.3,H*0.9,W*0.7,H*0.98))
        pants(d,W//2,int(H*0.08),int(W*0.44),col,trim,logo,stripe)
    save(card(f),name)
calc(BLK,GRY,'calca-preta.webp',RED,(RED,WHT))
calc(RED,DRED,'calca-vermelha.webp',BLK)
def camis(col,name,logo,stroke=None):
    def f(im,d):
        shadow(im,(W*0.25,H*0.8,W*0.75,H*0.9))
        tee(d,W//2,int(H*0.18),int(W*0.6),col,logo,stroke)
    save(card(f),name)
camis(BLK,'camiseta-preta.webp',RED)
camis(RED,'camiseta-vermelha.webp',BLK)
camis(WHT,'camiseta-branca.webp',RED,BLK)
def kit(im,d):
    shadow(im,(W*0.1,H*0.78,W*0.9,H*0.88))
    tee(d,int(W*0.33),int(H*0.25),int(W*0.42),BLK,RED)
    tee(d,int(W*0.67),int(H*0.3),int(W*0.42),RED,BLK)
save(card(kit),'kit-camisetas.webp')

# Banner 1 / boas-vindas: listras rubro-negras com monograma
def poster(w,h,name):
    im=Image.new('RGB',(w*S,h*S),BLK); d=ImageDraw.Draw(im)
    n=9; bw=im.width*1.6/n
    for i in range(n):
        if i%2==0:
            x=i*bw-im.height*0.3
            d.polygon([(x,0),(x+bw,0),(x+bw-im.height*0.4,im.height),(x-im.height*0.4,im.height)],fill=RED)
    d.ellipse([im.width*0.2,im.height*0.5-im.width*0.3,im.width*0.8,im.height*0.5+im.width*0.3],fill=BLK)
    mono(d,im.width/2,im.height/2,int(im.width*0.42),RED,WHT)
    save(im,name)
poster(736,980,'banner-colecao.webp')

# Logo: monograma M vermelho com contorno, fundo transparente
L=Image.new('RGBA',(260*S,360*S),(0,0,0,0)); d=ImageDraw.Draw(L)
mono(d,L.width/2,L.height/2,int(L.width*0.8),RED,BLK)
L.resize((260,360),Image.LANCZOS).save(f'{OUT}/logo-mark.png')
L.resize((260,360),Image.LANCZOS).save(f'{OUT}/logo-mark.webp','WEBP')
# Favicon
N=1024; F=Image.new('RGBA',(N,N),(0,0,0,0)); d=ImageDraw.Draw(F)
d.rounded_rectangle([0,0,N-1,N-1],radius=int(N*0.22),fill=BLK)
d.rectangle([0,int(N*0.82),N,N],fill=RED)
F2=Image.new('RGBA',(N,N),(0,0,0,0)); m=Image.new('L',(N,N),0); ImageDraw.Draw(m).rounded_rectangle([0,0,N-1,N-1],radius=int(N*0.22),fill=255)
F2.paste(F,(0,0),m); d=ImageDraw.Draw(F2)
mono(d,N/2,N*0.46,int(N*0.62),RED)
F2.resize((512,512),Image.LANCZOS).save(f'{OUT}/favicon.png')
F2.resize((180,180),Image.LANCZOS).save(f'{OUT}/apple-touch-icon.png')
F2.resize((64,64),Image.LANCZOS).save(f'{OUT}/favicon.ico',sizes=[(16,16),(32,32),(48,48)])
print('ok')
