<?php

class Order
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function createOrderWithItems(
        $usuario_id,
        $items,
        $total,
        $direccion,
        $telefono,
        $ruc_cliente,
        $metodo_pago,
        $notas,
        $envio_seleccionado,
        $nombre,
        $apellido,
        $email,
        $condicion_pago = 'Contado'
    ) {
        try {
            // 🔐 INICIAR TRANSACCIÓN
            $this->conn->beginTransaction();

            /* =========================
               CALCULAR SUBTOTAL
            =========================*/
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item['precio'] * $item['cantidad'];
            }

            // 🚚 ENVÍO EN GUARANÍES
            $envio = ($envio_seleccionado === 'si') ? 10000 : 0;

            /* =========================
               INSERTAR VENTA
            =========================*/
            $sqlVenta = "
                INSERT INTO ventas (
                    usuario_id,
                    ruc_cliente,
                    nombre_cliente,
                    apellido_cliente,
                    email_cliente,
                    telefono_cliente,
                    direccion_entrega,
                    metodo_pago,
                    condicion_pago,
                    subtotal,
                    envio,
                    total,
                    notas,
                    estado,
                    fecha
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', NOW())
            ";

            $stmtVenta = $this->conn->prepare($sqlVenta);
            $stmtVenta->execute([
                $usuario_id,
                $ruc_cliente,
                $nombre,
                $apellido,
                $email,
                $telefono,
                $direccion,
                $metodo_pago,
                $condicion_pago,
                $subtotal,
                $envio,
                $total,
                $notas
            ]);

            $venta_id = $this->conn->lastInsertId();

            /* =========================
               INSERTAR DETALLE + STOCK + MOVIMIENTOS
            =========================*/
            foreach ($items as $item) {
                $producto_id = $item['id'];
                $cantidad = $item['cantidad'];

                // 🔎 Verificar stock y obtener cantidad actual
                $stmtStock = $this->conn->prepare(
                    "SELECT cantidad FROM stock WHERE producto_id = ? FOR UPDATE"
                );
                $stmtStock->execute([$producto_id]);
                $stock = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$stock || $stock['cantidad'] < $cantidad) {
                    throw new Exception("Stock insuficiente para el producto ID {$producto_id}");
                }

                $stock_anterior = (int)$stock['cantidad'];
                $stock_actual = $stock_anterior - $cantidad;

                // 🧾 Insertar detalle
                $sqlDetalle = "
                    INSERT INTO venta_detalle (
                        venta_id,
                        producto_id,
                        marca,
                        modelo,
                        cantidad,
                        precio,
                        subtotal
                    ) VALUES (?, ?, ?, ?, ?, ?, ?)
                ";

                $stmtDetalle = $this->conn->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    $venta_id,
                    $producto_id,
                    $item['marca'],
                    $item['modelo'] ?? '',
                    $cantidad,
                    $item['precio'],
                    $item['precio'] * $cantidad
                ]);

                // 📉 Descontar stock
                $stmtUpdate = $this->conn->prepare(
                    "UPDATE stock SET cantidad = ? WHERE producto_id = ?"
                );
                $stmtUpdate->execute([
                    $stock_actual,
                    $producto_id
                ]);

                // 📝 REGISTRAR MOVIMIENTO EN movimientos_stock (ESTO ES LO QUE FALTABA)
                $sqlMovimiento = "
                    INSERT INTO movimientos_stock (
                        producto_id,
                        tipo,
                        cantidad,
                        stock_anterior,
                        stock_actual,
                        motivo,
                        usuario,
                        fecha
                    ) VALUES (?, 'SALIDA', ?, ?, ?, ?, ?, NOW())
                ";

                $stmtMovimiento = $this->conn->prepare($sqlMovimiento);
                $stmtMovimiento->execute([
                    $producto_id,
                    $cantidad,
                    $stock_anterior,
                    $stock_actual,
                    "Venta #{$venta_id}",
                    $_SESSION['user_name'] ?? 'sistema' // Obtener usuario de sesión
                ]);

                error_log("Movimiento registrado: Producto {$producto_id}, Venta #{$venta_id}");
            }

            // ✅ CONFIRMAR TODO
            $this->conn->commit();
            
            // Registrar en logs
            error_log("✅ Venta #{$venta_id} creada exitosamente con " . count($items) . " productos");
            
            return $venta_id;

        } catch (Exception $e) {
            // ❌ DESHACER TODO
            $this->conn->rollBack();
            error_log("❌ ERROR en venta: " . $e->getMessage());
            return false;
        }
    }

    /* =========================
       OBTENER NOMBRE DE USUARIO DESDE SESIÓN
       (método auxiliar)
    =========================*/
    private function getUsuarioActual()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'sistema';
    }

    /* =========================
       CONSULTAS
    =========================*/
    public function getAllOrders()
    {
        $sql = "SELECT * FROM ventas ORDER BY id DESC";
        return $this->conn->query($sql);
    }

    public function getOrderById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM ventas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($venta_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM venta_detalle WHERE venta_id = ?"
        );
        $stmt->execute([$venta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsInvoiced($id)
    {
        $stmt = $this->conn->prepare(
            "UPDATE ventas SET estado = 'facturado' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    /* =========================
       MÉTODO PARA VERIFICAR MOVIMIENTOS
    =========================*/
    public function verificarMovimientosVenta($venta_id)
    {
        try {
            // Obtener productos de esta venta
            $stmt = $this->conn->prepare(
                "SELECT producto_id FROM venta_detalle WHERE venta_id = ?"
            );
            $stmt->execute([$venta_id]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $resultados = [];
            foreach ($productos as $producto) {
                $stmt_mov = $this->conn->prepare(
                    "SELECT * FROM movimientos_stock 
                     WHERE producto_id = ? AND motivo LIKE ? 
                     ORDER BY fecha DESC LIMIT 1"
                );
                $stmt_mov->execute([$producto['producto_id'], "%Venta #{$venta_id}%"]);
                $movimiento = $stmt_mov->fetch(PDO::FETCH_ASSOC);
                
                $resultados[$producto['producto_id']] = $movimiento ? "REGISTRADO" : "NO REGISTRADO";
            }
            
            return $resultados;
        } catch (Exception $e) {
            error_log("Error al verificar movimientos: " . $e->getMessage());
            return false;
        }
    }
    
}