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

$ip = $_SERVER['REMOTE_ADDR'];

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Verificar dados
if (!isset($_POST["usuario"]) || !isset($_POST["senha"]) || empty($_POST["usuario"]) || empty($_POST["senha"])) {
    $conn->close();
    header("Location: /html/index.html?erro=2");
    exit;
}

require_once(__DIR__ . '/csrf.php');
if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
    $conn->close();
    header("Location: /html/index.html?erro=4");
    exit;
}

$usuario = $_POST["usuario"];
$senha = $_POST["senha"];

// Timeout mais de 5 tentativas em 15 minutos
$conn->query("DELETE FROM tentativaLogin WHERE tentativaTempo < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");

$stmt = $conn->prepare("SELECT COUNT(*) as tentativas FROM tentativaLogin WHERE ip_address = ? AND tentativaTempo > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
$stmt->bind_param("s", $ip);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if ($row['tentativas'] >= 5) {
    $conn->close();
    header("Location: /html/index.html?erro=3");
    exit;
}

// Preparar e query
$stmt = $conn->prepare("SELECT usuario, senha FROM login WHERE usuario=?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verifica se usuário existe
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    
    // Verifica se a senha está correta (comparando com hash no banco)
    if (password_verify($senha, $row['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario'] = $usuario;
        
        // Buscar avatar do usuário (se existir na tabela)
        $avatar = '../imagens/default.png'; // Caminho padrão
        $stmt_avatar = $conn->prepare("SELECT avatar FROM login WHERE usuario=?");
        $stmt_avatar->bind_param("s", $usuario);
        $stmt_avatar->execute();
        $result_avatar = $stmt_avatar->get_result();
        if ($result_avatar->num_rows > 0) {
            $row_avatar = $result_avatar->fetch_assoc();
            if (!empty($row_avatar['avatar'])) {
                $avatar = $row_avatar['avatar']; // Usar o caminho diretamente
            }
        }
        $_SESSION['avatar'] = $avatar;
        $stmt_avatar->close();
        
        $stmt->close();
        $conn->close();
        
        // Retornar JSON para AJAX ou redirecionar para form tradicional
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'sucesso' => true,
                'usuario' => $usuario,
                'avatar' => $avatar,
                'redirect' => '/html/menuUsuario.html'
            ]);
            exit;
        } else {
            header("Location: /html/menuUsuario.html");
        }
    } else {
        // Senha incorreta
        $stmt_tentativa = $conn->prepare("INSERT INTO tentativaLogin (ip_address) VALUES (?)");
        $stmt_tentativa->bind_param("s", $ip);
        $stmt_tentativa->execute();
        $stmt_tentativa->close();
        $stmt->close();
        $conn->close();
        header("Location: /html/index.html?erro=1");
        exit;
    }
} else {
    // Usuário não existe
    $stmt->close();
    $conn->close();
    header("Location: /html/index.html?erro=1");
    exit;
}
?>