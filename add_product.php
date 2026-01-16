<?php
require_once 'config.php';
require_once 'models/Stock.php';

// Verificar permisos
if (!isLoggedIn() || $_SESSION['user_rol_id'] != 1) {
    $_SESSION['error'] = "No tienes permisos";
    header("Location: ../login.php");
    exit;
}

$stock = new Stock();
$error = '';
$success = '';

// Obtener productos
$productos = $stock->obtenerProductos();
$imagen_path = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =========================
    // DATOS PRODUCTO
    // =========================
    $producto_id = intval($_POST['producto_id'] ?? 0);
    $codigo = trim($_POST['codigo'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $marca = trim($_POST['marca'] ?? '');
    $calidad = trim($_POST['calidad'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $precio = floatval($_POST['precio'] ?? 0);

    // =========================
    // DATOS STOCK
    // =========================
    $cantidad = intval($_POST['cantidad'] ?? 0);
    $cantidad_minima = intval($_POST['cantidad_minima'] ?? 5);
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $proveedor = trim($_POST['proveedor'] ?? '');
    $lote = trim($_POST['lote'] ?? '');
    $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? date('Y-m-d'));

    $errors = [];

    if ($producto_id <= 0) {
        if (!$codigo) $errors[] = "El código es obligatorio";
        if (!$modelo) $errors[] = "El modelo es obligatorio";
        if (!$marca) $errors[] = "La marca es obligatoria";
        if ($precio <= 0) $errors[] = "El precio debe ser mayor a 0";
    }

    if ($cantidad <= 0) {
        $errors[] = "La cantidad debe ser mayor a 0";
    }

    // =========================
    // IMAGEN
    // =========================
    if (!empty($_FILES['imagen']['name'])) {
        $permitidos = ['image/jpeg','image/png','image/webp','image/gif'];
        if (!in_array($_FILES['imagen']['type'], $permitidos)) {
            $errors[] = "Formato de imagen no permitido";
        } else {
            $dir = 'uploads/productos/';
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $imagen_path = $dir . uniqid('prod_') . '.' . pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['imagen']['tmp_name'], $imagen_path);
        }
    }

    if (empty($errors)) {

        // =========================
        // PRODUCTO
        // =========================
        if ($producto_id > 0) {
            $producto_id_final = $producto_id;
            if ($imagen_path) {
                $stock->actualizarImagenProducto($producto_id, $imagen_path);
            }
        } else {
            $producto_id_final = $stock->crearProducto([
                'codigo' => $codigo,
                'modelo' => $modelo,
                'marca' => $marca,
                'calidad' => $calidad,
                'color' => $color,
                'categoria' => $categoria,
                'precio' => $precio,
                'imagen' => $imagen_path
            ]);

            if (!$producto_id_final) {
                $error = "Error al crear el producto";
            }
        }

        // =========================
        // STOCK (SUMA, NO DUPLICA)
        // =========================
        if (!$error && $producto_id_final) {

            $usuario = $_SESSION['usuario'] ?? 'admin';

            $ok = $stock->entradaStock([
    'producto_id'      => $producto_id_final,
    'cantidad'         => $cantidad,
    'cantidad_minima'  => $cantidad_minima,
    'ubicacion'        => $ubicacion ?: null,
    'proveedor'        => $proveedor ?: null,
    'lote'             => $lote ?: null,
    'fecha_ingreso'    => $fecha_ingreso ?: date('Y-m-d'),
    'motivo' => $producto_id > 0
        ? 'Ingreso de stock'
        : 'Stock inicial de producto nuevo'
], $usuario);

            if ($ok) {
                $success = "Stock agregado correctamente";
                $_POST = [];
            } else {
                $error = "Error al registrar el stock";
            }
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

include 'views/header.php';
?>

<div class="container">
    <div class="admin-header">
        <h1>Agregar Stock</h1>
        <a href="stock/listar.php" class="btn btn-secondary">Volver al Listado</a>
    </div>

    <div class="form-container">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="product-form" id="stockForm" enctype="multipart/form-data">
            <div class="form-section">
                <h3>Información del Producto</h3>
                
                <div class="form-group">
                    <label for="producto_id">Seleccionar Producto Existente</label>
                    <select id="producto_id" name="producto_id" class="form-control" onchange="cargarProductoExistente(this)">
                        <option value="">-- Seleccionar producto existente --</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id']; ?>"
                                data-codigo="<?php echo htmlspecialchars($producto['codigo']); ?>"
                                data-modelo="<?php echo htmlspecialchars($producto['modelo']); ?>"
                                data-marca="<?php echo htmlspecialchars($producto['marca']); ?>"
                                data-calidad="<?php echo htmlspecialchars($producto['calidad'] ?? ''); ?>"
                                data-color="<?php echo htmlspecialchars($producto['color'] ?? ''); ?>"
                                data-categoria="<?php echo htmlspecialchars($producto['categoria'] ?? ''); ?>"
                                data-precio="<?php echo $producto['precio'] ?? 0; ?>"
                                data-imagen="<?php echo $producto['imagen'] ?? ''; ?>"
                                <?php echo (isset($_POST['producto_id']) && $_POST['producto_id'] == $producto['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($producto['codigo'] . ' - ' . $producto['modelo'] . ' - ' . $producto['marca']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">O complete los campos a continuación para crear un nuevo producto</small>
                </div>
                
                <!-- Sección de Imagen -->
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="imagen">Imagen del Producto</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="imagen" name="imagen" 
                                   accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                   onchange="previewImage(this)">
                            <label class="custom-file-label" for="imagen">Seleccionar imagen...</label>
                        </div>
                        <small class="form-text text-muted">Formatos permitidos: JPG, PNG, GIF, WebP. Máximo 5MB.</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <div class="image-preview-container">
                            <img id="imagePreview" src="<?php echo !empty($producto['imagen']) ? htmlspecialchars($producto['imagen']) : 'img/placeholder.png'; ?>" 
                                 alt="Vista previa de la imagen" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px; max-height: 200px;">
                            <small id="currentImageName" class="form-text text-muted mt-2"></small>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="codigo">Código *</label>
                        <input type="text" id="codigo" name="codigo" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['codigo'] ?? ''); ?>" 
                               placeholder="Código único">
                    </div>

                    <div class="form-group col-md-3">
                        <label for="modelo">Modelo *</label>
                        <input type="text" id="modelo" name="modelo" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['modelo'] ?? ''); ?>"
                               placeholder="Modelo del producto">
                    </div>
                    
                    <div class="form-group col-md-3">
                        <label for="marca">Marca *</label>
                        <input type="text" id="marca" name="marca" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['marca'] ?? ''); ?>"
                               placeholder="Marca del producto">
                    </div>
                    
                    <div class="form-group col-md-3">
                        <label for="calidad">Calidad</label>
                        <select id="calidad" name="calidad" class="form-control">
                            <option value="">Seleccionar calidad</option>
                            <option value="Alta" <?php echo (isset($_POST['calidad']) && $_POST['calidad'] == 'Alta') ? 'selected' : ''; ?>>Alta</option>
                            <option value="Media" <?php echo (isset($_POST['calidad']) && $_POST['calidad'] == 'Media') ? 'selected' : ''; ?>>Media</option>
                            <option value="Económica" <?php echo (isset($_POST['calidad']) && $_POST['calidad'] == 'Económica') ? 'selected' : ''; ?>>Económica</option>
                            <option value="Premium" <?php echo (isset($_POST['calidad']) && $_POST['calidad'] == 'Premium') ? 'selected' : ''; ?>>Premium</option>
                            <option value="Estándar" <?php echo (isset($_POST['calidad']) && $_POST['calidad'] == 'Estándar') ? 'selected' : ''; ?>>Estándar</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['color'] ?? ''); ?>"
                               placeholder="Color del producto">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="categoria">Categoría</label>
                        <input type="text" id="categoria" name="categoria" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['categoria'] ?? ''); ?>"
                               placeholder="Ej: Electrónica, Ropa, Hogar">
                    </div>
                    
                    <div class="form-group col-md-4">
                        <label for="precio">Precio</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" id="precio" name="precio" class="form-control"
                                   step="0.01" min="0" 
                                   value="<?php echo htmlspecialchars($_POST['precio'] ?? ''); ?>"
                                   placeholder="0.00">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>Información del Stock</h3>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="cantidad">Cantidad *</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control"
                               min="1" required 
                               value="<?php echo $_POST['cantidad'] ?? 1; ?>">
                        <small class="form-text text-muted">Cantidad a agregar al stock</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="cantidad_minima">Cantidad Mínima</label>
                        <input type="number" id="cantidad_minima" name="cantidad_minima" class="form-control"
                               min="0" 
                               value="<?php echo $_POST['cantidad_minima'] ?? 5; ?>">
                        <small class="form-text text-muted">Stock mínimo para alertas</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['ubicacion'] ?? ''); ?>"
                               placeholder="Ej: Estante A-1, Bodega 2">
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="proveedor">Proveedor</label>
                        <input type="text" id="proveedor" name="proveedor" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['proveedor'] ?? ''); ?>"
                               placeholder="Nombre del proveedor">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="lote">Número de Lote</label>
                        <input type="text" id="lote" name="lote" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['lote'] ?? ''); ?>"
                               placeholder="Ej: LOTE-2024-001">
                        <small class="form-text text-muted">Generado automáticamente si se deja vacío</small>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="fecha_ingreso">Fecha de Ingreso</label>
                        <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="form-control"
                               value="<?php echo $_POST['fecha_ingreso'] ?? date('Y-m-d'); ?>">
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar Producto y Stock</button>
                <button type="button" class="btn btn-secondary" onclick="limpiarFormulario()">Limpiar</button>
                <a href="listar.php" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
