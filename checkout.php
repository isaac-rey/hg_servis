<?php
require_once 'config.php';
require_once 'models/Order.php';
require_once 'models/Stock.php';


if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para realizar un pedido";
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Tu carrito está vacío";
    header("Location: products.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$error = '';

/* =========================
   FORMATO GUARANÍES
=========================*/
function formatGs($amount)
{
    return 'Gs. ' . number_format($amount, 0, ',', '.');
}

/* =========================
   ENVÍO DESDE CARRITO
=========================*/
$envio_seleccionado = $_SESSION['envio'] ?? 'no';
$envio_gs = ($envio_seleccionado === 'si') ? 10000 : 0;

/* =========================
   SUBTOTAL
=========================*/
$subtotal_gs = 0;
foreach ($_SESSION['cart'] as $item) {
    $precio = (int)$item['precio'];
    $cantidad = (int)$item['cantidad'];
    $subtotal_gs += $precio * $cantidad;
}

$total_gs = $subtotal_gs + $envio_gs;

/* =========================
   PROCESAR PEDIDO
=========================*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ruc_cliente = trim($_POST['ruc_cliente'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $notas = trim($_POST['notas'] ?? '');
    $condicion_pago = $_POST['condicion_pago'] ?? 'Contado';


    if (empty($ruc_cliente) || empty($nombre) || empty($apellido) || empty($email) || empty($telefono) || empty($direccion)) {
        $error = "Todos los campos obligatorios deben ser completados.";
    } else {
        try {
            $orderModel = new Order($db);

            $items_for_order = [];
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $items_for_order[] = [
                    'id'       => $product_id,
                    'marca'   => $item['marca'] . ' ' . ($item['modelo'] ?? ''),
                    'precio'   => $item['precio'],
                    'cantidad' => $item['cantidad']
                ];
            }

            // Crear pedido
            $order_id = $orderModel->createOrderWithItems(
                $_SESSION['user_id'],
                $items_for_order,
                $total_gs,
                $direccion,
                $telefono,
                $ruc_cliente,
                $metodo_pago,
                $notas,
                $envio_gs, // ✅ NO 'si' o 'no'
                $nombre,
                $apellido,
                $email,
                $condicion_pago
            );


            if ($order_id) {
                unset($_SESSION['cart']);
                unset($_SESSION['envio']);
                $_SESSION['success'] = "Pedido realizado exitosamente. Nº #$order_id";
                header("Location: order-view.php?id=$order_id");
                exit;
            } else {
                $error = "Error al crear el pedido.";
            }
        } catch (Exception $e) {
            $error = "Error interno al procesar el pedido.";
            error_log($e->getMessage());
        }
    }
    // Crear instancia de Stock
$stockModel = new Stock();

// Reducir stock por cada producto
foreach ($_SESSION['cart'] as $product_id => $item) {
    $salida_exitosa = $stockModel->salidaStock(
        $product_id,
        (int)$item['cantidad'],
        "Venta - Pedido #$order_id",
        $_SESSION['user_name'] ?? 'admin'
    );
    
    if (!$salida_exitosa) {
        // Manejar error de stock insuficiente
        $error = "Stock insuficiente para el producto ID: $product_id";
        break;
    }
}
}

$page_title = "Checkout - Finalizar Compra";
include 'views/header.php';
?>

<div class="container">
    <h1>Finalizar Compra</h1>

    <?php if (!empty($error)): ?>
        <div class="alert error">
            <strong>Error:</strong> <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="checkout-content">

        <!-- FORMULARIO -->
        <div class="checkout-form">
            <form method="POST" class="checkout-form">

                <div class="form-section">
                    <h3>Información del Cliente</h3>

                    <div class="form-group">
                        <label>RUC del cliente <span style="color:red;">*</span></label>
                        <input type="text" name="ruc_cliente" required placeholder="Ej: 1234567-8" value="<?= htmlspecialchars($_POST['ruc_cliente'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Nombre <span style="color:red;">*</span></label>
                        <input type="text" name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? $_SESSION['user_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Apellido <span style="color:red;">*</span></label>
                        <input type="text" name="apellido" required value="<?= htmlspecialchars($_POST['apellido'] ?? $_SESSION['user_apellido'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Email <span style="color:red;">*</span></label>
                        <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['user_email'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Teléfono <span style="color:red;">*</span></label>
                        <input type="tel" name="telefono" required value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                    </div>
                </div>

                 <!-- <div class="form-section">
                    <h3>Dirección de Entrega</h3>
                    <?php if ($envio_seleccionado === 'si'): ?>
                        <textarea name="direccion" required placeholder="Calle, número, barrio, ciudad"><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                    <?php else: ?>
                        <input type="hidden" name="direccion" value="Retiro en local">
                        <p><strong>Retiro en local</strong> – sin costo</p>
                    //<?php endif; ?>
                </div>  -->
                <div class="form-section">
                    <h3>Dirección de Entrega</h3>
                    <textarea name="direccion" required placeholder="Calle, número, barrio, ciudad"><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>
                </div>

                <div class="form-section">
                    <h3>Método de Pago</h3>
                    <label><input type="radio" name="metodo_pago" value="efectivo" checked> Efectivo</label><br>
                    <label><input type="radio" name="metodo_pago" value="tarjeta"> Tarjeta</label><br>
                    <label><input type="radio" name="metodo_pago" value="transferencia"> Transferencia</label>
                </div>

                <div class="form-section">
                    <label>Notas (opcional)</label>
                    <textarea name="notas"><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
                </div>
                <div class="form-section">
                    <h3>Condicion de venta</h3>
                    <label><input type="radio" name="condicion_pago" value="Contado" checked> Contado</label><br>
                    <label><input type="radio" name="condicion_pago" value="Credito"> Credito</label><br>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-large">Generar factura</button>

            </form>
        </div>

        <!-- RESUMEN -->
        <div class="order-summary">
            <div class="summary-card">
                <h3>Resumen del Pedido</h3>

                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="order-item">
                        <span><?= htmlspecialchars($item['marca'] . ' ' . ($item['modelo'] ?? '')); ?> x<?= $item['cantidad']; ?></span>
                        <span><?= formatGs($item['precio'] * $item['cantidad']); ?></span>
                    </div>
                <?php endforeach; ?>

                <div class="summary-totals">
                    <div class="summary-line">
                        <span>Subtotal:</span>
                        <span><?= formatGs($subtotal_gs); ?></span>
                    </div>

                    <div class="summary-line">
                        <span>Envío:</span>
                        <span><?= formatGs($envio_gs); ?></span>
                    </div>

                    <div class="summary-line total">
                        <span>Total:</span>
                        <span><?= formatGs($total_gs); ?></span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
        margin-top: 2rem;
    }

    .checkout-form,
    .order-summary {
        background: #fff;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }

    .form-section {
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 0.3rem;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.5rem;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    .btn-block {
        display: block;
        width: 100%;
        padding: 0.7rem;
        font-size: 1.1rem;
    }

    .summary-card h3 {
        margin-bottom: 1rem;
        color: #28a745;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        border-top: 1px solid #ddd;
        padding: 0.5rem 0;
    }

    .summary-line.total {
        font-weight: bold;
        font-size: 1.2rem;
        color: #28a745;
        border-top: 2px solid #28a745;
    }

    @media (max-width:768px) {
        .checkout-content {
            grid-template-columns: 1fr;
        }

        .order-summary {
            margin-top: 1.5rem;
        }
    }
</style>


<!-- Mantén los mismos estilos CSS que ya tienes -->
<style>
    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 2rem;
        margin-top: 2rem;
    }

    .form-section {
        background: white;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        margin-bottom: 1.5rem;
    }

    .form-section h3 {
        margin-top: 0;
        color: #333;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 0.5rem;
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
        color: #555;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
    }

    .payment-methods {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .payment-method input {
        display: none;
    }

    .payment-method label {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .payment-method input:checked+label {
        border-color: #007bff;
        background-color: #f8f9ff;
    }

    .payment-icon {
        font-size: 1.5rem;
    }

    .payment-info {
        display: flex;
        flex-direction: column;
    }

    .summary-card {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        position: sticky;
        top: 2rem;
    }

    .order-items {
        margin-bottom: 1.5rem;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid #e0e0e0;
    }

    .item-name {
        flex: 1;
        font-size: 0.9rem;
    }

    .item-quantity {
        color: #666;
        margin: 0 1rem;
        font-size: 0.9rem;
    }

    .item-price {
        font-weight: bold;
        font-size: 0.9rem;
    }

    .summary-totals {
        border-top: 2px solid #dee2e6;
        padding-top: 1rem;
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
    }

    .summary-line.total {
        font-size: 1.2rem;
        font-weight: bold;
        color: #28a745;
        border-top: 1px solid #dee2e6;
        padding-top: 0.5rem;
    }

    .conversion-info {
        text-align: center;
        margin: 0.5rem 0;
        color: #666;
        font-style: italic;
    }

    .delivery-info {
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #dee2e6;
    }

    .delivery-info h4 {
        margin-bottom: 0.5rem;
        color: #333;
    }

    .delivery-info p {
        margin: 0.25rem 0;
        font-size: 0.9rem;
    }

    .btn-large {
        padding: 1rem 2rem;
        font-size: 1.1rem;
    }

    .alert.error {
        background: #f8d7da;
        color: #721c24;
        padding: 1rem;
        border-radius: 6px;
        border: 1px solid #f5c6cb;
        margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
        .checkout-content {
            grid-template-columns: 1fr;
        }

        .summary-card {
            position: static;
        }
    }
</style>

<?php include 'views/footer.php'; ?>