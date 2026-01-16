<?php
require_once 'config.php';
require_once 'models/Product.php';

if (!isLoggedIn() || $_SESSION['user_roles_id'] != 1) {
    $_SESSION['error'] = "No tienes permisos para editar productos";
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'] ?? 0;
$productModel = new Product((new Database())->getConnection());
$product = $productModel->getById($product_id);

if (!$product) {
    $_SESSION['error'] = "Producto no encontrado";
    header("Location: products.php");
    exit;
}

$page_title = "Editar Producto";
include 'views/header.php';
?>

<div class="container mt-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white text-center py-3">
            <h1 class="h3 mb-0">Editar Producto</h1>
        </div>
        <div class="card-body p-5">
            <form method="POST" action="update-product.php">
                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">

                <div class="mb-4">
                    <label class="form-label fs-5 fw-bold">Nombre</label>
                    <input type="text" name="nombre" class="form-control form-control-lg" 
                           value="<?php echo htmlspecialchars($product['nombre']); ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-bold">Precio (GS)</label>
                    <input type="number" step="0.01" name="precio" class="form-control form-control-lg" 
                           value="<?php echo $product['precio']; ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control form-control-lg" rows="4"><?php echo htmlspecialchars($product['descripcion']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-bold">Stock</label>
                    <input type="number" name="stock" class="form-control form-control-lg" 
                           value="<?php echo $product['stock']; ?>" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fs-5 fw-bold">Ingredientes</label>
                    <textarea name="ingredientes" class="form-control form-control-lg" rows="4"><?php echo htmlspecialchars($product['ingredientes']); ?></textarea>
                </div>

                <div class="row mb-4 g-4">
                    <div class="col-md-6">
                        <label class="form-label fs-5 fw-bold">¿Es vegano?</label>
                        <select name="vegano" class="form-select form-select-lg">
                            <option value="1" <?php if ($product['vegano']) echo 'selected'; ?>>Sí</option>
                            <option value="0" <?php if (!$product['vegano']) echo 'selected'; ?>>No</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fs-5 fw-bold">¿Sin gluten?</label>
                        <select name="sin_gluten" class="form-select form-select-lg">
                            <option value="1" <?php if ($product['sin_gluten']) echo 'selected'; ?>>Sí</option>
                            <option value="0" <?php if (!$product['sin_gluten']) echo 'selected'; ?>>No</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="products.php" class="btn btn-secondary btn-lg me-3">Cancelar</a>
                    <button type="submit" class="btn btn-primary btn-lg">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    body {
        font-size: 1.1rem;
    }
    .card-body label {
        letter-spacing: 0.5px;
    }
    textarea.form-control-lg {
        resize: vertical;
    }
    .form-control-lg, .form-select-lg {
        font-size: 1.1rem;
        padding: 0.75rem 1rem;
    }
</style>

<?php include 'views/footer.php'; ?>
