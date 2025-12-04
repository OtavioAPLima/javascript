CREATE DATABASE teste;
USE teste;

CREATE TABLE Login(
    Login_ID int NOT NULL AUTO_INCREMENT,
    Usuario varchar(50),
    Senha varchar(50),
    Email varchar(100),
    PRIMARY KEY (Login_ID)
);

INSERT INTO Login (Usuario, Senha, Email) VALUES
('pessoa1', 'senha1', 'email@email.com');    