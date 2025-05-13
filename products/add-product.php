<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

include '../includes/db.php';

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_price = (float)($_POST['start_price'] ?? 0);
    $auction_end_time = $_POST['auction_end_time'] ?? '';
    $user_id = $_SESSION['user_id'];

    if (!$name || !$description || !$start_price || !$auction_end_time) {
        $message = "❌ Please fill in all fields.";
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageName = time() . '_' . basename($_FILES['image']['name']);
            $uploadDir = '../uploads/';
            $uploadPath = $uploadDir . $imageName;

            // Make sure the uploads folder exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Insert into database
                $stmt = $pdo->prepare("INSERT INTO products (user_id, name, description, image, start_price, current_price, is_active, auction_end_time, created_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW())");
                $stmt->execute([$user_id, $name, $description, $imageName, $start_price, $start_price, $auction_end_time]);

                $message = "✅ Product added successfully!";
            } else {
                $message = "❌ Failed to upload image.";
            }
        } else {
            $message = "❌ Image upload error.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product - Ankandi</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 40px; }
        .form-container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, textarea, button { width: 100%; padding: 10px; margin-bottom: 15px; border-radius: 5px; border: 1px solid #ccc; }
        button { background-color: #28a745; color: white; border: none; }
        button:hover { background-color: #218838; }
        .message { padding: 10px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New Product 🛒</h2>
        <?php if (!empty($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label>Name</label>
            <input type="text" name="name" required>

            <label>Description</label>
            <textarea name="description" rows="4" required></textarea>

            <label>Start Price (€)</label>
            <input type="number" step="0.01" name="start_price" required>

            <label>Auction End Time</label>
            <input type="datetime-local" name="auction_end_time" required>

            <label>Image</label>
            <input type="file" name="image" accept="image/*" required>

            <button type="submit">➕ Add Product</button>
        </form>
    </div>
</body>
</html>
