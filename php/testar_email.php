<?php
require_once __DIR__ . '/configSMTP.php';

// Testar envio de e-mail
$destinatario = "teste@exemplo.com";
$assunto = "Teste MailHog";
$corpoEmail = "
    <html>
    <body>
        <h2>Teste de E-mail</h2>
        <p>Se você está vendo isso, o MailHog está funcionando!</p>
        <p>Data/Hora: " . date('d/m/Y H:i:s') . "</p>
    </body>
    </html>
";

if (enviarEmail($destinatario, $assunto, $corpoEmail)) {
    echo "✅ E-mail enviado com sucesso!\n";
    echo "📬 Acesse http://localhost:8025 para ver o e-mail\n";
} else {
    echo "❌ Erro ao enviar e-mail\n";
}
?>
