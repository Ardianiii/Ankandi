<?php
require '../includes/db.php';

$product_id = $_POST['product_id'];

$stmt = $conn->prepare("SELECT b.user_id, b.bid_amount, b.timestamp, u.username
                        FROM bids b
                        LEFT JOIN users u ON b.user_id = u.id
                        WHERE b.product_id = ?
                        ORDER BY b.timestamp DESC LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if ($data) {
    // Show "System" if bot
    if ($data['user_id'] == 1) {
        $masked = "System";
    } else {
        $username = $data['username'];
        $masked = substr($username, 0, 1) . str_repeat('*', strlen($username) - 2) . substr($username, -1);
    }

    echo json_encode([
        'status' => 'success',
        'last_bidder' => $masked,
        'new_price' => $data['bid_amount'],
        'timestamp' => $data['timestamp']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No bids yet.']);
}
