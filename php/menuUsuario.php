<?php

$servername = "127.0.0.1";
$username = "root";
$password = "SenhaForte";
$dbname = "teste";
$port = 3306;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$action = $_POST['action'];

$Produto_ID = $_POST['Produto_ID'];
$NomeProduto = $_POST['NomeProduto'];
$QuantidadeProduto = $_POST['QuantidadeProduto'];       
$CategoriaProduto = $_POST['CategoriaProduto'];
$PrecoProduto = $_POST['PrecoProduto'];


// Recebendo valor de ação e executando pesquisa, cadastro ou alteração
switch($action) {
    case 'pesquisar':
        if (empty($NomeProduto) && empty($CategoriaProduto)) {
            header('Content-Type: application/json');
            echo json_encode(["erro" => "Nenhum campo foi preenchido."]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM Produtos WHERE NomeProduto=? OR CategoriaProduto=? OR Produto_ID=?");
            $stmt->bind_param("ssi", $NomeProduto, $CategoriaProduto, $Produto_ID);
            $stmt->execute();
            $result = $stmt->get_result();
            
            header('Content-Type: application/json');
            $produtos = [];
            while($row = $result->fetch_assoc()) {
                $produtos[] = $row;
            }
            echo json_encode($produtos);
            
            $stmt->close();
        }
        break;
    case 'cadastrar':
        if (empty($NomeProduto) || empty($QuantidadeProduto) || empty($CategoriaProduto) || empty($PrecoProduto)) {
            die("Todos os campos devem ser preenchidos para cadastrar um produto.");
        } else {
            $stmt = $conn->prepare("INSERT INTO Produtos (NomeProduto, QuantidadeProduto, CategoriaProduto, PrecoProduto) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sisd", $NomeProduto, $QuantidadeProduto, $CategoriaProduto, $PrecoProduto);
            if ($stmt->execute()) {
                header("Content-Type: application/json");
                echo json_encode(["sucessoCadastro" => "Produto cadastrado com sucesso."]);
                $stmt->close();
                $conn->close();


            } else {
                header("Content-Type: application/json");
                echo json_encode(["errorCadastro" => "Erro ao cadastrar produto: " . $stmt->error]);
                $stmt->close();
                $conn->close();
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
            die("Todos os campos devem ser preenchidos para alterar um produto.");
        } else {
            if ($result->num_rows == 0) {
                header("Content-Type: application/json");
                echo json_encode(["errorAlterar" => "Produto com ID $Produto_ID não encontrado."]);
                $stmt->close();
                $conn->close();
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
                $conn->close();
                exit;
            }
        }
    default:
        die("Ação inválida.");  
        break;
}







?>