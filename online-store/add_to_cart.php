<?php
require_once 'inc/header.php';
header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Invalid request'];
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode($response); exit;
}
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
if($product_id <= 0) {
    $response['message'] = 'Invalid product';
    echo json_encode($response); exit;
}
// Validate product exists
$stmt = $pdo->prepare('SELECT id FROM products WHERE id = ?');
$stmt->execute([$product_id]);
if(!$stmt->fetch()) {
    $response['message'] = 'Product not found';
    echo json_encode($response); exit;
}
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if(isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = $quantity;
}
$response['success'] = true;
$response['message'] = 'Product added to cart';
$response['cart_count'] = array_sum($_SESSION['cart']);
echo json_encode($response);
