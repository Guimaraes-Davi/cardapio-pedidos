# Cardápio & Pedidos

Sistema de cardápio e pedidos de delivery para um restaurante: o cliente monta o carrinho e finaliza o pedido, a operação acompanha a fila e avança o status de cada pedido, e o dono vê o faturamento do dia, do mês e os produtos mais vendidos.

## Demonstração online

**Link:** <!-- cole aqui a URL do Render, ex.: https://cardapio-pedidos.onrender.com -->

Publicado no plano gratuito do Render com Docker. O que esperar:

- **Demora para abrir na primeira vez:** o plano gratuito dorme depois de 15 minutos sem acesso, e o primeiro acesso depois disso leva cerca de 1 minuto para acordar.
- **Os dados voltam ao exemplo:** o banco é SQLite dentro do container. A cada reinício, o [`docker/start.sh`](docker/start.sh) roda `migrate:fresh --seed`, então pedidos criados na demonstração somem e o banco volta aos dados de exemplo.
- **Servidor simples:** `php artisan serve` atende uma requisição por vez, o que basta para uma demonstração. Em produção de verdade seria PHP-FPM com Nginx (ou FrankenPHP), e MySQL ou PostgreSQL num serviço gerenciado.
- **Dependências de desenvolvimento na imagem:** o `composer install` do [`Dockerfile`](Dockerfile) instala também as dependências de dev, porque os seeders usam o Faker para gerar clientes e pedidos. É uma troca consciente para a demo: em produção seria `--no-dev`, e os dados viriam de outra fonte.

Como a imagem é montada:

- **Estágio `front` (`node:22`):** `npm ci` e `npm run build` geram o `public/build` com o Vue e o Tailwind compilados.
- **Estágio final (`php:8.3-cli` com o Composer de `composer:2`):** instala as dependências PHP e copia só o `public/build` do primeiro estágio. Node e `node_modules` ficam fora da imagem final.
- **`bootstrap/app.php`:** `trustProxies(at: '*')` faz o Laravel aceitar os cabeçalhos `X-Forwarded-*` do proxy do Render, que recebe o HTTPS e repassa HTTP ao container. Sem isso, os assets sairiam com `http://` numa página `https://`, e o navegador os bloquearia (mixed content).

## Stack

- **Backend:** PHP 8.3, Laravel 13, API REST com Form Requests, API Resources e Eloquent
- **Banco:** SQLite para desenvolvimento; as migrations usam só tipos portáveis e funcionam igual no MySQL
- **Frontend:** Vue 3 (Composition API com `<script setup>`), vue-router e Tailwind CSS, como SPA servida pelo próprio Laravel e compilada pelo Vite
- **Testes:** PHPUnit (feature tests da API e unit test das regras de status)

## Como rodar

Pré-requisitos: PHP 8.2+, com as extensões `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo` e `curl`; Composer; Node 20+.

```bash
git clone <url-do-repositorio> cardapio-pedidos
cd cardapio-pedidos

composer install
npm install

cp .env.example .env            # no Windows (cmd): copy .env.example .env
php artisan key:generate

php -r "touch('database/database.sqlite');"
php artisan migrate --seed      # 3 categorias, 15 produtos, 20 clientes, 60 pedidos dos últimos 30 dias
```

Em dois terminais:

```bash
npm run dev
```

```bash
php artisan serve
```

Acesse http://localhost:8000.

Para rodar os testes:

```bash
php artisan test
```

## Telas

| Tela | O que faz |
|---|---|
| **Cardápio** (`/`) | Produtos agrupados por categoria, carrinho com quantidades e total dos itens, formulário do cliente e finalização do pedido |
| **Pedidos** (`/pedidos`) | Lista paginada com filtro por status, botões para avançar para o próximo status ou cancelar |
| **Resumo** (`/resumo`) | Faturamento do dia e do mês, pedidos entregues, ticket médio e top 5 produtos do mês |

## API

Todas as respostas usam o envelope `{ "data": ... }` dos API Resources. Valores em dinheiro vêm em centavos.

| Método | Rota | Descrição |
|---|---|---|
| `GET/POST` | `/api/categorias` | Lista as categorias com os produtos ativos / cria categoria |
| `GET/PUT/PATCH/DELETE` | `/api/categorias/{id}` | CRUD de categoria |
| `GET/POST` | `/api/produtos` | Lista / cria produto |
| `GET/PUT/PATCH/DELETE` | `/api/produtos/{id}` | CRUD de produto (produto já vendido não pode ser excluído: responde 409) |
| `GET` | `/api/pedidos?status=&data_inicio=&data_fim=&per_page=` | Pedidos paginados com cliente e itens |
| `POST` | `/api/pedidos` | Cria o pedido e responde **201** |
| `PATCH` | `/api/pedidos/{id}/status` | Muda o status (transição inválida responde **422**) |
| `GET` | `/api/relatorios/resumo` | Faturamento, pedidos, ticket médio e top 5 produtos |

