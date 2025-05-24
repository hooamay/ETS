<?php
require 'config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = $_POST['payment_date'];

    // Insert the payment into the payments table
    $stmt = $pdo->prepare("INSERT INTO payments (event_id, amount_paid, payment_date) VALUES (:event_id, :amount_paid, :payment_date)");
    $stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $stmt->bindParam(':amount_paid', $amount_paid, PDO::PARAM_STR);
    $stmt->bindParam(':payment_date', $payment_date, PDO::PARAM_STR);
    $stmt->execute();

    // Update the status of the event to 'Paid' in the events table
    $updateStmt = $pdo->prepare("UPDATE events SET status = 1 WHERE event_id = :event_id");
    $updateStmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $updateStmt->execute();

    // Retrieve the current page and search query from the form submission
    $current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $search_query = isset($_POST['search']) ? $_POST['search'] : '';

    // Redirect back to the same page with the same search and page number
    header("Location: user_dashboard.php?page=$current_page&search=" . urlencode($search_query));
    exit();
}
?>
