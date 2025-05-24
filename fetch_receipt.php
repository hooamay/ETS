<?php
// Database connection
require 'config.php';

if (isset($_GET['event_id'])) {
    $event_id = (int)$_GET['event_id'];

    // Fetch event details
    $event_stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = :event_id");
    $event_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
    $event_stmt->execute();
    $event = $event_stmt->fetch();

    if ($event) {
        // Fetch payment history for the event
        $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE event_id = :event_id");
        $payment_stmt->bindParam(':event_id', $event_id, PDO::PARAM_INT);
        $payment_stmt->execute();
        $payments = $payment_stmt->fetchAll();

        // Format payment history
        $payment_history = '';
        foreach ($payments as $payment) {
            $payment_history .= "₱" . number_format($payment['amount_paid'], 2) . " on " . $payment['payment_date'] . "<br>";
        }

        // Prepare response
        $response = [
            'success' => true,
            'event_id' => $event['event_id'],
            'event_name' => $event['event_name'],
            'event_date' => $event['event_date'],
            'event_fee' => "₱" . number_format($event['event_fee'], 2),
            'status' => $event['status'],
        ];
    } else {
        // If event not found
        $response = ['success' => false];
    }

    // Return response as JSON
    echo json_encode($response);
}
?>
