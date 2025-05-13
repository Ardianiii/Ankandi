<?php
session_start();
include 'includes/db.php';

// Fetch the latest 3 active products
$stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ankandi - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="background:#f8f9fa;">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">Ankandi</a>
        <a class="btn btn-outline-light ms-auto" href="buy_bids.php">Buy Bid Packages</a>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
    <?php
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT bid_balance FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $user['bid_balance'] ?? 0;
    ?>
    <span id="remaining-bids" class="text-white me-3">Bids Left: <?= $user['bid_balance'] ?></span>
    <a href="dashboard.php" class="btn btn-outline-light me-2">My Profile</a>
    <?php endif; ?>
</nav>

<header class="bg-primary text-white text-center py-5">
    <h1>Welcome to Ankandi</h1>
    <p class="lead">Bid smart. Win big.</p>
</header>

<div class="container my-5">
    <h2 class="mb-4">Featured Auctions</h2>
    <div class="row">
        <?php foreach ($products as $index => $product): ?>
            <?php
            // Fetch and mask the last bidder's username
            $masked = 'No bids yet';
            if ($product['last_bidder']) {
                $stmt_user = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                $stmt_user->execute([$product['last_bidder']]);
                $bidder = $stmt_user->fetch(PDO::FETCH_ASSOC);

                if ($bidder && $bidder['username']) {
                    $masked = substr($bidder['username'], 0, 3) . str_repeat('*', 4);
                }
            }
            ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <img src="uploads/<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="Product Image">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?> <?= $index === 2 ? '(Your Product)' : '' ?></h5>
                        <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                        <p>Current Price: <span id="product-price-<?= $product['id'] ?>">€<?= number_format($product['current_price'], 2) ?></span></p>
                        <p>Last Bidder: <span id="last-bidder-<?= $product['id'] ?>"><?= $masked ?></span></p>
                        <p>Time Left: <span id="countdown-<?= $product['id'] ?>">15</span> sec</p>

                        <button class="btn btn-primary bid-now-btn" data-product-id="<?= $product['id'] ?>">Bid Now</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container text-center">
        <h2>Why Use Ankandi?</h2>
        <p class="mb-1">✔ Easy to use bidding system</p>
        <p class="mb-1">✔ Only pay when you bid</p>
        <p class="mb-1">✔ Transparent auctions</p>
    </div>
</div>

<div class="container my-5">
    <h2 class="text-center mb-4">🎁 Buy Bid Packages</h2>
    <div class="row g-4">
        <!-- Starter / PLATINIUM -->
        <div class="col-md-4">
            <div class="card text-dark border-0 shadow" style="background-color: rgba(255, 255, 0, 0.2);">
                <div class="card-body d-flex flex-column justify-content-between" style="min-height: 300px;">
                    <div>
                        <h5 class="card-title text-uppercase fw-bold">Bid PLATINIUM</h5>
                        <p class="mb-1"><strong>Package:</strong> 500 Bids</p>
                        <p class="mb-1"><strong>Bonus:</strong> +10 Bids</p>
                        <p class="mb-1"><strong>Price:</strong> €200</p>
                    </div>
                    <div class="text-end mt-auto">
                        <a href="buy_bids.php?package=starter" class="btn btn-warning">Buy Now</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pro / DIAMOND -->
        <div class="col-md-4">
            <div class="card text-dark border-0 shadow" style="background-color: rgba(255, 255, 0, 0.2);">
                <div class="card-body d-flex flex-column justify-content-between" style="min-height: 300px;">
                    <div>
                        <h5 class="card-title text-uppercase fw-bold">Bid DIAMOND</h5>
                        <p class="mb-1"><strong>Package:</strong> 1000 Bids</p>
                        <p class="mb-1"><strong>Bonus:</strong> +100 Bids</p>
                        <p class="mb-1"><strong>Price:</strong> €400 <span class="text-muted"><del>€500</del></span></p>
                        <span class="badge bg-success">20% OFF</span>
                    </div>
                    <div class="text-end mt-auto">
                        <a href="buy_bids.php?package=pro" class="btn btn-warning">Buy Now</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Elite -->
        <div class="col-md-4">
            <div class="card text-dark border-0 shadow" style="background-color: rgba(255, 255, 0, 0.2);">
                <div class="card-body d-flex flex-column justify-content-between" style="min-height: 300px;">
                    <div>
                        <h5 class="card-title text-uppercase fw-bold">Bid ELITE</h5>
                        <p class="mb-1"><strong>Package:</strong> 2000 Bids</p>
                        <p class="mb-1"><strong>Bonus:</strong> +250 Bids</p>
                        <p class="mb-1"><strong>Price:</strong> €700 <span class="text-muted"><del>€1000</del></span></p>
                        <span class="badge bg-success">30% OFF</span>
                    </div>
                    <div class="text-end mt-auto">
                        <a href="buy_bids.php?package=elite" class="btn btn-warning">Buy Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="bg-dark text-white py-4 text-center">
    &copy; <?= date("Y") ?> Ankandi. All rights reserved.
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
