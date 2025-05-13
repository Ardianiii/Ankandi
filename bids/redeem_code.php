<?php
session_start();  // Start the session

include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $unique_code = $_POST['unique_code']; // Code the user received
    
    // Check if the code exists in the purchased_bids table
    $stmt = $pdo->prepare("SELECT * FROM purchased_bids WHERE code = ? AND status = 'pending'");
    $stmt->execute([$unique_code]);
    $purchase = $stmt->fetch();

    if ($purchase) {
        // Fetch the bid package details
        $package_id = $purchase['package_id'];
        $stmt = $pdo->prepare("SELECT * FROM bid_packages WHERE id = ?");
        $stmt->execute([$package_id]);
        $package = $stmt->fetch();

        if ($package) {
            // Activate the user's bids and update the status of the code to 'redeemed'
            $user_id = $_SESSION['user_id'];  // Get user ID from session
            $total_bids = $package['bids'] + $package['bonus']; // Include bonus bids

            // Update user’s bids in the database
            $stmt = $pdo->prepare("UPDATE users SET available_bids = available_bids + ? WHERE id = ?");
            $stmt->execute([$total_bids, $user_id]);

            // Mark the code as redeemed
            $stmt = $pdo->prepare("UPDATE purchased_bids SET status = 'redeemed' WHERE code = ?");
            $stmt->execute([$unique_code]);

            echo "Your bids have been successfully activated! You now have $total_bids bids.";
        } else {
            echo "Invalid package details.";
        }
    } else {
        echo "Invalid or expired code.";
    }
}
?>

<form method="POST" action="">
    <label>Enter Your Code:</label>
    <input type="text" name="unique_code" required>
    <button type="submit">Redeem Code</button>
</form>
