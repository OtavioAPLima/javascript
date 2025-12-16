<?php
// Verificar sessão ANTES de qualquer output
session_start();
if (!isset($_SESSION['Usuario'])) {
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
$Produto_ID = isset($_POST['Produto_ID']) ? $_POST['Produto_ID'] : null;
$NomeProduto = isset($_POST['NomeProduto']) ? $_POST['NomeProduto'] : null;
$QuantidadeProduto = isset($_POST['QuantidadeProduto']) ? $_POST['QuantidadeProduto'] : null;       
$CategoriaProduto = isset($_POST['CategoriaProduto']) ? $_POST['CategoriaProduto'] : null;
$PrecoProduto = isset($_POST['PrecoProduto']) ? $_POST['PrecoProduto'] : null;





// Recebendo valor de ação e executando pesquisa, cadastro ou alteração
switch($action) {
    case 'exibirTodos':
        $stmt = $conn->prepare("SELECT * FROM Produtos");
        $stmt->execute();
        $result = $stmt->get_result();
        
        header('Content-Type: application/json');
        $produtos = [];
        while($row = $result->fetch_assoc()) {
            $produtos[] = [
                'Produto_ID' => $row['Produto_ID'],
                'NomeProduto' => jsProtection($row['NomeProduto']),
                'CategoriaProduto' => jsProtection($row['CategoriaProduto']),
                'PrecoProduto' => $row['PrecoProduto'],
                'QuantidadeProduto' => $row['QuantidadeProduto']
            ];
        }
        echo json_encode($produtos);
        
        $stmt->close();
        break;
    case 'pesquisar':
        if (empty($NomeProduto) && empty($CategoriaProduto)) {
            header('Content-Type: application/json');
            echo json_encode(["erro" => "Nenhum campo foi preenchido."]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM Produtos WHERE NomeProduto=? OR CategoriaProduto=? OR Produto_ID=?");
            $stmt->bind_param("ssi", $NomeProduto, $CategoriaProduto, $Produto_ID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $produtos = [];
            while($row = $result->fetch_assoc()) {
                $produtos[] = [
                'Produto_ID' => $row['Produto_ID'],
                'NomeProduto' => jsProtection($row['NomeProduto']),
                'CategoriaProduto' => jsProtection($row['CategoriaProduto']),
                'PrecoProduto' => $row['PrecoProduto'],
                'QuantidadeProduto' => $row['QuantidadeProduto']
                ];             
            }           

            header('Content-Type: application/json');
            echo json_encode($produtos);
            
            $stmt->close();
        }
        break;
    case 'cadastrar':
        if (empty($NomeProduto) || empty($QuantidadeProduto) || empty($CategoriaProduto) || empty($PrecoProduto)) {
            header('Content-Type: application/json');
            echo json_encode(["errorCadastro" => "Todos os campos devem ser preenchidos para cadastrar um produto."]);
            exit;
        } else {
            $stmt = $conn->prepare("INSERT INTO Produtos (NomeProduto, QuantidadeProduto, CategoriaProduto, PrecoProduto) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sisd", $NomeProduto, $QuantidadeProduto, $CategoriaProduto, $PrecoProduto);
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
        $stmt = $conn->prepare("SELECT * FROM Produtos WHERE Produto_ID=?");
        $stmt->bind_param("i", $Produto_ID);
        $stmt->execute();
        $result = $stmt->get_result();

        if (empty($Produto_ID) || empty($NomeProduto) || empty($QuantidadeProduto) || empty($CategoriaProduto) || empty($PrecoProduto)) {
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

            $stmt = $conn->prepare("UPDATE Produtos SET NomeProduto=?, QuantidadeProduto=?, CategoriaProduto=?, PrecoProduto=? WHERE Produto_ID=?");
            $stmt->bind_param("sisdi", $NomeProduto, $QuantidadeProduto, $CategoriaProduto, $PrecoProduto, $Produto_ID);
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
        if (empty($Produto_ID)) {
            header("Content-Type: application/json");
            echo json_encode(["errorExcluir" => "ID do produto não informado."]);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT * FROM Produtos WHERE Produto_ID=?");
        $stmt->bind_param("i", $Produto_ID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            header("Content-Type: application/json");
            echo json_encode(["errorExcluir" => "Produto com ID $Produto_ID não encontrado."]);
            $stmt->close();
            exit;
        }
        
        $stmt->close();
        $stmt = $conn->prepare("DELETE FROM Produtos WHERE Produto_ID=?");
        $stmt->bind_param("i", $Produto_ID);
        
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