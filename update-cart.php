<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'remove') {
            // Eliminar producto del carrito
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['success'] = "Producto eliminado del carrito";
        } elseif ($action === 'update' && isset($_POST['quantity'])) {
            // Actualizar cantidad
            $quantity = intval($_POST['quantity']);
            if ($quantity > 0) {
                $_SESSION['cart'][$product_id]['cantidad'] = $quantity;
                $_SESSION['success'] = "Cantidad actualizada";
            } else {
                unset($_SESSION['cart'][$product_id]);
            }
        }
    }
}

header("Location: cart.php");
exit;
?>