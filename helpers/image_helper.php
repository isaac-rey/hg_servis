<?php
// helpers/image_helper.php

/**
 * Obtiene la imagen de un producto
 * Busca primero en la base de datos, luego en la carpeta img, finalmente usa placeholder
 */
function getProductImage($product)
{
    // 1. Verificar si ya tiene imagen en la base de datos
    if (!empty($product['imagen'])) {
        // Verificar si existe físicamente en la carpeta img
        if (file_exists('img/' . $product['imagen']) && !is_dir('img/' . $product['imagen'])) {
            return 'img/' . $product['imagen'];
        }
    }
    
    // 2. Buscar imagen en la carpeta img basándose en código/modelo
    $foundImage = findImageForProduct($product);
    
    if ($foundImage) {
        // Guardar en la base de datos para futuras consultas
        updateProductImage($product['id'], $foundImage);
        return 'img/' . $foundImage;
    }
    
    // 3. Si no se encuentra, usar placeholder
    return getPlaceholderImage($product);
}

/**
 * Busca imagen en la carpeta img basándose en código, modelo o marca
 */
function findImageForProduct($product)
{
    $imageDir = 'img/';
    
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
        return null;
    }
    
    // Obtener información del producto
    $codigo = strtolower(trim($product['codigo'] ?? ''));
    $modelo = strtolower(trim($product['modelo'] ?? ''));
    $marca = strtolower(trim($product['marca'] ?? ''));
    
    // Escanear archivos en la carpeta img
    $files = scandir($imageDir);
    $images = [];
    
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && !is_dir($imageDir . $file)) {
            $images[] = $file;
        }
    }
    
    // Lista de posibles nombres de archivo basados en el producto
    $possibleNames = [];
    
    // Basado en código
    if (!empty($codigo)) {
        $cleanCode = cleanFileName($codigo);
        $possibleNames = array_merge($possibleNames, [
            $cleanCode . '.jpg',
            $cleanCode . '.jpeg',
            $cleanCode . '.png',
            $cleanCode . '.webp',
            $cleanCode . '.gif',
            str_replace('_', '-', $cleanCode) . '.jpg',
            $marca . '_' . $cleanCode . '.jpg',
        ]);
    }
    
    // Basado en modelo
    if (!empty($modelo)) {
        $cleanModel = cleanFileName($modelo);
        $possibleNames = array_merge($possibleNames, [
            $cleanModel . '.jpg',
            $cleanModel . '.jpeg',
            $cleanModel . '.png',
            $marca . '_' . $cleanModel . '.jpg',
            $marca . '-' . $cleanModel . '.jpg',
            str_replace(' ', '_', $cleanModel) . '.jpg',
        ]);
    }
    
    // Buscar coincidencias exactas primero
    foreach ($possibleNames as $possibleName) {
        if (in_array($possibleName, $images)) {
            return $possibleName;
        }
    }
    
    // Buscar coincidencias parciales
    foreach ($images as $image) {
        $imageName = strtolower(pathinfo($image, PATHINFO_FILENAME));
        
        // Verificar si el código o modelo está en el nombre de la imagen
        if (!empty($codigo) && strpos($imageName, cleanFileName($codigo)) !== false) {
            return $image;
        }
        
        if (!empty($modelo) && strpos($imageName, cleanFileName($modelo)) !== false) {
            return $image;
        }
        
        // Verificar si el nombre de la imagen está en el código o modelo
        if (!empty($codigo) && strpos(cleanFileName($codigo), $imageName) !== false) {
            return $image;
        }
        
        if (!empty($modelo) && strpos(cleanFileName($modelo), $imageName) !== false) {
            return $image;
        }
    }
    
    return null;
}

/**
 * Limpia un nombre de archivo
 */
function cleanFileName($name)
{
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]/', '_', $name);
    $name = preg_replace('/_+/', '_', $name);
    return trim($name, '_');
}

/**
 * Actualiza la imagen en la base de datos
 */
