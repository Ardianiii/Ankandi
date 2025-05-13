<?php
session_start();
include 'includes/db.php';

$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
    exit();
}

$stmt = $pdo->prepare("SELECT current_price, last_bidder, auction_end_time FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    exit();
}

$time_left = (new DateTime($product['auction_end_time']))->getTimestamp() - time();
$minutes_left = floor($time_left / 60);
$seconds_left = $time_left % 60;

// Get masked username of the last bidder
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$product['last_bidder']]);
$last_bidder = $stmt->fetch(PDO::FETCH_ASSOC);
$maskedUsername = isset($last_bidder['username']) ? substr($last_bidder['username'], 0, 3) . '****' : 'No bids yet';

echo json_encode([
    'status' => 'success',
    'new_price' => number_format($product['current_price'], 2),
    'new_bidder' => $maskedUsername,
    'time_left' => $minutes_left . 'm ' . $seconds_left . 's'
]);
