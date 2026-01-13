// Buscar token CSRF ao carregar a página
fetch('../php/getCSRFToken.php', {
        credentials: 'same-origin'
    })
        .then(response => {
            console.log('Resposta recebida:', response.status);
            if (!response.ok) {
                throw new Error('Erro ao buscar token: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Token recebido:', data.csrf_token);
            document.getElementById('csrf_token').value = data.csrf_token;
        })
        .catch(error => {
            console.error('Erro ao buscar token CSRF:', error);
            alert('Erro ao carregar página. Recarregue e tente novamente.');
        });
    
// Buscar informações do usuário ao carregar a página

const sessaoUsuario = sessionStorage.getItem('nomeUsuario');
const avatarUsuario = sessionStorage.getItem('avatarUsuario');

document.getElementById('nomeUsuario').textContent = sessaoUsuario;
document.getElementById('imgAvatar').src = avatarUsuario;


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
            window.location.href = '../html/index.html'; // Redirecionar para a página de login
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
        loadForm.append('produto_ID', id);
        fetch('../php/menuUsuario.php', {
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


// Se não tiver os dados, usuário não está logado corretamente
if (!sessaoUsuario) {
    window.location.href = '/html/index.html';
}

// Função para exibir todas as tabelas ao carregar a página

function exibirTabelas() {
    let loadForm = new FormData();
    loadForm.append('action', 'exibirTodos');
    fetch('../php/menuUsuario.php', {
        method: 'POST',
        body: loadForm
    })
    .then(response => {
        // Debug: ver o que está vindo do servidor
        return response.text().then(text => {
            console.log('Resposta do servidor:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Erro ao parsear JSON:', e);
                console.error('Texto recebido:', text);
                throw e;
            }
        });
    })
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
            const bt = document.createElement('button');

            tr.className = 'alterarTabela';
            tr.onclick = function() { alterarTabela(this); };

            tr.dataset.id = p.produto_ID;
            tr.dataset.nome = p.nomeProduto;
            tr.dataset.categoria = p.categoriaProduto;
            tr.dataset.preco = p.precoProduto;
            tr.dataset.quantidade = p.quantidadeProduto;
            const tdId = document.createElement('td');
            tdId.textContent = p.produto_ID;
            tr.appendChild(tdId);

            const tdNome = document.createElement('td');
            tdNome.textContent = p.nomeProduto;
            tr.appendChild(tdNome);

            const tdCategoria = document.createElement('td');
            tdCategoria.textContent = p.categoriaProduto;
            tr.appendChild(tdCategoria);

            const tdPreco = document.createElement('td');
            tdPreco.textContent = `R$ ${p.precoProduto}`;
            tr.appendChild(tdPreco);

            const tdQuantidade = document.createElement('td');
            tdQuantidade.textContent = p.quantidadeProduto;
            tr.appendChild(tdQuantidade);


            tabela.appendChild(tr);
            
            
            // Adicionar categoria ao set de categorias únicas
            categoriasUnicas.add(p.categoriaProduto);
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

// Carregar tabela ao abrir a página
window.addEventListener('load', exibirTabelas);

//Enviar formulário sem atualizar a página
function naoEnviar(event) {
    event.preventDefault();
    const formulario = new FormData(event.target);
    

    //Pesquisar produto
    if (modoPesquisa) {
        formulario.append('action', 'pesquisar');
        fetch('../php/menuUsuario.php', {
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

                tr.dataset.id = p.produto_ID;
                tr.dataset.nome = p.nomeProduto;
                tr.dataset.categoria = p.categoriaProduto;
                tr.dataset.preco = p.precoProduto;
                tr.dataset.quantidade = p.quantidadeProduto;
                const tdId = document.createElement('td');
                tdId.textContent = p.produto_ID;
                tr.appendChild(tdId);

                const tdNome = document.createElement('td');
                tdNome.textContent = p.nomeProduto;
                tr.appendChild(tdNome);

                const tdCategoria = document.createElement('td');
                tdCategoria.textContent = p.categoriaProduto;
                tr.appendChild(tdCategoria);

                const tdPreco = document.createElement('td');
                tdPreco.textContent = `R$ ${p.precoProduto}`;
                tr.appendChild(tdPreco);

                const tdQuantidade = document.createElement('td');
                tdQuantidade.textContent = p.quantidadeProduto;
                tr.appendChild(tdQuantidade);

                tabela.appendChild(tr);
            });
         
        })
        .catch(error => console.error('Erro:', error));
    }


    //Cadastrar produto
    if (modoCadastro) {
        formulario.append('action', 'cadastrar');
        fetch('../php/menuUsuario.php', {
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
        fetch('../php/menuUsuario.php', {
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
    document.getElementById('produto_ID_Div').style.display = 'inline-flex';
    document.getElementById('quantidade_Produto_Div').style.display = 'none';
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
    document.getElementById('produto_ID_Div').style.display = 'none';
    document.getElementById('quantidade_Produto_Div').style.display = 'inline-flex';
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
    const bt = document.createElement('button');
    bt.onclick = function() { excluir(id); };
    bt.textContent = 'Excluir';
    
    // Preencher nova linha da tabela
    for (const td of linha.children) {
        const novaTd = document.createElement('td');
        novaTd.textContent = td.textContent;
        tr.appendChild(novaTd);
    }

    // Coluna de excluir
    const tdExcluir = document.createElement('td');
    tdExcluir.appendChild(bt);
    tr.appendChild(tdExcluir);

    //Verificar valores
    console.log('ID:', id, 'Nome:', nome, 'Categoria:', categoria, 'Preço:', preco, 'Quantidade:', quantidade);
    
    // Preencher formulário com os valores
    resultadosTabela.innerHTML = '';
    resultadosTabela.appendChild(tr);


    // Preencher formulário com os valores
    document.getElementById('produto_ID_Input').value = id;
    document.querySelector('input[name="nomeProduto"]').value = nome;
    document.querySelector('input[name="categoriaProduto"]').value = categoria;
    document.querySelector('input[name="precoProduto"]').value = preco;
    document.getElementById('quantidade_Produto_Input').value = quantidade;
    
    // Configurar modo de alteração
    modoCadastro = false;
    modoPesquisa = false;
    modoAlterar = true;
    
    // Configurar formulário para alteração
    document.getElementById('botaoForms').value = 'Alterar Produto';
    document.getElementById('PesquisaForm').style.display = 'flex';
    document.getElementById('produto_ID_Div').style.display = 'inline-flex';
    document.getElementById('quantidade_Produto_Div').style.display = 'inline-flex';
    document.getElementById('botaoForms').style.display = 'block';
    
}

// Função para o menu do usuário

function user() {
    const menuUsuarioDropdown = document.getElementById('menuUsuarioDropdown');
    menuUsuarioDropdown.style.display = 'flex';

}

window.onclick = function(event) {
    if (!event.target.matches('#menuUsuarioDropdown') && !event.target.matches('#nomeUsuario') &&!event.target.matches('#imgAvatar')) {
        menuUsuarioDropdown.style.display = 'none';

    }
}