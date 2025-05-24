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
    <title>ADMIN</title>
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
            margin-top: 30px;
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

        /* Add Event and Edit Event Buttons */
        .add-edit-btn-container {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 10px;
            margin-left: 10px;
        }
        .add-edit-btn {
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .add-edit-btn:hover {
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

        /* Delete All Button */
        .delete-all-btn {
            padding: 10px 20px;
            background-color: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
        }
        .delete-all-btn:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>ADD, DELETE, & EDIT EVENTS</h2>

    <!-- Add Event, Delete All Events, and Go to Database Buttons -->
    <div class="add-edit-btn-container">
        <button class="add-edit-btn" onclick="openAddEventModal()">Add Event</button>
        <button class="delete-all-btn" onclick="openDeleteAllModal()">Delete All Events</button> <!-- Delete All Events Button -->
        <button class="add-edit-btn" onclick="window.location.href='admin_dashboard.php'">Go to Database</button> <!-- Go to Database Button -->
    </div>

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
                    <th></th> <!-- New column for the Edit button -->
                </tr>
            </thead>
            <tbody>
    <?php foreach ($events as $event): ?>
        <tr>
            <td><?php echo htmlspecialchars($event['event_id']); ?></td>
            <td><?php echo htmlspecialchars($event['event_name']); ?></td>
            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
            <td><?php echo formatPeso($event['event_fee']); ?></td> <!-- Displaying fee as PHP -->
            <td>
                <button class="add-edit-btn" onclick="openEditEventModal(<?php echo $event['event_id']; ?>)">Edit</button>
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

    <a href="javascript:void(0);" class="logout-btn" onclick="openLogoutModal()">Logout</a>
</div>

<!-- Add Event Modal -->
<div id="addEventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Add Event</div>
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
            <button type="submit" class="modal-button confirm-btn">Add Event</button>
        </form>
        <div class="modal-footer">
            <button class="modal-button cancel-btn" onclick="closeAddEventModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Edit Event Modal -->
<div id="editEventModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Edit Event</div>
        <form action="edit_event.php" method="POST">
            <input type="hidden" name="event_id" id="edit_event_id">
            <label for="edit_event_name">Event Name:</label>
            <input type="text" name="event_name" id="edit_event_name" required>
            <br><br>
            <label for="edit_event_date">Event Date:</label>
            <input type="date" name="event_date" id="edit_event_date" required>
            <br><br>
            <label for="edit_event_fee">Event Fee:</label>
            <input type="number" name="event_fee" id="edit_event_fee" required>
            <br><br>
            <button type="submit" class="modal-button confirm-btn">Update Event</button>
        </form>
        <div class="modal-footer">
            <button class="modal-button cancel-btn" onclick="closeEditEventModal()">Cancel</button>
        </div>
    </div>
</div>

<!-- Logout Confirmation Modal -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Are you sure you want to logout?</div>
        <div class="modal-footer">
            <button class="modal-button confirm-btn" onclick="confirmLogout()">Yes</button>
            <button class="modal-button cancel-btn" onclick="closeLogoutModal()">No</button>
        </div>
    </div>
</div>

<!-- Delete All Confirmation Modal -->
<div id="deleteAllModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Are you sure you want to delete all events?</div>
        <div class="modal-footer">
            <button class="modal-button confirm-btn" onclick="confirmDeleteAll()">Yes</button>
            <button class="modal-button cancel-btn" onclick="closeDeleteAllModal()">No</button>
        </div>
    </div>
</div>

<script>
    // Open Add Event Modal
    function openAddEventModal() {
        document.getElementById('addEventModal').style.display = 'block';
    }

    // Close Add Event Modal
    function closeAddEventModal() {
        document.getElementById('addEventModal').style.display = 'none';
    }

    // Open Edit Event Modal
    function openEditEventModal(eventId) {
        // Fetch event data from the server or local data (to be implemented)
        const event = {
            event_id: eventId,
            event_name: 'Sample Event',
            event_date: '2025-03-10',
            event_fee: 1000
        };

        document.getElementById('edit_event_id').value = event.event_id;
        document.getElementById('edit_event_name').value = event.event_name;
        document.getElementById('edit_event_date').value = event.event_date;
        document.getElementById('edit_event_fee').value = event.event_fee;
        document.getElementById('editEventModal').style.display = 'block';
    }

    // Close Edit Event Modal
    function closeEditEventModal() {
        document.getElementById('editEventModal').style.display = 'none';
    }

    // Open Logout Modal
    function openLogoutModal() {
        document.getElementById('logoutModal').style.display = 'block';
    }

    // Close Logout Modal
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Confirm Logout and Redirect
    function confirmLogout() {
        window.location.href = 'logout.php';
    }

    // Open Delete All Modal
    function openDeleteAllModal() {
        document.getElementById('deleteAllModal').style.display = 'block';
    }

    // Close Delete All Modal
    function closeDeleteAllModal() {
        document.getElementById('deleteAllModal').style.display = 'none';
    }

    // Confirm Delete All Events
    function confirmDeleteAll() {
        window.location.href = 'delete_all_events.php';  // Implement this script for deletion logic
    }
</script>

</body>
</html>
