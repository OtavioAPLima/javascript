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
    header("Location: /html/index.html?erro=2");
    exit;
}

$Usuario = $_POST["Usuario"];
$Senha = $_POST["Senha"];

// Preparar e query
$stmt = $conn->prepare("SELECT Usuario, Senha FROM Login WHERE Usuario=?");
$stmt->bind_param("s", $Usuario);
$stmt->execute();
$result = $stmt->get_result();

session_start();

// Verifica se usuário existe
if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    
    // Verifica se a senha está correta (comparando com hash no banco)
    if (password_verify($Senha, $row['Senha'])) {
        $_SESSION['Usuario'] = $Usuario;
        $stmt->close();
        $conn->close();
        header("Location: /html/menuUsuario.html");
    } else {
        // Senha incorreta
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