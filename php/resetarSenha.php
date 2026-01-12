<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = "SenhaForte";
$dbname = "teste";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validar token CSRF
require_once(__DIR__ . '/csrf.php');
if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    $conn->close();
    header("Location: ../html/recuperarSenha.html?status=1");
    exit;
}

// Verificar dados
if (!isset($_POST["token"]) || !isset($_POST["senha"]) || !isset($_POST["senha2"]) || empty($_POST["token"]) || empty($_POST["senha"]) || empty($_POST["senha2"])) {
    $conn->close();
    header("Location: ../html/resetarSenha.html?status=2");
    exit;
}

$token = isset($_POST["token"]) ? $_POST["token"] : '';
$usuario = isset($_POST["usuario"]) ? $_POST["usuario"] : '';
$senha = isset($_POST["senha"]) ? $_POST["senha"] : '';
$senha2 = isset($_POST["senha2"]) ? $_POST["senha2"] : '';
$senhaCriptografada = password_hash($senha, PASSWORD_BCRYPT, ['cost' => 12]);

if ($senha !== $senha2) {
    $conn->close();
    header("Location: ../html/resetarSenha.html?status=3");
    exit;
}
// Validar token de recuperação
$stmt = $conn->prepare("SELECT email, expira FROM recuperacaoSenha WHERE token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/resetarSenha.html?status=4");
    exit;
}

$row = $result->fetch_assoc();
$email = $row['email'];
$expira = $row['expira'];
$currentDateTime = date('Y-m-d H:i:s');
if ($currentDateTime > $expira) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/resetarSenha.html?status=5");
    exit;
}
$stmt->close();

$stmt = $conn->prepare("SELECT email FROM login WHERE usuario=?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/resetarSenha.html?status=6");
    exit;
}
$row = $result->fetch_assoc();
if ($row['email'] !== $email) {
    $stmt->close();
    $conn->close();
    header("Location: ../html/resetarSenha.html?status=6");
    exit;
}
$stmt->close();

// Atualizar senha
$stmtUpdate = $conn->prepare("UPDATE login SET senha=? WHERE email=?");
$stmtUpdate->bind_param("ss", $senhaCriptografada, $email);
$stmtUpdate->execute();
$stmtUpdate->close();
$conn->close();
header("Location: ../html/index.html?status=1");
exit;