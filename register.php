<?php
require_once 'config.php';
require_once 'models/User.php';

if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $userModel = new User($db);
    
    $data = [
        'nombre' => $_POST['nombre'],
        'apellido' => $_POST['apellido'],
        'usuario' => $_POST['usuario'],
        'email' => $_POST['email'],
        'password' => $_POST['password'],
        'telefono' => $_POST['telefono']
    ];
    
    $result = $userModel->register($data);
    
    if ($result === true) {
        $_SESSION['success'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
        header("Location: login.php");
        exit;
    } else {
        $error = $result;
    }
}

$page_title = "Registrarse";
include 'views/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Crear Cuenta</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>
                <div class="form-group">
                    <label for="usuario">Usuarios:</label>
                    <input type="text" id="usuario" name="usuario" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Registrarse</button>
            </form>
            
            <p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
</div>

<?php include 'views/footer.php'; ?>