<?php
require_once 'config.php';
require_once 'models/Product.php';

if (!isLoggedIn() || $_SESSION['user_roles_id'] != 1) {
    $_SESSION['error'] = "No tienes permisos para editar productos";
    header("Location: products.php");
    exit;
}

$product_id = $_POST['id'] ?? 0;
$nombre = trim($_POST['nombre'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$descripcion = trim($_POST['descripcion'] ?? '');
$stock = intval($_POST['stock'] ?? 0);
$ingredientes = trim($_POST['ingredientes'] ?? '');
$vegano = isset($_POST['vegano']) ? intval($_POST['vegano']) : 0;
$sin_gluten = isset($_POST['sin_gluten']) ? intval($_POST['sin_gluten']) : 0;

if (!$product_id || !$nombre || $precio <= 0 || $stock < 0) {
    $_SESSION['error'] = "Datos inválidos";
    header("Location: edit-product.php?id=$product_id");
    exit;
}

$productModel = new Product((new Database())->getConnection());
$success = $productModel->update($product_id, [
    'nombre' => $nombre,
    'precio' => $precio,
    'descripcion' => $descripcion,
    'stock' => $stock,
    'ingredientes' => $ingredientes,
    'vegano' => $vegano,
    'sin_gluten' => $sin_gluten
]);

if ($success) {
    $_SESSION['success'] = "Producto actualizado correctamente";
} else {
    $_SESSION['error'] = "Error al actualizar el producto";
}

header("Location: products.php");
exit;