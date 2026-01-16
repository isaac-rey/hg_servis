<?php
require_once 'config.php';
require_once 'models/Order.php';

if (!isLoggedIn() || $_SESSION['user_rol_id'] != 1) {
    header("Location: ../login.php");
    exit;
}
$database = new Database();
$db = $database->getConnection(); // 🔥 ESTO FALTABA
$orderModel = new Order($db);
$orders = $orderModel->getAllOrders();

include 'views/header.php';
?>

<h1>Pedidos para Facturar</h1>

<table border="1" width="100%" cellpadding="8">
    <thead>
        <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>RUC</th>
            <th>Total</th>
            <th>Pago</th>
            <th>Estado</th>
            <th>Acción</th>
            <th>Factura</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($o = $orders->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= $o['nombre_cliente'] ?></td>
                <td><?= $o['apellido_cliente'] ?></td>
                <td><?= $o['ruc_cliente'] ?></td>
                <td>Gs. <?= number_format($o['total'], 0, ',', '.') ?></td>
                <td><?= $o['metodo_pago'] ?></td>
                <td>
                    <strong style="color:<?= $o['estado'] == 'Pendiente' ? 'red' : 'green' ?>">
                        <?= $o['estado'] ?>
                    </strong>
                </td>
                <td>
                    <a href="order-view.php?id=<?= $o['id'] ?>">Ver</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>


<?php include 'views/footer.php'; ?>