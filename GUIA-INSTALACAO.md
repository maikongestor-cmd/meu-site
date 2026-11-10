# Palestra Mantos: como instalar o site no WordPress

Arquivo do tema: **`dist/palestra-mantos.zip`**. Para baixar, abra o arquivo no GitHub e clique em *Download raw file*.

## 1. Instalar o WooCommerce (a loja)
1. No painel do WordPress, vá em **Plugins → Adicionar novo**.
2. Busque **WooCommerce**, clique em **Instalar** e depois em **Ativar**.
3. Se o assistente do WooCommerce abrir, você pode pular. O tema já configura o básico.

## 2. Instalar o tema
1. Vá em **Aparência → Temas → Adicionar novo → Enviar tema**.
2. Escolha o arquivo `palestra-mantos.zip`, clique em **Instalar agora** e depois em **Ativar**.

## 3. Criar o conteúdo em um clique
1. Vá em **Aparência → Configurar Palestra Mantos**.
2. Deixe todas as opções marcadas e clique em **Criar conteúdo da loja**.

Isso cria:
- **Páginas:** Início, Quem Somos, Trocas e Devoluções, Guia de Medidas, Política de Privacidade e Contato. Os textos são rascunhos para você revisar.
- **Categorias:** Calças, Camisetas e Conjuntos.
- **5 produtos** com as fotos e os tamanhos **P, M, G e GG**:
  - Calça Moletom Palestra Branca: R$ 147,00
  - Calça Moletom Palestra Verde: R$ 147,00
  - Conjunto Moletom Palestra Branco: R$ 267,00
  - Camiseta Palestra Logo P: R$ 127,00
  - Camiseta Palestra Escudo: R$ 127,00
  - Kit 2 Camisetas Oversized (Logo P + Escudo): R$ 187,00
- **Menus** do topo e do rodapé.
- **Loja em reais**, com frete fixo de R$ 19,90 *(exemplo)* e **frete grátis acima de R$ 249**.
- **Carrinho e checkout no modelo clássico**, que é o mais compatível com Appmax e Mercado Pago.

Pode rodar de novo sem medo: o que já existe não é duplicado. Se você criou o conteúdo antes de ativar o WooCommerce, rode de novo: ele cria as categorias e os produtos e coloca Calças, Camisetas e Conjuntos no menu.

## 4. Seus dados
Em **Aparência → Personalizar → Palestra Mantos** você muda:
- **WhatsApp:** só números, com 55 e DDD. Ex.: `5511987654321`. Hoje está um número de exemplo.
- **Contato:** telefone, e-mail, Instagram, Facebook, TikTok, cidade e CNPJ.
- **Loja:** texto da barra do topo, % de desconto no Pix, valor do frete grátis e número de parcelas.
- **Página inicial:** textos e fotos do banner.

Para trocar o logo por uma imagem, use **Aparência → Personalizar → Identidade do site → Logo**.

## 5. Pagamentos
1. **Mercado Pago:** em Plugins → Adicionar novo, busque *"Mercado Pago payments for WooCommerce"*. Instale, ative e coloque as credenciais em **WooCommerce → Configurações → Pagamentos**.
2. **Appmax:** instale o plugin da Appmax (pelo painel da Appmax ou pelo repositório de plugins) e coloque as credenciais.

**Desconto no Pix:** o tema já aplica os **5%** quando o cliente escolhe Pix no checkout. Funciona com qualquer forma de pagamento que tenha "pix" no nome interno, como o Pix do Mercado Pago e o da Appmax.
> ⚠️ Se você ativar um desconto de Pix **dentro** do plugin do Mercado Pago ou da Appmax, coloque **0** no desconto do tema. Assim o cliente não recebe o desconto duas vezes.

## 6. Frete
O frete fixo de R$ 19,90 é só um exemplo. Para calcular o frete pelos Correios ou por transportadoras, instale o **Melhor Envio** ou o **Frenet**. Depois remova o "Entrega padrão" em **WooCommerce → Configurações → Entrega → Brasil**.

## O que ainda falta você mandar ou revisar
- [x] WhatsApp: (11) 98995-8223
- [ ] E-mail, Instagram e CNPJ reais
- [ ] Textos de Quem Somos, Trocas, Guia de Medidas (medidas reais) e Privacidade
- [ ] Depoimentos reais de clientes. Eles ficam em `front-page.php`, e eu posso trocar para você.
- [ ] Mais fotos de cada produto (frente, costas e detalhes)
