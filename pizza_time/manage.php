<?php
require 'includes/session.php';

// Must be logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Get staffID for this user based on email
$sql = "SELECT staffID FROM users WHERE email = :email";
$staffID = pdo($pdo, $sql, ['email' => $_SESSION['email']])->fetchColumn();

if (!$staffID) {
    die("Access denied. Staff ID not found.");
}

// Get position from staff table
$sql = "SELECT position FROM staff WHERE staffID = :staffID";
$position = pdo($pdo, $sql, ['staffID' => $staffID])->fetchColumn();

if ($position !== 'Manager' && $position !== 'Owner') {
    die("Access denied. This page is for Managers and Owners only.");
}

// Initialize variables for salary update
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['targetStaffID'], $_POST['newSalary'])) {
    $targetStaffID = trim($_POST['targetStaffID']);
    $newSalary = trim($_POST['newSalary']);

    // Validate inputs
    if (!is_numeric($targetStaffID) || !is_numeric($newSalary)) {
        $update_message = 'Invalid input. Please enter valid numbers.';
    } else {
        // Update the salary in the database
        try {
            $sql = "UPDATE staff SET salary = :newSalary WHERE staffID = :targetStaffID";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'newSalary' => $newSalary,
                'targetStaffID' => $targetStaffID
            ]);
            $update_message = 'Salary updated successfully.';
        } catch (PDOException $e) {
            $update_message = 'Error updating salary: ' . $e->getMessage();
        }
    }
}

// Initialize variables for adding new staff
$add_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fname'], $_POST['lname'], $_POST['position'])) {
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $position = trim($_POST['position']);
    $salary = isset($_POST['salary']) && is_numeric($_POST['salary']) ? $_POST['salary'] : null;

    // Validate inputs
    if (empty($fname) || empty($lname) || !in_array($position, ['Server', 'Kitchen', 'Manager'])) {
        $add_message = 'Invalid input. Please fill out all required fields correctly.';
    } else {
        // Determine the next available staffID
        try {
            $sql = "SELECT MAX(staffID) AS maxStaffID FROM staff";
            $stmt = $pdo->query($sql);
            $maxStaffID = $stmt->fetchColumn();
            $newStaffID = $maxStaffID ? $maxStaffID + 1 : 1; // Start from 1 if no staff exists

            // Ensure staffID is not 0
            if ($newStaffID === 0) {
                $newStaffID = 1;
            }

            // Insert the new staff member into the database
            $sql = "INSERT INTO staff (staffID, position, fname, lname, salary) VALUES (:staffID, :position, :fname, :lname, :salary)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'staffID' => $newStaffID,
                'position' => $position,
                'fname' => $fname,
                'lname' => $lname,
                'salary' => $salary
            ]);
            $add_message = 'New staff member added successfully.';
        } catch (PDOException $e) {
            $add_message = 'Error adding staff member: ' . $e->getMessage();
        }
    }
}



// Handle clearing orders and food tables
$clear_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clearTables'])) {
    if ($_POST['clearTables'] === 'confirm') {
        try {
            $pdo->exec("TRUNCATE TABLE orders");
            $pdo->exec("TRUNCATE TABLE food");
            $clear_message = 'Orders and food tables have been cleared successfully.';
        } catch (PDOException $e) {
            $clear_message = 'Error clearing tables: ' . $e->getMessage();
        }
    } else {
        $clear_message = 'Click the button again to confirm clearing the tables.';
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manager page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://fonts.gstatic.com">

    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="CSS/style.css" as="style">

    <link rel = "stylesheet" href = "CSS/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom-green w-100">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- change link if you had the chance-->
            <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
                <ul class="navbar-nav menu">
                <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="manage.php">Managment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="order.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="server_lookup.php">Server_lookup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="order_view.php">Order_view</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-bold px-lg-5" href="logout.php">Log_out</a>
                    </li>
                </ul>
            </div>

        </div>
    </nav>
	

<!-- change staff salary -->
    <div class="container mt-5">
        <h2 class="text-center mb-4">Update Staff Salary</h2>

        <?php if ($update_message): ?>
            <div class="alert alert-info text-center"><?= htmlspecialchars($update_message) ?></div>
        <?php endif; ?>

        <form method="POST" action="manage.php" class="mb-4">
            <div class="mb-3">
                <label for="targetStaffID" class="form-label">Staff ID</label>
                <input type="text" name="targetStaffID" id="targetStaffID" class="form-control" placeholder="Enter Staff ID" required>
            </div>

            <div class="mb-3">
                <label for="newSalary" class="form-label">New Salary</label>
                <input type="text" name="newSalary" id="newSalary" class="form-control" placeholder="Enter New Salary" required>
            </div>
            <button type="submit" class="btn btn-dark w-100">Update Salary</button>
        </form>
    </div>


<!-- add in a new staff -->
    <div class="container mt-5">
    <h2 class="text-center mb-4">Add New Staff Member</h2>

    <?php if ($add_message): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($add_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="manage.php" class="mb-4">
        <div class="mb-3">
            <label for="fname" class="form-label">First Name</label>
            <input type="text" name="fname" id="fname" class="form-control" placeholder="Enter First Name" required>
        </div>
        <div class="mb-3">
            <label for="lname" class="form-label">Last Name</label>
            <input type="text" name="lname" id="lname" class="form-control" placeholder="Enter Last Name" required>
        </div>
        <div class="mb-3">
            <label for="position" class="form-label">Position</label>
            <select name="position" id="position" class="form-select" required>
                <option value="Server">Server</option>
                <option value="Kitchen">Kitchen</option>
                <option value="Manager">Manager</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="salary" class="form-label">Salary (Optional)</label>
            <input type="text" name="salary" id="salary" class="form-control" placeholder="Enter Salary">
        </div>
        <button type="submit" class="btn btn-dark w-100">Add Staff Member</button>
    </form>
</div>

<!-- clear all orders from database -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Clear Orders and Food Tables</h2>

    <?php if ($clear_message): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($clear_message) ?></div>
    <?php endif; ?>

    <form method="POST" action="manage.php" class="text-center">
        <input type="hidden" name="clearTables" value="<?= isset($_POST['clearTables']) && $_POST['clearTables'] !== 'confirm' ? 'confirm' : '' ?>">
        <button type="submit" class="btn btn-danger">
            <?= isset($_POST['clearTables']) && $_POST['clearTables'] !== 'confirm' ? 'Confirm Clear Tables' : 'Clear Tables' ?>
        </button>
    </form>
</div>



	<footer class="site-footer bg-dark text-white text-center border-top border-dark py-4 mt-auto">
        &copy; <?= date('Y') ?> Chicken Jockey Pizzaria <br> (Why'd you have to go and make things so complicated?)
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
