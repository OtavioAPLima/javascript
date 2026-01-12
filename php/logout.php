<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();
session_destroy();
echo json_encode(['sucessoLogout' => 'Logout realizado com sucesso.']);
?>