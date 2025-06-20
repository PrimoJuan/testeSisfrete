
# API de Pedidos

## Descrição

API REST criada para o teste técnico da empresa sisfrete.

## Tecnologias

- PHP 7.4
- Laravel 7
- MySQL
- Composer
- Postman (para teste das rotas)

## Tabelas principais

| Tabela           | Campos principais                              |
|------------------|------------------------------------------------|
| clientes         | idcliente, nomecliente, ...                     |
| produtos         | idproduto, descricaoproduto, precoproduto      |
| categorias       | idcategoria, descricaocategoria                  |
| produtoCategoria | idProdutoCategoria, idCategoria, idProduto     |
| pedidos          | idpedido, idcliente, datacadastro               |
| pedidoproduto    | idPedidoProduto, idpedido, idproduto, quantidade, precounitario |
| pagamentos       | idPagamento, idpedido, metodopagamento, valorpago, datapagamento, datacadastro |

## Rotas da API

### Criar pedido - POST `/api/pedidos`

**Corpo (JSON):**
```json
{
  "id_cliente": 1,
  "itens": [
    {"produto": 1, "quantidade": 2},
    {"produto": 2, "quantidade": 1}
  ],
  "metodo_pagamento": 1
}
```
- `metodo_pagamento`:  
  1 = Cartão de Crédito  
  2 = Boleto Bancário  
  3 = Pix

  Obs: Como não criei uma tabela para gerenciar os metódos de pagamento e ser um projeto pequeno, optei por passar esses valores fixo no código.

**Resposta (sucesso 201):**
```json
{
  "status": 201,
  "mensagem": "Pedido criado com sucesso",
  "pedido": {
    "id": 45,
    "cliente_nome": "João Silva",
    "data": "2025-06-20 13:37:40",
    "itens": [
      {
        "produto_id": 1,
        "descricao_produto": "Smartphone XYZ",
        "quantidade": 2,
        "preco_unitario": "1200.00"
      },
      {
        "produto_id": 2,
        "descricao_produto": "Livro Harry Potter",
        "quantidade": 1,
        "preco_unitario": "90.00"
      }
    ],
    "metodo_pagamento": "Cartão de Crédito",
    "valor_total": 2490.00
  }
}
```

---

## Atualizar pedido - PUT `/api/pedidos/{id}`

**Corpo (JSON):**
```json
{
  "id_cliente": 1,
  "itens": [
    {"produto": 1, "quantidade": 3}
  ],
  "metodo_pagamento": 2
}
```

**Resposta (sucesso 200):**
```json
{
  "status": 200,
  "mensagem": "Pedido atualizado com sucesso",
  "pedido": {
    "id": 45,
    "cliente_nome": "João Silva",
    "data": "2025-06-20 13:37:40",
    "itens": [...],
    "metodo_pagamento": "Boleto Bancário",
    "valor_total": 3600.00
  }
}
```

---

## Validações importantes

- `id_cliente` é obrigatório e deve existir no banco  
- `itens` deve ser array não vazio  
- Produtos duplicados no pedido não são permitidos  
- Quantidade dos produtos deve ser maior que zero  
- Produtos devem existir na tabela de produtos  
- `metodo_pagamento` deve ser 1, 2 ou 3  

---

## Estrutura das tabelas relevantes

### Produtos

| Campo            | Tipo     |
|------------------|----------|
| idProduto        | int      |
| descricaoProduto | varchar  |
| precoProduto     | decimal  |

### Categorias

| Campo            | Tipo     |
|------------------|----------|
| idCategoria      | int      |
| descricaoCategoria | varchar |

### ProdutoCategoria 

| Campo             | Tipo  |
|-------------------|-------|
| idProdutoCategoria| int   |
| idCategoria       | int   |
| idProduto         | int   |

---

## Exemplo de chamada GET com filtros

```
GET /api/produtos?categoria=2&preco_min=100&preco_max=2000
```
*Os filtros são opcionais.

---

## Escreva uma consulta que retorne o total de pedidos e a receita de cada cliente no último ano.
```sql
SELECT pedidos.idcliente,
       cli.nomecliente,
       Count(DISTINCT pedidos.idpedido)            AS totalpedidos,
       SUM(itens.precounitario * itens.quantidade) AS total
FROM   pedidos pedidos
       left join pedidoproduto itens
              ON itens.idpedido = pedidos.idpedido
       left join clientes cli
              ON cli.idcliente = pedidos.idcliente
WHERE  pedidos.datacadastro >= Date_sub(Curdate(), interval 1 year)
GROUP  BY pedidos.idcliente;
```

---

## Crie uma consulta que mostre os produtos mais vendidos por categoria
```sql
SELECT cat.idcategoria,
       cat.descricaocategoria,
       prodcat.idproduto,
       prod.descricaoproduto,
       Sum(itens.quantidade) AS total_vendido
FROM   categorias AS cat
       LEFT JOIN produtocategoria AS prodcat
              ON prodcat.idcategoria = cat.idcategoria
       LEFT JOIN produtos AS prod
              ON prod.idproduto = prodcat.idproduto
       LEFT JOIN pedidoproduto AS itens
              ON itens.idproduto = prodcat.idproduto
GROUP  BY cat.idcategoria,
          cat.descricaocategoria,
          prodcat.idproduto,
          prod.descricaoproduto
ORDER  BY total_vendido DESC;
```

---

## Explique como você otimizaria essas consultas para melhorar a performance.

A otimização de consultas depende do contexto do banco e do volume de dados, mas algumas boas práticas que costumo aplicar incluem, uso de índices nas colunas utilizadas em filtros, joins e ordenações. Evitar o uso de funções em colunas filtradas, pois isso pode impedir o uso eficiente de índices. Selecionar apenas os campos necessários na consulta, evitando SELECT * para reduzir o volume de dados. Evitar subconsultas desnecessárias, especialmente quando podem ser substituídas por JOINs mais eficientes. Aplicar paginação nos resultados para lidar com grandes volumes de dados sem sobrecarregar a aplicação.

---

## Observações e Comentários

Utilizei o laravel, por ser um framework no qual eu já estou mais habituado a trabalhar, o script do banco de dados vai estar em um arquivo chamado script.sql na raiz do projeto, para a criação e manutenção das tabelas utilizei o heidi sql. Sobre a função de listagem de produtos, optei por utilizar o eloquent e fazer as validações dos filtros recebidos pelo request, eles são opcionais então a consulta pode ser realizada sem nenhum deles. Sobre a criação e atualização de pedidos, fiz a validação básica dos elementos, em questão de segurança pode ser adicionado outras validações além também de uma camada de autenticação, além de tratamentos para que não seja possível enviar determinadas strings para inserção direta no banco de dados. Um pouco de tudo que tenho de conhecimento procurei aplicar neste pequeno projeto.  
