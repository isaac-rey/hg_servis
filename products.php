<?php
require_once 'config.php';
require_once 'models/Product.php';

// Verificar si existe image_helper.php
if (file_exists('helpers/image_helper.php')) {
    require_once 'helpers/image_helper.php';
} else {
    // Función temporal de respaldo
    function getProductImage($product)
    {
        $modelo = $product['modelo'] ?? 'Producto';
        $marca = strtolower($product['marca'] ?? '');
        
        $brandColors = [
            'apple' => 'A2AAAD',
            'samsung' => '1428A0',
            'xiaomi' => 'FF6900',
            'motorola' => '5C92D1',
            'huawei' => 'D10B25',
            'lg' => 'A500AF',
            'nokia' => '124191',
            'sony' => '003399',
        ];
        
        $color = $brandColors[$marca] ?? '9C27B0';
        $text = urlencode(substr($modelo, 0, 20));
        return "https://via.placeholder.com/500x300/{$color}/ffffff?text=" . $text;
    }
}

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die("<div style='padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>
        <h3>❌ Error de conexión a base de datos</h3>
        <p>No se pudo establecer conexión con la base de datos.</p>
        <p><a href='install.php'>Haz clic aquí para crear la base de datos</a></p>
    </div>");
}

// Escanear imágenes si se solicita (solo admin)
if (isset($_GET['action']) && $_GET['action'] == 'scan_images' && isLoggedIn() && $_SESSION['user_rol_id'] == 1) {
    if (function_exists('scanAndAssociateImages')) {
        $result = scanAndAssociateImages();
        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }
        header('Location: products.php');
        exit();
    }
}

$productModel = new Product($db);

// Obtener parámetros de filtrado
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$marca = $_GET['marca'] ?? '';

// Obtener productos según filtros
try {
    if ($search) {
        $products = $productModel->search($search);
        $page_title = "Búsqueda: " . htmlspecialchars($search);
    } elseif ($category) {
        $products = $productModel->getByCategory($category);
        $page_title = "Categoría: " . htmlspecialchars($category);
    } elseif ($marca) {
        $products = $productModel->getByBrand($marca);
        $page_title = "Marca: " . htmlspecialchars($marca);
    } else {
        $products = $productModel->getAll();
        $page_title = "Todos los Productos";
    }
} catch (Exception $e) {
    die("<div style='padding:20px; background:#f8d7da; color:#721c24; border-radius:5px;'>
        <h3>❌ Error al obtener productos</h3>
        <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
    </div>");
}

// Obtener datos para filtros
try {
    $categories_result = $productModel->getCategories();
    $marcas_result = $productModel->getBrands();

    $categories = [];
    foreach ($categories_result as $cat) {
        if (!empty($cat['categoria'])) {
            $categories[] = $cat['categoria'];
        }
    }

    $marcas = [];
    foreach ($marcas_result as $brand) {
        if (!empty($brand['marca'])) {
            $marcas[] = $brand['marca'];
        }
    }
} catch (Exception $e) {
    $categories = [];
    $marcas = [];
}

// Incluir header
include 'views/header.php';

// Mostrar mensajes de éxito/error
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>{$_SESSION['success_message']}</div>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<div class='alert alert-danger'>{$_SESSION['error_message']}</div>";
    unset($_SESSION['error_message']);
}
?>

