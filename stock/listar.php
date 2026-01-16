<?php
require_once 'config.php';
require_once 'stock/listar.php';

// Verificar permisos
if (!isLoggedIn() || $_SESSION['user_rol_id'] != 1) {
    $_SESSION['error'] = "No tienes permisos";
    header("Location: ../login.php");
    exit;
}

$stock = new Stock();
$registros = $stock->obtenerTodos();

include '../views/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Gestión de Stock</h1>
        <div class="header-actions">
            <a href="agregar.php" class="btn btn-primary">Agregar Stock</a>
            <a href="../productos/agregar.php" class="btn btn-secondary">Nuevo Producto</a>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Modelo</th>
                    <th>Marca</th>
                    <th>Cantidad</th>
                    <th>Ubicación</th>
                    <th>Lote</th>
                    <th>Proveedor</th>
                    <th>Fecha Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No hay registros de stock</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registros as $registro): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($registro['producto_codigo']); ?></td>
                        <td><?php echo htmlspecialchars($registro['producto_modelo']); ?></td>
                        <td><?php echo htmlspecialchars($registro['marca']); ?></td>
                        <td>
                            <span class="<?php echo ($registro['cantidad'] <= $registro['cantidad_minima']) ? 'stock-bajo' : 'stock-normal'; ?>">
                                <?php echo $registro['cantidad']; ?>
                            </span>
                            <?php if ($registro['cantidad_minima'] > 0): ?>
                                <small>(Mín: <?php echo $registro['cantidad_minima']; ?>)</small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($registro['ubicacion']); ?></td>
                        <td><?php echo htmlspecialchars($registro['lote']); ?></td>
                        <td><?php echo htmlspecialchars($registro['proveedor']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($registro['fecha_ingreso'])); ?></td>
                        <td class="actions">
                            <a href="editar.php?id=<?php echo $registro['id']; ?>" class="btn btn-sm">Editar</a>
                            <a href="eliminar.php?id=<?php echo $registro['id']; ?>" 
                               class="btn btn-sm btn-danger" 
                               onclick="return confirm('¿Eliminar este registro?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.stock-bajo {
    color: #dc3545;
    font-weight: bold;
}
.stock-normal {
    color: #28a745;
}
</style>

<?php include '../views/footer.php'; ?>