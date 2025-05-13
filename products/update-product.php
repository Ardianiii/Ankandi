<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated.']);
    exit();
}

include '../includes/db.php';

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$current_price = $_POST['current_price'] ?? 0;
$auction_end_time = $_POST['auction_end_time'] ?? '';

if (!$product_id || !$name || !$description || !$auction_end_time) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit();
}

// Only update products owned by this user
$stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, current_price = ?, auction_end_time = ? WHERE id = ? AND user_id = ?");
$success = $stmt->execute([$name, $description, $current_price, $auction_end_time, $product_id, $user_id]);

if ($success) {
    echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update product.']);
}
