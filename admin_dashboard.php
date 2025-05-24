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
$records_per_page = 11;

// Get the current page from the URL, default to page 1
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get the search query from the form, if any
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calculate the starting record based on the current page
$start_from = ($current_page - 1) * $records_per_page;

// If there is a search query, modify the SQL to include a WHERE clause
if ($search_query) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE :search_query OR email LIKE :search_query LIMIT :start_from, :records_per_page");
    $search_query_param = "%" . $search_query . "%";
    $stmt->bindParam(':search_query', $search_query_param, PDO::PARAM_STR);
} else {
    // No search, just get all records
    $stmt = $pdo->prepare("SELECT * FROM users LIMIT :start_from, :records_per_page");
}

$stmt->bindParam(':start_from', $start_from, PDO::PARAM_INT);
$stmt->bindParam(':records_per_page', $records_per_page, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll();

// Get the total number of records to calculate the number of pages (considering search query)
if ($search_query) {
    $total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE name LIKE :search_query OR email LIKE :search_query");
    $total_records_stmt->bindParam(':search_query', $search_query_param, PDO::PARAM_STR);
} else {
    $total_records_stmt = $pdo->prepare("SELECT COUNT(*) FROM users");
}
$total_records_stmt->execute();
$total_records = $total_records_stmt->fetchColumn();

// Calculate the total number of pages
$total_pages = ceil($total_records / $records_per_page);
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
            text-align: left; /* Align buttons to the left */
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
            text-align: left; /* Align the search bar to the left */
            padding-left: 20px; /* Optional: adds some space from the left edge */
        }
        .search-bar input {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            max-width: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
            outline: none; /* Remove the default outline when input is focused */
        }
        .search-bar input:focus {
            border-color: #3498db; /* Add a blue border when focused to highlight the input */
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
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>DATABASE RECORD</h2>

    <!-- Search Bar Form -->
    <div class="search-bar">
        <form action="" method="GET" id="search-form">
            <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search_query); ?>" id="search-input">
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registered On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php 
                                $createdAt = new DateTime($user['created_at']);
                                echo $createdAt->format('F j, Y g:i A');
                            ?>
                        </td>
                        <td><?php echo $user['is_verified'] ? 'Verified' : 'Not Verified'; ?></td>
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

<script>
    // Open the logout confirmation modal
    function openModal() {
        document.getElementById('logoutModal').style.display = 'block';
    }

    // Close the logout confirmation modal
    function closeModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Confirm logout and redirect to logout.php
    function confirmLogout() {
        window.location.href = "logout.php"; // Redirect to logout.php
    }

    // Close modal if user clicks outside of modal content
    window.onclick = function(event) {
        if (event.target == document.getElementById('logoutModal')) {
            closeModal();
        }
    }

    // Reset search field when it's cleared
    document.getElementById('search-input').addEventListener('input', function() {
        if (this.value === '') {
            window.location.href = window.location.pathname; // Redirect to the same page with no search
        }
    });
</script>

</body>
</html>
