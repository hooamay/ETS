<?php
session_start();
require 'config.php'; // Database connection

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$error_message = ''; // Initialize error message variable

// Set number of records per page
$records_per_page = 7;

// Get the current page from the URL, default to page 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query from the form, if any
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calculate the starting record based on the current page
$start_from = ($current_page - 1) * $records_per_page;

// If there is a search query, modify the SQL to include a WHERE clause
if ($search_query) {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_name LIKE :search_query OR event_date LIKE :search_query LIMIT :start_from, :records_per_page");
    $search_query_param = "%" . $search_query . "%";
    $stmt->bindParam(':search_query', $search_query_param, PDO::PARAM_STR);
} else {
    // No search, just get all records
    $stmt = $pdo->prepare("SELECT * FROM events LIMIT :start_from, :records_per_page");
}

$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$events = $stmt->fetchAll();

// Get the total number of records to calculate the number of pages (considering search query)
if ($search_query) {
    $total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE event_name LIKE :search_query OR event_date LIKE :search_query");
    $total_records_stmt->bindParam(':search_query', $search_query_param, PDO::PARAM_STR);
} else {
    $total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM events");
}
$total_records_stmt->execute();
$total_records = $total_records_stmt->fetchColumn();

// Calculate the total number of pages
$total_pages = ceil($total_records / $records_per_page);