function cargarProductoExistente(select) {
    const productoId = select.value;
    const campos = ['codigo', 'modelo', 'marca', 'calidad', 'color', 'categoria', 'precio'];
    
    if (productoId) {
        // Obtener datos del option seleccionado
        const selectedOption = select.options[select.selectedIndex];
        
        // Rellenar campos con datos del producto
        campos.forEach(campo => {
            const input = document.getElementById(campo);
            if (input) {
                const valor = selectedOption.getAttribute('data-' + campo) || '';
                input.value = valor;
                
                // Deshabilitar campos para producto existente (excepto precio)
                if (campo !== 'precio') {
                    input.disabled = true;
                }
            }
        });
        
        // Cargar imagen existente si existe
        const imagenUrl = selectedOption.getAttribute('data-imagen');
        const preview = document.getElementById('imagePreview');
        if (imagenUrl) {
            preview.src = imagenUrl;
            preview.style.display = 'block';
        } else {
            preview.src = 'img/placeholder.png';
        }
    } else {
        // Habilitar todos los campos para nuevo producto
        campos.forEach(campo => {
            const input = document.getElementById(campo);
            if (input) {
                input.disabled = false;
            }
        });
        
        // Restaurar imagen por defecto
        document.getElementById('imagePreview').src = 'img/placeholder.png';
    }
}

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const fileNameLabel = document.getElementById('currentImageName');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(file);
        
        // Mostrar nombre del archivo
        fileNameLabel.textContent = "Archivo: " + file.name;
        
        // Actualizar label del custom file input
        const label = input.nextElementSibling;
        label.textContent = file.name;
    }
}

