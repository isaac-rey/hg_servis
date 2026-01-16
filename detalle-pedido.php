<?php
require_once 'config.php';
require_once 'models/Product.php';
require_once 'helpers/image_helper.php';

$database = new Database();
$db = $database->getConnection();

$productModel = new Product($db);

$product_id = $_GET['id'] ?? 0;
$product = $productModel->getById($product_id);

if (!$product) {
    $_SESSION['error'] = "Producto no encontrado";
    header("Location: products.php");
    exit;
}

$page_title = $product['nombre'] . " - Vida Saludable";
$page_description = $product['descripcion'];

include 'views/header.php';
?>

<div class="container">
    <div class="product-detail">
        <div class="product-image">
            <?php
            // MAPEO DE IMÁGENES - ID Producto => Nombre de archivo real
            $imageMapping = [
                1 => 'cesar-salad.jpg',      // Ensalada César
                2 => 'Brownie.jpg',          // Brownie Saludable
                3 => 'sandwichvegano.jpg',   // Sándwich Integral Vegano
                4 => 'green-detox-jugo.jpg', // Green Detox
                5 => 'Quinoa-Power-Bowl.jpg', // Bowl Quinoa Power  
                6 => 'barradegranola.jpg',   // Barra de Granola
                8 => 'Quinoa-Power-Bowl.jpg', // Bowl Quinoa Power (ID 8)
                9 => 'green-detox-jugo.jpg',  // Green Detox (ID 9)
                10 => 'feijoada.jpg',   // Sándwich Integral Vegano (ID 10)
                11 => 'smoothie-de-mango-con-platano.jpg', //jugo (ID 11)
                12 => 'tacos.jpg',
            ];

            // Obtener el nombre correcto del archivo
            $imageFile = $imageMapping[$product['id']] ?? $product['imagen'];
            $imagePath = 'img/productos/' . $imageFile;
            $imageExists = file_exists($imagePath);
            ?>

            <?php if ($imageExists): ?>
                <img src="<?php echo $imagePath; ?>"
                    alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                    class="product-detail-img"
                    loading="lazy">
            <?php else: ?>
                <!-- Placeholder con color según categoría -->
                <?php
                $colors = [
                    1 => '4CAF50', // Ensaladas - Verde
                    2 => 'FF9800', // Bowls - Naranja
                    3 => '2196F3', // Sándwiches - Azul
                    4 => 'E91E63'  // Jugos - Rosa
                ];
                $color = $colors[$product['categoria_id']] ?? '9C27B0';
                ?>
                <img src="https://via.placeholder.com/500x400/<?php echo $color; ?>/ffffff?text=<?php echo urlencode($product['nombre']); ?>"
                    alt="<?php echo htmlspecialchars($product['nombre']); ?>"
                    class="product-detail-img"
                    loading="lazy">
            <?php endif; ?>

            <div class="product-badges">
                <?php if ($product['vegano']): ?><span class="badge vegan">🌱 Vegano</span><?php endif; ?>
                <?php if ($product['sin_gluten']): ?><span class="badge gluten-free">🌾 Sin Gluten</span><?php endif; ?>
                <?php if ($product['destacado']): ?><span class="badge featured">⭐ Destacado</span><?php endif; ?>
            </div>
        </div>

        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['nombre']); ?></h1>
            <p class="product-category"><?php echo htmlspecialchars($product['categoria_nombre']); ?></p>

            <div class="product-price"><?php echo formatPrice($product['precio']); ?></div>

            <div class="product-features">
                <?php if ($product['calorias']): ?>
                    <div class="feature"><strong>Calorías:</strong> <?php echo $product['calorias']; ?></div>
                <?php endif; ?>
                <?php if ($product['proteinas']): ?>
                    <div class="feature"><strong>Proteínas:</strong> <?php echo $product['proteinas']; ?>g</div>
                <?php endif; ?>
                <?php if ($product['carbohidratos']): ?>
                    <div class="feature"><strong>Carbohidratos:</strong> <?php echo $product['carbohidratos']; ?>g</div>
                <?php endif; ?>
                <?php if ($product['grasas']): ?>
                    <div class="feature"><strong>Grasas:</strong> <?php echo $product['grasas']; ?>g</div>
                <?php endif; ?>
                <div class="feature"><strong>Stock disponible:</strong> <?php echo $product['stock']; ?> unidades</div>
            </div>

            <div class="product-description">
                <h3>Descripción</h3>
                <p><?php echo htmlspecialchars($product['descripcion']); ?></p>
            </div>

            <?php if ($product['ingredientes']): ?>
                <div class="product-ingredients">
                    <h3>Ingredientes</h3>
                    <p><?php echo htmlspecialchars($product['ingredientes']); ?></p>
                </div>
            <?php endif; ?>

            <div class="product-actions">
                <?php if (isLoggedIn() && $product['stock'] > 0): ?>
                    <form method="POST" action="add-to-cart.php" class="add-to-cart-form">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <div class="quantity-control">
                            
                        </div>
                    </form>
                <?php elseif (!isLoggedIn()): ?>
                    <div class="login-required">
                        <a href="login.php" class="btn btn-primary">Iniciar Sesión para Comprar</a>
                        <p class="login-message">Debes iniciar sesión para agregar productos al carrito</p>
                    </div>
                <?php else: ?>
                    <div class="out-of-stock">
                        <button class="btn btn-disabled" disabled>Producto Agotado</button>
                        <p class="stock-message">Te notificaremos cuando esté disponible nuevamente</p>
                    </div>
                <?php endif; ?>

                <div class="social-share">
                    <span>Compartir:</span>
                    <button class="btn-share" onclick="shareProduct('facebook')">Facebook</button>
                    <button class="btn-share" onclick="shareProduct('twitter')">Twitter</button>
                    <button class="btn-share" onclick="shareProduct('whatsapp')">WhatsApp</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function shareProduct(social) {
        const url = window.location.href;
        const title = "<?php echo addslashes($product['nombre']); ?>";
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