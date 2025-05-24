<?php
session_start();
require 'config.php'; // Database connection

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Initialize error and success messages
$error_message = '';
$success_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data
    $event_id = $_POST['event_id'];
    $event_name = trim($_POST['event_name']);
    $event_date = trim($_POST['event_date']);
    $event_fee = trim($_POST['event_fee']);

    // Validation: Make sure all fields are filled
    if (empty($event_name) || empty($event_date) || empty($event_fee)) {
        $error_message = 'All fields are required.';
    } else {
        // Prepare and execute the update query
        $stmt = $pdo->prepare("UPDATE events SET event_name = :event_name, event_date = :event_date, event_fee = :event_fee WHERE event_id = :event_id");
        $stmt->bindParam(':event_name', $event_name);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':event_fee', $event_fee);
        $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);

        // Execute the query and check for success
        if ($stmt->execute()) {
            $success_message = 'Event updated successfully.';
        } else {
            $error_message = 'There was an error updating the event.';
        }
    }
}

// If an event ID is passed in the URL, fetch its data
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];
    
    // Prepare the SELECT query to fetch event details
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->execute();
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no event is found, redirect back to the event list
    if (!$event) {
        header("Location: admin_editevent.php");
        exit();
    }
} else {
    // If no event ID is provided, redirect back to the event list
    header("Location: admin_editevent.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <style>
        body {
            font-family: 'Century Gothic', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 40px;
            box-sizing: border-box;
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            font-size: 18px;
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="date"], input[type="number"] {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        button {
            padding: 12px 20px;
            background-color: #3498db;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
        .error-message {
            color: red;
            text-align: center;
        }
        .success-message {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Event</h2>

    <?php if ($error_message): ?>
        <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
    <?php endif; ?>

    <form action="edit_event.php" method="POST">
        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">

        <label for="event_name">Event Name:</label>
        <input type="text" name="event_name" id="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>

        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" id="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>

        <label for="event_fee">Event Fee:</label>
        <input type="number" name="event_fee" id="event_fee" value="<?php echo htmlspecialchars($event['event_fee']); ?>" required>

        <button type="submit">Update Event</button>
    </form>
</div>

</body>
</html>
