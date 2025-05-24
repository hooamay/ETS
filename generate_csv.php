<?php
session_start();
require 'config.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Get the search query from the URL, if any
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Fetch events and their payments
if ($search_query) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_name LIKE :search_query OR event_date LIKE :search_query");
    $search_query_param = "%" . $search_query . "%";
    $stmt->bindParam(':search_query', $search_query_param, PDO::PARAM_STR);
} else {
    // No search, get all events
    $stmt = $pdo->prepare("SELECT * FROM events");
}

$stmt->execute();
$events = $stmt->fetchAll();

// Prepare CSV output
$output = fopen('php://output', 'w');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="events_payment_report.csv"');

// Add CSV headers
fputcsv($output, ['Event ID', 'Event Name', 'Event Date', 'Fee', 'Payment Status', 'Amount Paid', 'Payment Date']);

// Fetch payment details and write them to the CSV
foreach ($events as $event) {
    // Fetch payment history for the current event
    $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE event_id = :event_id");
    $payment_stmt->bindParam(':event_id', $event['event_id'], PDO::PARAM_INT);
    $payment_stmt->execute();
    $payments = $payment_stmt->fetchAll();

    // If there are payments, include them in the CSV
    if ($payments) {
        foreach ($payments as $payment) {
            fputcsv($output, [
                $event['event_id'],
                $event['event_name'],
                $event['event_date'],
                $event['event_fee'],
                'Paid',
                $payment['amount_paid'],
                $payment['payment_date']
            ]);
        }
    } else {
        // If no payments, mark the status as "Not Paid"
        fputcsv($output, [
            $event['event_id'],
            $event['event_name'],
            $event['event_date'],
            $event['event_fee'],
            'Not Paid',
            '',
            ''
        ]);
    }
}

fclose($output);
exit();
?>
