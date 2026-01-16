<?php
session_start();

class Database {
    private $host = "localhost";
    private $db_name = "hg_servi";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Agrega charset y opciones adicionales para mejor compatibilidad
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $exception) {
            // En desarrollo muestra el error, en producción solo log
            if (true) { // Cambia a false en producción
                die("<div style='background:#f8d7da; padding:20px; border-radius:5px;'>
                    <h3 style='color:#721c24;'>Error de conexión a base de datos</h3>
                    <p><strong>Mensaje:</strong> " . $exception->getMessage() . "</p>
                    <p>Verifica que:</p>
                    <ol>
                        <li>MySQL esté corriendo en XAMPP</li>
                        <li>La base de datos 'hg_servi' exista</li>
                        <li>Las credenciales sean correctas</li>
                    </ol>
                </div>");
            } else {
                error_log("Error de conexión: " . $exception->getMessage());
                return null;
            }
        }
        return $this->conn;
    }
}

// Funciones helper básicas
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCartCount() {
    $count = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['cantidad'];
        }
    }
    return $count;
}

function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
    }
    return $total;
}

// Agregar estas funciones al config.php existente
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateSession() {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Verificar tiempo de sesión (8 horas)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 28800)) {
        session_destroy();
        return false;
    }
    
    return true;
}

function requireLogin() {
    if (!validateSession()) {
        $_SESSION['error'] = "Por favor inicia sesión para acceder a esta página";
        header("Location: login.php");
        exit;
    }
}

function hasRole($requiredRole) {
    if (!isLoggedIn()) return false;
    return $_SESSION['user_roles_id'] == $requiredRole;
}

function formatPrice($precio) {
    $tipo_cambio = 7000; // Ajusta este valor según necesites
    $precio_gs = $precio * $tipo_cambio;
    return 'Gs. ' . number_format($precio_gs, 0, ',', '.');
}

function formatPriceSimple($precio_gs) {
    return 'Gs. ' . number_format($precio_gs, 0, ',', '.');
}

// Agrega estas funciones en config.php
function getProductLikes($product_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) return 0;
    
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as likes FROM producto_likes WHERE producto_id = ?");
        $stmt->execute([$product_id]);
        $result = $stmt->fetch();
        return $result['likes'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error en getProductLikes: " . $e->getMessage());
        return 0;
    }
}

function hasUserLiked($user_id, $product_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) return false;
    
    try {
        $stmt = $db->prepare("SELECT id FROM producto_likes WHERE usuario_id = ? AND producto_id = ?");
        $stmt->execute([$user_id, $product_id]);
        return $stmt->fetch() !== false;
    } catch (PDOException $e) {
        error_log("Error en hasUserLiked: " . $e->getMessage());
        return false;
    }
}

function toggleProductLike($user_id, $product_id) {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) return false;
    
    try {
        // Verificar si ya dio like
        if (hasUserLiked($user_id, $product_id)) {
            // Quitar like
            $stmt = $db->prepare("DELETE FROM producto_likes WHERE usuario_id = ? AND producto_id = ?");
            $stmt->execute([$user_id, $product_id]);
            return 'unliked';
        } else {
            // Dar like
            $stmt = $db->prepare("INSERT INTO producto_likes (usuario_id, producto_id, fecha_like) VALUES (?, ?, NOW())");
            $stmt->execute([$user_id, $product_id]);
            return 'liked';
        }
    } catch (PDOException $e) {
        error_log("Error en toggleProductLike: " . $e->getMessage());
        return false;
    }
}


    
    // Imágenes por defecto según categoría
    $defaultImages = [
        'pantalla' => 'assets/defaults/screen.jpg',
        'bateria' => 'assets/defaults/battery.jpg',
        'carcasa' => 'assets/defaults/case.jpg',
        'flex' => 'assets/defaults/flex.jpg',
        'tapa' => 'assets/defaults/back_cover.jpg',
        'cámara' => 'assets/defaults/camera.jpg',
        'conector' => 'assets/defaults/connector.jpg',
    ];
    
    $categoria = strtolower($product['categoria'] ?? '');
    foreach ($defaultImages as $key => $image) {
        if (strpos($categoria, $key) !== false) {
            return $image;
        }
    }
    
    // Imagen genérica por defecto
    return 'assets/defaults/product.jpg';


// Función para debug (solo desarrollo)
function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($die) die();
}

// Función para verificar si la tabla productos existe
function checkProductsTable() {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) return false;
    
    try {
        $stmt = $db->query("SHOW TABLES LIKE 'productos'");
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }

    
    
}
?>