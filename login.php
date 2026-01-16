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
    
    // Validar y sanitizar entrada
    $identifier = trim($_POST['identifier']); // Campo único que puede ser email o usuario
    $password = $_POST['password'];
    
    // Validaciones básicas
    $errors = [];
    
    if (empty($identifier)) {
        $errors[] = "Por favor ingresa tu email o nombre de usuario";
    }
    
    if (empty($password)) {
        $errors[] = "Por favor ingresa tu contraseña";
    }
    
    if (empty($errors)) {
        // El método login() ya está preparado para aceptar email o usuario
        $user = $userModel->login($identifier, $password);
        
        if ($user) {
            // IMPORTANTE: Corregir 'roles_id' por 'rol_id' según tu tabla
            // Verifica cuál es el nombre correcto del campo en tu base de datos
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_usuario'] = $user['usuario'];
            $_SESSION['user_rol_id'] = $user['rol_id']; // Cambiado de 'roles_id' a 'rol_id'
            $_SESSION['login_time'] = time();
            
            // Guardar nombre completo para mostrar
            $_SESSION['user_fullname'] = $user['nombre'] . ' ' . $user['apellido'];
            
            $_SESSION['success'] = "¡Bienvenido de nuevo, " . $user['nombre'] . "!";
            header("Location: index.php");
            exit;
        } else {
            $error = "Email/Usuario o contraseña incorrectos";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

$page_title = "Iniciar Sesión";
include 'views/header.php';
?>

<div class="container">
    <div class="auth-container">
        <div class="auth-card">
            <h1>Iniciar Sesión</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="identifier">Email o Nombre de Usuario:</label>
                    <input type="text" id="identifier" name="identifier" 
                           value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>" 
                           required autocomplete="username"
                           placeholder="Ingresa tu email o nombre de usuario">
                    <small class="form-text text-muted">Puedes usar tu email o tu nombre de usuario para iniciar sesión</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" 
                           required autocomplete="current-password">
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
            </form>
            
            <div class="auth-links">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a></p>
                <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
            </div>
        </div>
    </div>
</div>

<script>
// Validación cliente para dar mejor experiencia de usuario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.auth-form');
    const identifierInput = document.getElementById('identifier');
    const passwordInput = document.getElementById('password');
    
    form.addEventListener('submit', function(e) {
        let errors = [];
        
        // Limpiar mensajes previos
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Validar identificador
        if (!identifierInput.value.trim()) {
            errors.push({field: identifierInput, message: 'Por favor ingresa tu email o usuario'});
        }
        
        // Validar contraseña
        if (!passwordInput.value.trim()) {
            errors.push({field: passwordInput, message: 'Por favor ingresa tu contraseña'});
        }
        
        // Mostrar errores
        errors.forEach(error => {
            error.field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = error.message;
            error.field.parentNode.appendChild(errorDiv);
        });
        
        if (errors.length > 0) {
            e.preventDefault();
        }
    });
    
    // Limpiar validación al escribir
    [identifierInput, passwordInput].forEach(input => {
        input.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            const errorMsg = this.parentNode.querySelector('.invalid-feedback');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });
});
</script>



<?php include 'views/footer.php'; ?>