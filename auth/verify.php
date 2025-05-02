<?php
include '../includes/db.php';

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    // Check if a user exists with this code
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_code = ?");
    $stmt->execute([$verification_code]);
    $user = $stmt->fetch();

    if ($user) {
        // Update user to set verified
        $update = $pdo->prepare("UPDATE users SET verification_code = NULL, is_verified = 1 WHERE id = ?");
        $update->execute([$user['id']]);
        echo "Email verified successfully! You can now <a href='../auth/login.php'>login</a>.";
    } else {
        echo "Invalid verification code.";
    }
} else {
    echo "No verification code provided.";
}
?>
