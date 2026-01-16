<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('HTTP/1.1 401 Unauthorized');
    echo 'Debes iniciar sesión para dar like';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $user_id = $_SESSION['user_id'];
    
    try {
        $result = toggleProductLike($user_id, $product_id);
        $likes_count = getProductLikes($product_id);
        
        echo json_encode([
            'status' => 'success',
            'action' => $result,
            'likes_count' => $likes_count
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al procesar el like'
        ]);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    echo 'Solicitud inválida';
}
?>