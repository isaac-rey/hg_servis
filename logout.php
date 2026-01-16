<?php
require_once 'config.php';

// Guardar nombre de usuario para el mensaje
$user_name = $_SESSION['user_name'] ?? 'Usuario';

// Guardar el carrito temporalmente
$saved_cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : null;

// Destruir la sesión actual
session_destroy();

// Iniciar nueva sesión
session_start();

// Restaurar el carrito si existía
if ($saved_cart) {
    $_SESSION['cart'] = $saved_cart;
}

// Mensaje de éxito
$_SESSION['success'] = "¡Hasta pronto, $user_name! Has cerrado sesión correctamente.";

// Redirigir al inicio
header("Location: index.php");
exit;
?>