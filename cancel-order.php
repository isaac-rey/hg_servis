<?php
require_once 'config.php';
require_once 'models/Order.php';

if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header("Location: orders.php");
    exit;
}

$order_id = $_POST['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$order_id) {
    $_SESSION['error'] = "ID de pedido inválido";
    header("Location: orders.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$orderModel = new Order($db);

if ($orderModel->cancelOrder($order_id, $user_id)) {
    $_SESSION['success'] = "Pedido cancelado exitosamente";
} else {
    $_SESSION['error'] = "No se pudo cancelar el pedido. Puede que ya no esté pendiente.";
}

header("Location: orders.php");
exit;
?>