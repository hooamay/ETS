<?php
require 'config.php';

$verificationMessage = ''; // Variable to hold the success/error message

if (isset($_GET['verification_code'])) {
    $verification_code = $_GET['verification_code'];

    // Check if the verification_code exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE verification_code = ? AND is_verified = 0");
    $stmt->execute([$verification_code]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify the user
        $updateStmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
        $updateStmt->execute([$user['id']]);

        $verificationMessage = "Your email has been successfully verified! You can now <a href='login.php'>login</a>.";
    } else {
        $verificationMessage = "Invalid or expired verification code!";
    }
} else {
    $verificationMessage = "No verification code provided!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        /* Base Styling */
        body {
            font-family: 'Century Gothic', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            color: #fff;
        }

        /* Centered Container */
        .verify-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0px 4px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        /* Heading */
        .verify-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
            font-weight: bold;
            color: #333;
        }

        /* Success and Error Message Styling */
        .success-message {
            color: #2ecc71;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        .error-message {
            color: #e74c3c;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }

        /* Styling the verification link */
        .verify-container a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .verify-container a:hover {
            color: #2980b9;
        }

        /* Animation for FadeIn */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(-50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Mobile Responsiveness */
        @media screen and (max-width: 600px) {
            .verify-container {
                padding: 30px;
                max-width: 90%;
            }

            .verify-container h2 {
                font-size: 24px;
            }

            .success-message, .error-message {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="verify-container">
    <h2>Email Verification</h2>

    <!-- Display success or error message based on the verification result -->
    <?php if ($verificationMessage): ?>
        <div class="<?php echo (strpos($verificationMessage, 'Invalid') === false) ? 'success-message' : 'error-message'; ?>">
            <?php echo $verificationMessage; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
