<?php
include '../includes/db.php'; // Include database connection

// Get POST data from the AJAX request
$product_id = $_POST['product_id'];
$user_id = $_POST['user_id'];
$new_price = $_POST['price'];
$timer = $_POST['timer'];

// Update product price
$sql = "UPDATE products SET current_price = :price, auction_end_time = NOW() + INTERVAL 15 SECOND WHERE id = :product_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['price' => $new_price, 'product_id' => $product_id]);

// Deduct 1 bid from the user's balance
$sql = "UPDATE users SET bid_balance = bid_balance - 1 WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['user_id' => $user_id]);

echo "Bid placed successfully!";
?>