// Function to format the fee as Philippine Peso
function formatPeso($amount) {
    return "₱" . number_format($amount, 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPCOMING EVENTS</title>
    <style>
        body {
            font-family: 'Century Gothic', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            width: 100%;
            padding: 40px;
            box-sizing: border-box;
        }
        .dashboard-container h2 {
            text-align: center;
            color: #333;
            font-size: 30px;
            margin-bottom: 20px;
        }
        .table-container {
            width: 100%;
            margin-top: 20px;
            overflow-x: auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table th, table td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #3498db;
            color: white;
        }
        table td {
            color: #333;
        }
        table tr:hover {
            background-color: #f1f1f1;
        }
        .logout-btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #e74c3c;
            color: white;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        .pagination {
            margin-top: 20px;
            text-align: left;
        }
        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a:hover {
            background-color: #2980b9;
        }
        .search-bar {
            margin-bottom: 20px;
            text-align: left;
            padding-left: 20px;
        }
        .search-bar input {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
            outline: none;
        }
        .search-bar input:focus {
            border-color: #3498db;
        }
        .search-bar button {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #2980b9;
        }

        /* Add Payment Button (Same style as the search button) */
        .add-payment-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .add-payment-btn:hover {
            background-color: #2980b9;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 400px;
            border-radius: 10px;
        }
        .modal-header {
            font-size: 20px;
            margin-bottom: 15px;
            text-align: center;
        }
        .modal-footer {
            text-align: center;
        }
        .modal-button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }
        .confirm-btn {
            background-color: #3498db;
            color: white;
        }
        .cancel-btn:hover, .confirm-btn:hover {
            opacity: 0.8;
        }

        /* Warning message for payment validation */
        .warning-message {
            color: red;
            font-size: 14px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>UPCOMING EVENTS</h2>

    <!-- Search Bar Form -->
    <div class="search-bar">
        <form action="" method="GET" id="search-form">
            <input type="text" name="search" placeholder="Search by event name or date" value="<?php echo htmlspecialchars($search_query); ?>" id="search-input">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Event ID</th>
                    <th>Event Name</th>
                    <th>Event Date</th>
                    <th>Fee</th>
                    <th>Status</th>
                    <th>Your Payment History</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
    <?php foreach ($events as $event): ?>
        <tr>
            <td><?php echo htmlspecialchars($event['event_id']); ?></td>
            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
            <td><?php echo formatPeso($event['event_fee']); ?></td> <!-- Displaying fee as PHP -->
            <td><?php echo $event['status'] == 1 ? 'Paid' : 'Not Paid'; ?></td>
            <td>
                <?php 
                    // Fetch Payment History for this Event
                    $payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE event_id = :event_id");
                    $payment_stmt->bindParam(':event_id', $event['event_id'], PDO::PARAM_INT);
                    $payment_stmt->execute();
                    $payments = $payment_stmt->fetchAll();

                    foreach ($payments as $payment) {
                        echo "" . formatPeso($payment['amount_paid']) . "<br>"; // Formatting payments in PHP
                        echo "" . htmlspecialchars($payment['payment_date']) . "<br>";
                    }
                ?>
            </td>
            <td>
                <?php if ($event['status'] == 0): ?>
                    <button onclick="openPaymentModal(<?php echo $event['event_id']; ?>, <?php echo $event['event_fee']; ?>)" class="add-payment-btn">Add Payment</button>
                <?php else: ?>
                    <button onclick="openReceiptModal(<?php echo $event['event_id']; ?>)" class="add-payment-btn">View Receipt</button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($current_page > 1): ?>
            <a href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Prev</a>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Next</a>
        <?php endif; ?>
    </div>

    <a href="javascript:void(0);" class="logout-btn" onclick="openModal()">Logout</a>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Are you sure you want to logout?</div>
        <div class="modal-footer">
            <button class="modal-button cancel-btn" onclick="closeModal()">Cancel</button>
            <button class="modal-button confirm-btn" onclick="confirmLogout()">Confirm</button>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div id="paymentModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Add Payment Fee</div>
        <!-- Add Payment Modal Form -->
<form action="add_payment.php" method="POST" onsubmit="return validatePayment()">
    <input type="hidden" name="event_id" id="event_id">
    <input type="hidden" name="page" value="<?php echo $current_page; ?>"> <!-- Pass current page -->
    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_query); ?>"> <!-- Pass search query -->
    <label for="amount_paid">Amount Paid:</label>
    <input type="number" name="amount_paid" required id="amount_paid">
    <br><br>
    <label for="payment_date">Payment Date:</label>
    <input type="date" name="payment_date" required id="payment_date" value="<?php echo date('Y-m-d'); ?>">
    <br><br>
    <div id="warning-message" class="warning-message" style="display: none;">
        Payment amount must be exactly equal to the event fee.
    </div>
    <button type="submit" class="modal-button confirm-btn">Confirm Payment</button>
</form>

        <div class="modal-footer">
            <button class="modal-button cancel-btn" onclick="closePaymentModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- View Receipt Modal -->
<div id="receiptModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Receipt for Event</div>
        <div class="modal-body" id="receipt-content">
            <!-- Receipt content will be inserted here -->
        </div>
        <div class="modal-footer">
            <button class="modal-button cancel-btn" onclick="closeReceiptModal()">Close</button>
        </div>
    </div>
</div>

<script>
    var eventFee = 0;

    // Open the add payment modal and set the event fee
    function openPaymentModal(eventId, fee) {
        document.getElementById('event_id').value = eventId;
        eventFee = fee;
        document.getElementById('paymentModal').style.display = 'block';
    }

    // Close the add payment modal
    function closePaymentModal() {
        document.getElementById('paymentModal').style.display = 'none';
    }

    // Open the view receipt modal
    function openReceiptModal(eventId) {
        // Send AJAX request to fetch event details and payment history
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_receipt.php?event_id=' + eventId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    var receiptContent = document.getElementById('receipt-content');
                    receiptContent.innerHTML = `
                        <strong>Event ID:</strong> ${response.event_id}<br>
                        <strong>Event Name:</strong> ${response.event_name}<br>
                        <strong>Event Date:</strong> ${response.event_date}<br>
                        <strong>Fee:</strong> ${response.event_fee}<br>
                        <strong>Status:</strong> ${response.status}<br>
                    `;
                    document.getElementById('receiptModal').style.display = 'block';
                } else {
                    alert('Failed to fetch data');
                }
            }
        };
        xhr.send();
    }

    function closeReceiptModal() {
    // Ensure the receipt modal is hidden when the "Close" button is clicked
    document.getElementById('receiptModal').style.display = 'none';
}

    // Open the logout confirmation modal
    function openModal() {
        document.getElementById('logoutModal').style.display = 'block';
    }

    // Close the logout confirmation modal
    function closeModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Confirm the logout action
    function confirmLogout() {
        window.location.href = 'logout.php'; // Redirect to logout.php where session will be destroyed
    }

    // Validate payment amount
    function validatePayment() {
        var amountPaid = document.getElementById('amount_paid').value;
        var warningMessage = document.getElementById('warning-message');
        
        if (parseFloat(amountPaid) !== eventFee) {
            warningMessage.style.display = 'block';
            return false;
        } else {
            warningMessage.style.display = 'none';
            return true;
        }
    }

    // Close modal if user clicks outside of modal content
   // Close modal if user clicks outside of modal content
window.onclick = function(event) {
    if (event.target == document.getElementById('logoutModal') || 
        event.target == document.getElementById('paymentModal') || 
        event.target == document.getElementById('receiptModal')) {
        closeModal();
        closePaymentModal();
        closeReceiptModal();
    }
}

function closeReceiptModal() {
        document.getElementById('receiptModal').style.display = 'none';
    }

    // Close modal if user clicks outside of modal content
    window.onclick = function(event) {
        if (event.target == document.getElementById('logoutModal') || 
            event.target == document.getElementById('paymentModal') || 
            event.target == document.getElementById('receiptModal')) {
            closeModal();
            closePaymentModal();
            closeReceiptModal();
        }
    }

</script>

</body>
</html>
