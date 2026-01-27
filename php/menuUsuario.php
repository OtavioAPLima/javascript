<?php
// Habilitar exibicao de erros temporariamente para debug
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Verificar sessao ANTES de qualquer output
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Verificar sessao do usuario
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

// Verificar conexao
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(["erro" => "Erro de conexão: " . $conn->connect_error]);
    exit;
}

// Verificar se a ação foi especificada
if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode(["erro" => "Ação não especificada."]);
    exit;
}

// Função para proteção contra XSS
function jsProtection($valor) {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

// Recebendo dados do formulário
$action = $_POST['action'];
$produto_ID = isset($_POST['produto_ID']) ? $_POST['produto_ID'] : null;
$nomeProduto = isset($_POST['nomeProduto']) ? $_POST['nomeProduto'] : null;
$quantidadeProduto = isset($_POST['quantidadeProduto']) ? $_POST['quantidadeProduto'] : null;       
$categoriaProduto = isset($_POST['categoriaProduto']) ? $_POST['categoriaProduto'] : null;
$precoProduto = isset($_POST['precoProduto']) ? str_replace(',', '.', trim($_POST['precoProduto'])) : null;
$tema = isset($_POST['tema']) ? $_POST['tema'] : null;
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

$filtroPrecoMaximo = isset($_POST['precoMaximo']) ? str_replace(',', '.', trim($_POST['precoMaximo'])) : null;
$filtroPrecoMinimo = isset($_POST['precoMinimo']) ? str_replace(',', '.', trim($_POST['precoMinimo'])) : null;
$filtroQuantidadeMaior = isset($_POST['quantidadeMaior']) ? $_POST['quantidadeMaior'] : null;
$filtroQuantidadeMenor = isset($_POST['quantidadeMenor']) ? $_POST['quantidadeMenor'] : null;

$filtros = [
    'precoMaximo' => $filtroPrecoMaximo,
    'precoMinimo' => $filtroPrecoMinimo,
    'quantidadeMaior' => $filtroQuantidadeMaior,
    'quantidadeMenor' => $filtroQuantidadeMenor
];

$filtrosSQL = [
    'precoMaximo' => "precoProduto <= ?",
    'precoMinimo' => "precoProduto >= ?",
    'quantidadeMaior' => "quantidadeProduto >= ?",
    'quantidadeMenor' => "quantidadeProduto <= ?",
];
// Recebendo valor de ação e executando pesquisa, cadastro ou alteração
switch($action) {
    // Exibir todos os produtos
    case 'exibirTodos':
        $condicoesSQL = [];
        $valoresSQL = [];

        if (!empty(array_filter($filtros))) {
            foreach (array_filter($filtros) as $chave => $valor) {
                $condicoesSQL[] = $filtrosSQL[$chave];
                $valoresSQL[] = $valor;       
            };
            $whereSQL = " WHERE " . implode(" AND ", $condicoesSQL);
            $query = "SELECT * FROM produtos" . $whereSQL . " LIMIT 5 OFFSET ?";
            $stmt = $conn->prepare($query);
            $tipos = str_repeat("d", count($valoresSQL)) . "i";
            $valoresSQL[] = $offset;
            $stmt->bind_param($tipos, ...$valoresSQL);

            $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM produtos");
            $stmtCount->execute();
            $resultCount = $stmtCount->get_result();
            $totalProdutos = $resultCount->fetch_assoc()['total'];
            
        } else {
            $stmt = $conn->prepare("SELECT * FROM produtos LIMIT 5 OFFSET ?");
            $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
            $stmt->bind_param("i", $offset);

            $stmtCount = $conn->prepare("SELECT COUNT(*) as total FROM produtos");
            $stmtCount->execute();
            $resultCount = $stmtCount->get_result();
            $totalProdutos = $resultCount->fetch_assoc()['total'];
            
        }
        
        $stmtCount->close();
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
        echo json_encode(["produtos" => $produtos, "totalProdutos" => $totalProdutos]);
        
        $stmt->close();
        exit;
    // Pesquisar produtos
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
        exit;
    // Cadastrar produto
    case 'cadastrar':
        if (!is_numeric($quantidadeProduto) || $quantidadeProduto < 0) {
            header('Content-Type: application/json');
            echo json_encode(["errorCadastro" => "Quantidade inválida."]);
            exit;
        }
        
        if (!is_numeric($precoProduto) || $precoProduto < 0) {
            header('Content-Type: application/json');
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
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                header("Content-Type: application/json");
                echo json_encode(["errorCadastro" => "Erro ao cadastrar produto: " . $e->getMessage()]);
                $stmt->close();
                exit;
            }
            if ($stmt->affected_rows > 0) {
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
        exit;
    // Alterar produto
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
            try {
                $stmt->execute();
            } catch (mysqli_sql_exception $e) {
                header("Content-Type: application/json");
                echo json_encode(["errorAlterar" => "Erro ao alterar produto: " . $e->getMessage()]);
                $stmt->close();
                exit;
            }
            if ($stmt->affected_rows >= 0) {
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
        exit;
    // Excluir produto
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
        exit;
    // Obter email do usuario
    case 'emailUsuario':
        $user = $_SESSION['usuario'];
        $stmt = $conn->prepare("SELECT email FROM login WHERE usuario=?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $emailUsuario = jsProtection($row['email']);
            header('Content-Type: application/json');
            echo json_encode(["emailUsuario" => $emailUsuario]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(["errorEmailUsuario" => "Usuário não encontrado."]);
        }
        $stmt->close();
        exit;
    // Alterar tema do usuario
    case 'alterarTema':
        $user = $_SESSION['usuario'];
        if ($tema == 'claro'){
            $stmt = $conn->prepare("UPDATE login SET tema = 0 WHERE usuario=?");
            $stmt->bind_param("s", $user);
            $_SESSION['tema'] = 0;
        } else if ($tema == 'escuro'){
            $stmt = $conn->prepare("UPDATE login SET tema = 1 WHERE usuario=?");
            $stmt->bind_param("s", $user);
            $_SESSION['tema'] = 1;
        } else {
            header("Content-Type: application/json");
            echo json_encode(["errorTema" => "Tema inválido."]);
            exit;
        }
        if ($stmt->execute()) {
            header("Content-Type: application/json");
            echo json_encode(["sucessoTema" => "Tema alterado com sucesso."]);
            $stmt->close();
        } else {
            header("Content-Type: application/json");
            echo json_encode(["errorTema" => "Erro ao alterar tema: " . $stmt->error]);
            $stmt->close();
            exit;
        }
        exit;;
    // Acao invalida
    default:
        header('Content-Type: application/json');
        echo json_encode(["erro" => "Ação inválida."]);
        exit;
}

// Fechar conexao
$conn->close();
?>