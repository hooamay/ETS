<?php
session_start();
require 'config.php';

$otpMessage = ''; // Variable to hold the success/error message

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['verify'])) {
    $otp = $_POST['otp'];
    $email = $_SESSION['email'];

    // Get user ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Check OTP
        $stmt = $pdo->prepare("SELECT * FROM otp_codes WHERE user_id = ? AND otp_code = ? AND expiry_time > ?");
        $stmt->execute([$user['id'], $otp, time()]);
        $otpData = $stmt->fetch();

        if ($otpData) {
            // OTP is correct, delete it from DB
            $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
            $stmt->execute([$user['id']]);

            // Set session for logged-in user
            $_SESSION['user_name'] = $email;
            header("Location: user_dashboard.php");
            exit();
        } else {
            $otpMessage = "Invalid or expired OTP!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <style>
        body {
            font-family: 'Century Gothic', sans-serif;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .otp-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
        .otp-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        .otp-container input {
            width: 100%;
            padding: 14px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            transition: border 0.3s ease;
            box-sizing: border-box;
        }
        .otp-container input:focus {
            border-color: #3498db;
        }
        .otp-container button {
            width: 100%;
            padding: 14px;
            background-color: #3498db;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
        }
        .otp-container button:hover {
            background-color: #2980b9;
        }
        .otp-container p {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
        .otp-container a {
            color: #3498db;
            text-decoration: none;
        }
        .otp-container a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="otp-container">
    <h2>Verify OTP</h2>
    <form method="post">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit" name="verify">Verify OTP</button>
    </form>

    <?php if ($otpMessage): ?>
        <div class="error-message">
            <?php echo $otpMessage; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>
