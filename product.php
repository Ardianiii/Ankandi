<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include 'includes/db.php';

if (!isset($_GET['id'])) {
    die("Product ID is missing.");
}

$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product || !$product['is_active']) {
    die("Product not found or auction not active.");
}

// Fetch user
$stmt = $pdo->prepare("SELECT username, bid_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?> - Auction</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #eef2f7; padding: 40px; }
        .auction-container { max-width: 800px; margin: auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        img { max-width: 100%; border-radius: 10px; }
        .timer { font-size: 32px; color: #e63946; margin-top: 10px; }
        .btn-bid { background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-top: 20px; display: inline-block; }
        .btn-bid:hover { background: #218838; }
        .info { margin-top: 15px; }
    </style>
    <script>
        let timer = 15;
        function updateTimer() {
            const timerEl = document.getElementById('timer');
            if (timer <= 0) {
                timerEl.textContent = "⏳ Waiting for next bid...";
                // Here you can trigger a fake bid mechanism via AJAX in real use
                return;
            }
            timerEl.textContent = timer + "s";
            timer--;
        }

        setInterval(updateTimer, 1000);
    </script>
</head>
<body>
<div class="auction-container">
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
    <p><?= htmlspecialchars($product['description']) ?></p>

    <div class="info">
        <strong>Current Price:</strong> €<?= number_format($product['current_price'], 2) ?><br>
        <strong>Your Bids:</strong> <?= (int)$user['bid_balance'] ?><br>
        <strong>Time Remaining:</strong> <span id="timer">15s</span>
    </div>

    <form action="place_bid.php" method="POST">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
        <button class="btn-bid" type="submit">📈 Place Bid (Cost: 1 Bid)</button>
    </form>
</div>
</body>
</html>
