<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarEmail($destinatario, $assunto, $corpoEmail) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuração MailHog
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->Port = 1025;  // Porta SMTP do MailHog
        $mail->SMTPAuth = false;
        $mail->SMTPAutoTLS = false;
        
        // Remetente e destinatário
        $mail->setFrom('noreply@seusite.com', 'Seu Site');
        $mail->addAddress($destinatario);
        
        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $assunto;
        $mail->Body = $corpoEmail;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Erro ao enviar e-mail: {$mail->ErrorInfo}");
        return false;
    }
}
?>