<?php
// stock.php - VERSIÓN CORREGIDA Y COMPLETA

// 1. Cargar config.php PRIMERO (aquí está isLoggedIn())
require_once 'config.php';

// 2. Verificar permisos usando la función isLoggedIn() de config.php
if (!isLoggedIn() || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != 1) {
    $_SESSION['error'] = "No tienes permisos para acceder a esta página";
    header("Location: login.php");
    exit();
}

// 3. Cargar el modelo Stock
require_once 'models/stock.php';

// 4. Instanciar el modelo
try {
    $stockModel = new Stock();
    $registros = $stockModel->obtenerTodos();
    $stockBajo = $stockModel->obtenerStockBajo();
} catch (Exception $e) {
    die("<div class='alert alert-danger'>Error al cargar stock: " . $e->getMessage() . "</div>");
}

// 5. Cargar header (NO lo pongas antes de la verificación de permisos)
include 'views/header.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stock-bajo {
            background-color: #ffe6e6 !important;
        }

        .stock-ok {
            background-color: #e6ffe6 !important;
        }

        .card-header {
            background-color: #2c3e50;
            color: white;
        }

        .btn-custom {
            background-color: #3498db;
            color: white;
        }

        .btn-custom:hover {
            background-color: #2980b9;
        }

        .badge-stock {
            font-size: 0.9em;
            padding: 4px 8px;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.075);
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Navbar con información de usuario -->
        <nav class="navbar navbar-light bg-white shadow-sm mb-4">
            <div class="container">
                <div class="d-flex justify-content-between w-100 align-items-center">
                    <h1 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-boxes"></i> Gestión de Stock
                    </h1>
                    <div class="user-info text-end">
                        <?php if (isset($_SESSION['user_nombre'])): ?>
                            <span class="me-3">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>
                                <small class="text-muted">(Rol: <?php echo $_SESSION['user_rol_id'] ?? 'N/A'; ?>)</small>
                            </span>
                        <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Mostrar mensajes de sesión -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['mensaje']);
                unset($_SESSION['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']);
                unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Alertas de stock bajo -->
        <?php if (count($stockBajo) > 0): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5><i class="fas fa-exclamation-triangle"></i> Alertas de Stock Bajo</h5>
                <ul class="mb-0">
                    <?php foreach ($stockBajo as $item): ?>
                        <li>
                            <strong><?php echo htmlspecialchars($item['producto_modelo'] ?? $item['producto_modelo'] ?? 'Producto no encontrado'); ?></strong>
                            (Código: <?php echo htmlspecialchars($item['producto_codigo'] ?? $item['codigo'] ?? 'N/A'); ?>) -
                            Stock actual: <?php echo htmlspecialchars($item['cantidad']); ?> |
                            Mínimo requerido: <?php echo htmlspecialchars($item['cantidad_minima']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Controles superiores -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Buscar por código, producto, marca o lote..."
                        value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                        <a href="stock.php" class="btn btn-secondary">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-4 text-end">
                <a href="add_product.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo Registro
                </a>
            </div>
        </div>

        <!-- Tabla de stock -->
        <div class="card shadow">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-list"></i> Registros de Stock</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Marca</th>
                                <th>Cantidad</th>
                                <th>Mínimo</th>
                                <th>Ubicación</th>
                                <th>Proveedor</th>
                                <th>Lote</th>
                                <th>Fecha Ingreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Si hay búsqueda
                            if (isset($_GET['q']) && trim($_GET['q']) !== '' && method_exists($stockModel, 'buscar')) {
                                $termino = trim($_GET['q']);
                                $registros = $stockModel->buscar($termino);
                            } else {
                                // Si no hay búsqueda, obtener todos
                                $registros = $stockModel->obtenerTodos();
                            }

                            if (count($registros) > 0): ?>
                                <?php foreach ($registros as $registro):
                                    $claseStock = $registro['cantidad'] <= $registro['cantidad_minima'] ? 'stock-bajo' : '';
                                ?>
                                    <tr class="<?php echo $claseStock; ?>">
                                        <td>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($registro['producto_codigo'] ?? $registro['codigo'] ?? 'N/A'); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($registro['producto_modelo'] ?? $registro['marca'] ?? 'N/A'); ?></strong>
                                            <?php if (isset($registro['categoria']) && !empty($registro['categoria'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($registro['categoria']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($registro['marca'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge <?php echo $registro['cantidad'] <= $registro['cantidad_minima'] ? 'bg-danger' : 'bg-success'; ?> badge-stock">
                                                <?php echo htmlspecialchars($registro['cantidad']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($registro['cantidad_minima']); ?></td>
                                        <td>
                                            <?php if (!empty($registro['ubicacion'])): ?>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($registro['ubicacion']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($registro['proveedor'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php if (!empty($registro['lote'])): ?>
                                                <span class="badge bg-warning text-dark">
                                                    <?php echo htmlspecialchars($registro['lote']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y', strtotime($registro['fecha_ingreso'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="editar.php?id=<?php echo $registro['id']; ?>" class="btn btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar.php?id=<?php echo $registro['id']; ?>"
                                                    class="btn btn-danger"
                                                    onclick="return confirm('¿Está seguro de eliminar este registro?');"
                                                    title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                                <a href="../productos/ver.php?id=<?php echo $registro['producto_id']; ?>"
                                                    class="btn btn-info" title="Ver producto">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-box-open fa-2x text-muted mb-2"></i><br>
                                        No hay registros de stock
                                        <?php if (isset($_GET['q'])): ?>
                                            <br><small class="text-muted">No se encontraron resultados para "<?php echo htmlspecialchars($_GET['q']); ?>"</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="row">
                    <div class="col-md-6">
                        Total registros: <strong><?php echo count($registros); ?></strong> |
                        Stock bajo: <strong class="text-danger"><?php echo count($stockBajo); ?></strong>
                    </div>
                    <div class="col-md-6 text-end">
                        <?php if (count($registros) > 0): ?>
                            <a href="exportar.php?<?php echo isset($_GET['q']) ? 'q=' . urlencode($_GET['q']) : ''; ?>"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-download"></i> Exportar
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-ocultar alertas después de 5 segundos
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Función para búsqueda en tiempo real (opcional)
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="q"]');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        this.form.submit();
                    }
                });
            }
        });
    </script>

</body>

</html>
<?php include 'views/footer.php'; ?>