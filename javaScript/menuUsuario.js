//Váriaveis de controle para o php
let modoPesquisa = false;
let modoCadastro = false;
let modoAlterar = false;

//Enviar formulário sem atualizar a página
function naoEnviar(event) {
    event.preventDefault();
    const formulario = new FormData(event.target);
    

    //Pesquisar produto
    if (modoPesquisa) {
        formulario.append('action', 'pesquisar');
        fetch('/php/menuUsuario.php', {
            method: 'POST',
            body: formulario
        })
        .then(response => response.json())
        .then(produtos => {
            console.log(produtos); 
            
            // Exibir os produtos na página 
            document.getElementById("resultadosTabela").innerHTML = '';
            produtos.forEach(p => {
                document.getElementById("resultadosTabela").innerHTML += `<tr class="alterarTabela" onclick="alterarTabela(this)" data-id="${p.Produto_ID}" data-nome="${p.NomeProduto}" data-categoria="${p.CategoriaProduto}" data-preco="${p.PrecoProduto}" data-quantidade="${p.QuantidadeProduto}"><td>${p.Produto_ID}</td> <td>${p.NomeProduto}</td> <td>${p.CategoriaProduto}</td> <td>R$ ${p.PrecoProduto}</td> <td>${p.QuantidadeProduto}</td></tr>`;
            });
            
            
            
        })
        .catch(error => console.error('Erro:', error));
    }


    //Cadastrar produto
    if (modoCadastro) {
        formulario.append('action', 'cadastrar');
        fetch('/php/menuUsuario.php', {
            method: 'POST',
            body: formulario
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            
            if (data.sucessoCadastro) {
                alert(data.sucessoCadastro);
            } else if (data.errorCadastro) {
                alert(data.errorCadastro);
            }
        })
        .catch(error => console.error('Erro:', error));
    }

    //Alterar produto
    if (modoAlterar) {
        formulario.append('action', 'alterar');
        fetch('/php/menuUsuario.php', {
            method: 'POST',
            body: formulario
        })
        .then(response => response.json())
        .then(produtos => {
            console.log(produtos);
            
            if (produtos.sucessoAlterar) {
                alert(produtos.sucessoAlterar);
            } else if (produtos.errorAlterar) {
                alert(produtos.errorAlterar);
            }
        })
        .catch(error => console.error('Erro:', error));

    }
}
    
    

//Esconder e alterar os formulários
document.getElementById('PesquisaForm').style.display = 'none';

//Abrir formulário de pesquisa
function pesquisar() {
    modoPesquisa = true;
    modoCadastro = false;
    modoAlterar = false;

    document.getElementById('botaoForms').value = 'Pesquisar Produtos';
    document.getElementById('resultadosTabela').innerHTML = '';
    document.getElementById('PesquisaForm').reset();
    document.getElementById('PesquisaForm').style.display = 'flex';
    document.getElementById('Produto_ID_Div').style.display = 'inline-flex';
    document.getElementById('Quantidade_Produto_Div').style.display = 'none';
    document.getElementById('botaoForms').style.display = 'block';
    
}


//Abrir formulário de cadastro
function cadastrar() {
    modoPesquisa = false;
    modoCadastro = true;
    modoAlterar = false;

    document.getElementById('botaoForms').value = 'Cadastrar Produtos';
    document.getElementById('PesquisaForm').reset();
    document.getElementById('PesquisaForm').style.display = 'flex';
    document.getElementById('Produto_ID_Div').style.display = 'none';
    document.getElementById('Quantidade_Produto_Div').style.display = 'inline-flex';
    document.getElementById('botaoForms').style.display = 'block';
}

function alterarTabela(linha) {
    // Pegar valores da linha clicada
    const id = linha.dataset.id;
    const nome = linha.dataset.nome;
    const categoria = linha.dataset.categoria;
    const preco = linha.dataset.preco;
    const quantidade = linha.dataset.quantidade;
    
    console.log('ID:', id, 'Nome:', nome, 'Categoria:', categoria, 'Preço:', preco, 'Quantidade:', quantidade);
    
    // Preencher formulário com os valores
    document.getElementById('Produto_ID_Input').value = id;
    document.querySelector('input[name="NomeProduto"]').value = nome;
    document.querySelector('input[name="CategoriaProduto"]').value = categoria;
    document.querySelector('input[name="PrecoProduto"]').value = preco;
    document.getElementById('Quantidade_Produto_Input').value = quantidade;
    
    // Configurar modo de alteração
    modoCadastro = false;
    modoPesquisa = false;
    modoAlterar = true;
    
    document.getElementById('botaoForms').value = 'Alterar Produto';
    document.getElementById('PesquisaForm').style.display = 'flex';
    document.getElementById('Produto_ID_Div').style.display = 'inline-flex';
    document.getElementById('Quantidade_Produto_Div').style.display = 'inline-flex';
    document.getElementById('botaoForms').style.display = 'block';
    
    
}