Exemplo de criação de pedido:

```json
POST /api/pedidos
{
  "cliente": { "nome": "Maria Souza", "telefone": "11988887777" },
  "observacao": "Sem cebola",
  "itens": [
    { "produto_id": 1, "quantidade": 2 },
    { "produto_id": 11, "quantidade": 3 }
  ]
}
```

## Decisões técnicas

- **Dinheiro em centavos (inteiro).** `R$ 24,90` é gravado como `2490`. Isso elimina erros de ponto flutuante (`0.1 + 0.2 !== 0.3`) em somas e médias. A formatação para R$ acontece só na exibição, com `Intl.NumberFormat` no front.
- **Total calculado no servidor.** O front envia apenas `produto_id` e `quantidade`. Em [`CriarPedido`](app/Actions/CriarPedido.php), o preço vem do banco e o subtotal, a taxa de entrega e o total são calculados ali. Qualquer preço ou total enviado pelo cliente é ignorado, e há um teste que garante isso.
- **Preço congelado no item.** `pedido_itens.preco_unitario_centavos` guarda o preço do momento da compra, assim um reajuste no cardápio não altera pedidos antigos nem relatórios.
- **Transação na criação do pedido.** Cliente, pedido e itens são gravados dentro de `DB::transaction`. Se qualquer passo falhar, nada fica gravado: nunca existe pedido sem itens.
- **Eager loading contra N+1.** `GET /api/pedidos` usa `with(['cliente', 'itens.produto'])`. Para 15 pedidos, a listagem caiu de 71 queries (uma por cliente, por lista de itens e por produto) para 4 (uma por tabela, com `WHERE id IN (...)`).
- **Índices em `pedidos.status` e `pedidos.created_at`.** São as colunas dos filtros da fila de pedidos e das consultas do relatório (`status = 'entregue' AND created_at BETWEEN ...`). Por isso o relatório usa `whereBetween` em vez de `whereDate`: aplicar uma função sobre a coluna impede o uso do índice.
- **Status como backed enum do PHP.** [`StatusPedido`](app/Enums/StatusPedido.php) concentra os valores válidos e a máquina de estados (`proximo()`, `podeMudarPara()`). No banco, o status é uma `string`, e não o tipo `ENUM` do MySQL: assim funciona no SQLite e um status novo não exige `ALTER TABLE`. O cast no model converte a coluna em enum automaticamente.
- **Transições de status validadas no Form Request.** O hook `after()` de [`AtualizarStatusPedidoRequest`](app/Http/Requests/AtualizarStatusPedidoRequest.php) só aceita o próximo status da sequência (recebido → em preparo → saiu para entrega → entregue) ou cancelado, e responde 422 com mensagem clara para qualquer outra transição.
- **Faturamento conta só pedidos entregues.** Cancelados nunca geraram receita, e os pedidos em andamento ainda podem ser cancelados.
- **Relatório com query builder.** JOIN entre `pedido_itens`, `pedidos` e `produtos` com `GROUP BY` e agregações no banco, em vez de carregar tudo em memória.

## Testes

```
Atualizar Status Pedido   avançar, cancelar, pular etapa (422), voltar (422), finalizado (422), status inexistente (422)
Criar Pedido              subtotal e total com preço do banco, preço congelado, sem itens (422), produto inexistente (422), produto inativo (422)
Relatorio Resumo          números exatos para um conjunto conhecido (só entregues, dia x mês x mês passado), cenário sem vendas
Spa                       rotas do front entregam a SPA; rota de API inexistente responde 404
Status Pedido (unit)      as 25 combinações de transição de status
```

## Uso de IA no desenvolvimento

O projeto foi desenvolvido com o apoio de um assistente de IA (Claude Code). Cada etapa foi revisada e validada:

- os endpoints foram exercitados manualmente (curl e navegador), inclusive com dados maliciosos, como um total falso enviado pelo cliente;
- o ganho do eager loading foi medido contando as queries;
- os testes foram conferidos introduzindo um bug de propósito na regra de status e confirmando que eles falham.

## Próximos passos

- **Autenticação com Sanctum:** separar o cliente da operação do restaurante e proteger as rotas de status, relatório e CRUD do cardápio.
- **Filas para notificar o cliente:** disparar um job a cada mudança de status (WhatsApp, SMS ou e-mail), sem atrasar a resposta da API.
- **Integração com pagamento:** Pix ou cartão via gateway, com o pedido aguardando a confirmação por webhook antes de ir para a cozinha.
- Endereço de entrega e taxa por distância, controle de estoque com lock pessimista e atualização da fila em tempo real (broadcasting).
