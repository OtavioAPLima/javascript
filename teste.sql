CREATE DATABASE teste;
USE teste;

CREATE TABLE Login(
    login_ID int NOT NULL AUTO_INCREMENT,
    usuario varchar(50) NOT NULL UNIQUE,
    senha varchar(255) NOT NULL,
    email varchar(100) NOT NULL UNIQUE,
    PRIMARY KEY (login_ID)
);

CREATE TABLE produtos(
    produto_ID int NOT NULL AUTO_INCREMENT,
    nomeProduto varchar(100) NOT NULL UNIQUE,
    categoriaProduto varchar(50) NOT NULL,
    quantidadeProduto int NOT NULL,
    precoProduto float(5,2) NOT NULL,
    PRIMARY KEY (produto_ID)
);

CREATE TABLE tentativaLogin ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    ip_address VARCHAR(45) NOT NULL, 
    tentativaTempo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip(ip_address),
    INDEX idx_tempo(tentativaTempo)
);

CREATE TABLE recuperacaoSenha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    token VARCHAR(64) NOT NULL,
    expira DATETIME NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expira (expira)
);


INSERT INTO login (usuario, senha, email) VALUES
('pessoa1', 'senha1', 'email@email.com');    

INSERT INTO produtos (nomeProduto, categoriaProduto, quantidadeProduto, precoProduto) VALUES
('ProdutoA', 'AAA', 10, 12.50),
('ProdutoB', 'BBB', 20, 15.00);