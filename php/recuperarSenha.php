<?php
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
    header("Location: ../html/recuperarSenha.html?erro=2");
    exit;
}

// Verificar dados
if (!isset($_POST["email"]) || empty($_POST["email"])) {
    $conn->close();
    header("Location: ../html/recuperarSenha.html?erro=1");
    exit;
}

$email = isset($_POST["email"]) ? $_POST["email"] : '';

$stmt = $conn->prepare("SELECT usuario FROM login WHERE email=?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Gerar token de recuperação
    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    $stmtToken = $conn->prepare("INSERT INTO recuperacaoSenha (email, token, expira) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expira=?");
    $stmtToken->bind_param("sssss", $email, $token, $expira, $token, $expira);
    $stmtToken->execute();
    
    // Enviar e-mail
    require_once 'configSMTP.php';
    
    $linkRecuperacao = "https://ideal-enigma-4j7qqww666xqcppq-8080.app.github.dev/html/resetarSenha.html?token=$token";
    $assunto = "Recuperação de Senha";
    $corpoEmail = "
        <html>
        <body>
            <h2>Recuperação de Senha</h2>
            <p>Você solicitou a recuperação de senha.</p>
            <p>Clique no link abaixo para criar uma nova senha:</p>
            <p><a href='$linkRecuperacao'>Recuperar Senha</a></p>
            <p>Este link expira em 1 hora.</p>
            <p>Se você não solicitou esta recuperação, ignore este e-mail.</p>
        </body>
        </html>
    ";
    
    if (enviarEmail($email, $assunto, $corpoEmail)) {
        echo "E-mail de recuperação enviado com sucesso!";
    } else {
        echo "Erro ao enviar e-mail.";
    }
} else {
    echo "E-mail não encontrado.";
}

$conn->close();
?>