<?php

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Validar token CSRF primeiro
require_once(__DIR__ . '/csrf.php');
if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    header("Location: ../html/register.html?erro=4");
    exit;
}

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
if (!isset($_POST["usuario"]) || !isset($_POST["senha"]) || !isset($_POST["senha2"]) || !isset($_POST["email"]) || empty($_POST["usuario"]) || empty($_POST["senha"]) || empty($_POST["senha2"]) || empty($_POST["email"])) {
    $conn->close();
    header("Location: ../html/register.html?erro=1");
    exit;
}

$usuario = $_POST["usuario"];
$senha = $_POST["senha"];
$senha2 = $_POST["senha2"];
$email = $_POST["email"];

if ($senha !== $senha2) {
    header("Location: ../html/register.html?erro=2");
    exit;
}

$senhaCriptografada = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

 // Verificar se o usuário já existe
$stmt = $conn->prepare("SELECT * FROM login WHERE usuario=?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/register.html?erro=3");
    exit;
}

// Inserir novo usuário
$defaultAvatar = "../imagens/default.png";
$stmt = $conn->prepare("INSERT INTO login (usuario, senha, email, avatar) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $usuario, $senhaCriptografada, $email, $defaultAvatar);

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

$stmt->close();
?>