<div class="container">
    <div class="page-header">
        <h1><?php echo $page_title; ?></h1>
        
        <?php if (isLoggedIn() && $_SESSION['user_rol_id'] == 1): ?>
            <div class="admin-actions-header">
                <a href="add-product.php" class="btn btn-success">➕ Agregar Producto</a>
                <?php if (function_exists('scanAndAssociateImages')): ?>
                    <a href="products.php?action=scan_images" 
                       class="btn btn-info"
                       onclick="return confirm('¿Escanear carpeta img y asociar imágenes automáticamente?')">
                       🔍 Asociar Imágenes
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="filters">
        <form method="GET" class="search-form">
            <div class="input-group">
                <input type="text" name="search" placeholder="Buscar por código, modelo o marca..."
                    value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                <button type="submit" class="btn btn-primary">🔍 Buscar</button>
            </div>
        </form>

        <!-- Filtro por categoría -->
        <div class="categories-filter">
            <strong>Filtrar por categoría:</strong>
            <a href="products.php" class="category-filter <?php echo empty($category) ? 'active' : ''; ?>">Todas</a>
            <?php foreach ($categories as $cat): ?>
                <a href="products.php?category=<?php echo urlencode($cat); ?>"
                    class="category-filter <?php echo $category == $cat ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Filtro por marca -->
        <div class="brands-filter">
            <strong>Filtrar por marca:</strong>
            <a href="products.php" class="brand-filter <?php echo empty($marca) ? 'active' : ''; ?>">Todas</a>
            <?php foreach ($marcas as $brand): ?>
                <a href="products.php?marca=<?php echo urlencode($brand); ?>"
                    class="brand-filter <?php echo $marca == $brand ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($brand); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Grid de productos -->
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div class="no-products">
                <h3>No se encontraron productos</h3>
                <p>Intenta con otros términos de búsqueda o revisa nuestros filtros.</p>
                <?php if (isLoggedIn() && $_SESSION['user_rol_id'] == 1): ?>
                    <p><a href="add-product.php" class="btn btn-success">➕ Agregar primer producto</a></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <div class="product-image">
                            <?php 
                            $imageSrc = getProductImage($product);
                            $isPlaceholder = strpos($imageSrc, 'placeholder.com') !== false;
                            ?>
                            <img src="<?php echo $imageSrc; ?>"
                                alt="<?php echo htmlspecialchars($product['modelo']); ?>"
                                class="product-img <?php echo $isPlaceholder ? 'placeholder-img' : 'real-img'; ?>"
                                loading="lazy"
                                onerror="this.onerror=null; this.src='https://via.placeholder.com/500x300/9C27B0/ffffff?text=<?php echo urlencode(substr($product['modelo'], 0, 15)); ?>'">
                            
                            <!-- Indicador de tipo de imagen -->
                            <?php if ($isPlaceholder): ?>
                                <div class="image-type-badge placeholder">Placeholder</div>
                            <?php else: ?>
                                <div class="image-type-badge real">Imagen Real</div>
                            <?php endif; ?>

                            <!-- Badges para productos de reparación -->
                            <div class="product-badges">
                                <?php if (!empty($product['calidad'])): ?>
                                    <?php if (strtolower($product['calidad']) == 'original'): ?>
                                        <span class="badge original">✅ <?php echo htmlspecialchars($product['calidad']); ?></span>
                                    <?php elseif (strtolower($product['calidad']) == 'alternativo'): ?>
                                        <span class="badge alternative">🔄 <?php echo htmlspecialchars($product['calidad']); ?></span>
                                    <?php else: ?>
                                        <span class="badge quality">⭐ <?php echo htmlspecialchars($product['calidad']); ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="product-info">
                        <!-- Código y modelo -->
                        <div class="product-code-model">
                            <?php if (!empty($product['codigo'])): ?>
                                <span class="product-code">📦 Código: <?php echo htmlspecialchars($product['codigo']); ?></span>
                            <?php endif; ?>
                            <h3 class="product-model"><?php echo htmlspecialchars($product['modelo']); ?></h3>
                        </div>

                        <!-- Marca y categoría -->
                        <div class="product-brand-category">
                            <span class="product-brand">📱 <?php echo htmlspecialchars($product['marca']); ?></span>
                            <?php if (!empty($product['categoria'])): ?>
                                <span class="product-category">📂 <?php echo htmlspecialchars($product['categoria']); ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Color (si existe) -->
                        <?php if (!empty($product['color'])): ?>
                            <div class="product-color">
                                <span>🎨 Color: <?php echo htmlspecialchars($product['color']); ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Precio -->
                        <div class="product-price">
                            <span class="current-price">
                                ₲<?php echo number_format($product['precio'], 0, ',', '.'); ?>
                            </span>
                        </div>

                        <!-- Fechas -->
                        <div class="product-dates">
                            <small>📅 Agregado: <?php echo date('d/m/Y', strtotime($product['created_at'])); ?></small>
                            <?php if (isset($product['updated_at']) && $product['updated_at'] != $product['created_at']): ?>
                                <small>🔄 Actualizado: <?php echo date('d/m/Y', strtotime($product['updated_at'])); ?></small>
                            <?php endif; ?>
                        </div>

                        <!-- Acciones -->
                        <?php if (isset($product['cantidad']) && $product['cantidad'] > 0): ?>
                            <form method="POST" action="add-to-cart.php" class="cart-form">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                <div class="quantity-group">
                                    <input type="number" name="quantity" min="1" max="<?= $product['cantidad'] ?>" value="1" class="qty-input">
                                    <button type="submit" class="btn btn-cart">🛒 Agregar</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="login-required">
                                <p>🔐 <a href="login.php" class="login-link">Inicia sesión</a> para comprar</p>
                            </div>
                        <?php endif; ?>

                        <!-- Botón ver detalles -->
                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-info btn-block">🔍 Ver Detalles</a>

                        <!-- Botones de administrador -->
                        <?php if (isLoggedIn() && $_SESSION['user_rol_id'] == 1): ?>
                            <div class="admin-actions">
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-warning">✏️ Editar</a>
                                <form method="POST" action="eliminar.php"
                                    class="delete-form"
                                    onsubmit="return confirm('¿Estás seguro de eliminar <?php echo addslashes($product['modelo']); ?>? Esta acción no se puede deshacer.')">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger">🗑️ Eliminar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    // Script para mejorar la interactividad
    document.addEventListener('DOMContentLoaded', function() {
        // Efecto hover en tarjetas
        const productCards = document.querySelectorAll('.product-card');
        productCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Validar cantidad en el input
        const quantityInputs = document.querySelectorAll('.qty-input');
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
                if (this.value > 10) this.value = 10;
            });
        });

        // Confirmación de eliminación mejorada
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('⚠️ ¿Estás seguro de eliminar este producto?\n\nEsta acción eliminará permanentemente el producto del sistema.\nNo se puede deshacer.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>

<?php include 'views/footer.php'; ?>