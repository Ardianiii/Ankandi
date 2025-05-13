<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
$user_id = $_SESSION['user_id'];

// Fetch products owned or bid on by the user
$stmt = $pdo->prepare("
    SELECT DISTINCT p.* FROM products p
    LEFT JOIN bids b ON p.id = b.auction_id
    WHERE p.user_id = :user_id OR b.user_id = :user_id
    ORDER BY p.created_at DESC
");
$stmt->execute(['user_id' => $user_id]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
<div class="container">
    <h2>My Products</h2>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Description</th>
            <th>Price</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><img src="../uploads/<?= htmlspecialchars($product['image']) ?>" width="80"></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td>€<?= number_format($product['current_price'], 2) ?></td>
                <td>
                    <?php if ($product['user_id'] === $user_id): ?>
                        <button class="btn btn-sm btn-primary edit-btn" data-product='<?= json_encode($product) ?>'>Edit</button>
                    <?php else: ?>
                        <span class="text-muted">Bidder</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm" method="POST" action="update-product.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="product_id" id="editProductId">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea class="form-control" name="description" id="editDescription" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Start Price</label>
                        <input type="number" step="0.01" class="form-control" name="start_price" id="editPrice" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const editModal = new bootstrap.Modal(document.getElementById('editModal'));

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const product = JSON.parse(btn.dataset.product);
            document.getElementById('editProductId').value = product.id;
            document.getElementById('editName').value = product.name;
            document.getElementById('editDescription').value = product.description;
            document.getElementById('editPrice').value = product.start_price;
            editModal.show();
        });
    });
</script>
</body>
</html>
