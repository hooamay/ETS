<?php
session_start();
require 'config.php'; // Database connection

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the event details from the form
    $event_name = trim($_POST['event_name']);
    $event_date = trim($_POST['event_date']);
    $event_fee = trim($_POST['event_fee']);

    // Set the status as 'Not Paid' by default
    $status = 'Not Paid';

    // Validate form data
    if (empty($event_name) || empty($event_date) || empty($event_fee)) {
        $error_message = 'Please fill in all fields.';
    } else {
        try {
            // Prepare the SQL query to insert the event into the database
            $stmt = $pdo->prepare("INSERT INTO events (event_name, event_date, event_fee, status) VALUES (:event_name, :event_date, :event_fee, :status)");

            // Bind the parameters
            $stmt->bindParam(':event_name', $event_name);
            $stmt->bindParam(':event_date', $event_date);
            $stmt->bindParam(':event_fee', $event_fee);
            $stmt->bindParam(':status', $status);

            // Execute the query
            $stmt->execute();

            // Redirect to the main page (or the page displaying the events)
            header("Location: admin_editevent.php");
            exit();

        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <style>
        .error-message {
            color: red;
            font-size: 16px;
        }
    </style>
</head>
<body>

<!-- Add Event Form -->
<div class="form-container">
    <h2>Add Event</h2>

    <!-- Display any error messages -->
    <?php if (isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="add_event.php" method="POST">
        <label for="event_name">Event Name:</label>
        <input type="text" name="event_name" required id="event_name">
        <br><br>
        
        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" required id="event_date">
        <br><br>
        
        <label for="event_fee">Event Fee:</label>
        <input type="number" name="event_fee" required id="event_fee">
        <br><br>
        
        <button type="submit">Add Event</button>
    </form>
</div>

</body>
</html>
