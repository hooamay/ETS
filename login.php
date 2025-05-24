<?php
session_start();
require 'config.php'; // Database connection
require 'mailer.php'; // Email sending function

$error_message = ''; // Initialize an error message variable

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_verified = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $otp = rand(100000, 999999);
        $expiry = time() + 300; // 5 mins expiry
        
        // Store OTP in DB
        $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, otp_code, expiry_time) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $otp, $expiry]);
        
        // Send OTP to email
        sendMail($email, "Your OTP Code", "Your OTP is: $otp");
        
        $_SESSION['email'] = $email;
        header("Location: verify_otp.php");
        exit();
    } else {
        $error_message = "Invalid credentials or email not verified!"; // Set the error message
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
            overflow: hidden;
        }
        .login-container {
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
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        .login-container input {
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
        .login-container input:focus {
            border-color: #3498db;
        }
        .login-container button {
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
        .login-container button:hover {
            background-color: #2980b9;
        }
        .login-container p {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
        .login-container a {
            color: #3498db;
            text-decoration: none;
        }
        .login-container a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red; 
            font-size: 14px;
            margin-bottom: 15px;
            font-weight: bold; 
            text-align: center;
        }
        .admin-link {
            margin-top: -30px;
            text-align: center;
        }
        .admin-link a {
            color: #e74c3c;
            font-size: 16px;
            text-decoration: none;
        }
        .admin-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>

    <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    
    <p>Don't have an account? <a href="register.php">Register here</a></p>

    <!-- Admin Link below Register link -->
    <div class="admin-link">
        <p><a href="admin.php">Go to Admin</a></p>
    </div>
</div>

</body>
</html>
