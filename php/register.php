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

// Verificar dados
if (!isset($_POST["Usuario"]) || !isset($_POST["Senha"]) || !isset($_POST["Senha2"]) || !isset($_POST["Email"]) || empty($_POST["Usuario"]) || empty($_POST["Senha"]) || empty($_POST["Senha2"]) || empty($_POST["Email"])) {
    header("Location: ../html/register.html?erro=1");
    exit;
}

$Usuario = $_POST["Usuario"];
$Senha = $_POST["Senha"];
$Senha2 = $_POST["Senha2"];
$Email = $_POST["Email"];


if ($Senha !== $Senha2) {
    header("Location: ../html/register.html?erro=2");
    exit;
}

$senhaCriptografada = password_hash($Senha, PASSWORD_BCRYPT, ['cost' => 12]);

 // Verificar se o usuário já existe
$stmt = $conn->prepare("SELECT * FROM Login WHERE Usuario=?");
$stmt->bind_param("s", $Usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/register.html?erro=3");
    exit;
}

$stmt->close();

// Inserir novo usuário
$stmt = $conn->prepare("INSERT INTO Login (Usuario, Senha, Email) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $Usuario, $senhaCriptografada, $Email);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/index.html");
    exit;
} else {
    $stmt->close();
    $conn->close();
    header("Location: ../html/register.html");
    exit;
}
?>