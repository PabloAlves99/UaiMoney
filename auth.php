<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Bloqueio para qualquer usuário deslogado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Trava auxiliar para telas exclusivas de Administrador
function exigirAdmin() {
    if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'adm') {
        // Redireciona com aviso ou volta pro dashboard
        header("Location: ./view/dashboard.php?erro=acesso_negado");
        exit();
    }
}
?>