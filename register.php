<?php

$servername = "localhost";
$username = "root";
$password = "SenhaForte";
$dbname = "teste";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_POST["User"]) || !isset($_POST["Senha"]) || !isset($_POST["Senha2"]) || !isset($_POST["Email"])) {
    die("Dados não enviados.");
}

$User = $_POST["User"];
$Senha = $_POST["Senha"];
$Senha2 = $_POST["Senha2"];
$Email = $_POST["Email"];

if ($Senha !== $Senha2) {
    $erro = "As senhas não coincidem.";
    die;
}

 // Verificar se o usuário já existe
$smtm = $conn->prepare("SELECT * FROM Usuario WHERE User=?");
$smtm->bind_param("s", $User);
$smtm->execute();
$result = $smtm->get_result();

if ($result->num_rows > 0) {
    $erro = "Usuário já existe.";
    die;
}

$smtm->close();

// Inserir novo usuário
$smtm = $conn->prepare("INSERT INTO Usuario (User, Senha, Email) VALUES (?, ?, ?)");
$smtm->bind_param("sss", $User, $Senha, $Email);

if ($smtm->execute()) {
    $CriarUser = "Sucesso";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$smtm->close();
$conn->close();
?>