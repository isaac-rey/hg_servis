<?php
require_once 'config.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = "Debes iniciar sesión para ver el carrito";
    header("Location: login.php");
    exit;
}

// Función para formatear precio en Gs.
function formatPriceGS($amount)
{
    return '₲' . number_format($amount, 0, ',', '.');
}

/* =========================
   ENVÍO (opcional)
========================= */

// Si el usuario selecciona envío
$envio_seleccionado = isset($_POST['envio']) ? $_POST['envio'] : ($_SESSION['envio'] ?? 'no');
$_SESSION['envio'] = $envio_seleccionado;

$envio_gs = ($envio_seleccionado === 'si') ? 10000 : 0;

/* =========================
   SUBTOTAL
========================= */

$subtotal_gs = 0;

if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cantidad = (int)($item['cantidad'] ?? 1);
        $precio_gs = (int)($item['precio'] ?? 0);
        $subtotal_gs += $precio_gs * $cantidad;
    }
}

$total_gs = $subtotal_gs + $envio_gs;

$page_title = "Carrito de Compras";
include 'views/header.php';
?>

<div class="container cart-page">
    <h1 class="page-title">🛒 Tu Carrito</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <h3>Tu carrito está vacío</h3>
            <p>Explorá nuestros productos y encontrá lo que necesitás</p>
            <a href="products.php" class="btn btn-primary">Ver Productos</a>
        </div>
    <?php else: ?>
        <div class="cart-layout">

            <!-- LISTA -->
            <div class="cart-list">
                <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                    <?php
                    $cantidad = (int)($item['cantidad'] ?? 1);
                    $precio_gs = (int)($item['precio'] ?? 0);
                    $subtotal_item = $precio_gs * $cantidad;
                    ?>
                    <div class="cart-card">
                        <div class="cart-info">
                            <h3><?= htmlspecialchars($item['marca'] . ' ' . $item['modelo']); ?></h3>
                            <span class="unit-price"><?= formatPriceGS($precio_gs); ?> c/u</span>
                        </div>

                        <div class="cart-qty">
                            <form method="POST" action="update-cart.php">
                                <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                                <input type="number" name="quantity" value="<?= $cantidad; ?>" min="1" max="50">
                                <button type="submit" name="action" value="update">Actualizar</button>
                            </form>
                        </div>

                        <div class="cart-subtotal">
                            <?= formatPriceGS($subtotal_item); ?>
                        </div>

                        <div class="cart-remove">
                            <form method="POST" action="update-cart.php">
                                <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                                <button type="submit" name="action" value="remove">✖</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- RESUMEN -->
            <aside class="cart-summary">
                <h3>Resumen</h3>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong><?= formatPriceGS($subtotal_gs); ?></strong>
                </div>

                <form method="POST" class="shipping-box">
                    <label>Envío</label>
                    <select name="envio" onchange="this.form.submit()">
                        <option value="no" <?= $envio_seleccionado === 'no' ? 'selected' : ''; ?>>
                            Retiro en local (Gratis)
                        </option>
                        <option value="si" <?= $envio_seleccionado === 'si' ? 'selected' : ''; ?>>
                            Envío a domicilio (+ <?= formatPriceGS(10000); ?>)
                        </option>
                    </select>
                </form>

                <div class="summary-total">
                    <span>Total</span>
                    <strong><?= formatPriceGS($total_gs); ?></strong>
                </div>

                <a href="checkout.php" class="btn btn-primary btn-lg">Finalizar Compra</a>
                <a href="products.php" class="btn btn-secondary">Seguir Comprando</a>
            </aside>

        </div>
    <?php endif; ?>
</div>


<style>
    .cart-page {
    margin-top: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.cart-layout {
    display: grid;
    grid-template-columns: 1fr 360px;
    gap: 2rem;
}

/* TARJETAS */
.cart-card {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    align-items: center;
    padding: 1.2rem 1.5rem;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 20px rgba(0,0,0,.06);
    margin-bottom: 1rem;
}

.cart-info h3 {
    margin: 0;
    font-size: 1.05rem;
}

.unit-price {
    color: #6c757d;
    font-size: .9rem;
}

.cart-qty input {
    width: 70px;
    padding: .4rem;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.cart-qty button {
    margin-left: .4rem;
    padding: .4rem .8rem;
    border-radius: 8px;
    border: none;
    background: #0d6efd;
    color: #fff;
    cursor: pointer;
}

.cart-subtotal {
    font-weight: 600;
    font-size: 1.05rem;
}

.cart-remove button {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #dc3545;
    cursor: pointer;
}

/* RESUMEN */
.cart-summary {
    background: #fff;
    border-radius: 18px;
    padding: 1.8rem;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
    position: sticky;
    top: 2rem;
}

.cart-summary h3 {
    margin-bottom: 1.2rem;
}

.summary-row,
.summary-total {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.summary-total {
    font-size: 1.3rem;
    border-top: 2px solid #eee;
    padding-top: 1rem;
}

.shipping-box label {
    font-size: .9rem;
    color: #555;
}

.shipping-box select {
    width: 100%;
    padding: .6rem;
    border-radius: 10px;
    border: 1px solid #ddd;
    margin-top: .4rem;
}

.btn-lg {
    display: block;
    width: 100%;
    padding: .8rem;
    margin-top: 1rem;
}

.btn-secondary {
    display: block;
    width: 100%;
    margin-top: .6rem;
    background: #f1f3f5;
    color: #333;
    border-radius: 10px;
}

/* VACÍO */
.empty-cart {
    text-align: center;
    padding: 3rem;
    background: #f8f9fa;
    border-radius: 16px;
}

/* RESPONSIVE */
@media (max-width: 900px) {
    .cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-card {
        grid-template-columns: 1fr;
        text-align: center;
        gap: .8rem;
    }

    .cart-summary {
        position: static;
    }
}

</style>

<?php include 'views/footer.php'; ?>