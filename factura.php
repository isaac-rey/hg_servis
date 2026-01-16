<?php
require_once 'config.php';
require_once 'models/Order.php';
require_once 'fpdf/fpdf.php';

/* =========================
   CONEXIÓN
=========================*/
$database = new Database();
$db = $database->getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Factura inválida");
}

$orderModel = new Order($db);
$order = $orderModel->getOrderById($_GET['id']);
$items = $orderModel->getOrderItems($_GET['id']);

if (!$order) {
    die("Pedido no encontrado");
}

