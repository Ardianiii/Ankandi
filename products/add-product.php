<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

include '../includes/db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_price = (float)$_POST['start_price'] ?? 1.0;
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];

    if ($name && $description && $image_name && $start_price > 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image_name);

        if (move_uploaded_file($image_tmp, $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO products 
                (name, description, image, start_price, current_price, is_active, auction_end_time, created_at)
                VALUES (?, ?, ?, ?, ?, 1, NOW() + INTERVAL 1 DAY, NOW())");
            $stmt->execute([$name, $description, $image_name, $start_price, $start_price]);

            $message = "✅ Product added successfully!";
        } else {
            $message = "❌ Failed to upload image.";
        }
    } else {
        $message = "❗ Please fill in all fields and upload an image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Ankandi</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 40px; }
        .form-container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea { width: 100%; padding: 10px; margin-top: 10px; border-radius: 6px; border: 1px solid #ccc; }
        button { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 6px; margin-top: 15px; cursor: pointer; }
        button:hover { background: #218838; }
        .message { margin-top: 15px; color: #333; }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Add New Product 🛒</h2>
    <form action="add-product.php" method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" required>

        <label>Description:</label>
        <textarea name="description" required></textarea>

        <label>Start Price (€):</label>
        <input type="number" name="start_price" step="0.01" required>

        <label>Product Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit">➕ Add Product</button>
    </form>

    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</div>
</body>
</html>
