<?php
// Habilitar exibição de erros temporariamente para debug
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Verificar sessão ANTES de qualquer output
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(["erro" => "Sessão expirada ou usuário não autenticado.", "redirect" => "/html/index.html"]);
    exit;
}

$servername = "127.0.0.1";
$username = "root";
$password = "SenhaForte";
$dbname = "teste";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["erro" => "Erro de conexão: " . $conn->connect_error]);
    exit;
}

if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(["erro" => "Ação não especificada."]);
    exit;
}

function jsProtection($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}


$action = $_POST['action'];
$produto_ID = isset($_POST['produto_ID']) ? $_POST['produto_ID'] : null;
$nomeProduto = isset($_POST['nomeProduto']) ? $_POST['nomeProduto'] : null;
$quantidadeProduto = isset($_POST['quantidadeProduto']) ? $_POST['quantidadeProduto'] : null;       
$categoriaProduto = isset($_POST['categoriaProduto']) ? $_POST['categoriaProduto'] : null;
$precoProduto = isset($_POST['precoProduto']) ? $_POST['precoProduto'] : null;





// Recebendo valor de ação e executando pesquisa, cadastro ou alteração
switch($action) {
    case 'exibirTodos':
        $stmt = $conn->prepare("SELECT * FROM produtos");
        $stmt->execute();
        $result = $stmt->get_result();
        
        header('Content-Type: application/json');
        $produtos = [];
        while($row = $result->fetch_assoc()) {
            $produtos[] = [
                'produto_ID' => $row['produto_ID'],
                'nomeProduto' => jsProtection($row['nomeProduto']),
                'categoriaProduto' => jsProtection($row['categoriaProduto']),
                'precoProduto' => $row['precoProduto'],
                'quantidadeProduto' => $row['quantidadeProduto']
            ];
        }
        echo json_encode($produtos);
        
        $stmt->close();
        break;
    case 'pesquisar':
        if (empty($nomeProduto) && empty($categoriaProduto) && empty($produto_ID) && empty($precoProduto) && empty($quantidadeProduto)) {
            header('Content-Type: application/json');
            echo json_encode(["erroPesquisa" => "Nenhum critério de pesquisa fornecido."]);
            exit;
        } else { 
            $stmt = $conn->prepare("SELECT * FROM produtos WHERE nomeProduto=? OR categoriaProduto=? OR produto_ID=? OR precoProduto=? OR quantidadeProduto=?");
            $stmt->bind_param("ssidd", $nomeProduto, $categoriaProduto, $produto_ID, $precoProduto, $quantidadeProduto);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $produtos = [];
            while($row = $result->fetch_assoc()) {
                $produtos[] = [
                'produto_ID' => $row['produto_ID'],
                'nomeProduto' => jsProtection($row['nomeProduto']),
                'categoriaProduto' => jsProtection($row['categoriaProduto']),
                'precoProduto' => $row['precoProduto'],
                'quantidadeProduto' => $row['quantidadeProduto']
                ];             
            }           

            header('Content-Type: application/json');
            echo json_encode($produtos);
            
            $stmt->close();
        }
        break;
    case 'cadastrar':
        if (!is_numeric($quantidadeProduto) || $quantidadeProduto < 0) {
        echo json_encode(["errorCadastro" => "Quantidade inválida."]);
        exit;
        }
        
        if (!is_numeric($precoProduto) || $precoProduto < 0) {
            echo json_encode(["errorCadastro" => "Preço inválido."]);
            exit;
        }
        if (empty($nomeProduto) || empty($quantidadeProduto) || empty($categoriaProduto) || empty($precoProduto)) {
            header('Content-Type: application/json');
            echo json_encode(["errorCadastro" => "Todos os campos devem ser preenchidos para cadastrar um produto."]);
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO produtos (nomeProduto, quantidadeProduto, categoriaProduto, precoProduto) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sisd", $nomeProduto, $quantidadeProduto, $categoriaProduto, $precoProduto);
            if ($stmt->execute()) {
                header("Content-Type: application/json");
                echo json_encode(["sucessoCadastro" => "Produto cadastrado com sucesso."]);
                $stmt->close();


            } else {
                header("Content-Type: application/json");
                echo json_encode(["errorCadastro" => "Erro ao cadastrar produto: " . $stmt->error]);
                $stmt->close();
                exit;
            }
        }
        break;
    case 'alterar':
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE produto_ID=?");
        $stmt->bind_param("i", $produto_ID);
        $stmt->execute();
        $result = $stmt->get_result();

        if (empty($produto_ID) || empty($nomeProduto) || empty($quantidadeProduto) || empty($categoriaProduto) || empty($precoProduto)) {
            header('Content-Type: application/json');
            echo json_encode(["errorAlterar" => "Todos os campos devem ser preenchidos para alterar um produto."]);
            exit;
        } else {
            if ($result->num_rows == 0) {
                header("Content-Type: application/json");
                echo json_encode(["errorAlterar" => "Produto com ID $Produto_ID não encontrado."]);
                $stmt->close();
                exit;
            }
            $stmt->close();

            $stmt = $conn->prepare("UPDATE produtos SET nomeProduto=?, quantidadeProduto=?, categoriaProduto=?, precoProduto=? WHERE produto_ID=?");
            $stmt->bind_param("sisdi", $nomeProduto, $quantidadeProduto, $categoriaProduto, $precoProduto, $produto_ID);
            if ($stmt->execute()) {
                header("Content-Type: application/json");
                echo json_encode(["sucessoAlterar" => "Produto alterado com sucesso."]);
                $stmt->close();
            } else {
                header("Content-Type: application/json");
                echo json_encode(["errorAlterar" => "Erro ao alterar produto: " . $stmt->error]);
                $stmt->close();
                exit;
                }
            }
        break;
    case 'excluir':
        if (empty($produto_ID)) {
            header("Content-Type: application/json");
            echo json_encode(["errorExcluir" => "ID do produto não informado."]);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT * FROM produtos WHERE produto_ID=?");
        $stmt->bind_param("i", $produto_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            header("Content-Type: application/json");
            echo json_encode(["errorExcluir" => "Produto com ID $produto_ID não encontrado."]);
            $stmt->close();
            exit;
        }
        
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM produtos WHERE produto_ID=?");
        $stmt->bind_param("i", $produto_ID);
        
        if ($stmt->execute()) {
            header("Content-Type: application/json");
            echo json_encode(["sucessoExcluir" => "Produto excluído com sucesso."]);
            $stmt->close();
        } else {
            header("Content-Type: application/json");
            echo json_encode(["errorExcluir" => "Erro ao excluir produto."]);
            $stmt->close();
        }
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(["erro" => "Ação inválida."]);
        exit;
        break;
}
$conn->close();
?>