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
if (!isset($_POST["Usuario"]) || !isset($_POST["Senha"]) || empty($_POST["Usuario"]) || empty($_POST["Senha"])) {
    $conn->close();
    header("Location: index.html");
    exit;
}

$Usuario = $_POST["Usuario"];
$Senha = $_POST["Senha"];

// Preparar e query
$stmt = $conn->prepare("SELECT Usuario, Senha FROM Login WHERE Usuario=? AND Senha=?");
$stmt->bind_param("ss", $Usuario, $Senha);
$stmt->execute();
$result = $stmt->get_result();

//login
if ($result->num_rows == 1) {
    $stmt->close();
    $conn->close();
    header("Location: /html/menuUsuario.html");
} else {
    $stmt->close();
    $conn->close();
    header("Location: /html/index.html?erro=1");
    exit;
}
?>