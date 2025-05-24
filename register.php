<?php
require 'config.php';
require 'mailer.php';

$registrationSuccess = ''; // Variable to hold the success message

if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $verification_code = bin2hex(random_bytes(50)); // Generate verification code

    try {
        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $checkStmt->execute([$email]);

        if ($checkStmt->rowCount() > 0) {
            throw new Exception("Email already registered! Try logging in instead.");
        }

        // Insert user into database (inactive by default)
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_code, is_verified) VALUES (?, ?, ?, ?, 0)");
        if (!$stmt->execute([$name, $email, $password, $verification_code])) {
            throw new Exception("Error registering user.");
        }

        $verificationLink = "https://localhost/project/verify.php?verification_code=$verification_code";
        $message = "Click this link to verify your email: <a href='$verificationLink'>$verificationLink</a>";

        // Send verification email
        if (!sendMail($email, "Email Verification", $message)) {
            throw new Exception("Error sending verification email.");
        }

        $registrationSuccess = "Registration successful! Please check your email to verify your account."; // Success message
    } catch (Exception $e) {
        $registrationSuccess = "Error: " . $e->getMessage(); // Error message if something goes wrong
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
        .register-container {
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
        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        .register-container input {
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
        .register-container input:focus {
            border-color: #3498db;
        }
        .register-container button {
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
        .register-container button:hover {
            background-color: #2980b9;
        }
        .register-container p {
            text-align: center;
            margin-top: 20px;
            color: #777;
        }
        .register-container a {
            color: #3498db;
            text-decoration: none;
        }
        .register-container a:hover {
            text-decoration: underline;
        }
        .success-message {
            color: #2ecc71;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }
        .error-message {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 20px;
            text-align: center;
        }

        /* Admin link styling */
        .admin-link {
            margin-top: -30px; /* Move up by 30px */
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

<div class="register-container">
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
    <?php if ($registrationSuccess): ?>
        <div class="<?php echo (strpos($registrationSuccess, 'Error') === false) ? 'success-message' : 'error-message'; ?>">
            <?php echo $registrationSuccess; ?>
        </div>
    <?php endif; ?>
    <p>Already have an account? <a href="login.php">Login here</a></p>

    <!-- Admin Link below Register link, with -30px margin to position correctly -->
    <div class="admin-link">
        <p><a href="admin.php">Go to Admin</a></p>
    </div>
</div>

</body>
</html>