function limpiarFormulario() {
    if (confirm('¿Está seguro de que desea limpiar todos los campos?')) {
        document.getElementById('stockForm').reset();
        document.getElementById('producto_id').value = '';
        
        // Restaurar imagen por defecto
        document.getElementById('imagePreview').src = 'img/placeholder.png';
        document.getElementById('currentImageName').textContent = '';
        
        // Restaurar label del file input
        document.querySelector('.custom-file-label').textContent = 'Seleccionar imagen...';
        
        // Habilitar todos los campos
        const campos = ['codigo', 'modelo', 'marca', 'calidad', 'color', 'categoria', 'precio'];
        campos.forEach(campo => {
            const input = document.getElementById(campo);
            if (input) {
                input.disabled = false;
            }
        });
    }
}

// Generar número de lote automático si está vacío
document.getElementById('fecha_ingreso').addEventListener('change', function() {
    const loteInput = document.getElementById('lote');
    if (!loteInput.value.trim()) {
        const fecha = this.value.replace(/-/g, '');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        loteInput.value = 'LOTE-' + fecha.substring(2) + '-' + random;
    }
});

// Generar código automático basado en modelo y marca
document.getElementById('modelo').addEventListener('blur', function() {
    const codigoInput = document.getElementById('codigo');
    const modelo = this.value.trim().toUpperCase();
    const marca = document.getElementById('marca').value.trim().toUpperCase();
    
    if (!codigoInput.value && modelo && marca) {
        // Generar código único
        const timestamp = Date.now().toString().slice(-4);
        const codigo = marca.substring(0, 3) + modelo.substring(0, 3) + timestamp;
        codigoInput.value = codigo;
    }
});

