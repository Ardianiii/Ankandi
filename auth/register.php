<?php
include '../includes/db.php';
require '../vendor/autoload.php'; // This loads PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $verification_code = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, verification_code) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $password, $verification_code])) {
        
        // Send verification email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // For example if using Gmail SMTP
            $mail->SMTPAuth = true;
            $mail->Username = 'email'; // YOUR GMAIL EMAIL
            $mail->Password = 'your pasword here'; // YOUR GMAIL PASSWORD or App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('your-email@gmail.com', 'Ankandi');
            $mail->addAddress($email, $username);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->Body    = "Please click the link below to verify your email:<br><br>
                <a href='http://localhost/Ankandi/auth/verify.php?code=$verification_code'>Verify Email</a>";

            $mail->send();
            echo 'Registration successful! Please check your email to verify.';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        echo "Something went wrong. Please try again.";
    }
}
?>

<!-- HTML Registration Form -->
<form method="POST" action="">
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Register</button>
</form>
