<?php
session_start();
header('Content-Type: application/json');
include '../includes/db.php';

$is_auto = isset($_POST['auto_bid']) && $_POST['auto_bid'] == 1;

if ($is_auto) {
    $user_id = 1; // System bot ID
} else {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
        exit();
    }
    $user_id = $_SESSION['user_id'];
}

$auction_id = $_POST['product_id'] ?? null;
if (!$auction_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid auction ID.']);
    exit();
}

// Get user
$stmt = $pdo->prepare("SELECT username, bid_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$is_auto && (!$user || $user['bid_balance'] <= 0)) {
    echo json_encode(['status' => 'error', 'message' => 'Not enough bids.']);
    exit();
}

$username = $user['username'] ?? 'AutoBid';
$maskedUsername = $is_auto ? 'Auto***' : substr($username, 0, 3) . str_repeat('*', 4);

// Get auction info
$stmt = $pdo->prepare("SELECT id, current_price, last_bid_time, auction_end_time FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$auction_id]);
$auction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$auction) {
    echo json_encode(['status' => 'error', 'message' => 'Auction not found.']);
    exit();
}

$now = new DateTime();
$end_time = new DateTime($auction['auction_end_time']);
$last_bid_time = new DateTime($auction['last_bid_time']);
$diff = $now->getTimestamp() - $last_bid_time->getTimestamp();

// If auto-bid is triggered but someone already bid recently, skip it
if ($is_auto && $diff < 15) {
    echo json_encode(['status' => 'skipped', 'message' => 'Recent bid detected, skipping auto-bid.']);
    exit();
}

// Auction expired?
if ($now > $end_time) {
    echo json_encode(['status' => 'error', 'message' => 'Auction ended.']);
    exit();
}

// Increase price
$new_price = $auction['current_price'] + 0.01;

// Deduct user bid (not for system)
if (!$is_auto) {
    $pdo->prepare("UPDATE users SET bid_balance = bid_balance - 1 WHERE id = ?")->execute([$user_id]);
}

// Extend time by 15 seconds
$new_end_time = $now->add(new DateInterval('PT15S'))->format('Y-m-d H:i:s');

// Update auction
$stmt = $pdo->prepare("UPDATE products SET current_price = ?, last_bidder = ?, last_bid_time = NOW(), auction_end_time = ? WHERE id = ?");
$stmt->execute([$new_price, $user_id, $new_end_time, $auction_id]);

// Insert bid history
$pdo->prepare("INSERT INTO bids (user_id, auction_id, bid_amount, bid_time) VALUES (?, ?, ?, NOW())")
    ->execute([$user_id, $auction_id, 0.01]);

// Return success
$response = [
    'status' => 'success',
    'new_price' => number_format($new_price, 2),
    'new_bidder' => $maskedUsername,
    'new_end_time' => $new_end_time
];

if (!$is_auto) {
    $stmt = $pdo->prepare("SELECT bid_balance FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $response['new_balance'] = $stmt->fetchColumn();
}

echo json_encode($response);
exit();
