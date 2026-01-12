<?php
session_start();
require_once(__DIR__ . '/csrf.php');

header('Content-Type: application/json');
echo json_encode(['csrf_token' => gerarTokenCSRF()]);
?>
