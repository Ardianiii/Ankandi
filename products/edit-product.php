<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/db.php';

$product_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$product_id) {
    die("❌ Invalid product ID.");
}

// Fetch product and verify ownership
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
$stmt->execute([$product_id, $user_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("❌ Product not found or access denied.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $auction_end_time = $_POST['auction_end_time'] ?? '';
    
    $imageName = $product['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $imageName = basename($_FILES['image']['name']);
        $uploadPath = $uploadDir . $imageName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
    }

    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, image = ?, auction_end_time = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $description, $imageName, $auction_end_time, $product_id, $user_id]);

    echo "✅ Product updated successfully!";
    header("Refresh: 2; URL=my-products.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px; }
        .form-container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea, button { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #007bff; color: white; border: none; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>✏️ Edit Product</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>

            <label>Description</label>
            <textarea name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>

            <label>Auction End Time</label>
            <input type="datetime-local" name="auction_end_time" value="<?= date('Y-m-d\TH:i', strtotime($product['auction_end_time'])) ?>" required>

            <label>Replace Image (optional)</label>
            <input type="file" name="image" accept="image/*">

            <button type="submit">💾 Save Changes</button>
        </form>
    </div>
</body>
</html>
