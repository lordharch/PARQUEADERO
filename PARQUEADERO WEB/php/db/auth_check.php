<?php
session_start();

/**
 * Verifica si hay un usuario logueado
 * @return bool
 */
function isAuthorized() {
    return isset($_SESSION['usuario_id']);
}

/**
 * Inicia sesión del usuario
 * @param int $id_usuario
 * @param string $nombre
 */
function login($id_usuario, $nombre) {
    $_SESSION['usuario_id'] = $id_usuario;
    $_SESSION['usuario_nombre'] = $nombre;
}

/**
 * Cierra sesión del usuario
 */
function logout() {
    session_unset();
    session_destroy();
}
?>
