<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/db.php';
include_once 'helpers.php';


// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, bid_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch live auctions
$stmt = $pdo->prepare("SELECT id, name, current_price, auction_end_time, image FROM products WHERE is_active = 1");
$stmt->execute();
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);

function maskUsername($username) {
    $length = strlen($username);
    if ($length <= 2) return '**';
    return substr($username, 0, 1) . str_repeat('*', $length - 2) . substr($username, -1);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Ankandi</title>
    <link rel="stylesheet" href="assets/css/dashboard.css"> 
    <script src="assets/js/script.js"></script>
</head>
<body>
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($user['username']) ?> 👋</h1>
    <p><strong>Your Bid Balance:</strong> <?= (int)$user['bid_balance'] ?> bids</p>

    <a class="btn" href="buy-bids.php">💳 Buy Bids</a>
    <a class="btn" href="auth/logout.php">🚪 Logout</a>

    <div class="auction-list">
        <h2>Live Auctions 📦</h2>

        <?php if (count($auctions) > 0): ?>
            <?php foreach ($auctions as $auction): ?>
                <div class="auction-item">
                    <img src="uploads/<?= htmlspecialchars($auction['image']) ?>" alt="<?= htmlspecialchars($auction['name']) ?>">
                    <div class="auction-details">
                        <h3><?= htmlspecialchars($auction['name']) ?></h3>
                        <p><strong>Current Price:</strong> €<?= number_format($auction['current_price'], 2) ?></p>
                        <p><strong>Retail Value:</strong> €649.00</p> <!-- Placeholder, replace if dynamic -->
                        <p><strong>Auction Ends:</strong> <?= getTimeLeft($auction['auction_end_time']) ?></p>

                        <?php
                        // Fetch last bidder (optional)
                        $lastBidStmt = $pdo->prepare("
                        SELECT u.username
                        FROM bids b
                        JOIN users u ON b.user_id = u.id
                        WHERE b.auction_id = ?
                        ORDER BY b.bid_time DESC
                        LIMIT 1
                    ");
                    $lastBidStmt->execute([$auction['id']]);
                    
                        $lastBid = $lastBidStmt->fetch(PDO::FETCH_ASSOC);
                        ?>

                        <?php if ($lastBid): ?>
                            <p><strong>Last Bidder:</strong> <?= maskUsername($lastBid['username']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="auction-actions">
                        <?php if ($user['bid_balance'] > 0): ?>
                            <a href="#" class="btn place-bid-btn" data-id="<?= $auction['id'] ?>">Place Bid</a>
                        <?php else: ?>
                            <span style="color: red;">Insufficient bids</span>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Bids Table -->
                                
                    <div class="recent-bids">
                        <h4>Recent Bids 🕑</h4>
                        <table border="1" cellpadding="5">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Bid (€)</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetching the bids for the specific auction
                                $bidStmt = $pdo->prepare("
                                    SELECT u.username, b.bid_amount, b.bid_time
                                    FROM bids b
                                    JOIN users u ON b.user_id = u.id
                                    WHERE b.auction_id = ?  -- Correct column name for auction_id
                                    ORDER BY b.bid_time DESC
                                    LIMIT 5
                                ");
                                $bidStmt->execute([$auction['id']]);

                                // Display the bid history
                                while ($bid = $bidStmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<tr>
                                        <td>" . date('d M H:i', strtotime($bid['bid_time'])) . "</td>
                                        <td>€" . number_format($bid['bid_amount'], 2) . "</td>
                                        <td>" . maskUsername($bid['username']) . "</td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No live auctions available at the moment.</p>
        <?php endif; ?>
    </div>
</div>


</body>
</html>
