<?php
require_once 'config.php';
require_once 'models/Order.php';

/* =========================
   CREAR CONEXIÓN
=========================*/
$database = new Database();
$db = $database->getConnection();

/* =========================
   VALIDAR ID
=========================*/
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Pedido inválido");
}

$orderModel = new Order($db);
$orderModel->markAsInvoiced($_POST['id']);

header("Location: orders.php");
exit;
