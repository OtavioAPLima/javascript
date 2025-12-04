<?php

$servername = "127.0.0.1";
$username = "root";
$password = "SenhaForte";
$dbname = "teste";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_POST["Usuario"]) || !isset($_POST["Senha"]) || !isset($_POST["Senha2"]) || !isset($_POST["Email"]) || empty($_POST["Usuario"]) || empty($_POST["Senha"]) || empty($_POST["Senha2"]) || empty($_POST["Email"])) {
    die("Dados não enviados.");
}

$Usuario = $_POST["Usuario"];
$Senha = $_POST["Senha"];
$Senha2 = $_POST["Senha2"];
$Email = $_POST["Email"];

if ($Senha !== $Senha2) {
    $erro = "As senhas não coincidem.";
    die;
}

 // Verificar se o usuário já existe
$smtm = $conn->prepare("SELECT * FROM Login WHERE Usuario=?");
$smtm->bind_param("s", $Usuario);
$smtm->execute();
$result = $smtm->get_result();

if ($result->num_rows > 0) {
    $erro = "Usuário já existe.";
    die;
}

$smtm->close();

// Inserir novo usuário
$smtm = $conn->prepare("INSERT INTO Login (Usuario, Senha, Email) VALUES (?, ?, ?)");
$smtm->bind_param("sss", $Usuario, $Senha, $Email);

if ($smtm->execute()) {
    $smtm->close();
    $conn->close();
    header("Location: index.html");
    exit;
} else {
    $smtm->close();
    $conn->close();
    header("Location: register.html");
    exit;
}
?>