<?php
session_start();
session_destroy();
echo json_encode(['sucessoLogout' => 'Logout realizado com sucesso.']);
?>