<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/db.php';

// Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, bid_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch live auctions
$stmt = $pdo->prepare("SELECT id, name, current_price, auction_end_time, image FROM products WHERE is_active = 1");
$stmt->execute();
$auctions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Ankandi</title>
    <link rel="stylesheet" href="assets/style.css"> <!-- Optional custom CSS -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 40px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .auction-list {
            margin-top: 30px;
        }

        .auction-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            border-bottom: 1px solid #ccc;
        }

        .auction-item img {
            height: 60px;
            width: auto;
            margin-right: 20px;
        }

        .auction-details {
            flex-grow: 1;
        }

        .auction-actions {
            text-align: right;
        }
    </style>
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
                        <p><strong>Auction Ends:</strong> <?= date('d M Y H:i:s', strtotime($auction['auction_end_time'])) ?></p>
                    </div>
                    <div class="auction-actions">
                        <a class="btn" href="product.php?id=<?= $auction['id'] ?>">Place Bid</a>
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
