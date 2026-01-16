<?php
require_once 'config.php';
require_once 'models/Product.php';

$database = new Database();
$db = $database->getConnection();

$productModel = new Product($db);
$featured_products = $productModel->getFeatured();
$categories = $productModel->getCategories();

$page_title = "Vida Saludable - Comida Nutritiva y Deliciosa";
$page_description = "Descubre nuestra selección de comida saludable, orgánica y nutritiva. Envío a domicilio. Opciones veganas, sin gluten y bajas en calorías.";

include 'views/header.php';
include 'views/home.php';
include 'views/footer.php';
?>