// Validar tamaño de imagen
document.getElementById('imagen').addEventListener('change', function(e) {
    const file = this.files[0];
    if (file) {
        const maxSize = 5 * 1024 * 1024; // 5MB
        
        if (file.size > maxSize) {
            alert('La imagen es demasiado grande. El tamaño máximo es 5MB.');
            this.value = ''; // Limpiar input
            document.querySelector('.custom-file-label').textContent = 'Seleccionar imagen...';
            document.getElementById('imagePreview').src = 'img/placeholder.png';
        }
    }
});

// Validación antes de enviar
document.getElementById('stockForm').addEventListener('submit', function(e) {
    const productoId = document.getElementById('producto_id').value;
    const codigo = document.getElementById('codigo').value.trim();
    const modelo = document.getElementById('modelo').value.trim();
    const marca = document.getElementById('marca').value.trim();
    const precio = document.getElementById('precio').value;
    const cantidad = document.getElementById('cantidad').value;
    const imagenInput = document.getElementById('imagen');
    
    let errores = [];
    
    // Validar producto
    if (!productoId) {
        if (!codigo) errores.push('El código del producto es obligatorio');
        if (!modelo) errores.push('El modelo del producto es obligatorio');
        if (!marca) errores.push('La marca del producto es obligatoria');
        if (!precio || parseFloat(precio) <= 0) errores.push('El precio debe ser mayor a 0');
    }
    
    // Validar stock
    if (!cantidad || parseInt(cantidad) <= 0) errores.push('La cantidad debe ser mayor a 0');
    
    // Validar imagen si se seleccionó
    if (imagenInput.files.length > 0) {
        const file = imagenInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!allowedTypes.includes(file.type)) {
            errores.push('Formato de imagen no válido. Use JPG, PNG, GIF o WebP.');
        }
        
        if (file.size > 5 * 1024 * 1024) {
            errores.push('La imagen no debe superar los 5MB.');
        }
    }
    
    if (errores.length > 0) {
        e.preventDefault();
        alert('Por favor corrija los siguientes errores:\n\n' + errores.join('\n'));
        return false;
    }
});

// Cargar datos si ya hay producto seleccionado al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const productoSelect = document.getElementById('producto_id');
    if (productoSelect.value) {
        cargarProductoExistente(productoSelect);
    }
    
    // Actualizar nombre del archivo en el label
    const imagenInput = document.getElementById('imagen');
    imagenInput.addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : 'Seleccionar imagen...';
        this.nextElementSibling.textContent = fileName;
    });
});
</script>



<?php include 'views/footer.php'; ?>