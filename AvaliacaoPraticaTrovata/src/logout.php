<?php
/**
 *
 * Objetivo: Arquivo que processa o logout dessa conta / Destroi e Finaliza o Session 
 *           Volta para o começo
 * 
 */

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();

    header('Location: ../index.php');
    exit;
}

?>