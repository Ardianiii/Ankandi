<?php
include '../includes/db.php';

$now = new DateTime();

// Find active auctions that haven't had a bid in the last 15 seconds
$stmt = $pdo->prepare("
    SELECT id FROM products
    WHERE is_active = 1
    AND TIMESTAMPDIFF(SECOND, last_bid_time, NOW()) >= 15
    AND auction_end_time > NOW()
");
$stmt->execute();
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For each, trigger a bot bid
foreach ($auctions as $auction) {
    $ch = curl_init('http://localhost/ankandi/bids/place_bid.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'product_id' => $auction['id'],
        'auto_bid' => 1
    ]));
    curl_exec($ch);
    curl_close($ch);
}
