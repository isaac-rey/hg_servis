<?php

class Stock
{
    private $conn;
    private $tabla = "stock";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /* =========================
       CONSULTAS GENERALES
    ==========================*/

    public function obtenerTodos()
    {
        $sql = "SELECT 
                s.*,
                p.codigo AS producto_codigo,
                p.modelo AS producto_modelo,
                p.marca,
                p.precio,
                p.color,
                p.categoria,
                p.calidad,
                p.imagen
            FROM {$this->tabla} s
            INNER JOIN productos p ON p.id = s.producto_id
            ORDER BY s.id DESC";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductos()
    {
        return $this->conn
            ->query("SELECT * FROM productos ORDER BY modelo ASC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       PRODUCTOS
    ==========================*/

    // 🔍 Devuelve el producto si existe
    public function obtenerProductoPorCodigo(string $codigo)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM productos WHERE codigo = ? LIMIT 1"
        );
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ Crea solo si no existe
    public function crearProducto(array $datos)
    {
        $existente = $this->obtenerProductoPorCodigo($datos['codigo']);

        if ($existente) {
            return $existente['id']; // ⬅ reutiliza
        }

        $sql = "INSERT INTO productos
                (codigo, modelo, marca, calidad, color, categoria, precio, imagen)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $datos['codigo'],
            $datos['modelo'],
            $datos['marca'],
            $datos['calidad'],
            $datos['color'],
            $datos['categoria'],
            $datos['precio'],
            $datos['imagen'] ?? null
        ]);

        return $this->conn->lastInsertId();
    }

    public function actualizarImagenProducto(int $id, string $ruta): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE productos SET imagen = ? WHERE id = ?"
        );
        return $stmt->execute([$ruta, $id]);
    }

    /* =========================
       ENTRADA DE STOCK
    ==========================*/

    public function entradaStock(array $datos, string $usuario = 'sistema'): bool
    {
        try {
            if ($datos['cantidad'] <= 0) {
                return false;
            }

            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "SELECT cantidad FROM stock WHERE producto_id = ?"
            );
            $stmt->execute([$datos['producto_id']]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            $anterior = $stock['cantidad'] ?? 0;
            $actual = $anterior + $datos['cantidad'];

            if ($stock) {
                $this->conn->prepare(
                    "UPDATE stock SET cantidad = ? WHERE producto_id = ?"
                )->execute([$actual, $datos['producto_id']]);
            } else {
                $this->conn->prepare(
                    "INSERT INTO stock
                    (producto_id, cantidad, cantidad_minima, ubicacion, proveedor, lote, fecha_ingreso)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"
                )->execute([
                    $datos['producto_id'],
                    $datos['cantidad'],
                    $datos['cantidad_minima'] ?? 1,
                    $datos['ubicacion'] ?? null,
                    $datos['proveedor'] ?? null,
                    $datos['lote'] ?? null,
                    $datos['fecha_ingreso'] ?? date('Y-m-d')
                ]);
            }

            $this->conn->prepare(
                "INSERT INTO movimientos_stock
                (producto_id, tipo, cantidad, stock_anterior, stock_actual, motivo, usuario, fecha)
                VALUES (?, 'ENTRADA', ?, ?, ?, ?, ?, NOW())"
            )->execute([
                $datos['producto_id'],
                $datos['cantidad'],
                $anterior,
                $actual,
                $datos['motivo'] ?? 'Ingreso de mercadería',
                $usuario
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error en entradaStock: " . $e->getMessage());
            return false;
        }
    }

    /* =========================
       SALIDA DE STOCK
    ==========================*/

    public function salidaStock(int $producto_id, int $cantidad, string $motivo, string $usuario = 'sistema'): bool
    {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare(
                "SELECT cantidad FROM stock WHERE producto_id = ? FOR UPDATE"
            );
            $stmt->execute([$producto_id]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$stock) {
                error_log("Producto ID $producto_id no encontrado en stock");
                $this->conn->rollBack();
                return false;
            }

            if ($stock['cantidad'] < $cantidad) {
                error_log("Stock insuficiente. Producto ID $producto_id: disponible={$stock['cantidad']}, solicitado=$cantidad");
                $this->conn->rollBack();
                return false;
            }

            $anterior = (int)$stock['cantidad'];
            $actual = $anterior - $cantidad;

            // Actualizar stock
            $stmt_update = $this->conn->prepare(
                "UPDATE stock SET cantidad = ? WHERE producto_id = ?"
            );
            $stmt_update->execute([$actual, $producto_id]);

            // Insertar movimiento
            $stmt_insert = $this->conn->prepare(
                "INSERT INTO movimientos_stock
                (producto_id, tipo, cantidad, stock_anterior, stock_actual, motivo, usuario, fecha)
                VALUES (?, 'SALIDA', ?, ?, ?, ?, ?, NOW())"
            );
            
            $stmt_insert->execute([
                $producto_id,
                $cantidad,
                $anterior,
                $actual,
                $motivo,
                $usuario
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            error_log("Error en salidaStock: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    /* =========================
       ALERTAS DE STOCK BAJO
    ==========================*/

    public function obtenerStockBajo()
    {
        $sql = "SELECT 
                s.id,
                s.producto_id,
                s.cantidad,
                s.cantidad_minima,
                s.ubicacion,
                s.proveedor,
                s.lote,
                s.fecha_ingreso,
                p.codigo AS producto_codigo,
                p.modelo AS producto_modelo,
                p.marca
            FROM {$this->tabla} s
            INNER JOIN productos p ON p.id = s.producto_id
            WHERE s.cantidad <= s.cantidad_minima
            ORDER BY s.cantidad ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ========================= 
       BÚSQUEDA DE PRODUCTOS 
    ==========================*/
    
    public function buscar($termino)
    {
        $sql = "SELECT 
                s.*, 
                p.codigo, 
                p.modelo, 
                p.marca,
                p.precio, 
                p.color, 
                p.categoria, 
                p.calidad, 
                p.imagen
            FROM {$this->tabla} s 
            INNER JOIN productos p ON p.id = s.producto_id 
            WHERE p.modelo LIKE :termino 
            OR p.marca LIKE :termino 
            OR p.categoria LIKE :termino 
            OR p.codigo LIKE :termino 
            ORDER BY p.modelo ASC";

        $stmt = $this->conn->prepare($sql);
        $likeTerm = '%' . trim($termino) . '%';
        $stmt->bindParam(':termino', $likeTerm, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       MÉTODOS ADICIONALES ÚTILES
    ==========================*/

    public function obtenerStockPorProductoId(int $producto_id)
    {
        $stmt = $this->conn->prepare(
            "SELECT s.*, p.modelo, p.marca, p.codigo 
             FROM stock s 
             INNER JOIN productos p ON p.id = s.producto_id 
             WHERE s.producto_id = ?"
        );
        $stmt->execute([$producto_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerMovimientosProducto(int $producto_id, int $limit = 10)
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM movimientos_stock 
             WHERE producto_id = ? 
             ORDER BY fecha DESC 
             LIMIT ?"
        );
        $stmt->execute([$producto_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarStockSuficiente(int $producto_id, int $cantidad): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT cantidad FROM stock WHERE producto_id = ?"
        );
        $stmt->execute([$producto_id]);
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $stock && $stock['cantidad'] >= $cantidad;
    }
}