window.onload = function() {
    fetchCSRFToken();
    
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    if (token) {
        document.getElementById('token').value = token;
    } else {
        alert('Token de redefinição de senha ausente. Verifique o link enviado por email.');
    }
};

function fetchCSRFToken() {
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
}
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(String(email).toLowerCase());
}

function enviarFormulario(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    if (!validarEmail(email)) {
        alert('Por favor, insira um endereço de email válido.');
        return;
    }

    const csrfToken = document.getElementById('csrf_token').value;

    const formData = new FormData();
    formData.append('email', email);
    formData.append('csrf_token', csrfToken);

    fetch('../php/recuperarSenha.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Instruções para redefinir sua senha foram enviadas para o seu email.');
        } else {
            alert('Erro: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erro ao enviar formulário:', error);
        alert('Ocorreu um erro ao processar sua solicitação. Tente novamente mais tarde.');
    });
}

//document.getElementById('resetarSenhaForm').addEventListener('submit', enviarFormulario);

const urlParams = new URLSearchParams(window.location.search);
switch (urlParams.get('status')) {
    case 1:
        alert('Erro ao validar sessão. Tente novamente.');
        break;
    case 2:
        alert('Erro: Campos obrigatórios não preenchidos.');
        break;
    case 3:
        alert('Erro: Senha e confirmação não coincidem.');
        break;
    case 4:
        alert('Erro: Recuperação inválida.');
        break;
    case 5:
        alert('Erro: Sessão expirada. Solicite um novo link de redefinição.');
        break;
    case 6:
        alert('Link de redefinição de senha inválido ou expirado.');
        break;
    default:
        
        break;
}

// Validação de senha forte
senha = document.getElementById('senha');
senha2 = document.getElementById('senha2');
window.addEventListener('input', (event) => {
    if (senha.value.length < 8 ) {
        senha.style.borderColor = 'red';
        document.getElementById('caractere').style.color = 'red';
    }   else {
        senha.style.borderColor = 'rgb(204, 204, 204)';
        document.getElementById('caractere').style.color = 'green';
    }   
    if (!senha.value.match(/[A-Z]/)) {              
        senha.style.borderColor = 'red';
        document.getElementById('maiuscula').style.color = 'red';
    }   else {
        senha.style.borderColor = 'rgb(204, 204, 204)';
        document.getElementById('maiuscula').style.color = 'green';
    }
    if (!senha.value.match(/[a-z]/)) {              
        senha.style.borderColor = 'red';
        document.getElementById('minuscula').style.color = 'red';
    }   else {
        senha.style.borderColor = 'rgb(204, 204, 204)';
        document.getElementById('minuscula').style.color = 'green';
    }
    if (!senha.value.match(/[0-9]/)) {              
        senha.style.borderColor = 'red';
        document.getElementById('numero').style.color = 'red';
    }   else {
        senha.style.borderColor = 'rgb(204, 204, 204)';
        document.getElementById('numero').style.color = 'green';
    }
    if (!senha.value.match(/[!@#$%^&*]/)) {              
        senha.style.borderColor = 'red';
        document.getElementById('especial').style.color = 'red';
    }   else {
        senha.style.borderColor = 'rgb(204, 204, 204)';
        document.getElementById('especial').style.color = 'green';
    }
    if (senha.value !== senha2.value) {
        senha2.style.borderColor = 'red';
        document.getElementById('senhasIguais').style.color = 'red';
    } else {
        senha2.style.borderColor = 'rgb(204, 204, 204)';
        document.getElementById('senhasIguais').style.color = 'green';
    }
});

