<?php
session_start();

require_once 'config.php';
require_once 'models/Product.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity   = intval($_POST['quantity'] ?? 1);

    if ($product_id <= 0) {
        $_SESSION['error'] = 'Producto inválido';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    if ($quantity < 1) {
        $quantity = 1;
    }

    $database = new Database();
    $db = $database->getConnection();

    $productModel = new Product($db);
    $product = $productModel->getById($product_id);

    // ❌ Producto inexistente
    if (!$product) {
        $_SESSION['error'] = 'Producto no encontrado';
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    // 🧮 Cantidad actual en carrito
    $cantidadEnCarrito = $_SESSION['cart'][$product_id]['cantidad'] ?? 0;
    $cantidadTotal = $cantidadEnCarrito + $quantity;

    // ❌ Stock insuficiente
    if ($product['cantidad'] < $cantidadTotal) {
        $_SESSION['error'] = 'Stock insuficiente. Disponible: ' . $product['cantidad'];
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    // 🛒 Inicializar carrito
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // ➕ Agregar o actualizar producto
    $_SESSION['cart'][$product_id] = [
        'id'       => $product['id'],
        'imagen'   => $product['imagen'],
        'modelo'   => $product['modelo'],
        'marca'    => $product['marca'],
        'precio'   => $product['precio'],
        'cantidad' => $cantidadTotal
    ];

    $_SESSION['success'] = 'Producto agregado al carrito';
}

header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
exit;
