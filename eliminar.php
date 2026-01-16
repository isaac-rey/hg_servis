<?php
// eliminar_producto.php - VERSIÓN CORREGIDA
session_start();

require_once 'config.php';

// Verificar permisos
if (!isLoggedIn() || !isset($_SESSION['user_rol_id']) || $_SESSION['user_rol_id'] != 1) {
    $_SESSION['error'] = "No tienes permisos para realizar esta acción";
    header("Location: login.php");
    exit();
}

// Aceptar tanto GET como POST para mayor flexibilidad
$product_id = $_GET['id'] ?? $_POST['id'] ?? 0;

if (!$product_id || !is_numeric($product_id)) {
    $_SESSION['error'] = "ID de producto inválido";
    header("Location: products.php");
    exit();
}

$product_id = (int)$product_id;

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Verificar si el producto existe y obtener información
    $stmt = $db->prepare("SELECT id, codigo, modelo, marca, categoria FROM productos WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        $_SESSION['error'] = "Producto no encontrado";
        header("Location: products.php");
        exit();
    }
    
    // 2. VERIFICAR TODAS LAS TABLAS RELACIONADAS
    $tablas_relacionadas = [
        'stock' => 'producto_id',
        'ventas' => 'producto_id',
        'detalle_ventas' => 'producto_id',
        'historial_stock' => 'producto_id',
        'compras' => 'producto_id',
        // Agrega aquí todas las tablas que puedan referenciar productos
    ];
    
    $relaciones = [];
    
    foreach ($tablas_relacionadas as $tabla => $campo) {
        try {
            $check_sql = "SELECT COUNT(*) as total FROM $tabla WHERE $campo = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->execute([$product_id]);
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['total'] > 0) {
                $relaciones[] = [
                    'tabla' => $tabla,
                    'total' => $result['total']
                ];
            }
        } catch (Exception $e) {
            // La tabla puede no existir, continuar con las demás
            continue;
        }
    }
    
    // 3. Si hay relaciones, mostrar mensaje detallado
    if (!empty($relaciones)) {
        $mensaje = "❌ No se puede eliminar el producto porque tiene registros relacionados:<br>";
        
        foreach ($relaciones as $rel) {
            $mensaje .= "• Tabla <strong>{$rel['tabla']}</strong>: {$rel['total']} registro(s)<br>";
        }
        
        $mensaje .= "<br>Primero elimine estos registros o considere usar eliminación suave.";
        
        $_SESSION['error'] = $mensaje;
        header("Location: products.php");
        exit();
    }
    
    // 4. Si no hay relaciones, eliminar el producto
    $delete_stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
    
    if ($delete_stmt->execute([$product_id])) {
        $_SESSION['success'] = "✅ Producto eliminado correctamente<br>" .
                              "• Código: " . htmlspecialchars($product['codigo']) . "<br>" .
                              "• Modelo: " . htmlspecialchars($product['modelo']) . "<br>" .
                              "• Marca: " . htmlspecialchars($product['marca']);
    } else {
        $_SESSION['error'] = "❌ Error al eliminar el producto";
    }
    
} catch (PDOException $e) {
    error_log("Error deleting product ID $product_id: " . $e->getMessage());
    $_SESSION['error'] = "❌ Error en la base de datos: " . $e->getMessage();
}

header("Location: products.php");
exit();
?>