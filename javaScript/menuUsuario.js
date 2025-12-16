// Váriaveis de controle para o php
let modoPesquisa = false;
let modoCadastro = false;
let modoAlterar = false;
let modoExcluir = false;

// Set para categorias únicas
const categoriasUnicas = new Set();

// Limpar tabela
const tabela = document.getElementById("resultadosTabela");
tabela.innerHTML = '';

// Logout do usuário
function logout() {
    fetch('/php/logout.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        console.log(data);
        if (data.sucessoLogout) {
            alert(data.sucessoLogout);
            window.location.href = '/html/index.html'; // Redirecionar para a página de login
        } else if (data.errorLogout) {
            alert(data.errorLogout);
        }
    })
    .catch(error => console.error('Erro:', error));
}

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



// Função para exibir todas as tabelas ao carregar a página
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
        
        // Verificar se há erro de autenticação
        if (produtos.erro && produtos.redirect) {
            alert(produtos.erro);
            window.location.href = produtos.redirect;
            return;
        }
        
        // Limpar tabela e preparar para exibir
        const tabela = document.getElementById("resultadosTabela");
        tabela.innerHTML = '';
        
        // Evitar categorias duplicadas
        const categoriasUnicas = new Set();
        
        // Exibir os produtos na página 
        produtos.forEach(p => {
            const tr = document.createElement('tr');

            tr.className = 'alterarTabela';
            tr.onclick = function() { alterarTabela(this); };

            tr.dataset.id = p.Produto_ID;
            tr.dataset.nome = p.NomeProduto;
            tr.dataset.categoria = p.CategoriaProduto;
            tr.dataset.preco = p.PrecoProduto;
            tr.dataset.quantidade = p.QuantidadeProduto;

            const tdId = document.createElement('td');
            tdId.textContent = p.Produto_ID;
            tr.appendChild(tdId);

            const tdNome = document.createElement('td');
            tdNome.textContent = p.NomeProduto;
            tr.appendChild(tdNome);

            const tdCategoria = document.createElement('td');
            tdCategoria.textContent = p.CategoriaProduto;
            tr.appendChild(tdCategoria);

            const tdPreco = document.createElement('td');
            tdPreco.textContent = `R$ ${p.PrecoProduto}`;
            tr.appendChild(tdPreco);

            const tdQuantidade = document.createElement('td');
            tdQuantidade.textContent = p.QuantidadeProduto;
            tr.appendChild(tdQuantidade);

            tabela.appendChild(tr);
            
            
            // Adicionar categoria ao set de categorias únicas
            categoriasUnicas.add(p.CategoriaProduto);
        });
        
        // Limpar e inserir o datalist com categorias únicas
        const categoriaElement = document.getElementById("categorias");
        categoriaElement.innerHTML = '';
        categoriasUnicas.forEach(categoria => {
            const option = document.createElement('option');
            option.value = categoria;
            categoriaElement.appendChild(option);
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
            tabela.innerHTML = '';
            produtos.forEach(p => {
                const tr = document.createElement('tr');

                tr.className = 'alterarTabela';
                tr.onclick = function() { alterarTabela(this); };

                tr.dataset.id = p.Produto_ID;
                tr.dataset.nome = p.NomeProduto;
                tr.dataset.categoria = p.CategoriaProduto;
                tr.dataset.preco = p.PrecoProduto;
                tr.dataset.quantidade = p.QuantidadeProduto;

                const tdId = document.createElement('td');
                tdId.textContent = p.Produto_ID;
                tr.appendChild(tdId);

                const tdNome = document.createElement('td');
                tdNome.textContent = p.NomeProduto;
                tr.appendChild(tdNome);

                const tdCategoria = document.createElement('td');
                tdCategoria.textContent = p.CategoriaProduto;
                tr.appendChild(tdCategoria);

                const tdPreco = document.createElement('td');
                tdPreco.textContent = `R$ ${p.PrecoProduto}`;
                tr.appendChild(tdPreco);

                const tdQuantidade = document.createElement('td');
                tdQuantidade.textContent = p.QuantidadeProduto;
                tr.appendChild(tdQuantidade);

                tabela.appendChild(tr);
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

    const resultadosTabela = document.getElementById("resultadosTabela");
    const tr = document.createElement('tr');

    for (const td of linha.children) {
        const novaTd = document.createElement('td');
        novaTd.textContent = td.textContent;
        tr.appendChild(novaTd);
    }

    //Verificar valores
    console.log('ID:', id, 'Nome:', nome, 'Categoria:', categoria, 'Preço:', preco, 'Quantidade:', quantidade);
    
    // Preencher formulário com os valores
    resultadosTabela.innerHTML = '';
    resultadosTabela.appendChild(tr);

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


