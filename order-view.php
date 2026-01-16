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
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Pedido inválido");
}

$orderModel = new Order($db);
$order = $orderModel->getOrderById($_GET['id']);
$items = $orderModel->getOrderItems($_GET['id']);

if (!$order) {
    die("Pedido no encontrado");
}
include 'views/header.php';
?>

<link rel="stylesheet" href="css/order-success.css">

<div class="order-container">

    <h2>Pedido Nº <?= $order['id'] ?></h2>

    <div class="order-info">
        <p><strong>Cliente:</strong><br>
            <?= htmlspecialchars($order['nombre_cliente'] . ' ' . $order['apellido_cliente']) ?>
        </p>
        <p><strong>RUC:</strong><br><?= htmlspecialchars($order['ruc_cliente']) ?></p>
        <p><strong>Dirección:</strong><br><?= htmlspecialchars($order['direccion_entrega']) ?></p>
        <p><strong>Condición:</strong><br><?= htmlspecialchars($order['condicion_pago']) ?></p>
    </div>

    <h3>Detalle</h3>

    <table class="order-table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td><?= htmlspecialchars($i['marca']) ?></td>
                    <td><?= $i['cantidad'] ?></td>
                    <td>Gs. <?= number_format($i['precio'], 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="order-total">
        <h2>Total: Gs. <?= number_format($order['total'], 0, ',', '.') ?></h2>
    </div>

    <?php if ($order['estado'] === 'pendiente'): ?>
        <div class="order-actions">
            <form method="GET" action="factura.php" target="_blank">
                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                <button class="btn-invoice" type="submit">🧾 Imprimir Factura</button>
            </form>

        </div>
    <?php else: ?>
        <div class="order-status">✔ Pedido ya facturado</div>
    <?php endif; ?>

</div>


<?php include 'views/footer.php'; ?>