function updateProductImage($productId, $filename)
{
    global $db;
    
    if ($db && $productId) {
        try {
            $stmt = $db->prepare("UPDATE productos SET imagen = ? WHERE id = ?");
            $stmt->execute([$filename, $productId]);
            return true;
        } catch (Exception $e) {
            error_log("Error updating product image: " . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

/**
 * Genera una imagen placeholder
 */
function getPlaceholderImage($product)
{
    $modelo = $product['modelo'] ?? 'Producto';
    $marca = strtolower($product['marca'] ?? '');
    
    $brandColors = [
        'apple' => 'A2AAAD',
        'samsung' => '1428A0',
        'xiaomi' => 'FF6900',
        'motorola' => '5C92D1',
        'huawei' => 'D10B25',
        'lg' => 'A500AF',
        'nokia' => '124191',
        'sony' => '003399',
        'oppo' => '008B6E',
        'vivo' => '4169E1',
        'realme' => 'FF6B35',
        'oneplus' => 'F5010C',
        'google' => '4285F4',
    ];
    
    $color = $brandColors[$marca] ?? '9C27B0';
    $text = urlencode(substr($modelo, 0, 20));
    return "https://via.placeholder.com/500x300/{$color}/ffffff?text=" . $text;
}

/**
 * Escanea la carpeta img y asocia imágenes automáticamente
 */
function scanAndAssociateImages()
{
    global $db;
    
    if (!$db) {
        return ['success' => false, 'message' => 'No database connection'];
    }
    
    try {
        // Obtener todos los productos
        $stmt = $db->query("SELECT id, codigo, modelo, marca FROM productos");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener todas las imágenes
        $imageDir = 'img/';
        $images = [];
        
        if (is_dir($imageDir)) {
            $files = scandir($imageDir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && !is_dir($imageDir . $file)) {
                    $images[] = $file;
                }
            }
        }
        
        $updated = 0;
        $total = count($products);
        
        // Asociar imágenes
        foreach ($products as $product) {
            $foundImage = findImageForProduct($product);
            
            if ($foundImage) {
                $updateStmt = $db->prepare("UPDATE productos SET imagen = ?, updated_at = NOW() WHERE id = ?");
                $updateStmt->execute([$foundImage, $product['id']]);
                $updated++;
            }
        }
        
        return [
            'success' => true,
            'message' => "Proceso completado. Se actualizaron $updated de $total productos.",
            'updated' => $updated,
            'total' => $total
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Maneja la subida de una nueva imagen
 */
function handleImageUpload($fileInput, $productCode = '')
{
    if (!isset($fileInput['error']) || $fileInput['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al subir archivo'];
    }
    
    // Validar tipo de archivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = mime_content_type($fileInput['tmp_name']);
    
    if (!in_array($fileType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    // Validar tamaño (máximo 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($fileInput['size'] > $maxSize) {
        return ['success' => false, 'message' => 'La imagen es demasiado grande (máximo 5MB)'];
    }
    
    // Obtener extensión
    $fileExtension = strtolower(pathinfo($fileInput['name'], PATHINFO_EXTENSION));
    
    // Generar nombre único
    if (!empty($productCode)) {
        $newFileName = cleanFileName($productCode) . '.' . $fileExtension;
    } else {
        $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
    }
    
    // Directorio de destino
    $uploadDir = 'img/';
    
    // Crear directorio si no existe
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Ruta completa
    $destination = $uploadDir . $newFileName;
    
    // Mover archivo
    if (move_uploaded_file($fileInput['tmp_name'], $destination)) {
        // Crear miniaturas si es necesario
        createThumbnail($destination, $uploadDir . 'thumbs/' . $newFileName, 200, 200);
        
        return [
            'success' => true,
            'filename' => $newFileName,
            'path' => $destination,
            'message' => 'Imagen subida correctamente'
        ];
    }
    
    return ['success' => false, 'message' => 'Error al mover el archivo'];
}

/**
 * Crea una miniatura de la imagen
 */
function createThumbnail($source, $destination, $width, $height)
{
    if (!file_exists($source)) return false;
    
    // Crear directorio de miniaturas si no existe
    $thumbDir = dirname($destination);
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }
    
    // Obtener información de la imagen
    $info = getimagesize($source);
    if (!$info) return false;
    
    list($origWidth, $origHeight, $type) = $info;
    
    // Crear imagen según el tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $srcImage = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $srcImage = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $srcImage = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $srcImage = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    // Calcular nuevas dimensiones manteniendo proporción
    $ratio = $origWidth / $origHeight;
    if ($width / $height > $ratio) {
        $width = $height * $ratio;
    } else {
        $height = $width / $ratio;
    }
    
    // Crear imagen destino
    $dstImage = imagecreatetruecolor($width, $height);
    
    // Preservar transparencia para PNG y GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($dstImage, imagecolorallocatealpha($dstImage, 0, 0, 0, 127));
        imagealphablending($dstImage, false);
        imagesavealpha($dstImage, true);
    }
    
    // Redimensionar
    imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $width, $height, $origWidth, $origHeight);
    
    // Guardar miniatura
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($dstImage, $destination, 90);
            break;
        case IMAGETYPE_PNG:
            imagepng($dstImage, $destination, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($dstImage, $destination);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($dstImage, $destination, 90);
            break;
    }
    
    // Liberar memoria
    imagedestroy($srcImage);
    imagedestroy($dstImage);
    
    return file_exists($destination);
}
?>