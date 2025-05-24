<?php
session_start();
require 'config.php'; // Include your database connection file

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Check if the request is coming from the correct action (DELETE ALL)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    try {
        // Prepare and execute the SQL query to delete all events
        $stmt = $pdo->prepare("DELETE FROM events");
        $stmt->execute();

        // Redirect back to the dashboard or any other page after deletion
        header("Location: admin_editevent.php?message=All events have been deleted.");
        exit();

    } catch (PDOException $e) {
        // If an error occurs, display an error message
        echo "Error deleting events: " . $e->getMessage();
    }
} else {
    // If the request method isn't GET, redirect to the dashboard
    header("Location: admin_dashboard.php");
    exit();
}
