<?php
require_once 'config.php';
require_once 'models/Product.php';

requireLogin();
if (!hasRole(1)) {
    $_SESSION['error'] = "No tienes permisos para editar productos";
    header("Location: index.php");
    exit;
}

$product_id = $_POST['id'] ?? 0;
$nombre = sanitizeInput($_POST['nombre'] ?? '');
$descripcion = sanitizeInput($_POST['descripcion'] ?? '');
$precio = floatval($_POST['precio'] ?? 0);
$stock = intval($_POST['stock'] ?? 0);
$ingredientes = sanitizeInput($_POST['ingredientes'] ?? '');
$vegano = isset($_POST['vegano']) ? intval($_POST['vegano']) : 0;
$sin_gluten = isset($_POST['sin_gluten']) ? intval($_POST['sin_gluten']) : 0;

if (!$product_id || !$nombre || $precio <= 0 || $stock < 0) {
    $_SESSION['error'] = "Datos inválidos";
    header("Location: edit_producto.php?id=$product_id");
    exit;
}

$productModel = new Product((new Database())->getConnection());
$success = $productModel->update($product_id, [
    'nombre' => $nombre,
    'descripcion' => $descripcion,
    'precio' => $precio,
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

header("Location: index.php");
exit;