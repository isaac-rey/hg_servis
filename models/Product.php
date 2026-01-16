<?php
class Product
{
    private $conn;
    private $table = 'productos';
    private $stockTable = 'stock';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* =========================
       CONSULTAS GENERALES
    ==========================*/

    // Productos destacados
    public function getFeatured($limit = 6)
    {
        $query = "
            SELECT p.*, IFNULL(s.cantidad, 0) AS cantidad
            FROM {$this->table} p
            LEFT JOIN {$this->stockTable} s ON s.producto_id = p.id
            WHERE p.precio > 0
            ORDER BY p.precio DESC, p.id DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAll()
    {
        $query = "
            SELECT p.*, IFNULL(s.cantidad, 0) AS cantidad
            FROM {$this->table} p
            LEFT JOIN {$this->stockTable} s ON s.producto_id = p.id
            WHERE p.precio > 0
            ORDER BY p.marca, p.modelo
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Productos por categoría
    public function getByCategory($category)
    {
        $query = "
            SELECT p.*, IFNULL(s.cantidad, 0) AS cantidad
            FROM {$this->table} p
            LEFT JOIN {$this->stockTable} s ON s.producto_id = p.id
            WHERE p.categoria = ? AND p.precio > 0
            ORDER BY p.precio DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$category]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Producto por ID (🔥 ESTE ES EL MÁS IMPORTANTE PARA product-detail.php)
    public function getById($id)
    {
        $query = "
            SELECT p.*, IFNULL(s.cantidad, 0) AS cantidad
            FROM {$this->table} p
            LEFT JOIN {$this->stockTable} s ON s.producto_id = p.id
            WHERE p.id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Buscar productos
    public function search($term)
    {
        $query = "
            SELECT p.*, IFNULL(s.cantidad, 0) AS cantidad
            FROM {$this->table} p
            LEFT JOIN {$this->stockTable} s ON s.producto_id = p.id
            WHERE (p.modelo LIKE ? OR p.marca LIKE ? OR p.categoria LIKE ?)
            AND p.precio > 0
            ORDER BY p.marca, p.modelo
        ";

        $term = "%$term%";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$term, $term, $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       LISTAS AUXILIARES
    ==========================*/

    public function getCategories()
    {
        $query = "SELECT DISTINCT categoria FROM {$this->table}
                  WHERE categoria IS NOT NULL AND categoria != ''
                  ORDER BY categoria";

        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Productos por marca
    public function getBrands()
    {
        $query = "SELECT DISTINCT marca FROM {$this->table}
                  WHERE marca IS NOT NULL AND marca != ''
                  ORDER BY marca";

        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getColors()
    {
        $query = "SELECT DISTINCT color FROM {$this->table}
                  WHERE color IS NOT NULL AND color != ''
                  ORDER BY color";

        return $this->conn->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =========================
       CRUD
    ==========================*/

    public function create($data)
    {
        $query = "
            INSERT INTO {$this->table}
            (modelo, marca, calidad, color, categoria, precio, created_at, updated_at)
            VALUES (:modelo, :marca, :calidad, :color, :categoria, :precio, NOW(), NOW())
        ";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':modelo' => $data['modelo'],
            ':marca' => $data['marca'],
            ':calidad' => $data['calidad'] ?? null,
            ':color' => $data['color'] ?? null,
            ':categoria' => $data['categoria'] ?? null,
            ':precio' => $data['precio']
        ]);
    }

    public function update($id, $data)
    {
        $query = "
            UPDATE {$this->table} SET
            modelo = :modelo,
            marca = :marca,
            calidad = :calidad,
            color = :color,
            categoria = :categoria,
            precio = :precio,
            updated_at = NOW()
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':modelo' => $data['modelo'],
            ':marca' => $data['marca'],
            ':calidad' => $data['calidad'] ?? null,
            ':color' => $data['color'] ?? null,
            ':categoria' => $data['categoria'] ?? null,
            ':precio' => $data['precio'],
            ':id' => $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function exists($id)
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }
    // Productos por marca
    public function getByBrand($brand) {
        $query = "
            SELECT p.*, IFNULL(s.cantidad, 0) AS cantidad
            FROM {$this->table} p
            LEFT JOIN {$this->stockTable} s ON s.producto_id = p.id
            WHERE p.marca = ? AND p.precio > 0
            ORDER BY p.precio DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$brand]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
