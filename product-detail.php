<?php
require_once 'config.php';
require_once 'models/Product.php';

$database = new Database();
$db = $database->getConnection();

$productModel = new Product($db);

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = $productModel->getById($product_id);

if (!$product) {
    $_SESSION['error'] = "Producto no encontrado";
    header("Location: products.php");
    exit;
}

$page_title = $product['modelo'] . " - Detalle del Producto";
$page_description = "Detalle del producto " . $product['modelo'];

include 'views/header.php';
?>

<div class="container">
    <div class="product-detail">

        <!-- IMAGEN -->
        <div class="product-image">
            <?php
            $imagePath = 'img/productos/' . $product['imagen'];
            $imageExists = !empty($product['imagen']) && file_exists($imagePath);
            ?>

            <?php if ($imageExists): ?>
                <img src="<?= $imagePath ?>"
                     alt="<?= htmlspecialchars($product['modelo']) ?>"
                     class="product-detail-img"
                     loading="lazy">
            <?php else: ?>
                <img src="https://via.placeholder.com/500x400/cccccc/000000?text=<?= urlencode($product['modelo']) ?>"
                     alt="<?= htmlspecialchars($product['modelo']) ?>"
                     class="product-detail-img"
                     loading="lazy">
            <?php endif; ?>
        </div>

        <!-- INFO -->
        <div class="product-info">
            <h1><?= htmlspecialchars($product['modelo']) ?></h1>

            <p class="product-category">
                <strong>Categoría:</strong> <?= htmlspecialchars($product['categoria']) ?>
            </p>

            <div class="product-price">
                <?= number_format($product['precio'], 0, ',', '.') ?> ₲
            </div>

            <div class="product-features">
                <div class="feature"><strong>Código:</strong> <?= htmlspecialchars($product['codigo']) ?></div>
                <div class="feature"><strong>Marca:</strong> <?= htmlspecialchars($product['marca']) ?></div>

                <?php if (!empty($product['calidad'])): ?>
                    <div class="feature"><strong>Calidad:</strong> <?= htmlspecialchars($product['calidad']) ?></div>
                <?php endif; ?>

                <?php if (!empty($product['color'])): ?>
                    <div class="feature"><strong>Color:</strong> <?= htmlspecialchars($product['color']) ?></div>
                <?php endif; ?>

                <div class="feature">
                    <strong>Stock disponible:</strong> <?= (int)$product['cantidad'] ?> unidades
                </div>
            </div>

            <!-- ACCIONES -->
            <div class="product-actions">

                <?php if (isLoggedIn() && $product['cantidad'] > 0): ?>
                    <form method="POST" action="add-to-cart.php" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <div class="quantity-control">
                            <label for="quantity">Cantidad:</label>
                            <input type="number"
                                   id="quantity"
                                   name="quantity"
                                   value="1"
                                   min="1"
                                   max="<?= $product['cantidad'] ?>">

                            <button type="submit" class="btn btn-primary">
                                Agregar al Carrito
                            </button>
                        </div>
                    </form>

                <?php elseif (!isLoggedIn()): ?>
                    <div class="login-required">
                        <a href="login.php" class="btn btn-primary">
                            Iniciar Sesión para Comprar
                        </a>
                        <p class="login-message">
                            Debes iniciar sesión para agregar productos al carrito
                        </p>
                    </div>

                <?php else: ?>
                    <div class="out-of-stock">
                        <button class="btn btn-disabled" disabled>
                            Producto Agotado
                        </button>
                    </div>
                <?php endif; ?>

                <div class="social-share">
                    <span>Compartir:</span>
                    <button onclick="shareProduct('facebook')">Facebook</button>
                    <button onclick="shareProduct('twitter')">Twitter</button>
                    <button onclick="shareProduct('whatsapp')">WhatsApp</button>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function shareProduct(social) {
    const url = window.location.href;
    const title = "<?= addslashes($product['modelo']); ?>";
    const text = "Mira este producto: " + title;

    let shareUrl = '';
    switch (social) {
        case 'facebook':
            shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            break;
        case 'twitter':
            shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
            break;
        case 'whatsapp':
            shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
            break;
    }

    if (shareUrl) {
        window.open(shareUrl, '_blank', 'width=600,height=400');
    }
}
</script>

<?php include 'views/footer.php'; ?>
