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
if (!isset($_POST["Email"]) || empty($_POST["Email"])) {
    $conn->close();
    header("Location: ../html/recuperarSenha.html");
    exit;
}

$Email = $_POST["Email"];

$stmt = $conn ->prepare("SELECT U")
?>