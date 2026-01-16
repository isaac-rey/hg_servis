<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Vida Saludable - Comida Nutritiva'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'Tienda de comida saludable, orgánica y nutritiva. Envío a domicilio. Opciones veganas y sin gluten.'; ?>">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/productos.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link rel="stylesheet" href="assets/css/productos1.css">
    <link rel="stylesheet" href="assets/css/add_product.css">
    <link rel="stylesheet" href="assets/css/stock.css">
    <link rel="stylesheet" href="assets/css/order.css">
    <link rel="stylesheet" href="assets/css/order-success.css">

</head>

<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <a href="index.php">📱 Hg Servi</a>
            </div>

            <nav class="nav">
                <a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Inicio</a>
                <a href="products.php" class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">Productos</a>

                <?php if (isLoggedIn()): ?>
                    <?php if ($_SESSION['user_rol_id'] == 1): ?>
                        <a href="add_product.php" class="<?php echo $current_page == 'add_product.php' ? 'active' : ''; ?>">Agregar Producto</a>
                        <a href="stock.php" class="<?php echo $current_page == 'stock.php' ? 'active' : ''; ?>">Stock</a>
                    <?php endif; ?>
                    <a href="cart.php" class="<?php echo $current_page == 'cart.php' ? 'active' : ''; ?>">
                        Carrito (<?php echo getCartCount(); ?>)
                    </a>
                    <a href="orders.php" class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">Mis Pedidos</a>
                    <a href="logout.php">Cerrar Sesión (<?php echo $_SESSION['user_name']; ?>)</a>
                <?php else: ?>
                    <a href="login.php" class="<?php echo $current_page == 'login.php' ? 'active' : ''; ?>">Ingresar</a>
                    <a href="register.php" class="<?php echo $current_page == 'register.php' ? 'active' : ''; ?>">Registrarse</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert success">
            <?php echo $_SESSION['success'];
            unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert error">
            <?php echo $_SESSION['error'];
            unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <main class="main">