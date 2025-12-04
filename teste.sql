CREATE DATABASE teste;
USE teste;

CREATE TABLE Login(
    Login_ID int NOT NULL AUTO_INCREMENT,
    Usuario varchar(50) NOT NULL,
    Senha varchar(50) NOT NULL,
    Email varchar(100) NOT NULL,
    PRIMARY KEY (Login_ID)
);

CREATE TABLE Produtos(
    Produto_ID int NOT NULL AUTO_INCREMENT,
    NomeProduto varchar(100) NOT NULL,
    CategoriaProduto varchar(50) NOT NULL,
    QuantidadeProduto int NOT NULL,
    PRIMARY KEY (Produto_ID)
);

INSERT INTO Login (Usuario, Senha, Email) VALUES
('pessoa1', 'senha1', 'email@email.com');    

INSERT INTO Produtos (NomeProduto, CategoriaProduto, QuantidadeProduto) VALUES
('ProdutoA', 'AAA', 10),
('ProdutoB', 'BBB', 20);