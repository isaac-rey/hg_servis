<?php
class User {
    private $conn;
    private $table = 'usuarios'; // Asegúrate que este nombre coincida con tu tabla

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        // Validar datos de entrada
        $required_fields = ['nombre', 'apellido', 'email', 'usuario', 'password'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return "El campo $field es obligatorio";
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return "El formato del email no es válido";
        }

        if (strlen($data['password']) < 6) {
            return "La contraseña debe tener al menos 6 caracteres";
        }

        // Verificar si el email ya existe
        if ($this->emailExists($data['email'])) {
            return "El email ya está registrado";
        }

        // Verificar si el usuario ya existe
        if ($this->usernameExists($data['usuario'])) {
            return "El nombre de usuario ya está en uso";
        }

        $query = "INSERT INTO " . $this->table . " 
                 (nombre, apellido, email, usuario, password, telefono, rol_id, activo) 
                 VALUES (?, ?, ?, ?, ?, ?, 2, 1)"; // Asumiendo rol 2 = cliente
        
        $stmt = $this->conn->prepare($query);
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        if ($stmt->execute([
            trim($data['nombre']),
            trim($data['apellido']), 
            trim($data['email']),
            trim($data['usuario']),
            $hashed_password, 
            !empty($data['telefono']) ? trim($data['telefono']) : NULL
        ])) {
            return true;
        }
        return "Error al registrar usuario: " . implode(", ", $stmt->errorInfo());
    }

    public function login($email, $password) {
        // También se puede permitir login con nombre de usuario
        $identifier = trim($email);
        
        // Buscar por email o usuario
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE (email = ? OR usuario = ?) AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Actualizar último login
            $this->updateLastLogin($user['id']);
            return $user;
        }
        return false;
    }

    private function updateLastLogin($id) {
        $query = "UPDATE " . $this->table . " SET ultimo_login = NOW() WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
    }

    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table . " WHERE usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$username]);
        return $stmt->rowCount() > 0;
    }

    public function getUserById($id) {
        $query = "SELECT u.*, r.nombre as rol_nombre 
                 FROM " . $this->table . " u 
                 LEFT JOIN roles r ON u.rol_id = r.id 
                 WHERE u.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método adicional para actualizar datos del usuario
    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        
        if (!empty($data['nombre'])) {
            $fields[] = "nombre = ?";
            $params[] = trim($data['nombre']);
        }
        
        if (!empty($data['apellido'])) {
            $fields[] = "apellido = ?";
            $params[] = trim($data['apellido']);
        }
        
        if (!empty($data['telefono'])) {
            $fields[] = "telefono = ?";
            $params[] = trim($data['telefono']);
        }
        
        if (!empty($data['direccion'])) {
            $fields[] = "direccion = ?";
            $params[] = trim($data['direccion']);
        }
        
        if (empty($fields)) {
            return "No hay datos para actualizar";
        }
        
        // Añadir updated_at
        $fields[] = "updated_at = NOW()";
        
        $params[] = $id;
        
        $query = "UPDATE " . $this->table . " SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }
}
?>