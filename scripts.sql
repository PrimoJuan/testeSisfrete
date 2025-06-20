/*Criação do banco de dados*/
CREATE DATABASE `ecommerce` /*!40100 COLLATE 'utf8mb4_0900_ai_ci' */

/*Rodar o script de cima Primeiro*/
/*Criação da tabela Clientes com Id, Nome, Data de Cadastro e Data de Atualização, tabela sem FK*/
CREATE TABLE `clientes`
  (
     `idcliente`       INT NOT NULL auto_increment,
     `nomecliente`     VARCHAR(255) NOT NULL,
     `datacadastro`    TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
     `dataatualizacao` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY ( `idcliente`)
  ) COLLATE='utf8mb4_unicode_ci'; 

/* Criação da tabela Pedidos com Id, Id Cliente FK da tabela clientes, Data de Criação e Data de Atualização, Indice para a PK (idPedido) e específico em IdCliente (FK tabela Clientes)*/
CREATE TABLE `pedidos`
  (
     `idpedido`        INT NOT NULL auto_increment,
     `idcliente`       INT NOT NULL,
     `datacadastro`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `dataatualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`idpedido`),
     INDEX `idcliente` (`idcliente`),
     CONSTRAINT `fk__clientes` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (
     `idcliente`) ON UPDATE no action ON DELETE no action
  ) COLLATE='utf8mb4_0900_ai_ci';

/*Criação da tabela Produtos, com id, Descrição do Produto, Preço, Data de Cadastro e Data de Atualização, Indice pela PK, não possui FK*/
CREATE TABLE `produtos`
  (
     `idproduto`        INT NOT NULL auto_increment,
     `descricaoproduto` VARCHAR(255) NOT NULL DEFAULT '',
     `precoproduto`     DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
     `datacadastro`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `dataatualizacao`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`idproduto`)
  ) COLLATE='utf8mb4_unicode_ci';

/*Criação da Tabela Categorias, com id, Descrição, Data de Cadastro e Data de atualização, Indice pela PK, não possui FK*/
CREATE TABLE `categorias`
  (
     `idcategoria`        INT NOT NULL auto_increment,
     `descricaocategoria` VARCHAR(255) NOT NULL DEFAULT '',
     `datacadastro`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `dataatualizacao`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`idcategoria`)
  ) COLLATE='utf8mb4_unicode_ci';

/*Tabela Pivô para categoria de produtos, como um produto pode ter várias categorias, e uma categoria vários produtos, criei uma tabela para gerenciar esse relacionamento, onde possui um Id próprio, o id do produto e o id da categoria, ambos FK, indices tanto no ID unico da tabela, como no IdProduto e IdCategoria, além da restrição para que um produto e uma categoria não sejam cadastrados mais de uma vez garantindo a integridade no banco de dados e evitando duplicatas*/
CREATE TABLE `produtocategoria`
  (
     `idprodutocategoria` INT NOT NULL auto_increment,
     `idproduto`          INT NOT NULL,
     `idcategoria`        INT NOT NULL,
     PRIMARY KEY (`idprodutocategoria`),
     UNIQUE KEY `unq_produtocategoria` (`idproduto`, `idcategoria`),
     INDEX `idproduto` (`idproduto`),
     INDEX `idcategoria` (`idcategoria`),
     CONSTRAINT `fk__produtos` FOREIGN KEY (`idproduto`) REFERENCES `produtos` (
     `idproduto`) ON UPDATE no action ON DELETE no action,
     CONSTRAINT `fk__categorias` FOREIGN KEY (`idcategoria`) REFERENCES
     `categorias` (`idcategoria`) ON UPDATE no action ON DELETE no action
  ) COLLATE='utf8mb4_0900_ai_ci';

/*Tabela pivô para os produtos de um pedido, pois um pedido pode conter vários produtos e um produto pode estar em vários pedidos. Criei esta tabela para gerenciar esse relacionamento, que possui o id, o id do pedido e o id do produto, ambos como chaves estrangeiras (FK). Existem índices no ID único da tabela, assim como nos campos idPedido e idProduto, além de uma restrição única para garantir que um produto não seja cadastrado mais de uma vez no mesmo pedido, assegurando a integridade dos dados e evitando registros duplicados no banco.*/
CREATE TABLE `pedidoproduto`
  (
     `idpedidoproduto` INT NOT NULL auto_increment,
     `idpedido`        INT NOT NULL,
     `idproduto`       INT NOT NULL,
     `quantidade`      INT NOT NULL DEFAULT 1,
     `precounitario`   DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
     PRIMARY KEY (`idpedidoproduto`),
     UNIQUE KEY `unq_pedidoproduto` (`idpedido`, `idproduto`),
     FOREIGN KEY (`idpedido`) REFERENCES `pedidos`(`idpedido`) ON DELETE CASCADE
     ON UPDATE CASCADE,
     FOREIGN KEY (`idproduto`) REFERENCES `produtos`(`idproduto`) ON DELETE
     CASCADE ON UPDATE CASCADE
  ) COLLATE='utf8mb4_unicode_ci';

/*Tabela para registrar os pagamentos realizados em cada pedido. Cada registro possui um id próprio, o id do pedido como chave estrangeira (FK), o método de pagamento utilizado, o valor pago, a data do pagamento e a data de cadastro do registro. Há índices no id único da tabela e na coluna idPedido para otimizar consultas que relacionam pagamentos aos pedidos, garantindo integridade e performance nas operações do banco de dados.*/
CREATE TABLE `pagamentos`
  (
     `idpagamento`     INT NOT NULL auto_increment,
     `idpedido`        INT NOT NULL,
     `metodopagamento` VARCHAR(100) NOT NULL DEFAULT '',
     `valorpago`       DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
     `datapagamento`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `datacadastro`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`idpagamento`),
     INDEX `idx_pagamentos_idpedido` (`idpedido`),
     FOREIGN KEY (`idpedido`) REFERENCES `pedidos`(`idpedido`) ON UPDATE no
     action ON DELETE no action
  ) COLLATE='utf8mb4_unicode_ci'; 