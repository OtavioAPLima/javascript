//Váriaveis de controle para o php
let modoPesquisa = false;
let modoCadastro = false;
let modoAlterar = false;
let modoExcluir = false;


// Excluir produto
function excluir(id) { 
    if (confirm('ID do produto a ser excluído: ' + id)) {
        let loadForm = new FormData();
        loadForm.append('action', 'excluir');
        loadForm.append('Produto_ID', id);
        fetch('/php/menuUsuario.php', {
            method: 'POST',
            body: loadForm
            })
        .then(response => response.json())
        .then(data => {
            console.log(data);
                
            if (data.sucessoExcluir) {
                alert(data.sucessoExcluir);
                exibirTabelas();
            } else if (data.errorExcluir) {
                alert(data.errorExcluir);
            }
        })
            
        }
    else {
            alert('Operação cancelada.');
    }
}



//Função para exibir todas as tabelas ao carregar a página
function exibirTabelas() {
    let loadForm = new FormData();
    loadForm.append('action', 'exibirTodos');
    fetch('/php/menuUsuario.php', {
        method: 'POST',
        body: loadForm
    })
    .then(response => response.json())
    .then(produtos => {
        console.log(produtos); 
        
        // Exibir os produtos na página 
        document.getElementById("resultadosTabela").innerHTML = '';
        
        // Evitar categorias duplicadas
        const categoriasUnicas = new Set();
        
        produtos.forEach(p => {
            document.getElementById("resultadosTabela").innerHTML += `<tr class="alterarTabela" onclick="alterarTabela(this)" data-id="${p.Produto_ID}" data-nome="${p.NomeProduto}" data-categoria="${p.CategoriaProduto}" data-preco="${p.PrecoProduto}" data-quantidade="${p.QuantidadeProduto}"><td>${p.Produto_ID}</td> <td>${p.NomeProduto}</td> <td>${p.CategoriaProduto}</td> <td>R$ ${p.PrecoProduto}</td> <td>${p.QuantidadeProduto}</td></tr>`;
            categoriasUnicas.add(p.CategoriaProduto);
        });
        
        // Limpar e inserir o datalist com categorias únicas
        document.getElementById('categorias').innerHTML = '';
        categoriasUnicas.forEach(categoria => {
            document.getElementById('categorias').innerHTML += `<option value="${categoria}">`;
        });
        
        
        
    })
    .catch(error => console.error('Erro:', error));

}
window.addEventListener('load', exibirTabelas);

//Função para deixar o menu responsivo



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
                exibirTabelas();
                alert(data.sucessoCadastro);
            } else if (data.errorCadastro) {
                alert(data.errorCadastro);
            }
        })
        .catch(error => console.error('Erro:', error));
    }

    //Alterar produto
    if (modoAlterar) {
        document.getElementById("resultadosTabela").innerHTML = '';
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
    // Configurar modo de pesquisa
    modoPesquisa = true;
    modoCadastro = false;
    modoAlterar = false;

    // Recarregar tabela para limpar seleção anterior
    exibirTabelas();

    // Configurar formulário para pesquisa
    document.getElementById('PesquisaForm').reset();
    document.getElementById('botaoForms').value = 'Pesquisar Produtos';
    document.getElementById('PesquisaForm').style.display = 'flex';
    document.getElementById('Produto_ID_Div').style.display = 'inline-flex';
    document.getElementById('Quantidade_Produto_Div').style.display = 'none';
    document.getElementById('botaoForms').style.display = 'block';
    
}


//Abrir formulário de cadastro
function cadastrar() {
    // Configurar modo de cadastro
    modoPesquisa = false;
    modoCadastro = true;
    modoAlterar = false;

    // Recarregar tabela para limpar seleção anterior
    exibirTabelas();

    // Configurar formulário para cadastro
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
    
    //Verificar valores
    console.log('ID:', id, 'Nome:', nome, 'Categoria:', categoria, 'Preço:', preco, 'Quantidade:', quantidade);
    
    // Preencher formulário com os valores
    document.getElementById("resultadosTabela").innerHTML = '';
    document.getElementById("resultadosTabela").innerHTML += `<tr class="alterarTabela"><td>${id}</td> <td>${nome}</td> <td>${categoria}</td> <td>R$ ${preco}</td> <td>${quantidade}</td> <td onclick="excluir(${id})"><p>Excluir</p></td> </tr>`;
    document.getElementById('Produto_ID_Input').value = id;
    document.querySelector('input[name="NomeProduto"]').value = nome;
    document.querySelector('input[name="CategoriaProduto"]').value = categoria;
    document.querySelector('input[name="PrecoProduto"]').value = preco;
    document.getElementById('Quantidade_Produto_Input').value = quantidade;
    
    // Configurar modo de alteração
    modoCadastro = false;
    modoPesquisa = false;
    modoAlterar = true;
    
    // Configurar formulário para alteração
    document.getElementById('botaoForms').value = 'Alterar Produto';
    document.getElementById('PesquisaForm').style.display = 'flex';
    document.getElementById('Produto_ID_Div').style.display = 'inline-flex';
    document.getElementById('Quantidade_Produto_Div').style.display = 'inline-flex';
    document.getElementById('botaoForms').style.display = 'block';
    
    
}


