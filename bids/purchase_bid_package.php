<?php
session_start();  // Start the session

include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $package_id = $_POST['package_id']; // The ID of the selected bid package

    // Fetch the bid package details from the database
    $stmt = $pdo->prepare("SELECT * FROM bid_packages WHERE id = ?");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();

    if ($package) {
        // Generate a unique code for the user
        $unique_code = $package['code_prefix'] . bin2hex(random_bytes(8)); // Random string
        
        // Store the package purchase and the code in the database
        $user_id = $_SESSION['user_id'];  // Get user ID from session
        $stmt = $pdo->prepare("INSERT INTO purchased_bids (user_id, package_id, code, status) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $package_id, $unique_code, 'pending']); // 'pending' means user has to redeem the code

        echo "Your purchase was successful! Here is your unique code: $unique_code";
    } else {
        echo "Package not found!";
    }
}
?>

<form method="POST" action="">
    <label>Select Bid Package:</label>
    <select name="package_id">
        <!-- Loop through packages -->
        <?php
        $stmt = $pdo->query("SELECT * FROM bid_packages");
        $packages = $stmt->fetchAll();
        foreach ($packages as $package) {
            echo "<option value='" . $package['id'] . "'>" . $package['name'] . " - " . $package['price'] . "€</option>";
        }
        ?>
    </select>
    <button type="submit">Buy Package</button>
</form>
