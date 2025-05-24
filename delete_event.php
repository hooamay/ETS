<?php
session_start();
require 'config.php'; // Database connection

// Check if the user is logged in and the event_id is passed via POST
if (isset($_SESSION['email']) && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];

    // Prepare the SQL query to delete the event
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = :event_id");
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);

    // Execute the query
    if ($stmt->execute()) {
        echo "Event deleted successfully"; // You can return a success message
    } else {
        echo "Error deleting the event";
    }
} else {
    echo "Unauthorized access";
}
?>
