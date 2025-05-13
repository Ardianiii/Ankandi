<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/db.php';

$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$product_id) {
    die("❌ Invalid product ID.");
}

// Check if product exists and belongs to user
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("❌ Product not found or access denied.");
}

// Delete product
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);

// Optionally: delete image file
$imagePath = '../uploads/' . $product['image'];
if (file_exists($imagePath)) {
    unlink($imagePath);
}

header("Location: my-products.php");
exit();
?>
